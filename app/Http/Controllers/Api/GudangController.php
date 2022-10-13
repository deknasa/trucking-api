<?php

namespace App\Http\Controllers\Api;

use App\Models\Gudang;
use App\Http\Requests\StoreGudangRequest;
use App\Http\Requests\UpdateGudangRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GudangController extends Controller
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

        $totalRows = DB::table((new Gudang)->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new Gudang)->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Gudang)->getTable())->select(
                'gudang.id',
                'gudang.gudang',
                'parameter.text as statusaktif',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                ->orderBy('gudang.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'gudang') {
            $query = DB::table((new Gudang)->getTable())->select(
                'gudang.id',
                'gudang.gudang',
                'parameter.text as statusaktif',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('gudang.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Gudang)->getTable())->select(
                    'gudang.id',
                    'gudang.gudang',
                    'parameter.text as statusaktif',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('gudang.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Gudang)->getTable())->select(
                    'gudang.id',
                    'gudang.gudang',
                    'parameter.text as statusaktif',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('gudang.id', 'asc');
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
                            $query = $query->where('gudang.' . $search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('gudang.' . $search['field'], 'LIKE', "%$search[data]%");
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

        $jenisorder = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $jenisorder,
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
    public function store(StoreGudangRequest $request)
    {
        DB::beginTransaction();

        try {
            $gudang = new Gudang();
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($gudang->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'ENTRY GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            // /* Set position and page */
            // $del = 0;
            // $data = $this->getid($gudang->id, $request, $del);
            // $gudang->position = @$data->row;

            // if (isset($request->limit)) {
            //     $gudang->page = ceil($gudang->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gudang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Gudang $gudang)
    {
        return response([
            'status' => true,
            'data' => $gudang
        ]);
    }

    public function edit(Gudang $gudang)
    {
        //
    }
    /**
     * @ClassName 
     */
    public function update(StoreGudangRequest $request, Gudang $gudang)
    {
        try {
            $gudang = Gudang::findOrFail($gudang->id);
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->modifiedby = auth('api')->user()->name;

            if ($gudang->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'EDIT GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'EDIT',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                // /* Set position and page */

                // $gudang->position = $this->getid($gudang->id, $request, 0)->row;
                // if (isset($request->limit)) {
                //     $gudang->page = ceil($gudang->position / $request->limit);
                // }

                /* Set position and page */
                $selected = $this->getPosition($gudang, $gudang->getTable());
                $gudang->position = $selected->position;
                $gudang->page = ceil($gudang->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $gudang
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
    public function destroy(Gudang $gudang, Request $request)
    {
        DB::beginTransaction();
        try {
        $delete = Gudang::destroy($gudang->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($gudang->getTable()),
                'postingdari' => 'DELETE GUDANG',
                'idtrans' => $gudang->id,
                'nobuktitrans' => $gudang->id,
                'aksi' => 'DELETE',
                'datajson' => $gudang->toArray(),
                'modifiedby' => $gudang->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            // $data = $this->getid($gudang->id, $request, $del);
            // $gudang->position = @$data->row;
            // $gudang->id = @$data->id;
            // if (isset($request->limit)) {
            //     $gudang->page = ceil($gudang->position / $request->limit);
            // }

            $data = $this->getid($gudang->id, $request, $del);
             /* Set position and page */
             $gudang->position = $data->row ?? 0;
             $gudang->id = $data->id ?? 0;
             if (isset($request->limit)) {
                 $gudang->page = ceil($gudang->position / $request->limit);
             }
            // dd($cabang);
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gudang
            ]);
        }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gudang')->getColumns();

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
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'statusgudang' => Parameter::where(['grp' => 'status gudang'])->get(),
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
            $table->string('gudang', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Gudang)->getTable())->select(
                'gudang.id as id_',
                'gudang.gudang',
                'parameter.text as statusaktif',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                ->orderBy('gudang.id', $params['sortorder']);
        } else if ($params['sortname'] == 'gudang' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new Gudang)->getTable())->select(
                'gudang.id as id_',
                'gudang.gudang',
                'parameter.text as statusaktif',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('gudang.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Gudang)->getTable())->select(
                    'gudang.id as id_',
                    'gudang.gudang',
                    'parameter.text as statusaktif',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('gudang.id', $params['sortorder']);
            } else {
                $query = DB::table((new Gudang)->getTable())->select(
                    'gudang.id as id_',
                    'gudang.gudang',
                    'parameter.text as statusaktif',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('gudang.id', 'asc');
            }
        }


        DB::table($temp)->insertUsing(['id_', 'gudang', 'statusaktif',  'modifiedby', 'created_at', 'updated_at'], $query);


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
