<?php

namespace App\Http\Controllers\Api;

use App\Models\Zona;
use App\Http\Requests\StoreZonaRequest;
use App\Http\Requests\UpdateZonaRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ZonaController extends Controller
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

        $totalRows = DB::table((new Zona())->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new Zona())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Zona())->getTable())->select(
                'zona.id',
                'zona.zona',
                'zona.keterangan',
                'parameter.text as statusaktif',
                'zona.modifiedby',
                'zona.created_at',
                'zona.updated_at'
            )
            ->leftJoin('parameter', 'zona.statusaktif', '=', 'parameter.id')
            ->orderBy('zona.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'zona' or $params['sortIndex'] == 'keterangan') {
            $query = DB::table((new Zona())->getTable())->select(
                'zona.id',
                'zona.zona',
                'zona.keterangan',
                'parameter.text as statusaktif',
                'zona.modifiedby',
                'zona.created_at',
                'zona.updated_at'
            )
                ->leftJoin('parameter', 'zona.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('zona.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Zona())->getTable())->select(
                    'zona.id',
                    'zona.zona',
                    'zona.keterangan',
                    'parameter.text as statusaktif',
                    'zona.modifiedby',
                    'zona.created_at',
                    'zona.updated_at'
                )
                    ->leftJoin('parameter', 'zona.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('zona.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Zona())->getTable())->select(
                    'zona.id',
                    'zona.zona',
                    'zona.keterangan',
                    'parameter.text as statusaktif',
                    'zona.modifiedby',
                    'zona.created_at',
                    'zona.updated_at'
                )
                    ->leftJoin('parameter', 'zona.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('zona.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('zona.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('zona.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }
                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $zona = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $zona,
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
    public function store(StoreZonaRequest $request)
    {
        DB::beginTransaction();

        try {
            $zona = new Zona();
            $zona->zona = $request->zona;
            $zona->statusaktif = $request->statusaktif;
            $zona->keterangan = $request->keterangan;
            $zona->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($zona->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'ENTRY ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $zona->toArray(),
                    'modifiedby' => $zona->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($zona->id, $request, $del);
            $zona->position = $data->row;

            if (isset($request->limit)) {
                $zona->page = ceil($zona->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $zona
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Zona $zona)
    {
        return response([
            'status' => true,
            'data' => $zona
        ]);
    }

    public function edit(Zona $zona)
    {
        //
    }
 /**
     * @ClassName 
     */
    public function update(StoreZonaRequest $request, Zona $zona)
    {
        try {
            $zona = Zona::findOrFail($zona->id);
            $zona->zona = $request->zona;
            $zona->keterangan = $request->keterangan;
            $zona->statusaktif = $request->statusaktif;
            $zona->modifiedby = auth('api')->user()->name;

            if ($zona->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($zona->getTable()),
                    'postingdari' => 'EDIT ZONA',
                    'idtrans' => $zona->id,
                    'nobuktitrans' => $zona->id,
                    'aksi' => 'EDIT',
                    'datajson' => $zona->toArray(),
                    'modifiedby' => $zona->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $zona->position = $this->getid($zona->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $zona->page = ceil($zona->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $zona
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
    public function destroy(Zona $zona, Request $request)
    {
        $delete = Zona::destroy($zona->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($zona->getTable()),
                'postingdari' => 'DELETE ZONA',
                'idtrans' => $zona->id,
                'nobuktitrans' => $zona->id,
                'aksi' => 'DELETE',
                'datajson' => $zona->toArray(),
                'modifiedby' => $zona->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($zona->id, $request, $del);
            $zona->position = $data->row  ?? 0;
            $zona->id = $data->id  ?? 0;
            if (isset($request->limit)) {
                $zona->page = ceil($zona->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $zona
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('zona')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp'=>'status aktif'])->get(),
        ];

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
            $table->string('zona', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Zona())->getTable())->select(
                'zona.id as id_',
                'zona.zona',
                'zona.keterangan',
                'zona.statusaktif',
                'zona.modifiedby',
                'zona.created_at',
                'zona.updated_at'
            )
                ->orderBy('zona.id', $params['sortorder']);
        } else if ($params['sortname'] == 'zona' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new Zona())->getTable())->select(
                'zona.id as id_',
                'zona.zona',
                'zona.keterangan',
                'zona.statusaktif',
                'zona.modifiedby',
                'zona.created_at',
                'zona.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('zona.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Zona())->getTable())->select(
                    'zona.id as id_',
                    'zona.zona',
                    'zona.keterangan',
                    'zona.statusaktif',
                    'zona.modifiedby',
                    'zona.created_at',
                    'zona.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('zona.id', $params['sortorder']);
            } else {
                $query = DB::table((new Zona())->getTable())->select(
                    'zona.id as id_',
                    'zona.zona',
                    'zona.keterangan',
                    'zona.statusaktif',
                    'zona.modifiedby',
                    'zona.created_at',
                    'zona.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('zona.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'zona', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
