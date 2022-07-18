<?php

namespace App\Http\Controllers\Api;

use App\Models\Kelompok;
use App\Http\Requests\StoreKelompokRequest;
use App\Http\Requests\UpdateKelompokRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KelompokController extends Controller
{
          /**
     * @ClassName 
     */
    public function index()
    {
        $kelompok = new Kelompok();

        return response([
            'data' => $kelompok->get(),
            'attributes' => [
                'totalRows' => $kelompok->totalRows,
                'totalPages' => $kelompok->totalPages
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
    public function store(StoreKelompokRequest $request)
    {
        DB::beginTransaction();

        try {
            $kelompok = new Kelompok();
            $kelompok->kodekelompok = $request->kodekelompok;
            $kelompok->keterangan = $request->keterangan;
            $kelompok->statusaktif = $request->statusaktif;
            $kelompok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kelompok->getTable()),
                    'postingdari' => 'ENTRY KELOMPOK',
                    'idtrans' => $kelompok->id,
                    'nobuktitrans' => $kelompok->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kelompok->toArray(),
                    'modifiedby' => $kelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($kelompok->id, $request, $del);
            $kelompok->position = @$data->row;

            if (isset($request->limit)) {
                $kelompok->page = ceil($kelompok->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kelompok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kelompok $kelompok)
    {
        return response([
            'status' => true,
            'data' => $kelompok
        ]);
    }

    public function edit(Kelompok $kelompok)
    {
        //
    }
      /**
     * @ClassName 
     */
    public function update(StoreKelompokRequest $request, Kelompok $kelompok)
    {
        try {
            $kelompok = Kelompok::findOrFail($kelompok->id);
            $kelompok->kodekelompok = $request->kodekelompok;
            $kelompok->keterangan = $request->keterangan;
            $kelompok->statusaktif = $request->statusaktif;
            $kelompok->modifiedby = auth('api')->user()->name;

            if ($kelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kelompok->getTable()),
                    'postingdari' => 'EDIT KELOMPOK',
                    'idtrans' => $kelompok->id,
                    'nobuktitrans' => $kelompok->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kelompok->toArray(),
                    'modifiedby' => $kelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $kelompok->position = $this->getid($kelompok->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $kelompok->page = ceil($kelompok->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $kelompok
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
    public function destroy(Kelompok $kelompok, Request $request)
    {
        $delete = Kelompok::destroy($kelompok->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($kelompok->getTable()),
                'postingdari' => 'DELETE KELOMPOK',
                'idtrans' => $kelompok->id,
                'nobuktitrans' => $kelompok->id,
                'aksi' => 'DELETE',
                'datajson' => $kelompok->toArray(),
                'modifiedby' => $kelompok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($kelompok->id, $request, $del);
            $kelompok->position = @$data->row  ?? 0;
            $kelompok->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $kelompok->page = ceil($kelompok->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kelompok
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kelompok')->getColumns();

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
            $table->string('kodekelompok', 50)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Kelompok)->getTable())->select(
                'kelompok.id as id_',
                'kelompok.kodekelompok',
                'kelompok.keterangan',
                'kelompok.statusaktif',
                'kelompok.modifiedby',
                'kelompok.created_at',
                'kelompok.updated_at'
            )
                ->orderBy('kelompok.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodekelompok' or $params['sortname'] == 'keterangan') {
            $query = DB::table((new Kelompok)->getTable())->select(
                'kelompok.id as id_',
                'kelompok.kodekelompok',
                'kelompok.keterangan',
                'kelompok.statusaktif',
                'kelompok.modifiedby',
                'kelompok.created_at',
                'kelompok.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('kelompok.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Kelompok)->getTable())->select(
                    'kelompok.id as id_',
                    'kelompok.kodekelompok',
                    'kelompok.keterangan',
                    'kelompok.statusaktif',
                    'kelompok.modifiedby',
                    'kelompok.created_at',
                    'kelompok.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kelompok.id', $params['sortorder']);
            } else {
                $query = DB::table((new Kelompok)->getTable())->select(
                    'kelompok.id as id_',
                    'kelompok.kodekelompok',
                    'kelompok.keterangan',
                    'kelompok.statusaktif',
                    'kelompok.modifiedby',
                    'kelompok.created_at',
                    'kelompok.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kelompok.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodekelompok', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
