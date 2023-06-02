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
    public function report()
    {
        
    }

    public function default()
    {

        $cabang = new Cabang();
        return response([
            'status' => true,
            'data' => $cabang->default(),
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
        // dd($cabang);
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $cabang = new Cabang();
        $cabang = $cabang->lockAndDestroy($id);
        if ($cabang) {
            $logTrail = [
                'namatabel' => strtoupper($cabang->getTable()),
                'postingdari' => 'DELETE CABANG',
                'idtrans' => $cabang->id,
                'nobuktitrans' => $cabang->id,
                'aksi' => 'DELETE',
                'datajson' => $cabang->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


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
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    /**
     * @ClassName 
     */
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $cabangs = $decodedResponse['data'];

        //dd($cabangs);
        $i = 0;
        foreach ($cabangs as $index => $params) {


            $statusaktif = $params['statusaktif'];


            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $cabangs[$i]['statusaktif'] = $statusaktif;
            $i++;
        }

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

        return response([
            'data' => $data
        ]);
    }
}
