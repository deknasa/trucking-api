<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisEmkl;
use App\Http\Requests\StoreJenisEmklRequest;
use App\Http\Requests\UpdateJenisEmklRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JenisEmklController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenisemkl = new JenisEmkl();

        return response([
            'data' => $jenisemkl->get(),
            'attributes' => [
                'totalRows' => $jenisemkl->totalRows,
                'totalPages' => $jenisemkl->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreJenisEmklRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisemkl = new JenisEmkl();
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan;
            $jenisemkl->statusaktif = $request->statusaktif;
            $jenisemkl->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'ENTRY JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable());
            $jenisemkl->position = $selected->position;
            $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisemkl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(JenisEmkl $jenisemkl)
    {
        return response([
            'status' => true,
            'data' => $jenisemkl
        ]);
    }

    
    /**
     * @ClassName 
     */
    public function update(StoreJenisEmklRequest $request, JenisEmkl $jenisemkl)
    {
        try {
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan;
            $jenisemkl->modifiedby = auth('api')->user()->name;
            $jenisemkl->statusaktif = $request->statusaktif;

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'EDIT JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                
                 /* Set position and page */
                $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable());
                $jenisemkl->position = $selected->position;
                $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $jenisemkl
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
     * @param  \App\Models\JenisEmkl  $jenisEmkl
     * @return \Illuminate\Http\Response
     */
    /**
     * @ClassName 
     */
    public function destroy(JenisEmkl $jenisemkl, Request $request)
    {
        $delete = JenisEmkl::destroy($jenisemkl->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($jenisemkl->getTable()),
                'postingdari' => 'DELETE JENISEMKL',
                'idtrans' => $jenisemkl->id,
                'nobuktitrans' => $jenisemkl->id,
                'aksi' => 'DELETE',
                'datajson' => $jenisemkl->toArray(),
                'modifiedby' => $jenisemkl->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable(), true);
            $jenisemkl->position = $selected->position;
            $jenisemkl->id = $selected->id;
            $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenisemkl
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisemkl')->getColumns();

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
            $table->string('kodejenisemkl', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 300)->default('')->nullable();
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new JenisEmkl)->getTable())->select(
                'jenisemkl.id as id_',
                'jenisemkl.kodejenisemkl',
                'jenisemkl.keterangan',
                'parameter.text as statusaktif',
                'jenisemkl.modifiedby',
                'jenisemkl.created_at',
                'jenisemkl.updated_at'
            )
                ->leftJoin('parameter', 'jenisemkl.statusaktif', '=', 'parameter.id')

                ->orderBy('jenisemkl.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodejenisemkl' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new JenisEmkl)->getTable())->select(
                'jenisemkl.id as id_',
                'jenisemkl.kodejenisemkl',
                'jenisemkl.keterangan',
                'parameter.text as statusaktif',
                'jenisemkl.modifiedby',
                'jenisemkl.created_at',
                'jenisemkl.updated_at'
            )
                ->leftJoin('parameter', 'jenisemkl.statusaktif', '=', 'parameter.id')

                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('jenisemkl.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new JenisEmkl)->getTable())->select(
                    'jenisemkl.id as id_',
                    'jenisemkl.kodejenisemkl',
                    'jenisemkl.keterangan',
                    'parameter.text as statusaktif',
                    'jenisemkl.modifiedby',
                    'jenisemkl.created_at',
                    'jenisemkl.updated_at'
                )
                    ->leftJoin('parameter', 'jenisemkl.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisemkl.id', $params['sortorder']);
            } else {
                $query = DB::table((new JenisEmkl)->getTable())->select(
                    'jenisemkl.id as id_',
                    'jenisemkl.kodejenisemkl',
                    'jenisemkl.keterangan',
                    'parameter.text as statusaktif',
                    'jenisemkl.modifiedby',
                    'jenisemkl.created_at',
                    'jenisemkl.updated_at'
                )
                    ->leftJoin('parameter', 'jenisemkl.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('jenisemkl.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodejenisemkl', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
