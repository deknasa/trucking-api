<?php

namespace App\Http\Controllers\Api;

use App\Models\Satuan;
use App\Http\Requests\StoreSatuanRequest;
use App\Http\Requests\UpdateSatuanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SatuanController extends Controller
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

        $totalRows = DB::table((new Satuan())->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new Satuan())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Satuan())->getTable())->select(
                'satuan.id',
                'satuan.satuan',
                'parameter.text as statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at'
            )
            ->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id')
            ->orderBy('satuan.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'satuan') {
            $query = DB::table((new Satuan())->getTable())->select(
                'satuan.id',
                'satuan.satuan',
                'parameter.text as statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at'
            )
                ->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('satuan.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Satuan())->getTable())->select(
                    'satuan.id',
                    'satuan.satuan',
                    'parameter.text as statusaktif',
                    'satuan.modifiedby',
                    'satuan.created_at',
                    'satuan.updated_at'
                )
                    ->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('satuan.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Satuan())->getTable())->select(
                    'satuan.id',
                    'satuan.satuan',
                    'parameter.text as statusaktif',
                    'satuan.modifiedby',
                    'satuan.created_at',
                    'satuan.updated_at'
                )
                    ->leftJoin('parameter', 'satuan.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('satuan.id', 'asc');
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
                            $query = $query->where('satuan.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('satuan.'.$search['field'], 'LIKE', "%$search[data]%");
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

        $satuan = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $satuan,
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
    public function store(StoreSatuanRequest $request)
    {
        DB::beginTransaction();

        try {
            $satuan = new Satuan();
            $satuan->satuan = $request->satuan;
            $satuan->statusaktif = $request->statusaktif;
            $satuan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($satuan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'ENTRY SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => $satuan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($satuan->id, $request, $del);
            $satuan->position = $data->row;

            if (isset($request->limit)) {
                $satuan->page = ceil($satuan->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $satuan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Satuan $satuan)
    {
        return response([
            'status' => true,
            'data' => $satuan
        ]);
    }

    public function edit(Satuan $satuan)
    {
        //
    }
 /**
     * @ClassName 
     */
    public function update(StoreSatuanRequest $request, Satuan $satuan)
    {
        try {
            $satuan = Satuan::findOrFail($satuan->id);
            $satuan->satuan = $request->satuan;
            $satuan->statusaktif = $request->statusaktif;
            $satuan->modifiedby = auth('api')->user()->name;

            if ($satuan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'EDIT SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => $satuan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $satuan->position = $this->getid($satuan->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $satuan->page = ceil($satuan->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $satuan
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
    public function destroy(Satuan $satuan, Request $request)
    {
        $delete = Satuan::destroy($satuan->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($satuan->getTable()),
                'postingdari' => 'DELETE SATUAN',
                'idtrans' => $satuan->id,
                'nobuktitrans' => $satuan->id,
                'aksi' => 'DELETE',
                'datajson' => $satuan->toArray(),
                'modifiedby' => $satuan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($satuan->id, $request, $del);
            $satuan->position = $data->row  ?? 0;
            $satuan->id = $data->id  ?? 0;
            if (isset($request->limit)) {
                $satuan->page = ceil($satuan->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $satuan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('satuan')->getColumns();

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
            $table->string('satuan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Satuan())->getTable())->select(
                'satuan.id as id_',
                'satuan.satuan',
                'satuan.statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at'
            )
                ->orderBy('satuan.id', $params['sortorder']);
        } else if ($params['sortname'] == 'satuan') {
            $query = DB::table((new Satuan())->getTable())->select(
                'satuan.id as id_',
                'satuan.satuan',
                'satuan.statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('satuan.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Satuan())->getTable())->select(
                    'satuan.id as id_',
                    'satuan.satuan',
                    'satuan.statusaktif',
                    'satuan.modifiedby',
                    'satuan.created_at',
                    'satuan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('satuan.id', $params['sortorder']);
            } else {
                $query = DB::table((new Satuan())->getTable())->select(
                    'satuan.id as id_',
                    'satuan.satuan',
                    'satuan.statusaktif',
                    'satuan.modifiedby',
                    'satuan.created_at',
                    'satuan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('satuan.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'satuan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
