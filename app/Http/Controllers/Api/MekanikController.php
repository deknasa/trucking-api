<?php

namespace App\Http\Controllers\Api;

use App\Models\Mekanik;
use App\Models\AkunPusat;
use App\Http\Requests\StoreMekanikRequest;
use App\Http\Requests\UpdateMekanikRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MekanikController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mekanik = new Mekanik();

        return response([
            'data' => $mekanik->get(),
            'attributes' => [
                'totalRows' => $mekanik->totalRows,
                'totalPages' => $mekanik->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreMekanikRequest $request)
    {
        DB::beginTransaction();
        try {
            $mekanik = new Mekanik();
            $mekanik->namamekanik = $request->namamekanik;
            $mekanik->keterangan = $request->keterangan;
            $mekanik->statusaktif = $request->statusaktif;
            $mekanik->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($mekanik->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mekanik->getTable()),
                    'postingdari' => 'ENTRY MEKANIK',
                    'idtrans' => $mekanik->id,
                    'nobuktitrans' => $mekanik->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $mekanik->toArray(),
                    'modifiedby' => $mekanik->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($mekanik->id, $request, $del);
            $mekanik->position = $data->row;

            if (isset($request->limit)) {
                $mekanik->page = ceil($mekanik->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mekanik
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Mekanik $mekanik)
    {
        return response([
            'status' => true,
            'data' => $mekanik
        ]);
    }

    public function edit(Mekanik $mekanik)
    {
        //
    }
    /**
     * @ClassName 
     */
    public function update(StoreMekanikRequest $request, Mekanik $mekanik)
    {
        try {
            $mekanik->namamekanik = $request->namamekanik;
            $mekanik->keterangan = $request->keterangan;
            $mekanik->statusaktif = $request->statusaktif;
            $mekanik->modifiedby = auth('api')->user()->name;

            if ($mekanik->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mekanik->getTable()),
                    'postingdari' => 'EDIT MEKANIK',
                    'idtrans' => $mekanik->id,
                    'nobuktitrans' => $mekanik->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mekanik->toArray(),
                    'modifiedby' => $mekanik->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $mekanik->position = $this->getid($mekanik->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $mekanik->page = ceil($mekanik->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $mekanik
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
     * @ClassName 
     */
    public function destroy(Mekanik $mekanik, Request $request)
    {
        $delete = Mekanik::destroy($mekanik->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($mekanik->getTable()),
                'postingdari' => 'DELETE MEKANIK',
                'idtrans' => $mekanik->id,
                'nobuktitrans' => $mekanik->id,
                'aksi' => 'DELETE',
                'datajson' => $mekanik->toArray(),
                'modifiedby' => $mekanik->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($mekanik->id, $request, $del);
            $mekanik->position = @$data->row  ?? 0;
            $mekanik->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $mekanik->page = ceil($mekanik->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mekanik
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'status' => Parameter::where(['grp'=>'status aktif'])->get(),
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
            $table->string('namamekanik', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif',300)->default('')->nullable();
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Mekanik)->getTable())->select(
                'mekanik.id as id_',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'mekanik.statusaktif',
                'mekanik.modifiedby',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
                ->orderBy('mekanik.id', $params['sortorder']);
        } else if ($params['sortname'] == 'namamekanik' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new Mekanik)->getTable())->select(
                'mekanik.id as id_',
                'mekanik.namamekanik',
                'mekanik.keterangan',
                'mekanik.statusaktif',
                'mekanik.modifiedby',
                'mekanik.created_at',
                'mekanik.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('mekanik.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Mekanik)->getTable())->select(
                    'mekanik.id as id_',
                    'mekanik.namamekanik',
                    'mekanik.keterangan',
                    'mekanik.statusaktif',
                    'mekanik.modifiedby',
                    'mekanik.created_at',
                    'mekanik.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('mekanik.id', $params['sortorder']);
            } else {
                $query = DB::table((new Mekanik)->getTable())->select(
                    'mekanik.id as id_',
                    'mekanik.namamekanik',
                    'mekanik.keterangan',
                    'mekanik.statusaktif',
                    'mekanik.modifiedby',
                    'mekanik.created_at',
                    'mekanik.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('mekanik.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'namamekanik', 'keterangan', 'statusaktif','modifiedby', 'created_at', 'updated_at'], $query);


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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mekanik')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
