<?php

<<<<<<< HEAD
namespace App\Http\Controllers;

use App\Models\Mekanik;
use App\Http\Requests\StoreMekanikRequest;
use App\Http\Requests\UpdateMekanikRequest;

class MekanikController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMekanikRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMekanikRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function show(Mekanik $mekanik)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
=======
namespace App\Http\Controllers\Api;

use App\Models\Mekanik;
use App\Models\AkunPusat;
use App\Http\Requests\StoreMekanikRequest;
use App\Http\Requests\UpdateMekanikRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MekanikController extends Controller
{

    public function index()
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Mekanik::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = Mekanik::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = Mekanik::select(
                'mekanik.id',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'parameter.text as statusaktif',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
            ->leftJoin('parameter', 'mekanik.statusaktif', '=', 'parameter.id')
            ->orderBy('mekanik.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'namamekanik' or $params['sortIndex'] == 'keterangan') {
            $query = Mekanik::select(
                'mekanik.id',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'parameter.text as statusaktif',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
                ->leftJoin('parameter', 'mekanik.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('mekanik.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Mekanik::select(
                'mekanik.id',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'parameter.text as statusaktif',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
                    ->leftJoin('parameter', 'mekanik.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('mekanik.id', $params['sortOrder']);
            } else {
                $query = Mekanik::select(
                    'mekanik.id',
                    'mekanik.namamekanik',
                    'mekanik.keterangan',
                    'parameter.text as statusaktif',
                    'mekanik.created_at',
                    'mekanik.updated_at'
                )
                    ->leftJoin('parameter', 'mekanik.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('mekanik.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
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

        $mekanik = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $mekanik,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function store(StoreMekanikRequest $request)
    {
        DB::beginTransaction();
        try {
            $mekanik = new Mekanik();
            $mekanik->namamekanik = $request->namamekanik;
            $mekanik->keterangan = $request->keterangan;
            $mekanik->statusaktif = $request->statusaktif;
            $mekanik->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($mekanik->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mekanik->getTable()),
                    'postingdari' => 'ENTRY MEKANIK',
                    'idtrans' => $mekanik->id,
                    'nobuktitrans' => $mekanik->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $mekanik->toArray(),
                    'modifiedby' => $mekanik->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($mekanik->id, $request, $del);
            $mekanik->position = $data->row;

            if (isset($request->limit)) {
                $mekanik->page = ceil($mekanik->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mekanik
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Mekanik $mekanik)
    {
        return response([
            'status' => true,
            'data' => $mekanik
        ]);
    }

>>>>>>> 45bc0d5a7d263f6ec185c4c06e9fc88025a55e7c
    public function edit(Mekanik $mekanik)
    {
        //
    }

<<<<<<< HEAD
    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMekanikRequest  $request
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMekanikRequest $request, Mekanik $mekanik)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mekanik  $mekanik
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mekanik $mekanik)
    {
        //
=======
    public function update(StoreMekanikRequest $request, Mekanik $mekanik)
    {
        try {
            $mekanik->namamekanik = $request->namamekanik;
            $mekanik->keterangan = $request->keterangan;
            $mekanik->statusaktif = $request->statusaktif;
            $mekanik->modifiedby = $request->modifiedby;

            if ($mekanik->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mekanik->getTable()),
                    'postingdari' => 'EDIT MEKANIK',
                    'idtrans' => $mekanik->id,
                    'nobuktitrans' => $mekanik->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mekanik->toArray(),
                    'modifiedby' => $mekanik->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $mekanik->position = $this->getid($mekanik->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $mekanik->page = ceil($mekanik->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $mekanik
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy(Mekanik $mekanik, Request $request)
    {
        $delete = Mekanik::destroy($mekanik->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($mekanik->getTable()),
                'postingdari' => 'DELETE MEKANIK',
                'idtrans' => $mekanik->id,
                'nobuktitrans' => $mekanik->id,
                'aksi' => 'DELETE',
                'datajson' => $mekanik->toArray(),
                'modifiedby' => $mekanik->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($mekanik->id, $request, $del);
            $mekanik->position = @$data->row;
            $mekanik->id = @$data->id;
            if (isset($request->limit)) {
                $mekanik->page = ceil($mekanik->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mekanik
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'status' => Parameter::where(['grp'=>'status aktif'])->get(),
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
            $table->string('namamekanik', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif',300)->default('')->nullable();
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Mekanik::select(
                'mekanik.id as id_',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'mekanik.statusaktif',
                'mekanik.modifiedby',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
                ->orderBy('mekanik.id', $params['sortorder']);
        } else if ($params['sortname'] == 'namamekanik' or $params['sortname'] == 'keterangan') {
            $query = Mekanik::select(
                'mekanik.id as id_',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'mekanik.statusaktif',
                'mekanik.modifiedby',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('mekanik.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Mekanik::select(
                    'mekanik.id as id_',
                    'mekanik.namamekanik',
                    'mekanik.keterangan',
                    'mekanik.statusaktif',
                    'mekanik.modifiedby',
                    'mekanik.created_at',
                    'mekanik.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('mekanik.id', $params['sortorder']);
            } else {
                $query = Mekanik::select(
                    'mekanik.id as id_',
                    'mekanik.namamekanik',
                    'mekanik.keterangan',
                    'mekanik.statusaktif',
                    'mekanik.modifiedby',
                    'mekanik.created_at',
                    'mekanik.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('mekanik.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'namamekanik', 'keterangan', 'statusaktif','modifiedby', 'created_at', 'updated_at'], $query);


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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mekanik')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
>>>>>>> 45bc0d5a7d263f6ec185c4c06e9fc88025a55e7c
    }
}
