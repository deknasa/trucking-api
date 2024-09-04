<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Http\Requests\ParameterRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\UpdateParameterRequest;

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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ParameterController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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

    public function default()
    {
        $parameter = new Parameter();
        return response([
            'status' => true,
            'data' => $parameter->default()
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(ParameterRequest $request)
    {
        $data = [
            'id' => $request->id,
            "grp" => $request->grp,
            "subgrp" => $request->subgrp,
            "text" => $request->text,
            "kelompok" => $request->kelompok,
            "type" => $request->type,
            "grup" => $request->grup,
            "default" => $request->default,
            "key" => $request->key,
            "value" => $request->value,
        ];
        DB::beginTransaction();

        try {

            $parameter = (new Parameter())->processStore( $data);
            $parameter->position = $this->getPosition($parameter, $parameter->getTable())->position;
            if ($request->limit==0) {
                $parameter->page = ceil($parameter->position / (10));
            } else {
                $parameter->page = ceil($parameter->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $parameter
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function addrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key.*' => 'required',
            'value.*' => 'required',
        ], [
            'key.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'value.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'key' => 'judul',
            'value' => 'keterangan',
            'key.*' => 'judul',
            'value.*' => 'keterangan',
        ]);
        if ($validator->fails()) {
        
            return response()->json( [
                "message"=> "The given data was invalid.",
                "errors"=> $validator->messages()
            ],422);
        }
        return true;
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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateParameterRequest $request, $id)
    {

        $data = [
            'id' => $request->id,
            "grp" => $request->grp,
            "subgrp" => $request->subgrp,
            "text" => $request->text,
            "kelompok" => $request->kelompok,
            "type" => $request->type,
            "grup" => $request->grup,
            "default" => $request->default,
            "key" => $request->key,
            "value" => $request->value,
        ];
        DB::beginTransaction();

        try {
            $parameter = Parameter::lockForUpdate()->findOrFail($id);
            $parameter = (new Parameter())->processUpdate($parameter, $data);
            $parameter->position = $this->getPosition($parameter, $parameter->getTable())->position;
            if ($request->limit==0) {
                $parameter->page = ceil($parameter->position / (10));
            } else {
                $parameter->page = ceil($parameter->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $parameter
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try{
            $parameter = (new parameter())->processDestroy($id);
            $selected = $this->getPosition($parameter, $parameter->getTable(), true);
            $parameter->position = $selected->position;
            $parameter->id = $selected->id;
            if ($request->limit==0) {
                $parameter->page = ceil($parameter->position / (10));
            } else {
                $parameter->page = ceil($parameter->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $parameter
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getcoa(Request $request)
    {

        $parameter = new Parameter();
        return response([
            'data' => $parameter->getcoa($request->filter)
        ]);
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

        $array = [];
        if (request()->id != 0) {
            $memo = json_decode($query->memo);
            if ($memo != '') {
                $i = 0;
                foreach ($memo as $index => $value) {
                    $array[$i]['key'] = $index;
                    $array[$i]['value'] = $value;

                    $i++;
                }
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
    
    public function getParamterDefault(Request $request)
    {

        $querydata = Parameter::select('id as id', 'text')
            ->where('grp', '=',  $request->grp)
            ->where('subgrp', '=',  $request->subgrp)
            ->where('default', 'YA')
            ->orderBy('id');


        $data = $querydata->first();
        return response([
            'data' => $data
        ]);
    }
    

    public function getparamrequest(Request $request)
    {
        $querydata = Parameter::select('id as id', 'text')
            ->where('grp', '=',  $request->grp)
            ->where('subgrp', '=',  $request->subgrp)
            ->where('text', '=',  $request->text)
            ->orderBy('id');


        $data = $querydata->first();
        return $data;
    }

    public function getparamid($grp, $subgrp)
    {

        $querydata = Parameter::select('id as id', 'text')
            ->where('grp', '=',  $grp)
            ->where('subgrp', '=',  $subgrp)
            ->orderBy('id');


        $data = $querydata->first();
        return $data;
    }
    public function getparamfirst(Request $request)
    {

        $querydata = Parameter::select('id as id', 'text')
            ->where('grp', '=',  $request->grp)
            ->where('subgrp', '=', $request->subgrp)
            ->orderBy('id');


        $data = $querydata->first();
        return $data;
    }

    public function getParamByText(Request $request)
    {

        $querydata = Parameter::where('grp', '=',  $request->grp)
            ->where('text', '=',  $request->text)
            ->first();

        if($querydata != null){
            $data = $querydata;
        }else{
            $data = [];
        }

        return $data;
    }

     /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }
    
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {
                
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        } else {
            header('Access-Control-Allow-Origin: *');

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $parameters = $decodedResponse['data'];

            $judulLaporan = $parameters[0]['judulLaporan'];

            $i = 0;
            foreach ($parameters as $index => $params) {
                $memo = $params['memo'];
                $result = json_decode($memo, true);
                $memo = $result['MEMO'];
                $parameters[$i]['memo'] = $memo;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
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
                    'label' => 'Memo',
                    'index' => 'memo',
                ],
            ];

            $this->toExcel($judulLaporan, $parameters, $columns);
        }
    }

    public function combo(Request $request)
    {
        // $parameters = Parameter::where('grp', '=', $request->grp)
        //     ->where('subgrp', '=', $request->subgrp)
        //     ->get();

        $parameter = new Parameter();
        return response([
            'data' => $parameter->combo()
        ]);
    }


    public function comboapproval(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
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
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
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
