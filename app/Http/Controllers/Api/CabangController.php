<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCabangRequest;
use App\Http\Requests\UpdateCabangRequest;
use App\Http\Requests\DestroyCabangRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\Cabang;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class CabangController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $cabang = new Cabang();
        return response([
            'data' => $cabang->get(),
            'attributes' => [
                'totalRows' => $cabang->totalRows,
                'totalPages' => $cabang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreCabangRequest $request)
    {
        DB::beginTransaction();
        try {
            $cabang = new Cabang();
            $cabang->kodecabang = $request->kodecabang;
            $cabang->namacabang = $request->namacabang;
            $cabang->statusaktif = $request->statusaktif;
            $cabang->modifiedby = auth('api')->user()->name;

            if ($cabang->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($cabang->getTable()),
                    'postingdari' => 'ENTRY CABANG',
                    'idtrans' => $cabang->id,
                    'nobuktitrans' => $cabang->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $cabang->toArray(),
                    'modifiedby' => $cabang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

             /* Set position and page */
             $selected = $this->getPosition($cabang, $cabang->getTable());
             $cabang->position = $selected->position;
             $cabang->page = ceil($cabang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $cabang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(Cabang $cabang)
    {
        return response([
            'status' => true,
            'data' => $cabang
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateCabangRequest $request, Cabang $cabang)
    {
        DB::beginTransaction();
        try {
            $cabang->kodecabang = $request->kodecabang;
            $cabang->namacabang = $request->namacabang;
            $cabang->statusaktif = $request->statusaktif;
            $cabang->modifiedby = auth('api')->user()->name;

            if ($cabang->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($cabang->getTable()),
                    'postingdari' => 'EDIT CABANG',
                    'idtrans' => $cabang->id,
                    'nobuktitrans' => $cabang->id,
                    'aksi' => 'EDIT',
                    'datajson' => $cabang->toArray(),
                    'modifiedby' => $cabang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($cabang, $cabang->getTable());
            $cabang->position = $selected->position;
            $cabang->page = ceil($cabang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $cabang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Cabang $cabang, Request $request)
    {
        DB::beginTransaction();

        try {
            $delete = Cabang::destroy($cabang->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($cabang->getTable()),
                    'postingdari' => 'DELETE CABANG',
                    'idtrans' => $cabang->id,
                    'nobuktitrans' => $cabang->id,
                    'aksi' => 'DELETE',
                    'datajson' => $cabang->toArray(),
                    'modifiedby' => $cabang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

               /* Set position and page */
            $selected = $this->getPosition($cabang, $cabang->getTable(), true);
            $cabang->position = $selected->position;
            $cabang->id = $selected->id;
            $cabang->page = ceil($cabang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $cabang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $cabangs = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Cabang',
                'index' => 'kodecabang',
            ],
            [
                'label' => 'Nama Cabang',
                'index' => 'namacabang',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Cabang', $cabangs, $columns);
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

        return response([
            'data' => $data
        ]);
    }
}
