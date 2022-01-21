<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Http\Requests\StoreCabangRequest;
use App\Http\Requests\UpdateCabangRequest;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CabangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 100,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Cabang::count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Sorting */
        if ($params['sortIndex'] == 'id') {
            $query = Cabang::select(
                'cabang.id',
                'cabang.kodecabang',
                'cabang.namacabang',
                'parameter.text as statusaktif',
                'cabang.modifiedby',
                'cabang.created_at',
                'cabang.updated_at'
            )
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->orderBy('cabang.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Cabang::select(
                    'cabang.id',
                    'cabang.kodecabang',
                    'cabang.namacabang',
                    'parameter.text as statusaktif',
                    'cabang.modifiedby',
                    'cabang.created_at',
                    'cabang.updated_at'
                )
                    ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('cabang.id', $params['sortOrder']);
            } else {
                $query = Cabang::select(
                    'cabang.id',
                    'cabang.kodecabang',
                    'cabang.namacabang',
                    'parameter.text as statusaktif',
                    'cabang.modifiedby',
                    'cabang.created_at',
                    'cabang.updated_at'
                )
                    ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('cabang.id', 'asc');
            }
        }

        $time1 = microtime(true);

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
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

        $cabangs = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        // echo $time2-$time1;
        // echo '---';
        return response([
            'status' => true,
            'data' => $cabangs,
            'attributes' => $attributes,
            'params' => $params
        ]);
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
     * @param  \App\Http\Requests\StoreCabangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCabangRequest $request)
    {


        DB::beginTransaction();
        try {
            $cabang = new Cabang();
            $cabang->kodecabang = $request->kodecabang;
            $cabang->namacabang = $request->namacabang;
            $cabang->statusaktif = $request->statusaktif;

            $cabang->save();
            DB::commit();
            /* Set position and page */
            $del=0;
            $cabang->position = $this->getPosition($cabang->id, $request,$del);
            // dd($cabang->position );
            if (isset($request->limit)) {
                $cabang->page = ceil($cabang->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $cabang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function show(Cabang $cabang)
    {
        return response([
            'status' => true,
            'data' => $cabang
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function edit(Cabang $cabang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCabangRequest  $request
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCabangRequest $request, Cabang $cabang)
    {
        try {
            $update = $cabang->update($request->validated());


            if ($update) {
                /* Set position and page */
                $cabang->position = cabang::orderBy($request->sortname ?? 'id', $request->sortorder ?? 'asc')
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $cabang->{$request->sortname})
                    ->where('id', '<=', $cabang->id)
                    ->count();

                if (isset($request->limit)) {
                    $cabang->page = ceil($cabang->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $cabang
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cabang  $cabang
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cabang $cabang,StoreCabangRequest $request)
    {
        $delete = Cabang::destroy($cabang->id);
        $del=1;
        if ($delete) {
            $cabang->position = $this->getPosition($cabang->id, $request,$del);
            if (isset($request->limit)) {
                $cabang->page = ceil($cabang->position / $request->limit);
            }            
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus'
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('cabang')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combostatus(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];

        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create('##temp_combo', function ($table) {
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
            });

            DB::table('##temp_combo')->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table('##temp_combo')
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }

    public function getPosition2(Request $request,$del)
    {



        Schema::create('##temp_cabang_row', function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('kodecabang', 300)->default('');
            $table->string('namacabang', 300)->default('');
            $table->string('statusaktif', 100)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($request->sortname == 'id') {
            $query = Cabang::select(
                'cabang.id as id_',
                'cabang.kodecabang',
                'cabang.namacabang',
                'parameter.text as statusaktif',
                'cabang.modifiedby',
                'cabang.created_at',
                'cabang.updated_at'
            )
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->orderBy('cabang.id', $request->sortorder);
        } else {
            if ($request->sortorder == 'asc') {
                $query = Cabang::select(
                    'cabang.id as id_',
                    'cabang.kodecabang',
                    'cabang.namacabang',
                    'parameter.text as statusaktif',
                    'cabang.modifiedby',
                    'cabang.created_at',
                    'cabang.updated_at'
                )
                    ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('cabang.id', $request->sortorder);
            } else {
                $query = Cabang::select(
                    'cabang.id as id_',
                    'cabang.kodecabang',
                    'cabang.namacabang',
                    'parameter.text as statusaktif',
                    'cabang.modifiedby',
                    'cabang.created_at',
                    'cabang.updated_at'
                )
                    ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('cabang.id', 'asc');
            }
        }

        $time0 = microtime(true);
        // $bindings = $query->getBindings();
        // $time01=microtime(true);
        // $insertQuery = 'INSERT into ##temp_cabang_row (id_,cabang,statusaktif,modifiedby,created_at,updated_at) '
        //     . $query->toSql();
        // $time02=microtime(true);
        //     DB::insert($insertQuery, $bindings);
        DB::table('##temp_cabang_row')->insertUsing(['id_', 'kodecabang','namacabang', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);

        $time1 = microtime(true);

        // $query = $query->get();
        // $row=0;
        // foreach ($query as $query) {
        //     $row=$row+1;
        //     DB::table('##temp_cabang_row')->insert([
        //         [
        //             'id' => $query->id,
        //             'cabang' => $query->cabang,
        //             'statusaktif' => $query->statusaktif,
        //             'modifiedby' => $query->modifiedby,
        //             'created_at' => $query->created_at,
        //             'updated_at' => $query->updated_at,
        //             'row' => $row
        //                     ]
        //     ]);             

        // }



        // ->where('id', '=',  $request->id)

   
        if ($del==1) {
            $querydata = DB::table('##temp_cabang_row ')
            ->select('id as row')
            ->where('id', '=', $request->indexRow)
            ->orderBy('id');

        } else {
            $querydata = DB::table('##temp_cabang_row ')
            ->select('id as row')
            ->where('id_', '=',  $id)
            ->orderBy('id');

        }

        // ->getLineNumber('Jane Doe');;

        $data = $querydata->get();

        $time2 = microtime(true);
        echo $time0;
        echo '---';
        echo $time1;
        echo '---';
        echo $time2;
        echo '---';

        echo $time2 - $time1;
        echo '---';
        echo $time1 - $time0;




        return response([
            'data' => $data
        ]);
    }


    public function getPosition($id, $request,$del)
    {

        Schema::create('##temp_cabang_row', function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('kodecabang', 300)->default('');
            $table->string('namacabang', 300)->default('');
            $table->string('statusaktif', 100)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });



        if ($request->sortname == 'id') {
            $query = Cabang::select(
                'cabang.id as id_',
                'cabang.kodecabang',
                'cabang.namacabang',
                'parameter.text as statusaktif',
                'cabang.modifiedby',
                'cabang.created_at',
                'cabang.updated_at'
            )
                ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                ->orderBy('cabang.id', $request->sortorder);
        } else {
            if ($request->sortorder == 'asc') {
                $query = Cabang::select(
                    'cabang.id as id_',
                    'cabang.kodecabang',
                    'cabang.namacabang',
                    'parameter.text as statusaktif',
                    'cabang.modifiedby',
                    'cabang.created_at',
                    'cabang.updated_at'
                )
                    ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('cabang.id', $request->sortorder);
            } else {
                $query = Cabang::select(
                    'cabang.id as id_',
                    'cabang.kodecabang',
                    'cabang.namacabang',
                    'parameter.text as statusaktif',
                    'cabang.modifiedby',
                    'cabang.created_at',
                    'cabang.updated_at'
                )
                    ->leftJoin('parameter', 'cabang.statusaktif', '=', 'parameter.id')
                    ->orderBy($request->sortname, $request->sortorder)
                    ->orderBy('cabang.id', 'asc');
            }
        }



        DB::table('##temp_cabang_row')->insertUsing(['id_', 'kodecabang','namacabang', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


        if ($del==1) {
            $querydata = DB::table('##temp_cabang_row ')
            ->select('id as row')
            ->where('id', '=', $request->indexRow)
            ->orderBy('id');

        } else {
            $querydata = DB::table('##temp_cabang_row ')
            ->select('id as row')
            ->where('id_', '=',  $id)
            ->orderBy('id');

        }


        $data = $querydata->first();

        return $data->row;
    }
}
