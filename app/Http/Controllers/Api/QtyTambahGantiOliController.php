<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;


use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

use App\Http\Requests\StoreLogTrailRequest;
use App\Models\QtyTambahGantiOli;
use App\Http\Requests\StoreQtyTambahGantiOliRequest;
use App\Http\Requests\UpdateQtyTambahGantiOliRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\DestroyQtyTambahGantiOliRequest;
use App\Http\Requests\RangeExportReportRequest;

class QtyTambahGantiOliController extends Controller
{
     /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $qtytambahgantioli = new QtyTambahGantiOli();

        return response([
            'data' => $qtytambahgantioli->get(),
            'attributes' => [
                'totalRows' => $qtytambahgantioli->totalRows,
                'totalPages' => $qtytambahgantioli->totalPages
            ]
        ]);
    }

    public function cekValidasi(Request $request, $id)
    {
        $qtytambahgantioli = new QtyTambahGantiOli();
        $dataMaster = $qtytambahgantioli->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $server = '';
        if ($request->from == 'tas') {
            $id = db::table('qtytambahgantioli')->from(db::raw("qtytambahgantioli a with (readuncommitted)"))
                ->select('a.id')
                ->where('a.tas_id', $id)->first()->id ?? 0;
            $server = ' tnl';
        }
        $cekdata = $qtytambahgantioli->cekvalidasihapus($id);
        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();

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
            $cektnl = $this->CekValidasiToTnl("qtytambahgantioli/" . $id . "/cekValidasi", $data);
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
                    (new MyModel())->updateEditingBy('qtytambahgantioli', $id, $aksi);
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
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                ];
                
                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('qtytambahgantioli', $id, $aksi);

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

        $qtytambahgantioli = new QtyTambahGantiOli();
        return response([
            'status' => true,
            'data' => $qtytambahgantioli->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreQtyTambahGantiOliRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'keterangan' => strtoupper($request->keterangan) ?? '',
                'qty' => $request->qty,
                'statusaktif' => $request->statusaktif,
                'statusoli' => $request->statusoli,
                'statusservicerutin' => $request->statusservicerutin,
                'tas_id' => $request->tas_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            $qtytambahgantioli = (new QtyTambahGantiOli())->processStore($data);

            if ($request->from == '') {
                $qtytambahgantioli->position = $this->getPosition($qtytambahgantioli, $qtytambahgantioli->getTable())->position;
                if ($request->limit == 0) {
                    $qtytambahgantioli->page = ceil($qtytambahgantioli->position / (10));
                } else {
                    $qtytambahgantioli->page = ceil($qtytambahgantioli->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $qtytambahgantioli->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('qtytambahgantioli', 'add', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $qtytambahgantioli
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show(QtyTambahGantiOli $qtytambahgantioli)
    {
        return response([
            'status' => true,
            'data' => $qtytambahgantioli
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateQtyTambahGantiOliRequest $request, QtyTambahGantiOli $qtytambahgantioli)
    {
        DB::beginTransaction();
        try {
            $data = [
                'keterangan' => strtoupper($request->keterangan) ?? '',
                'qty' => $request->qty,
                'statusaktif' => $request->statusaktif,
                'statusoli' => $request->statusoli,
                'statusservicerutin' => $request->statusservicerutin,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            $qtytambahgantioli = (new QtyTambahGantiOli())->processUpdate($qtytambahgantioli, $data);
            if ($request->from == '') {
                $qtytambahgantioli->position = $this->getPosition($qtytambahgantioli, $qtytambahgantioli->getTable())->position;
                if ($request->limit == 0) {
                    $qtytambahgantioli->page = ceil($qtytambahgantioli->position / (10));
                } else {
                    $qtytambahgantioli->page = ceil($qtytambahgantioli->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $qtytambahgantioli->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('qtytambahgantioli', 'edit', $data);
            }
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $qtytambahgantioli
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
            $qtytambahgantioli = (new QtyTambahGantiOli())->processDestroy($id);
            if ($request->from == '') {
                $selected = $this->getPosition($qtytambahgantioli, $qtytambahgantioli->getTable(), true);
                $qtytambahgantioli->position = $selected->position;
                $qtytambahgantioli->id = $selected->id;
                if ($request->limit == 0) {
                    $qtytambahgantioli->page = ceil($qtytambahgantioli->position / (10));
                } else {
                    $qtytambahgantioli->page = ceil($qtytambahgantioli->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('qtytambahgantioli', 'delete', $data);
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $qtytambahgantioli
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('qtytambahgantioli')->getColumns();

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
            $qtytambahgantiolis = $decodedResponse['data'];

            $judulLaporan = $qtytambahgantiolis[0]['judulLaporan'];
            $i = 0;
            foreach ($qtytambahgantiolis as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $result = json_decode($statusaktif, true);
                $statusaktif = $result['MEMO'];
                $qtytambahgantiolis[$i]['statusaktif'] = $statusaktif;

                $statusoli = $params['statusoli'];
                $result = json_decode($statusoli, true);
                $statusoli = $result['MEMO'];
                $qtytambahgantiolis[$i]['statusoli'] = $statusoli;

                $statusservicerutin = $params['statusservicerutin'];
                $result = json_decode($statusservicerutin, true);
                $statusservicerutin = $result['MEMO'];
                $qtytambahgantiservicerutins[$i]['statusservicerutin'] = $statusservicerutin;

                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Qty',
                    'index' => 'qty',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Status Oli',
                    'index' => 'statusoli',
                ],
                [
                    'label' => 'Status Service Rutin',
                    'index' => 'statusservicerutin',
                ],
            ];

            $this->toExcel($judulLaporan, $qtytambahgantiolis, $columns);
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
            (new QtyTambahGantiOli())->processApprovalnonaktif($data);

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
            $qtytambahgantioli = QtyTambahGantiOli::find($data['Id'][$i]);

            $qtytambahgantioli->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($qtytambahgantioli->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($qtytambahgantioli->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF QTY TAMBAH GANTI OLI',
                    'idtrans' => $qtytambahgantioli->id,
                    'nobuktitrans' => $qtytambahgantioli->id,
                    'aksi' => $aksi,
                    'datajson' => $qtytambahgantioli->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $qtytambahgantioli;
    }
}
