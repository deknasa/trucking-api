<?php

namespace App\Http\Controllers\Api;

use App\Models\Merk;
use App\Http\Requests\StoreMerkRequest;
use App\Http\Requests\UpdateMerkRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MerkController extends Controller
{
   /**
     * @ClassName 
     */
    public function index()
    {
        $merk = new Merk();

        return response([
            'data' => $merk->get(),
            'attributes' => [
                'totalRows' => $merk->totalRows,
                'totalPages' => $merk->totalPages
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
    public function store(StoreMerkRequest $request)
    {
        DB::beginTransaction();

        try {
            $merk = new Merk();
            $merk->kodemerk = $request->kodemerk;
            $merk->keterangan = $request->keterangan;
            $merk->statusaktif = $request->statusaktif;
            $merk->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($merk->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'ENTRY MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $merk->toArray(),
                    'modifiedby' => $merk->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($merk->id, $request, $del);
            $merk->position = @$data->row;

            if (isset($request->limit)) {
                $merk->page = ceil($merk->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $merk
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Merk $merk)
    {
        return response([
            'status' => true,
            'data' => $merk
        ]);
    }

    public function edit(Merk $merk)
    {
        //
    }
   /**
     * @ClassName 
     */
    public function update(StoreMerkRequest $request, Merk $merk)
    {
        try {
            $merk = Merk::findOrFail($merk->id);
            $merk->kodemerk = $request->kodemerk;
            $merk->keterangan = $request->keterangan;
            $merk->statusaktif = $request->statusaktif;
            $merk->modifiedby = auth('api')->user()->name;

            if ($merk->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'EDIT MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => 'EDIT',
                    'datajson' => $merk->toArray(),
                    'modifiedby' => $merk->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $merk->position = $this->getid($merk->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $merk->page = ceil($merk->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $merk
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
    public function destroy(Merk $merk, Request $request)
    {
        $delete = Merk::destroy($merk->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($merk->getTable()),
                'postingdari' => 'DELETE MERK',
                'idtrans' => $merk->id,
                'nobuktitrans' => $merk->id,
                'aksi' => 'DELETE',
                'datajson' => $merk->toArray(),
                'modifiedby' => $merk->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($merk->id, $request, $del);
            $merk->position = @$data->row  ?? 0;
            $merk->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $merk->page = ceil($merk->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $merk
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('merk')->getColumns();

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
            $table->string('kodemerk', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Merk)->getTable())->select(
                'merk.id as id_',
                'merk.kodemerk',
                'merk.keterangan',
                'merk.statusaktif',
                'merk.modifiedby',
                'merk.created_at',
                'merk.updated_at'
            )
                ->orderBy('merk.id', $params['sortorder']);
        } else if ($params['sortname'] == 'keterangan') {
            $query = DB::table((new Merk)->getTable())->select(
                'merk.id as id_',
                'merk.kodemerk',
                'merk.keterangan',
                'merk.statusaktif',
                'merk.modifiedby',
                'merk.created_at',
                'merk.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('merk.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Merk)->getTable())->select(
                    'merk.id as id_',
                    'merk.kodemerk',
                    'merk.keterangan',
                    'merk.statusaktif',
                    'merk.modifiedby',
                    'merk.created_at',
                    'merk.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('merk.id', $params['sortorder']);
            } else {
                $query = DB::table((new Merk)->getTable())->select(
                    'merk.id as id_',
                    'merk.kodemerk',
                    'merk.keterangan',
                    'merk.statusaktif',
                    'merk.modifiedby',
                    'merk.created_at',
                    'merk.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('merk.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodemerk','keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
