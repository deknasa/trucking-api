<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTrucking;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranTruckingRequest;
use App\Http\Requests\UpdatePengeluaranTruckingRequest;
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
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        /* Sorting */
        $query = DB::table((new PengeluaranTrucking())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        $totalRows = $query->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new PengeluaranTrucking())->getTable())->select(
                'pengeluarantrucking.id',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
                'pengeluarantrucking.coa',
                'pengeluarantrucking.statusformat',
                'pengeluarantrucking.modifiedby',
                'pengeluarantrucking.created_at',
                'pengeluarantrucking.updated_at'
            )->orderBy('pengeluarantrucking.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new PengeluaranTrucking())->getTable())->select(
                    'pengeluarantrucking.id',
                    'pengeluarantrucking.kodepengeluaran',
                    'pengeluarantrucking.keterangan',
                    'pengeluarantrucking.coa',
                    'pengeluarantrucking.statusformat',
                    'pengeluarantrucking.modifiedby',
                    'pengeluarantrucking.created_at',
                    'pengeluarantrucking.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('pengeluarantrucking.id', $params['sortOrder']);
            } else {
                $query = DB::table((new PengeluaranTrucking())->getTable())->select(
                    'pengeluarantrucking.id',
                    'pengeluarantrucking.kodepengeluaran',
                    'pengeluarantrucking.keterangan',
                    'pengeluarantrucking.coa',
                    'pengeluarantrucking.statusformat',
                    'pengeluarantrucking.modifiedby',
                    'pengeluarantrucking.created_at',
                    'pengeluarantrucking.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('pengeluarantrucking.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                default:

                    break;
            }

            $totalRows = $query->count();
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $pengeluaranTruckings = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $pengeluaranTruckings,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }
   /**
     * @ClassName 
     */
    public function store(StorePengeluaranTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $pengeluaranTrucking = new PengeluaranTrucking();
            $pengeluaranTrucking->kodepengeluaran = $request->kodepengeluaran;
            $pengeluaranTrucking->keterangan = $request->keterangan;
            $pengeluaranTrucking->coa = $request->coa;
            $pengeluaranTrucking->statusformat = $request->statusformat;
            $pengeluaranTrucking->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pengeluaranTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                    'postingdari' => 'ENTRY PENGELUARAN TRUCKING',
                    'idtrans' => $pengeluaranTrucking->id,
                    'nobuktitrans' => $pengeluaranTrucking->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranTrucking->toArray(),
                    'modifiedby' => $pengeluaranTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($pengeluaranTrucking->id, $request, $del);
            $pengeluaranTrucking->position = $data->row;

            if (isset($request->limit)) {
                $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(PengeluaranTrucking $pengeluaranTrucking)
    {
        return response([
            'status' => true,
            'data' => $pengeluaranTrucking
        ]);
    }

    public function edit(PengeluaranTrucking $pengeluaranTrucking)
    {
        //
    }
   /**
     * @ClassName 
     */
    public function update(StorePengeluaranTruckingRequest $request, PengeluaranTrucking $pengeluaranTrucking)
    {
        try {
            $pengeluaranTrucking->kodepengeluaran = $request->kodepengeluaran;
            $pengeluaranTrucking->keterangan = $request->keterangan;
            $pengeluaranTrucking->coa = $request->coa;
            $pengeluaranTrucking->statusformat = $request->statusformat;
            $pengeluaranTrucking->modifiedby = auth('api')->user()->name;

            if ($pengeluaranTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN TRUCKING',
                    'idtrans' => $pengeluaranTrucking->id,
                    'nobuktitrans' => $pengeluaranTrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranTrucking->toArray(),
                    'modifiedby' => $pengeluaranTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $pengeluaranTrucking->position = $this->getid($pengeluaranTrucking->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $pengeluaranTrucking
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
   /**
     * @ClassName 
     */
    public function destroy(PengeluaranTrucking $pengeluaranTrucking, Request $request)
    {
        $delete = PengeluaranTrucking::destroy($pengeluaranTrucking->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                'postingdari' => 'DELETE PENGELUARAN TRUCKING',
                'idtrans' => $pengeluaranTrucking->id,
                'nobuktitrans' => $pengeluaranTrucking->id,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranTrucking->toArray(),
                'modifiedby' => $pengeluaranTrucking->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($pengeluaranTrucking->id, $request, $del);
            $pengeluaranTrucking->position = @$data->row  ?? 0;
            $pengeluaranTrucking->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranTrucking
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $pengeluaranTruckings = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
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
                'label' => 'COA',
                'index' => 'coa',
            ],
            [
                'label' => 'Format Bukti',
                'index' => 'statusformat',
            ],
        ];

        $this->toExcel('Pengeluaran Trucking', $pengeluaranTruckings, $columns);
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
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('kodepengeluaran', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('coa', 300)->default('');
            $table->string('statusformat', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = PengeluaranTrucking::select(
                'pengeluarantrucking.id as id_',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
                'pengeluarantrucking.coa',
                'pengeluarantrucking.statusformat',
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
                    'pengeluarantrucking.coa',
                    'pengeluarantrucking.statusformat',
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
                    'pengeluarantrucking.coa',
                    'pengeluarantrucking.statusformat',
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
            'coa',
            'statusformat',
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
