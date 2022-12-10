<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\ParameterRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Resources\Parameter as ResourcesParameter;
use App\Http\Resources\ParameterResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\QueryException;

class ParameterController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $parameter = new Parameter();

        return response([
            'data' => $parameter->get(),
            'attributes' => [
                'totalRows' => $parameter->totalRows,
                'totalPages' => $parameter->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(ParameterRequest $request)
    {
        DB::beginTransaction();

        try {
            $parameter = new Parameter();
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->kelompok = $request->kelompok ?? '';
            $parameter->type = $request->type ?? 0;
            $parameter->singkatan = $request->singkatan ?? '';
            $parameter->warna = $request->warna ?? '';
            $parameter->modifiedby = auth('api')->user()->name;

            $detailmemo = [];
            for ($i = 0; $i < count($request->key); $i++) {
                $datadetailmemo = [
                    $request->key[$i] => $request->value[$i],
                ];
                $detailmemo = array_merge($detailmemo, $datadetailmemo);
            }

            $parameter->memo = json_encode($detailmemo);
            if ($parameter->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($parameter->getTable()),
                    'postingdari' => 'ENTRY PARAMETER',
                    'idtrans' => $parameter->id,
                    'nobuktitrans' => $parameter->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $parameter->toArray(),
                    'modifiedby' => $parameter->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($parameter, $parameter->getTable());
            $parameter->position = $selected->position;
            $parameter->page = ceil($parameter->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $parameter->page = ceil($parameter->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $parameter
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $parameter = new Parameter();
        return response([
            'status' => true,
            'data' => $parameter->findAll($id)
        ]);
    }

    /**
     * @ClassName
     */
    public function update(ParameterRequest $request, Parameter $parameter)
    {
        DB::beginTransaction();

        try {
            $parameter->grp = $request->grp;
            $parameter->subgrp = $request->subgrp;
            $parameter->text = $request->text;
            $parameter->kelompok = $request->kelompok ?? '';
            $parameter->type = $request->type ?? 0;
            $parameter->singkatan = $request->singkatan ?? '';
            $parameter->warna = $request->warna ?? '';
            $parameter->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $detailmemo = [];
            for ($i = 0; $i < count($request->key); $i++) {
                $datadetailmemo = [
                    $request->key[$i] => $request->value[$i],
                ];
                $detailmemo = array_merge($detailmemo, $datadetailmemo);
            }

            $parameter->memo = json_encode($detailmemo);
            if ($parameter->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($parameter->getTable()),
                    'postingdari' => 'EDIT PARAMETER',
                    'idtrans' => $parameter->id,
                    'nobuktitrans' => $parameter->id,
                    'aksi' => 'EDIT',
                    'datajson' => $parameter->toArray(),
                    'modifiedby' => $parameter->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($parameter, $parameter->getTable());
                $parameter->position = $selected->position;
                $parameter->page = ceil($parameter->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $parameter
                ]);
            } else {
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(Parameter $parameter, Request $request)
    {
        DB::beginTransaction();

        $delete = Parameter::destroy($parameter->id);

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($parameter->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $parameter->id,
                'nobuktitrans' => $parameter->id,
                'aksi' => 'DELETE',
                'datajson' => $parameter->toArray(),
                'modifiedby' => $parameter->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($parameter, $parameter->getTable(), true);
            $parameter->position = $selected->position;
            $parameter->id = $selected->id;
            $parameter->page = ceil($parameter->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $parameter
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('parameter')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function detail()
    {
        $query = Parameter::select('memo')->where('id', request()->id)->first();

        $memo = json_decode($query->memo);

        $array = [];
        if ($memo != '') {

            $i = 0;
            foreach ($memo as $index => $value) {
                $array[$i]['key'] = $index;
                $array[$i]['value'] = $value;

                $i++;
            }
        }

        return response([
            'data' => $array
        ]);
    }

    public function getparameterid($grp, $subgrp, $text)
    {

        $querydata = Parameter::select('id as id', 'text')
            ->where('grp', '=',  $grp)
            ->where('subgrp', '=',  $subgrp)
            ->where('text', '=',  $text)
            ->orderBy('id');


        $data = $querydata->first();
        return $data;
    }

    /**
     * @ClassName
     */
    public function export()
    {
        header('Access-Control-Allow-Origin: *');

        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $parameters = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Group',
                'index' => 'grp',
            ],
            [
                'label' => 'Subgroup',
                'index' => 'subgrp',
            ],
            [
                'label' => 'Text',
                'index' => 'text',
            ],
            [
                'label' => 'Type',
                'index' => 'type',
            ],
            [
                'label' => 'Singkatan',
                'index' => 'singkatan',
            ],
            [
                'label' => 'Warna',
                'index' => 'warna',
            ],
            [
                'label' => 'Memo',
                'index' => 'memo',
            ],
        ];

        $this->toExcel('Parameter', $parameters, $columns);
    }

    public function combo(Request $request)
    {
        $parameters = Parameter::where('grp', '=', $request->grp)
            ->where('subgrp', '=', $request->subgrp)
            ->get();

        return response([
            'data' => $parameters
        ]);
    }


    public function comboapproval(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
            });

 
            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        // $datajson[$index]['updated_at']
        return response([
            'data' => $data
        ]);
    }

    public function combolist(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
            });

 
            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        // $datajson[$index]['updated_at']
        return response([
            'data' => $data
        ]);
    }
        
}
