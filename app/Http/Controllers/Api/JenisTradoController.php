<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisTrado;
use App\Http\Requests\StoreJenisTradoRequest;
use App\Http\Requests\UpdateJenisTradoRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisTradoController extends Controller
{
      /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = JenisTrado::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = JenisTrado::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = JenisTrado::select(
                'jenistrado.id',
                'jenistrado.kodejenistrado',
                'jenistrado.keterangan',
                'parameter.text as statusaktif',
                'jenistrado.modifiedby',
                'jenistrado.created_at',
                'jenistrado.updated_at'
            )
            ->leftJoin('parameter', 'jenistrado.statusaktif', '=', 'parameter.id')
            ->orderBy('jenistrado.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodejenistrado' or $params['sortIndex'] == 'keterangan') {
            $query = JenisTrado::select(
                'jenistrado.id',
                'jenistrado.kodejenistrado',
                'jenistrado.keterangan',
                'parameter.text as statusaktif',
                'jenistrado.modifiedby',
                'jenistrado.created_at',
                'jenistrado.updated_at'
            )
                ->leftJoin('parameter', 'jenistrado.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('jenistrado.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = JenisTrado::select(
                    'jenistrado.id',
                    'jenistrado.kodejenistrado',
                    'jenistrado.keterangan',
                    'parameter.text as statusaktif',
                    'jenistrado.modifiedby',
                    'jenistrado.created_at',
                    'jenistrado.updated_at'
                )
                    ->leftJoin('parameter', 'jenistrado.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('jenistrado.id', $params['sortOrder']);
            } else {
                $query = JenisTrado::select(
                    'jenistrado.id',
                    'jenistrado.kodejenistrado',
                    'jenistrado.keterangan',
                    'parameter.text as statusaktif',
                    'jenistrado.modifiedby',
                    'jenistrado.created_at',
                    'jenistrado.updated_at'
                )
                    ->leftJoin('parameter', 'jenistrado.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('jenistrado.id', 'asc');
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
                            $query = $query->where('jenistrado.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('jenistrado.'.$search['field'], 'LIKE', "%$search[data]%");
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

        $jenistrado = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $jenistrado,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }
      /**
     * @ClassName 
     */
    public function store(StoreJenisTradoRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenistrado = new jenistrado();
            $jenistrado->kodejenistrado = $request->kodejenistrado;
            $jenistrado->statusaktif = $request->statusaktif;
            $jenistrado->keterangan = $request->keterangan;
            $jenistrado->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenistrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenistrado->getTable()),
                    'postingdari' => 'ENTRY JENIS TRADO',
                    'idtrans' => $jenistrado->id,
                    'nobuktitrans' => $jenistrado->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenistrado->toArray(),
                    'modifiedby' => $jenistrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($jenistrado->id, $request, $del);
            $jenistrado->position = $data->row;

            if (isset($request->limit)) {
                $jenistrado->page = ceil($jenistrado->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenistrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\jenistrado  $jenistrado
     * @return \Illuminate\Http\Response
     */
    public function show(jenistrado $jenistrado)
    {
        return response([
            'status' => true,
            'data' => $jenistrado
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\jenistrado  $jenistrado
     * @return \Illuminate\Http\Response
     */
    public function edit(jenistrado $jenistrado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatejenistradoRequest  $request
     * @param  \App\Models\jenistrado  $jenistrado
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function update(StoreJenisTradoRequest $request, jenistrado $jenistrado)
    {
        try {
            $jenistrado = JenisTrado::findOrFail($jenistrado->id);
            $jenistrado->kodejenistrado = $request->kodejenistrado;
            $jenistrado->keterangan = $request->keterangan;
            $jenistrado->statusaktif = $request->statusaktif;
            $jenistrado->modifiedby = $request->modifiedby;

            if ($jenistrado->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenistrado->getTable()),
                    'postingdari' => 'EDIT JENIS TRADO',
                    'idtrans' => $jenistrado->id,
                    'nobuktitrans' => $jenistrado->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenistrado->toArray(),
                    'modifiedby' => $jenistrado->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $jenistrado->position = $this->getid($jenistrado->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $jenistrado->page = ceil($jenistrado->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $jenistrado
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\jenistrado  $jenistrado
     * @return \Illuminate\Http\Response
     */
          /**
     * @ClassName 
     */
    public function destroy(jenistrado $jenistrado, Request $request)
    {
        $delete = JenisTrado::destroy($jenistrado->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($jenistrado->getTable()),
                'postingdari' => 'DELETE JENIS TRADO',
                'idtrans' => $jenistrado->id,
                'nobuktitrans' => $jenistrado->id,
                'aksi' => 'DELETE',
                'datajson' => $jenistrado->toArray(),
                'modifiedby' => $jenistrado->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($jenistrado->id, $request, $del);
            $jenistrado->position = $data->row;
            $jenistrado->id = $data->id;
            if (isset($request->limit)) {
                $jenistrado->page = ceil($jenistrado->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenistrado
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenistrado')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($jenistrado, $request)
    {
        return JenisTrado::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $jenistrado->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
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
            $table->string('kodejenistrado', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = JenisTrado::select(
                'jenistrado.id as id_',
                'jenistrado.kodejenistrado',
                'jenistrado.keterangan',
                'jenistrado.statusaktif',
                'jenistrado.modifiedby',
                'jenistrado.created_at',
                'jenistrado.updated_at'
            )
                ->orderBy('jenistrado.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodejenistrado' or $params['sortname'] == 'keterangan') {
            $query = JenisTrado::select(
                'jenistrado.id as id_',
                'jenistrado.kodejenistrado',
                'jenistrado.keterangan',
                'jenistrado.statusaktif',
                'jenistrado.modifiedby',
                'jenistrado.created_at',
                'jenistrado.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('jenistrado.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = JenisTrado::select(
                    'jenistrado.id as id_',
                    'jenistrado.kodejenistrado',
                    'jenistrado.keterangan',
                    'jenistrado.statusaktif',
                    'jenistrado.modifiedby',
                    'jenistrado.created_at',
                    'jenistrado.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenistrado.id', $params['sortorder']);
            } else {
                $query = JenisTrado::select(
                    'jenistrado.id as id_',
                    'jenistrado.kodejenistrado',
                    'jenistrado.keterangan',
                    'jenistrado.statusaktif',
                    'jenistrado.modifiedby',
                    'jenistrado.created_at',
                    'jenistrado.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenistrado.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodejenistrado', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
