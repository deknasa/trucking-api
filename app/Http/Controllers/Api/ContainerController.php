<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Container;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreContainerRequest;
use App\Http\Requests\UpdateContainerRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyContainerRequest;
use App\Http\Requests\RangeExportReportRequest;

class ContainerController extends Controller
{


    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $container = new Container();

        return response([
            'data' => $container->get(),
            'attributes' => [
                'totalRows' => $container->totalRows,
                'totalPages' => $container->totalPages
            ]
        ]);
    }

    public function cekValidasi(Request $request, $id)
    {
        $container = new Container();
        $dataMaster = $container->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $server = '';
        if ($request->from == 'tas') {
            $id = db::table('container')->from(db::raw("container a with (readuncommitted)"))
                ->select('a.id')
                ->where('a.tas_id', $id)->first()->id ?? 0;
            $server = ' tnl';
        }
        $cekdata = $container->cekvalidasihapus($id);
        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
        if( $aksi == 'edit'){
            $cekdata['kondisi'] = false;
        }
        if ($cekdata['kondisi'] == true) {
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $server = ' tas';
            }
            goto selesai;
        }

        $data['tas_id'] = $id;
        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            $data = [
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $cektnl = $this->CekValidasiToTnl("container/" . $id . "/cekValidasi", $data);
            return response($cektnl['data']);
        }

        selesai:
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . "$server)' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('container', $id, $aksi);
                }
                
                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                ];
                
                // return response($data);
            } else {
                
                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->kodecontainer . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                ];
                
                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('container', $id, $aksi);

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function default()
    {

        $container = new Container();
        return response([
            'status' => true,
            'data' => $container->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan) ?? '',
                'nominalsumbangan' => $request->nominalsumbangan,
                'statusaktif' => $request->statusaktif,
                'tas_id' => $request->tas_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            // $container = (new container())->processStore($data);
            $container = new Container();
            $container->processStore($data, $container);            

            if ($request->from == '') {
                $container->position = $this->getPosition($container, $container->getTable())->position;
                if ($request->limit == 0) {
                    $container->page = ceil($container->position / (10));
                } else {
                    $container->page = ceil($container->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $container->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('container', 'add', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $container
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $container = new Container();
        return response([
            'status' => true,
            'data' => $container->findAll($id)
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateContainerRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodecontainer' => strtoupper($request->kodecontainer),
                'keterangan' => strtoupper($request->keterangan) ?? '',
                'nominalsumbangan' => $request->nominalsumbangan,
                'statusaktif' => $request->statusaktif,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            // $container = (new Container())->processUpdate($container, $data);
            $container = new Container();
            $containers = $container->findOrFail($id);
            $container = $container->processUpdate($containers, $data);
            if ($request->from == '') {
                $container->position = $this->getPosition($container, $container->getTable())->position;
                if ($request->limit == 0) {
                    $container->page = ceil($container->position / (10));
                } else {
                    $container->page = ceil($container->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $container->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('container', 'edit', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // $container = (new container())->processDestroy($id);
            $container = new Container();
            $containers = $container->findOrFail($id);
            $container = $container->processDestroy($containers);            
            if ($request->from == '') {
                $selected = $this->getPosition($container, $container->getTable(), true);
                $container->position = $selected->position;
                $container->id = $selected->id;
                if ($request->limit == 0) {
                    $container->page = ceil($container->position / (10));
                } else {
                    $container->page = ceil($container->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('container', 'delete', $data);
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $container
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('container')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combostatus(Request $request)
    {
        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        } else {
            header('Access-Control-Allow-Origin: *');
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $containers = $decodedResponse['data'];

            $judulLaporan = $containers[0]['judulLaporan'];
            $i = 0;
            foreach ($containers as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $containers[$i]['statusaktif'] = $statusaktif;

                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Container',
                    'index' => 'kodecontainer',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Nominal Sumbangan',
                    'index' => 'nominalsumbangan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $containers, $columns);
        }
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Container())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Container())->processApprovalaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $container = Container::find($data['Id'][$i]);

            $container->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($container->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($container->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF CONTAINER',
                    'idtrans' => $container->id,
                    'nobuktitrans' => $container->id,
                    'aksi' => $aksi,
                    'datajson' => $container->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $container;
    }
}
