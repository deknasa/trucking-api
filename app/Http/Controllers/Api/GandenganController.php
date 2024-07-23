<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Stok;
use App\Models\Error;
use App\Models\MyModel;

use App\Models\LogTrail;

use App\Models\Gandengan;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreGandenganRequest;
use App\Http\Requests\UpdateGandenganRequest;
use App\Http\Requests\DestroyGandenganRequest;
use App\Http\Requests\ApprovalGandenganRequest;
use App\Http\Requests\RangeExportReportRequest;

class GandenganController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $gandengan = new gandengan();
        return response([
            'data' => $gandengan->get(),
            'attributes' => [
                'totalRows' => $gandengan->totalRows,
                'totalPages' => $gandengan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }
    public function cekValidasi($id)
    {
        $gandengan = new Gandengan();
        $cekdata = $gandengan->cekvalidasihapus($id);
        $dataMaster = $gandengan->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        if( $aksi == 'EDIT'){
            $cekdata['kondisi'] = false;
        }
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
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
                    (new MyModel())->updateEditingBy('gandengan', $id, $aksi);
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
            (new MyModel())->updateEditingBy('gandengan', $id, $aksi);
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

        $gandengan = new Gandengan();
        return response([
            'status' => true,
            'data' => $gandengan->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreGandenganRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodegandengan' => $request->kodegandengan,
                'keterangan' => $request->keterangan ?? '',
                'trado_id' => $request->trado_id,
                'container_id' => $request->container_id,
                'jumlahroda' => $request->jumlahroda,
                'jumlahbanserap' => $request->jumlahbanserap,
                'statusaktif' => $request->statusaktif,
                'tas_id' => $request->tas_id ?? '',
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            // $gandengan = (new Gandengan())->processStore($data);
            $gandengan = new Gandengan();
            $gandengan->processStore($data, $gandengan);            
            if ($request->from == '') {
                $selected = $this->getPosition($gandengan, $gandengan->getTable());
                $gandengan->position = $selected->position;
                if ($request->limit == 0) {
                    $gandengan->page = ceil($gandengan->position / (10));
                } else {
                    $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $gandengan->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('gandengan', 'add', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gandengan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gandengan  $gandengan
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $gandengan = new Gandengan();
        return response([
            'status' => true,
            'data' => $gandengan->findAll($id)
        ]);
    }



    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateGandenganRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodegandengan' => $request->kodegandengan,
                'keterangan' => $request->keterangan ?? '',
                'trado_id' => $request->trado_id,
                'container_id' => $request->container_id,
                'jumlahroda' => $request->jumlahroda,
                'jumlahbanserap' => $request->jumlahbanserap,
                'statusaktif' => $request->statusaktif,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            
            // $gandengan = (new Gandengan())->processUpdate($gandengan, $data);
            $gandengan = new Gandengan();
            $gandengans = $gandengan->findOrFail($id);
            $gandengan = $gandengan->processUpdate($gandengans, $data);            
            if ($request->from == '') {
                $gandengan->position = $this->getPosition($gandengan, $gandengan->getTable())->position;
                if ($request->limit == 0) {
                    $gandengan->page = ceil($gandengan->position / (10));
                } else {
                    $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $gandengan->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('gandengan', 'edit', $data);
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $gandengan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyGandenganRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            // $gandengan = (new Gandengan())->processDestroy($id);
            $gandengan = new Gandengan();
            $gandengans = $gandengan->findOrFail($id);
            $gandengan = $gandengan->processDestroy($gandengans);

            if ($request->from == '') {
                $selected = $this->getPosition($gandengan, $gandengan->getTable(), true);
                $gandengan->position = $selected->position;
                $gandengan->id = $selected->id;
                if ($request->limit == 0) {
                    $gandengan->page = ceil($gandengan->position / (10));
                } else {
                    $gandengan->page = ceil($gandengan->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('gandengan', 'delete', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gandengan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $gandengans = $decodedResponse['data'];

            $judulLaporan = $gandengans[0]['judulLaporan'];

            $i = 0;
            foreach ($gandengans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $gandengans[$i]['statusaktif'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'GANDENGAN',
                    'index' => 'kodegandengan',
                ],
                [
                    'label' => 'NAMA GANDENGAN',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'NO POLISI',
                    'index' => 'trado',
                ],
                [
                    'label' => 'JUMLAH BAN',
                    'index' => 'jumlahroda',
                ],
                [
                    'label' => 'JUMLAH BAN SERAP',
                    'index' => 'jumlahbanserap',
                ],

                [
                    'label' => 'STATUS AKTIF',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $gandengans, $columns);
        }
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalGandenganRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Gandengan())->processApprovalnonaktif($data);

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
     * @Keterangan APRROVAL AKTIF
     */
    public function approvalaktif(ApprovalGandenganRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Gandengan())->processApprovalaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gandengan')->getColumns();

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
}
