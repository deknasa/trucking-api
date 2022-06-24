<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Penerima;
use App\Http\Requests\StorePenerimaRequest;
use App\Http\Requests\UpdatePenerimaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaController extends Controller
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

        $totalRows = DB::table((new Penerima)->getTable())->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Penerima)->getTable())->select(
                'penerima.id',
                'penerima.namapenerima',
                'penerima.npwp',
                'penerima.noktp',
                'parameter_statusaktif.text as statusaktif',
                'parameter_statuskaryawan.text as statuskaryawan',
                'penerima.modifiedby',
                'penerima.created_at',
                'penerima.updated_at'
            )
                ->leftJoin('parameter as parameter_statusaktif', 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
                ->leftJoin('parameter as parameter_statuskaryawan', 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id')
                ->orderBy('penerima.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Penerima)->getTable())->select(
                    'penerima.id',
                    'penerima.namapenerima',
                    'penerima.npwp',
                    'penerima.noktp',
                    'parameter_statusaktif.text as statusaktif',
                    'parameter_statuskaryawan.text as statuskaryawan',
                    'penerima.modifiedby',
                    'penerima.created_at',
                    'penerima.updated_at'
                )
                    ->leftJoin('parameter as parameter_statusaktif', 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statuskaryawan', 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id')
                    ->orderBy('penerima.' . $params['sortIndex'], $params['sortOrder'])
                    ->orderBy('penerima.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Penerima)->getTable())->select(
                    'penerima.id',
                    'penerima.namapenerima',
                    'penerima.npwp',
                    'penerima.noktp',
                    'parameter_statusaktif.text as statusaktif',
                    'parameter_statuskaryawan.text as statuskaryawan',
                    'penerima.modifiedby',
                    'penerima.created_at',
                    'penerima.updated_at'
                )
                    ->leftJoin('parameter as parameter_statusaktif', 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statuskaryawan', 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id')
                    ->orderBy('penerima.' . $params['sortIndex'], $params['sortOrder'])
                    ->orderBy('penerima.id', 'asc');
            }
        }


        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where('penerima.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere('penerima.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }


            $totalRows = count($query->get());

            $totalPages = ceil($totalRows / $params['limit']);
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $penerimas = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $penerimas,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function show(Penerima $penerima)
    {
        return response([
            'status' => true,
            'data' => $penerima
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePenerimaRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerima = new Penerima();
            $penerima->namapenerima = $request->namapenerima;
            $penerima->npwp = $request->npwp;
            $penerima->noktp = $request->noktp;
            $penerima->statusaktif = $request->statusaktif;
            $penerima->statuskaryawan = $request->statuskaryawan;
            $penerima->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerima->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerima->getTable()),
                    'postingdari' => 'ENTRY PENERIMA',
                    'idtrans' => $penerima->id,
                    'nobuktitrans' => $penerima->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerima->toArray(),
                    'modifiedby' => $penerima->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($penerima->id, $request, $del);
            $penerima->position = $data->row;

            if (isset($request->limit)) {
                $penerima->page = ceil($penerima->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerima
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
        /**
     * @ClassName 
     */
    public function update(UpdatePenerimaRequest $request, Penerima $penerima)
    {
        try {
            $penerima->namapenerima = $request->namapenerima;
            $penerima->npwp = $request->npwp;
            $penerima->noktp = $request->noktp;
            $penerima->statusaktif = $request->statusaktif;
            $penerima->statuskaryawan = $request->statuskaryawan;
            $penerima->modifiedby = auth('api')->user()->name;

            if ($penerima->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerima->getTable()),
                    'postingdari' => 'EDIT PENERIMA',
                    'idtrans' => $penerima->id,
                    'nobuktitrans' => $penerima->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerima->toArray(),
                    'modifiedby' => $penerima->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $penerima->position = $this->getid($penerima->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $penerima->page = ceil($penerima->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $penerima
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
    public function destroy(Penerima $penerima, Request $request)
    {
        $delete = Penerima::destroy($penerima->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($penerima->getTable()),
                'postingdari' => 'DELETE PENERIMA',
                'idtrans' => $penerima->id,
                'nobuktitrans' => $penerima->id,
                'aksi' => 'DELETE',
                'datajson' => $penerima->toArray(),
                'modifiedby' => $penerima->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($penerima->id, $request, $del);
            $penerima->position = $data->row  ?? 0;
            $penerima->id = $data->id  ?? 0;
            if (isset($request->limit)) {
                $penerima->page = ceil($penerima->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerima
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerima')->getColumns();

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
            $table->string('namapenerima', 300)->default('');
            $table->string('npwp', 300)->default('');
            $table->string('noktp', 300)->default('');
            $table->string('statusaktif', 300)->default('');
            $table->string('statuskaryawan', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Penerima)->getTable())->select(
                'penerima.id',
                'penerima.namapenerima',
                'penerima.npwp',
                'penerima.noktp',
                'parameter_statusaktif.text as statusaktif',
                'parameter_statuskaryawan.text as statuskaryawan',
                'penerima.modifiedby',
                'penerima.created_at',
                'penerima.updated_at'
            )
                ->leftJoin('parameter as parameter_statusaktif', 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
                ->leftJoin('parameter as parameter_statuskaryawan', 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id')
                ->orderBy('penerima.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Penerima)->getTable())->select(
                    'penerima.id',
                    'penerima.namapenerima',
                    'penerima.npwp',
                    'penerima.noktp',
                    'parameter_statusaktif.text as statusaktif',
                    'parameter_statuskaryawan.text as statuskaryawan',
                    'penerima.modifiedby',
                    'penerima.created_at',
                    'penerima.updated_at'
                )
                    ->leftJoin('parameter as parameter_statusaktif', 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statuskaryawan', 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id')
                    ->orderBy('penerima.' . $params['sortname'], $params['sortorder'])
                    ->orderBy('penerima.id', $params['sortorder']);
            } else {
                $query = DB::table((new Penerima)->getTable())->select(
                    'penerima.id',
                    'penerima.namapenerima',
                    'penerima.npwp',
                    'penerima.noktp',
                    'parameter_statusaktif.text as statusaktif',
                    'parameter_statuskaryawan.text as statuskaryawan',
                    'penerima.modifiedby',
                    'penerima.created_at',
                    'penerima.updated_at'
                )
                    ->leftJoin('parameter as parameter_statusaktif', 'penerima.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statuskaryawan', 'penerima.statuskaryawan', '=', 'parameter_statuskaryawan.id')
                    ->orderBy('penerima.' . $params['sortname'], $params['sortorder'])
                    ->orderBy('penerima.id', 'asc');
            }
        }

        DB::table($temp)->insertUsing([
            'id_',
            'namapenerima',
            'npwp',
            'noktp',
            'statusaktif',
            'statuskaryawan',
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

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $penerimas = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Nama Penerima',
                'index' => 'namapenerima',
            ],
            [
                'label' => 'NPWP',
                'index' => 'npwp',
            ],
            [
                'label' => 'No KTP',
                'index' => 'noktp',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
            [
                'label' => 'Status Karyawan',
                'index' => 'statuskaryawan',
            ],
        ];

        $this->toExcel('Penerima', $penerimas, $columns);
    }
}
