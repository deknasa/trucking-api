<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\SubKelompok;
use App\Http\Requests\StoreSubKelompokRequest;
use App\Http\Requests\UpdateSubKelompokRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubKelompokController extends Controller
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

        /* Sorting */
        $query = DB::table((new SubKelompok())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        $totalRows = $query->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new SubKelompok())->getTable())->select(
                'subkelompok.id',
                'subkelompok.kodesubkelompok',
                'subkelompok.keterangan',
                'kelompok.keterangan as kelompok_id',
                'parameter.text as statusaktif',
                'subkelompok.modifiedby',
                'subkelompok.created_at',
                'subkelompok.updated_at'
            )
                ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                ->orderBy('subkelompok.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'grp' or $params['sortIndex'] == 'subgrp') {
            $query = DB::table((new SubKelompok())->getTable())->select(
                'subkelompok.id',
                'subkelompok.kodesubkelompok',
                'subkelompok.keterangan',
                'kelompok.keterangan as kelompok_id',
                'parameter.text as statusaktif',
                'subkelompok.modifiedby',
                'subkelompok.created_at',
                'subkelompok.updated_at'
            )
                ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('subkelompok.text', $params['sortOrder'])
                ->orderBy('subkelompok.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new SubKelompok())->getTable())->select(
                    'subkelompok.id',
                    'subkelompok.kodesubkelompok',
                    'subkelompok.keterangan',
                    'kelompok.keterangan as kelompok_id',
                    'parameter.text as statusaktif',
                    'subkelompok.modifiedby',
                    'subkelompok.created_at',
                    'subkelompok.updated_at'
                )
                    ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                    ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('subkelompok.id', $params['sortOrder']);
            } else {
                $query = DB::table((new SubKelompok())->getTable())->select(
                    'subkelompok.id',
                    'subkelompok.kodesubkelompok',
                    'subkelompok.keterangan',
                    'kelompok.keterangan as kelompok_id',
                    'parameter.text as statusaktif',
                    'subkelompok.modifiedby',
                    'subkelompok.created_at',
                    'subkelompok.updated_at'
                )
                    ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                    ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('subkelompok.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->where('subkelompok.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere('subkelompok.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                default:

                    break;
            }

            $totalRows = $query->count();
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $subKelompoks = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $subKelompoks,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function show(SubKelompok $subKelompok)
    {
        return response([
            'status' => true,
            'data' => $subKelompok
        ]);
    }
 /**
     * @ClassName 
     */
    public function store(StoreSubKelompokRequest $request)
    {
        DB::beginTransaction();

        try {
            $subKelompok = new SubKelompok();
            $subKelompok->kodesubkelompok = $request->kodesubkelompok;
            $subKelompok->keterangan = $request->keterangan;
            $subKelompok->kelompok_id = $request->kelompok_id;
            $subKelompok->statusaktif = $request->statusaktif;
            $subKelompok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($subKelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($subKelompok->getTable()),
                    'postingdari' => 'ENTRY PARAMETER',
                    'idtrans' => $subKelompok->id,
                    'nobuktitrans' => $subKelompok->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $subKelompok->toArray(),
                    'modifiedby' => $subKelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($subKelompok->id, $request, $del);
            $subKelompok->position = $data->row;

            if (isset($request->limit)) {
                $subKelompok->page = ceil($subKelompok->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $subKelompok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
 /**
     * @ClassName 
     */
    public function update(UpdateSubKelompokRequest $request, SubKelompok $subKelompok)
    {
        try {
            $subKelompok->kodesubkelompok = $request->kodesubkelompok;
            $subKelompok->keterangan = $request->keterangan;
            $subKelompok->kelompok_id = $request->kelompok_id;
            $subKelompok->statusaktif = $request->statusaktif;
            $subKelompok->modifiedby = auth('api')->user()->name;

            if ($subKelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($subKelompok->getTable()),
                    'postingdari' => 'EDIT PARAMETER',
                    'idtrans' => $subKelompok->id,
                    'nobuktitrans' => $subKelompok->id,
                    'aksi' => 'EDIT',
                    'datajson' => $subKelompok->toArray(),
                    'modifiedby' => $subKelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $subKelompok->position = $this->getid($subKelompok->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $subKelompok->page = ceil($subKelompok->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $subKelompok
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
    public function destroy(SubKelompok $subKelompok, Request $request)
    {
        $delete = SubKelompok::destroy($subKelompok->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($subKelompok->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $subKelompok->id,
                'nobuktitrans' => $subKelompok->id,
                'aksi' => 'DELETE',
                'datajson' => $subKelompok->toArray(),
                'modifiedby' => $subKelompok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($subKelompok->id, $request, $del);
            $subKelompok->position = $data->row  ?? 0;
            $subKelompok->id = $data->id  ?? 0;
            if (isset($request->limit)) {
                $subKelompok->page = ceil($subKelompok->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $subKelompok
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $subKelompoks = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Subkelompok',
                'index' => 'kodesubkelompok',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Kelompok',
                'index' => 'kelompok_id',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Sub Kelompok', $subKelompoks, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('subkelompok')->getColumns();

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
            $table->string('grp', 300)->default('');
            $table->string('subgrp', 300)->default('');
            $table->string('text', 300)->default('');
            $table->string('memo', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = SubKelompok::select(
                'subkelompok.id as id_',
                'subkelompok.kodesubkelompok',
                'subkelompok.keterangan',
                'kelompok.keterangan as kelompok_id',
                'parameter.text as statusaktif',
                'subkelompok.modifiedby',
                'subkelompok.created_at',
                'subkelompok.updated_at'
            )
                ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                ->orderBy('parameter.id', $params['sortorder']);
        } else if ($params['sortname'] == 'grp' or $params['sortname'] == 'subgrp') {
            $query = SubKelompok::select(
                'subkelompok.id as id_',
                'subkelompok.kodesubkelompok',
                'subkelompok.keterangan',
                'kelompok.keterangan as kelompok_id',
                'parameter.text as statusaktif',
                'subkelompok.modifiedby',
                'subkelompok.created_at',
                'subkelompok.updated_at'
            )
                ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('parameter.text', $params['sortorder'])
                ->orderBy('parameter.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = SubKelompok::select(
                    'subkelompok.id as id_',
                    'subkelompok.kodesubkelompok',
                    'subkelompok.keterangan',
                    'kelompok.keterangan as kelompok_id',
                    'parameter.text as statusaktif',
                    'subkelompok.modifiedby',
                    'subkelompok.created_at',
                    'subkelompok.updated_at'
                )
                    ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                    ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('parameter.id', $params['sortorder']);
            } else {
                $query = SubKelompok::select(
                    'subkelompok.id as id_',
                    'subkelompok.kodesubkelompok',
                    'subkelompok.keterangan',
                    'kelompok.keterangan as kelompok_id',
                    'parameter.text as statusaktif',
                    'subkelompok.modifiedby',
                    'subkelompok.created_at',
                    'subkelompok.updated_at'
                )
                    ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
                    ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('parameter.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'grp', 'subgrp', 'text', 'memo', 'modifiedby', 'created_at', 'updated_at'], $query);


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
