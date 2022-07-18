<?php

namespace App\Http\Controllers\Api;

use App\Models\Kerusakan;
use App\Http\Requests\StoreKerusakanRequest;
use App\Http\Requests\UpdateKerusakanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KerusakanController extends Controller
{

          /**
     * @ClassName 
     */
    public function index()
    {
        $kerusakan = new Kerusakan();

        return response([
            'data' => $kerusakan->get(),
            'attributes' => [
                'totalRows' => $kerusakan->totalRows,
                'totalPages' => $kerusakan->totalPages
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
    public function store(StoreKerusakanRequest $request)
    {
        DB::beginTransaction();

        try {
            $kerusakan = new Kerusakan();
            $kerusakan->keterangan = $request->keterangan;
            $kerusakan->statusaktif = $request->statusaktif;
            $kerusakan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kerusakan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kerusakan->getTable()),
                    'postingdari' => 'ENTRY KERUSAKAN',
                    'idtrans' => $kerusakan->id,
                    'nobuktitrans' => $kerusakan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kerusakan->toArray(),
                    'modifiedby' => $kerusakan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($kerusakan->id, $request, $del);
            $kerusakan->position = @$data->row;

            if (isset($request->limit)) {
                $kerusakan->page = ceil($kerusakan->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kerusakan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kerusakan $kerusakan)
    {
        return response([
            'status' => true,
            'data' => $kerusakan
        ]);
    }

    public function edit(Kerusakan $kerusakan)
    {
        //
    }
      /**
     * @ClassName 
     */
    public function update(StoreKerusakanRequest $request, Kerusakan $kerusakan)
    {
        try {
            $kerusakan = Kerusakan::findOrFail($kerusakan->id);
            $kerusakan->keterangan = $request->keterangan;
            $kerusakan->statusaktif = $request->statusaktif;
            $kerusakan->modifiedby = auth('api')->user()->name;

            if ($kerusakan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kerusakan->getTable()),
                    'postingdari' => 'EDIT KERUSAKAN',
                    'idtrans' => $kerusakan->id,
                    'nobuktitrans' => $kerusakan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kerusakan->toArray(),
                    'modifiedby' => $kerusakan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $kerusakan->position = $this->getid($kerusakan->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $kerusakan->page = ceil($kerusakan->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $kerusakan
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
    public function destroy(Kerusakan $kerusakan, Request $request)
    {
        $delete = Kerusakan::destroy($kerusakan->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($kerusakan->getTable()),
                'postingdari' => 'DELETE KERUSAKAN',
                'idtrans' => $kerusakan->id,
                'nobuktitrans' => $kerusakan->id,
                'aksi' => 'DELETE',
                'datajson' => $kerusakan->toArray(),
                'modifiedby' => $kerusakan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($kerusakan->id, $request, $del);
            $kerusakan->position = @$data->row  ?? 0;
            $kerusakan->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $kerusakan->page = ceil($kerusakan->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kerusakan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kerusakan')->getColumns();

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
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Kerusakan)->getTable())->select(
                'kerusakan.id as id_',
                'kerusakan.keterangan',
                'kerusakan.statusaktif',
                'kerusakan.modifiedby',
                'kerusakan.created_at',
                'kerusakan.updated_at'
            )
                ->orderBy('kerusakan.id', $params['sortorder']);
        } else if ($params['sortname'] == 'keterangan') {
            $query = DB::table((new Kerusakan)->getTable())->select(
                'kerusakan.id as id_',
                'kerusakan.keterangan',
                'kerusakan.statusaktif',
                'kerusakan.modifiedby',
                'kerusakan.created_at',
                'kerusakan.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('kerusakan.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Kerusakan)->getTable())->select(
                    'kerusakan.id as id_',
                    'kerusakan.keterangan',
                    'kerusakan.statusaktif',
                    'kerusakan.modifiedby',
                    'kerusakan.created_at',
                    'kerusakan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kerusakan.id', $params['sortorder']);
            } else {
                $query = DB::table((new Kerusakan)->getTable())->select(
                    'kerusakan.id as id_',
                    'kerusakan.keterangan',
                    'kerusakan.statusaktif',
                    'kerusakan.modifiedby',
                    'kerusakan.created_at',
                    'kerusakan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kerusakan.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
