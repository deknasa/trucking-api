<?php

namespace App\Http\Controllers\Api;

use App\Models\Mandor;
use App\Http\Requests\StoreMandorRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MandorController extends Controller
{
     /**
     * @ClassName 
     */
    public function index()
    {
        $mandor = new Mandor();

        return response([
            'data' => $mandor->get(),
            'attributes' => [
                'totalRows' => $mandor->totalRows,
                'totalPages' => $mandor->totalPages
            ]
        ]);
    }

    public function create()
    {
        //
    }
     /**
     * @ClassName 
     */
    public function store(StoreMandorRequest $request)
    {
        DB::beginTransaction();

        try {
            $mandor = new Mandor();
            $mandor->namamandor = $request->namamandor;
            $mandor->keterangan = $request->keterangan;
            $mandor->statusaktif = $request->statusaktif;
            $mandor->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($mandor->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'ENTRY MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => $mandor->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($mandor, $mandor->getTable(), true);
            $mandor->position = $selected->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mandor
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Mandor $mandor)
    {
        return response([
            'status' => true,
            'data' => $mandor
        ]);
    }

    public function edit($id)
    {
        //
    }
     /**
     * @ClassName 
     */
    public function update(Request $request, Mandor $mandor)
    {
        try {
            $mandor = Mandor::findOrFail($mandor->id);
            $mandor->namamandor = $request->namamandor;
            $mandor->keterangan = $request->keterangan;
            $mandor->statusaktif = $request->statusaktif;
            $mandor->modifiedby = auth('api')->user()->name;

            if ($mandor->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'EDIT MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => $mandor->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $selected = $this->getPosition($mandor, $mandor->getTable(), true);
                $mandor->position = $selected->position;
                $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $mandor
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
    public function destroy(Mandor $mandor, Request $request)
    {
        $delete = Mandor::destroy($mandor->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($mandor->getTable()),
                'postingdari' => 'DELETE MANDOR',
                'idtrans' => $mandor->id,
                'nobuktitrans' => $mandor->id,
                'aksi' => 'DELETE',
                'datajson' => $mandor->toArray(),
                'modifiedby' => $mandor->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            $selected = $this->getPosition($mandor, $mandor->getTable(), true);
            $mandor->position = $selected->position;
            $mandor->id = $selected->id;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mandor
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mandor')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
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
            $table->string('namamandor', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Mandor)->getTable())->select(
                'mandor.id as id_',
                'mandor.namamandor',
                'mandor.keterangan',
                'mandor.statusaktif',
                'mandor.modifiedby',
                'mandor.created_at',
                'mandor.updated_at'
            )
                ->orderBy('mandor.id', $params['sortorder']);
        } else if ($params['sortname'] == 'keterangan') {
            $query = DB::table((new Mandor)->getTable())->select(
                'mandor.id as id_',
                'mandor.namamandor',
                'mandor.keterangan',
                'mandor.statusaktif',
                'mandor.modifiedby',
                'mandor.created_at',
                'mandor.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('mandor.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Mandor)->getTable())->select(
                    'mandor.id as id_',
                    'mandor.namamandor',
                    'mandor.keterangan',
                    'mandor.statusaktif',
                    'mandor.modifiedby',
                    'mandor.created_at',
                    'mandor.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('mandor.id', $params['sortorder']);
            } else {
                $query = DB::table((new Mandor)->getTable())->select(
                    'mandor.id as id_',
                    'mandor.namamandor',
                    'mandor.keterangan',
                    'mandor.statusaktif',
                    'mandor.modifiedby',
                    'mandor.created_at',
                    'mandor.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('mandor.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'namamandor', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
