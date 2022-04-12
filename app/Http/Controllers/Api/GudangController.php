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
    
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Gudang::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = Gudang::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = Gudang::select(
                'gudang.id',
                'gudang.gudang',
                'parameter.text as statusaktif',
                'p.text as statusgudang',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
            ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter AS p', 'gudang.statusgudang', '=', 'p.id')
            ->orderBy('gudang.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'gudang') {
            $query = Gudang::select(
                'gudang.id',
                'gudang.gudang',
                'parameter.text as statusaktif',
                'p.text as statusgudang',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                ->leftJoin('parameter AS p', 'gudang.statusgudang', '=', 'p.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('gudang.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Gudang::select(
                    'gudang.id',
                    'gudang.gudang',
                    'parameter.text as statusaktif',
                    'p.text as statusgudang',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                    ->leftJoin('parameter AS p', 'gudang.statusgudang', '=', 'p.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('gudang.id', $params['sortOrder']);
            } else {
                $query = Gudang::select(
                    'gudang.id',
                    'gudang.gudang',
                    'parameter.text as statusaktif',
                    'p.text as statusgudang',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->leftJoin('parameter', 'gudang.statusaktif', '=', 'parameter.id')
                    ->leftJoin('parameter AS p', 'gudang.statusgudang', '=', 'p.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('gudang.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('gudang.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('gudang.'.$search['field'], 'LIKE', "%$search[data]%");
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

    public function store(StoreGudangRequest $request)
    {
        DB::beginTransaction();

        try {
            $gudang = new Gudang();
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->statusgudang = $request->statusgudang;
            $gudang->modifiedby = $request->modifiedby;
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

            /* Set position and page */
            $del = 0;
            $data = $this->getid($gudang->id, $request, $del);
            $gudang->position = @$data->row;

            if (isset($request->limit)) {
                $gudang->page = ceil($gudang->position / $request->limit);
            }

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

    public function update(StoreGudangRequest $request, Gudang $gudang)
    {
        try {
            $gudang = Gudang::findOrFail($gudang->id);
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->statusgudang = $request->statusgudang;
            $gudang->modifiedby = $request->modifiedby;

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

                /* Set position and page */
                $gudang->position = $this->getid($gudang->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $gudang->page = ceil($gudang->position / $request->limit);
                }

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

    public function destroy(Gudang $gudang, Request $request)
    {
        $delete = Gudang::destroy($gudang->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($gudang->getTable()),
                'postingdari' => 'DELETE JENIS ORDER',
                'idtrans' => $gudang->id,
                'nobuktitrans' => $gudang->id,
                'aksi' => 'DELETE',
                'datajson' => $gudang->toArray(),
                'modifiedby' => $gudang->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($gudang->id, $request, $del);
            $gudang->position = @$data->row;
            $gudang->id = @$data->id;
            if (isset($request->limit)) {
                $gudang->page = ceil($gudang->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gudang
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gudang')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($gudang, $request)
    {
        return Gudang::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $gudang->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp'=>'status aktif'])->get(),
            'statusgudang' => Parameter::where(['grp'=>'status gudang'])->get(),
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
            $table->string('statusgudang', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Gudang::select(
                'gudang.id as id_',
                'gudang.gudang',
                'gudang.statusaktif',
                'gudang.statusgudang',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->orderBy('gudang.id', $params['sortorder']);
        } else if ($params['sortname'] == 'gudang' or $params['sortname'] == 'keterangan') {
            $query = Gudang::select(
                'gudang.id as id_',
                'gudang.gudang',
                'gudang.statusaktif',
                'gudang.statusgudang',
                'gudang.modifiedby',
                'gudang.created_at',
                'gudang.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('gudang.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Gudang::select(
                    'gudang.id as id_',
                    'gudang.gudang',
                    'gudang.statusaktif',
                    'gudang.statusgudang',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('gudang.id', $params['sortorder']);
            } else {
                $query = Gudang::select(
                    'gudang.id as id_',
                    'gudang.gudang',
                    'gudang.statusaktif',
                    'gudang.statusgudang',
                    'gudang.modifiedby',
                    'gudang.created_at',
                    'gudang.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('gudang.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'gudang', 'statusaktif', 'statusgudang', 'modifiedby', 'created_at', 'updated_at'], $query);


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
