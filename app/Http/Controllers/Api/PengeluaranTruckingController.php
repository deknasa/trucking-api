<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTrucking;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranTruckingRequest;
use App\Http\Requests\UpdatePengeluaranTruckingRequest;
use App\Http\Requests\DestroyPengeluaranTruckingRequest;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranTruckingController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        return response([
            'data' => $pengeluaranTrucking->get(),
            'attributes' => [
                'totalRows' => $pengeluaranTrucking->totalRows,
                'totalPages' => $pengeluaranTrucking->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        $cekdata = $pengeluaranTrucking->cekvalidasihapus($id);
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
     */
    public function store(StorePengeluaranTruckingRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pengeluaranTrucking = (new PengeluaranTrucking())->processStore($request->all());
            $pengeluaranTrucking->position = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable())->position;
            $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));


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
     */
    public function update(UpdatePengeluaranTruckingRequest $request, PengeluaranTrucking $pengeluaranTrucking): JsonResponse
    {
        DB::beginTransaction();
        try {
            $pengeluaranTrucking = (new PengeluaranTrucking())->processUpdate($pengeluaranTrucking, $request->all());
            $pengeluaranTrucking->position = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable())->position;
            $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));

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
     */
    public function destroy(DestroyPengeluaranTruckingRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranTrucking = (new PengeluaranTrucking())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable(), true);
            $pengeluaranTrucking->position = $selected->position;
            $pengeluaranTrucking->id = $selected->id;
            $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));

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

    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $pengeluaranTruckings = $decodedResponse['data'];

            $judulLaporan = $pengeluaranTruckings[0]['judulLaporan'];

            $i = 0;
            foreach ($pengeluaranTruckings as $index => $params) {

                $statusaktif = $params['format'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


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
                    'index' => 'coadebet',
                ],
                [
                    'label' => 'COA Kredit',
                    'index' => 'coakredit',
                ],
                [
                    'label' => 'COA Posting Debet',
                    'index' => 'coapostingdebet',
                ],
                [
                    'label' => 'COA Posting Kredit',
                    'index' => 'coapostingkredit',
                ],
                [
                    'label' => 'COA Debet Keterangan',
                    'index' => 'coadebet_keterangan',
                ],
                [
                    'label' => 'COA Kredit Keterangan',
                    'index' => 'coakredit_keterangan',
                ],
                [
                    'label' => 'COA Posting Debet Keterangan',
                    'index' => 'coapostingdebet_keterangan',
                ],
                [
                    'label' => 'COA Posting Kredit Keterangan',
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
}
