<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\PengeluaranTrucking;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StorePengeluaranTruckingRequest;
use App\Http\Requests\UpdatePengeluaranTruckingRequest;
use App\Http\Requests\DestroyPengeluaranTruckingRequest;

class PengeluaranTruckingController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        return response([
            'data' => $pengeluaranTrucking->get(),
            'acos' => $pengeluaranTrucking->acos(),
            'attributes' => [
                'totalRows' => $pengeluaranTrucking->totalRows,
                'totalPages' => $pengeluaranTrucking->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        return response([
            'status' => true,
            'data' => $pengeluaranTrucking->default()
        ]);
    }

    public function cekValidasi($id)
    {
        $pengeluaranTrucking = new PengeluaranTrucking();

        $dataMaster = $pengeluaranTrucking->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $cekdata = $pengeluaranTrucking->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {

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
                    (new MyModel())->updateEditingBy('pengeluarantrucking', $id, $aksi);
                }

                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

                $data = [
                    'status' => true,
                    'message' => ["keterangan" => $keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('pengeluarantrucking', $id, $aksi);

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranTruckingRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepengeluaran' => $request->kodepengeluaran,
                'keterangan' => $request->keterangan ?? '',
                'coadebet' => $request->coadebet ?? '',
                'coakredit' => $request->coakredit ?? '',
                'coapostingdebet' => $request->coapostingdebet ?? '',
                'coapostingkredit' => $request->coapostingkredit ?? '',
                'statusaktif' => $request->statusaktif,
                'format' => $request->format,
                'tas_id' => $request->tas_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',

            ];
            $pengeluaranTrucking = (new PengeluaranTrucking())->processStore($data);
            if ($request->from == '') {
                $pengeluaranTrucking->position = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable())->position;
                if ($request->limit == 0) {
                    $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / (10));
                } else {
                    $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $pengeluaranTrucking->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('pengeluarantrucking', 'add', $data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranTrucking
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        return response([
            'status' => true,
            'data' => $pengeluaranTrucking->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengeluaranTruckingRequest $request, PengeluaranTrucking $pengeluaranTrucking): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodepengeluaran' => $request->kodepengeluaran,
                'keterangan' => $request->keterangan ?? '',
                'coadebet' => $request->coadebet ?? '',
                'coakredit' => $request->coakredit ?? '',
                'coapostingdebet' => $request->coapostingdebet ?? '',
                'coapostingkredit' => $request->coapostingkredit ?? '',
                'statusaktif' => $request->statusaktif,
                'format' => $request->format,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',

            ];

            $pengeluaranTrucking = (new PengeluaranTrucking())->processUpdate($pengeluaranTrucking, $data);
            if ($request->from == '') {
                $pengeluaranTrucking->position = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable())->position;
                if ($request->limit == 0) {
                    $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / (10));
                } else {
                    $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $pengeluaranTrucking->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('pengeluarantrucking', 'edit', $data);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $pengeluaranTrucking
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
    public function destroy(DestroyPengeluaranTruckingRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranTrucking = (new PengeluaranTrucking())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable(), true);
            $pengeluaranTrucking->position = $selected->position;
            $pengeluaranTrucking->id = $selected->id;
            if ($request->limit == 0) {
                $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / (10));
            } else {
                $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $pengeluaranTruckings = $decodedResponse['data'];

            $judulLaporan = $pengeluaranTruckings[0]['judulLaporan'];

            $i = 0;
            foreach ($pengeluaranTruckings as $index => $params) {

                $statusaktif = $params['format'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['SINGKATAN'];


                $pengeluaranTruckings[$i]['format'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Pengeluaran',
                    'index' => 'kodepengeluaran',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'COA Debet',
                    'index' => 'coadebet_keterangan',
                ],
                [
                    'label' => 'COA Kredit',
                    'index' => 'coakredit_keterangan',
                ],
                [
                    'label' => 'COA Posting Debet',
                    'index' => 'coapostingdebet_keterangan',
                ],
                [
                    'label' => 'COA Posting Kredit',
                    'index' => 'coapostingkredit_keterangan',
                ],
                [
                    'label' => 'Format Bukti',
                    'index' => 'format',
                ],
            ];

            $this->toExcel($judulLaporan, $pengeluaranTruckings, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluarantrucking')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getid($id, $request, $del)
    {
        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->nullable();
            $table->string('kodepengeluaran', 300)->nullable();
            $table->string('keterangan', 300)->nullable();
            $table->string('coadebet', 300)->nullable();
            $table->string('coakredit', 300)->nullable();
            $table->string('coapostingdebet', 300)->nullable();
            $table->string('coapostingkredit', 300)->nullable();

            $table->string('format', 300)->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = PengeluaranTrucking::select(
                'pengeluarantrucking.id as id_',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
                'pengeluarantrucking.coadebet',
                'pengeluarantrucking.coakredit',
                'pengeluarantrucking.coapostingdebet',
                'pengeluarantrucking.coapostingkredit',
                'pengeluarantrucking.format',
                'pengeluarantrucking.modifiedby',
                'pengeluarantrucking.created_at',
                'pengeluarantrucking.updated_at'
            )
                ->orderBy('pengeluarantrucking.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = PengeluaranTrucking::select(
                    'pengeluarantrucking.id as id_',
                    'pengeluarantrucking.kodepengeluaran',
                    'pengeluarantrucking.keterangan',
                    'pengeluarantrucking.coadebet',
                    'pengeluarantrucking.coakredit',
                    'pengeluarantrucking.coapostingdebet',
                    'pengeluarantrucking.coapostingkredit',
                    'pengeluarantrucking.format',
                    'pengeluarantrucking.modifiedby',
                    'pengeluarantrucking.created_at',
                    'pengeluarantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('pengeluarantrucking.id', $params['sortorder']);
            } else {
                $query = PengeluaranTrucking::select(
                    'pengeluarantrucking.id as id_',
                    'pengeluarantrucking.kodepengeluaran',
                    'pengeluarantrucking.keterangan',
                    'pengeluarantrucking.coadebet',
                    'pengeluarantrucking.coakredit',
                    'pengeluarantrucking.coapostingdebet',
                    'pengeluarantrucking.coapostingkredit',
                    'pengeluarantrucking.format',
                    'pengeluarantrucking.modifiedby',
                    'pengeluarantrucking.created_at',
                    'pengeluarantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('pengeluarantrucking.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing([
            'id_',
            'kodepengeluaran',
            'keterangan',
            'coadebet',
            'coakredit',
            'coapostingdebet',
            'coapostingkredit',
            'format',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $query);


        if ($del == 1) {
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
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
            (new PengeluaranTrucking())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
