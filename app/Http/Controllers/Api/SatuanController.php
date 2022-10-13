<?php

namespace App\Http\Controllers\Api;

use App\Models\Satuan;
use App\Http\Requests\StoreSatuanRequest;
use App\Http\Requests\UpdateSatuanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SatuanController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $satuan = new Satuan();

        return response([
            'data' => $satuan->get(),
            'attributes' => [
                'totalRows' => $satuan->totalRows,
                'totalPages' => $satuan->totalPages
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
    public function store(StoreSatuanRequest $request)
    {
        DB::beginTransaction();

        try {
            $satuan = new Satuan();
            $satuan->satuan = $request->satuan;
            $satuan->statusaktif = $request->statusaktif;
            $satuan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($satuan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'ENTRY SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => $satuan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            // $del = 0;
            // $data = $this->getid($satuan->id, $request, $del);
            // $satuan->position = $data->row;

            // if (isset($request->limit)) {
            //     $satuan->page = ceil($satuan->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($satuan, $satuan->getTable());
            $satuan->position = $selected->position;
            $satuan->page = ceil($satuan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $satuan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Satuan $satuan)
    {
        return response([
            'status' => true,
            'data' => $satuan
        ]);
    }

    public function edit(Satuan $satuan)
    {
        //
    }
    /**
     * @ClassName 
     */
    public function update(StoreSatuanRequest $request, Satuan $satuan)
    {
        try {
            $satuan = Satuan::findOrFail($satuan->id);
            $satuan->satuan = $request->satuan;
            $satuan->statusaktif = $request->statusaktif;
            $satuan->modifiedby = auth('api')->user()->name;

            if ($satuan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($satuan->getTable()),
                    'postingdari' => 'EDIT SATUAN',
                    'idtrans' => $satuan->id,
                    'nobuktitrans' => $satuan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $satuan->toArray(),
                    'modifiedby' => $satuan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                // /* Set position and page */
                // $satuan->position = $this->getid($satuan->id, $request, 0)->row;

                // if (isset($request->limit)) {
                //     $satuan->page = ceil($satuan->position / $request->limit);
                // }

                /* Set position and page */
                $selected = $this->getPosition($satuan, $satuan->getTable());
                $satuan->position = $selected->position;
                $satuan->page = ceil($satuan->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $satuan
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
    public function destroy(Satuan $satuan, Request $request)
    {
        $delete = Satuan::destroy($satuan->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($satuan->getTable()),
                'postingdari' => 'DELETE SATUAN',
                'idtrans' => $satuan->id,
                'nobuktitrans' => $satuan->id,
                'aksi' => 'DELETE',
                'datajson' => $satuan->toArray(),
                'modifiedby' => $satuan->modifiedby
            ];

            $data = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($data);
            DB::commit();

            $data = $this->getid($satuan->id, $request, $del);
            // $satuan->position = $data->row ?? 0;
            // $satuan->id = $data->id  ?? 0;
            // if (isset($request->limit)) {
            //     $satuan->page = ceil($satuan->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($satuan, $satuan->getTable(), true);
            $satuan->position = $selected->position;
            $satuan->id = $selected->id;
            $satuan->page = ceil($satuan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $satuan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('satuan')->getColumns();

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
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
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
            $table->string('satuan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Satuan())->getTable())->select(
                'satuan.id as id_',
                'satuan.satuan',
                'satuan.statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at'
            )
                ->orderBy('satuan.id', $params['sortorder']);
        } else if ($params['sortname'] == 'satuan') {
            $query = DB::table((new Satuan())->getTable())->select(
                'satuan.id as id_',
                'satuan.satuan',
                'satuan.statusaktif',
                'satuan.modifiedby',
                'satuan.created_at',
                'satuan.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('satuan.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Satuan())->getTable())->select(
                    'satuan.id as id_',
                    'satuan.satuan',
                    'satuan.statusaktif',
                    'satuan.modifiedby',
                    'satuan.created_at',
                    'satuan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('satuan.id', $params['sortorder']);
            } else {
                $query = DB::table((new Satuan())->getTable())->select(
                    'satuan.id as id_',
                    'satuan.satuan',
                    'satuan.statusaktif',
                    'satuan.modifiedby',
                    'satuan.created_at',
                    'satuan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('satuan.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'satuan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
