<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTrucking;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanTruckingRequest;
use App\Http\Requests\UpdatePenerimaanTruckingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanTruckingController extends Controller
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
        $query = DB::table((new PenerimaanTrucking())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        $totalRows = $query->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new PenerimaanTrucking())->getTable())->select(
                'penerimaantrucking.id',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
                'penerimaantrucking.coa',
                'penerimaantrucking.formatbukti',
                'penerimaantrucking.modifiedby',
                'penerimaantrucking.created_at',
                'penerimaantrucking.updated_at'
            )->orderBy('penerimaantrucking.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new PenerimaanTrucking())->getTable())->select(
                    'penerimaantrucking.id',
                    'penerimaantrucking.kodepenerimaan',
                    'penerimaantrucking.keterangan',
                    'penerimaantrucking.coa',
                    'penerimaantrucking.formatbukti',
                    'penerimaantrucking.modifiedby',
                    'penerimaantrucking.created_at',
                    'penerimaantrucking.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('penerimaantrucking.id', $params['sortOrder']);
            } else {
                $query = DB::table((new PenerimaanTrucking())->getTable())->select(
                    'penerimaantrucking.id',
                    'penerimaantrucking.kodepenerimaan',
                    'penerimaantrucking.keterangan',
                    'penerimaantrucking.coa',
                    'penerimaantrucking.formatbukti',
                    'penerimaantrucking.modifiedby',
                    'penerimaantrucking.created_at',
                    'penerimaantrucking.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('penerimaantrucking.id', 'asc');
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

        $penerimaanTruckings = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $penerimaanTruckings,
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
    public function store(StorePenerimaanTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerimaanTrucking = new PenerimaanTrucking();
            $penerimaanTrucking->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanTrucking->keterangan = $request->keterangan;
            $penerimaanTrucking->coa = $request->coa;
            $penerimaanTrucking->formatbukti = $request->formatbukti;
            $penerimaanTrucking->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerimaanTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                    'postingdari' => 'ENTRY PEnerimaan TRUCKING',
                    'idtrans' => $penerimaanTrucking->id,
                    'nobuktitrans' => $penerimaanTrucking->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaanTrucking->toArray(),
                    'modifiedby' => $penerimaanTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($penerimaanTrucking->id, $request, $del);
            $penerimaanTrucking->position = $data->row;

            if (isset($request->limit)) {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(PenerimaanTrucking $penerimaanTrucking)
    {
        return response([
            'status' => true,
            'data' => $penerimaanTrucking
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaanTruckingRequest $request, PenerimaanTrucking $penerimaanTrucking)
    {
        try {
            $penerimaanTrucking->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanTrucking->keterangan = $request->keterangan;
            $penerimaanTrucking->coa = $request->coa;
            $penerimaanTrucking->formatbukti = $request->formatbukti;
            $penerimaanTrucking->modifiedby = auth('api')->user()->name;

            if ($penerimaanTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                    'postingdari' => 'EDIT PEnerimaan TRUCKING',
                    'idtrans' => $penerimaanTrucking->id,
                    'nobuktitrans' => $penerimaanTrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanTrucking->toArray(),
                    'modifiedby' => $penerimaanTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $penerimaanTrucking->position = $this->getid($penerimaanTrucking->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $penerimaanTrucking->page = ceil($penerimaanTrucking->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $penerimaanTrucking
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
    public function destroy(PenerimaanTrucking $penerimaanTrucking, Request $request)
    {
        $delete = PenerimaanTrucking::destroy($penerimaanTrucking->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                'postingdari' => 'DELETE PENERIMAAN TRUCKING',
                'idtrans' => $penerimaanTrucking->id,
                'nobuktitrans' => $penerimaanTrucking->id,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanTrucking->toArray(),
                'modifiedby' => $penerimaanTrucking->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($penerimaanTrucking->id, $request, $del);
            $penerimaanTrucking->position = @$data->row  ?? 0;
            $penerimaanTrucking->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanTrucking
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
        $penerimaanTruckings = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
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
                'label' => 'COA',
                'index' => 'coa',
            ],
            [
                'label' => 'Format Bukti',
                'index' => 'formatbukti',
            ],
        ];

        $this->toExcel('Penerimaan Trucking', $penerimaanTruckings, $columns);
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
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('kodepenerimaan', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('coa', 300)->default('');
            $table->string('formatbukti', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = PenerimaanTrucking::select(
                'penerimaantrucking.id as id_',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
                'penerimaantrucking.coa',
                'penerimaantrucking.formatbukti',
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
                    'penerimaantrucking.coa',
                    'penerimaantrucking.formatbukti',
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
                    'penerimaantrucking.coa',
                    'penerimaantrucking.formatbukti',
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
            'coa',
            'formatbukti',
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
