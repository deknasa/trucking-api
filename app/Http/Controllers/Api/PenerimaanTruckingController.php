<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTrucking;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanTruckingRequest;
use App\Http\Requests\UpdatePenerimaanTruckingRequest;
use App\Http\Requests\DestroyPenerimaanTruckingRequest;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanTruckingController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $penerimaanTrucking = new PenerimaanTrucking();
        return response([
            'data' => $penerimaanTrucking->get(),
            'acos' => $penerimaanTrucking->acos(),
            'attributes' => [
                'totalRows' => $penerimaanTrucking->totalRows,
                'totalPages' => $penerimaanTrucking->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $penerimaanTrucking = new PenerimaanTrucking();
        $cekdata = $penerimaanTrucking->cekvalidasihapus($id);
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
        } else {
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
    public function store(StorePenerimaanTruckingRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepenerimaan' => $request->kodepenerimaan,
                'keterangan' => $request->keterangan ?? '',
                'coadebet' => $request->coadebet ?? '',
                'coakredit' => $request->coakredit ?? '',
                'coapostingdebet' => $request->coapostingdebet ?? '',
                'coapostingkredit' => $request->coapostingkredit ?? '',
                'statusaktif' => $request->statusaktif ?? 1,
                'format' => $request->format
            ];
            $penerimaanTrucking = (new PenerimaanTrucking())->processStore($data);
            $penerimaanTrucking->position = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable())->position;
            if ($request->limit==0) {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / (10));
            } else {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTrucking
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $penerimaanTrucking = new PenerimaanTrucking();
        return response([
            'status' => true,
            'data' => $penerimaanTrucking->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePenerimaanTruckingRequest $request, PenerimaanTrucking $penerimaanTrucking): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodepenerimaan' => $request->kodepenerimaan,
                'keterangan' => $request->keterangan ?? '',
                'coadebet' => $request->coadebet ?? '',
                'coakredit' => $request->coakredit ?? '',
                'coapostingdebet' => $request->coapostingdebet ?? '',
                'coapostingkredit' => $request->coapostingkredit ?? '',
                'statusaktif' => $request->statusaktif ?? 1,
                'format' => $request->format
            ];

            $penerimaanTrucking = (new PenerimaanTrucking())->processUpdate($penerimaanTrucking, $data);
            $penerimaanTrucking->position = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable())->position;
            if ($request->limit==0) {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / (10));
            } else {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $penerimaanTrucking
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
    public function destroy(DestroyPenerimaanTruckingRequest $request, $id)
    {

        DB::beginTransaction();

        try {
            $penerimaanTrucking = (new PenerimaanTrucking())->processDestroy($id);
            $selected = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable(), true);
            $penerimaanTrucking->position = $selected->position;
            $penerimaanTrucking->id = $selected->id;
            if ($request->limit==0) {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / (10));
            } else {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanTrucking
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
            $penerimaanTruckings = $decodedResponse['data'];

            $judulLaporan = $penerimaanTruckings[0]['judulLaporan'];

            $i = 0;
            foreach ($penerimaanTruckings as $index => $params) {

                $statusaktif = $params['format'];

                $result = json_decode($statusaktif, true);
                $statusaktif = $result['SINGKATAN'];


                $penerimaanTruckings[$i]['format'] = $statusaktif;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Penerimaan',
                    'index' => 'kodepenerimaan',
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

            $this->toExcel($judulLaporan, $penerimaanTruckings, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaantrucking')->getColumns();

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
            $table->string('kodepenerimaan', 300)->nullable();
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
            $query = PenerimaanTrucking::select(
                'penerimaantrucking.id as id_',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
                'penerimaantrucking.coadebet',
                'penerimaantrucking.coakredit',
                'penerimaantrucking.coapostingdebet',
                'penerimaantrucking.coapostingkredit',
                'penerimaantrucking.format',
                'penerimaantrucking.modifiedby',
                'penerimaantrucking.created_at',
                'penerimaantrucking.updated_at'
            )
                ->orderBy('penerimaantrucking.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = PenerimaanTrucking::select(
                    'penerimaantrucking.id as id_',
                    'penerimaantrucking.kodepenerimaan',
                    'penerimaantrucking.keterangan',
                    'penerimaantrucking.coadebet',
                    'penerimaantrucking.coakredit',
                    'penerimaantrucking.coapostingdebet',
                    'penerimaantrucking.coapostingkredit',
                    'penerimaantrucking.format',
                    'penerimaantrucking.modifiedby',
                    'penerimaantrucking.created_at',
                    'penerimaantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('penerimaantrucking.id', $params['sortorder']);
            } else {
                $query = PenerimaanTrucking::select(
                    'penerimaantrucking.id as id_',
                    'penerimaantrucking.kodepenerimaan',
                    'penerimaantrucking.keterangan',
                    'penerimaantrucking.coadebet',
                    'penerimaantrucking.coakredit',
                    'penerimaantrucking.coapostingdebet',
                    'penerimaantrucking.coapostingkredit',
                    'penerimaantrucking.format',
                    'penerimaantrucking.modifiedby',
                    'penerimaantrucking.created_at',
                    'penerimaantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('penerimaantrucking.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing([
            'id_',
            'kodepenerimaan',
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
}
