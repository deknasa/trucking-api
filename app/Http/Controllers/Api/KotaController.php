<?php

namespace App\Http\Controllers\Api;

use App\Models\Kota;
use App\Http\Requests\StoreKotaRequest;
use App\Http\Requests\UpdateKotaRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Zona;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KotaController extends Controller
{
     /**
     * @ClassName 
     */
    public function index()
    {
        $kota = new Kota();

        return response([
            'data' => $kota->get(),
            'attributes' => [
                'totalRows' => $kota->totalRows,
                'totalPages' => $kota->totalPages
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
    public function store(StoreKotaRequest $request)
    {
        DB::beginTransaction();

        try {
            $kota = new Kota();
            $kota->kodekota = $request->kodekota;
            $kota->keterangan = $request->keterangan;
            $kota->zona_id = $request->zona_id;
            $kota->statusaktif = $request->statusaktif;
            $kota->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kota->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kota->getTable()),
                    'postingdari' => 'ENTRY KOTA',
                    'idtrans' => $kota->id,
                    'nobuktitrans' => $kota->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kota->toArray(),
                    'modifiedby' => $kota->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($kota->id, $request, $del);
            $kota->position = @$data->row;

            if (isset($request->limit)) {
                $kota->page = ceil($kota->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kota
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kota $kota)
    {
        return response([
            'status' => true,
            'data' => $kota
        ]);
    }

    public function edit(Kota $kota)
    {
        //
    }
     /**
     * @ClassName 
     */
    public function update(StoreKotaRequest $request, $id)
    {
        try {
            $kota = Kota::findOrFail($id);
            $kota->kodekota = $request->kodekota;
            $kota->keterangan = $request->keterangan;
            $kota->zona_id = $request->zona_id;
            $kota->statusaktif = $request->statusaktif;
            $kota->modifiedby = auth('api')->user()->name;

            if ($kota->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kota->getTable()),
                    'postingdari' => 'EDIT KOTA',
                    'idtrans' => $kota->id,
                    'nobuktitrans' => $kota->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kota->toArray(),
                    'modifiedby' => $kota->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $kota->position = $this->getid($kota->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $kota->page = ceil($kota->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $kota
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
    public function destroy(Kota $kota, Request $request)
    {
        $delete = $kota->delete();

        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($kota->getTable()),
                'postingdari' => 'DELETE KOTA',
                'idtrans' => $kota->id,
                'nobuktitrans' => $kota->id,
                'aksi' => 'DELETE',
                'datajson' => $kota->toArray(),
                'modifiedby' => $kota->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($kota->id, $request, $del);
            $kota->position = @$data->row  ?? 0;
            $kota->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $kota->page = ceil($kota->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kota
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kota')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($kota, $request)
    {
        return DB::table((new Kota)->getTable())->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $kota->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp'=>'status aktif'])->get(),
            'zona' => Zona::all(),
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
            $table->string('kodekota', 50)->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('zona_id', 50)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Kota)->getTable())->select(
                'kota.id as id_',
                'kota.kodekota',
                'kota.keterangan',
                'kota.zona_id',
                'kota.statusaktif',
                'kota.modifiedby',
                'kota.created_at',
                'kota.updated_at'
            )
                ->orderBy('kota.id', $params['sortorder']);
        } else if ($params['sortname'] == 'keterangan') {
            $query = DB::table((new Kota)->getTable())->select(
                'kota.id as id_',
                'kota.kodekota',
                'kota.keterangan',
                'kota.zona_id',
                'kota.statusaktif',
                'kota.modifiedby',
                'kota.created_at',
                'kota.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('kota.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Kota)->getTable())->select(
                    'kota.id as id_',
                    'kota.kodekota',
                    'kota.keterangan',
                    'kota.zona_id',
                    'kota.statusaktif',
                    'kota.modifiedby',
                    'kota.created_at',
                    'kota.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kota.id', $params['sortorder']);
            } else {
                $query = DB::table((new Kota)->getTable())->select(
                    'kota.id as id_',
                    'kota.kodekota',
                    'kota.keterangan',
                    'kota.zona_id',
                    'kota.statusaktif',
                    'kota.modifiedby',
                    'kota.created_at',
                    'kota.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kota.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodekota', 'keterangan', 'zona_id', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
