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

    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Kota::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = Kota::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = Kota::select(
                'kota.id',
                'kota.kodekota',
                'kota.keterangan',
                'zona.zona',
                'parameter.text as statusaktif',
                'kota.modifiedby',
                'kota.created_at',
                'kota.updated_at'
            )
            ->leftJoin('parameter', 'kota.statusaktif', '=', 'parameter.id')
            ->leftJoin('zona', 'kota.zona_id', '=', 'zona.id')
            ->orderBy('kota.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'keterangan') {
            $query = Kota::select(
                'kota.id',
                'kota.kodekota',
                'kota.keterangan',
                'zona.zona',
                'parameter.text as statusaktif',
                'kota.modifiedby',
                'kota.created_at',
                'kota.updated_at'
            )
                ->leftJoin('parameter', 'kota.statusaktif', '=', 'parameter.id')
                ->leftJoin('zona', 'kota.zona_id', '=', 'zona.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('kota.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Kota::select(
                    'kota.id',
                    'kota.kodekota',
                    'kota.keterangan',
                    'zona.zona',
                    'parameter.text as statusaktif',
                    'kota.modifiedby',
                    'kota.created_at',
                    'kota.updated_at'
                )
                    ->leftJoin('parameter', 'kota.statusaktif', '=', 'parameter.id')
                    ->leftJoin('zona', 'kota.zona_id', '=', 'zona.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('kota.id', $params['sortOrder']);
            } else {
                $query = Kota::select(
                    'kota.id',
                    'kota.kodekota',
                    'kota.keterangan',
                    'zona.zona',
                    'parameter.text as statusaktif',
                    'kota.modifiedby',
                    'kota.created_at',
                    'kota.updated_at'
                )
                    ->leftJoin('parameter', 'kota.statusaktif', '=', 'parameter.id')
                    ->leftJoin('zona', 'kota.zona_id', '=', 'zona.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('kota.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('kota.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('kota.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }
                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $kota = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $kota,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }


    public function create()
    {
        //
    }

    public function store(StoreKotaRequest $request)
    {
        DB::beginTransaction();

        try {
            $kota = new Kota();
            $kota->kodekota = $request->kodekota;
            $kota->keterangan = $request->keterangan;
            $kota->zona_id = $request->zona_id;
            $kota->statusaktif = $request->statusaktif;
            $kota->modifiedby = $request->modifiedby;
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

    public function show(Kota $Kota,$id)
    {
        return response([
            'status' => true,
            'data' => Kota::find($id)->first()
        ]);
    }

    public function edit(Kota $kota)
    {
        //
    }

    public function update(StoreKotaRequest $request, $id)
    {
        try {
            $kota = Kota::findOrFail($id);
            $kota->kodekota = $request->kodekota;
            $kota->keterangan = $request->keterangan;
            $kota->zona_id = $request->zona_id;
            $kota->statusaktif = $request->statusaktif;
            $kota->modifiedby = $request->modifiedby;

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

    public function destroy($id, Request $request)
    {
        $kota = Kota::find($id)->first();
        
        $delete = Kota::destroy($kota->id);
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
            $kota->position = @$data->row;
            $kota->id = @$data->id;
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
        return Kota::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $kota->{$request->sortname})
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
            $query = Kota::select(
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
            $query = Kota::select(
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
                $query = Kota::select(
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
                $query = Kota::select(
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
