<?php

namespace App\Http\Controllers\Api;

use App\Models\Tarif;
use App\Http\Requests\StoreTarifRequest;
use App\Http\Requests\UpdateTarifRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Container;
use App\Models\Kota;
use App\Models\Zona;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TarifController extends Controller
{
 /**
     * @ClassName 
     */
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $totalRows = DB::table((new Tarif())->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new Tarif())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Tarif())->getTable())->select(
                'tarif.id',
                'tarif.tujuan',
                'container.keterangan as container_id',
                'tarif.nominal',
                'parameter.text as statusaktif',
                'tarif.tujuanasal',
                'tarif.sistemton',
                'kota.kodekota as kota_id',
                'zona.zona as zona_id',
                'tarif.nominalton',
                'tarif.tglberlaku',
                'p.text as statuspenyesuaianharga',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at'
            )
            ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin('container', 'tarif.container_id', '=', 'container.id')
            ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'parameter.id')
            ->orderBy('tarif.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'tujuan' or $params['sortIndex'] == 'container_id') {
            $query = DB::table((new Tarif())->getTable())->select(
                'tarif.id',
                'tarif.tujuan',
                'container.keterangan as container_id',
                'tarif.nominal',
                'parameter.text as statusaktif',
                'tarif.tujuanasal',
                'tarif.sistemton',
                'kota.kodekota as kota_id',
                'zona.zona as zona_id',
                'tarif.nominalton',
                'tarif.tglberlaku',
                'p.text as statuspenyesuaianharga',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at'
            )
                ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
                ->leftJoin('container', 'tarif.container_id', '=', 'container.id')
                ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
                ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
                ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('tarif.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Tarif())->getTable())->select(
                    'tarif.id',
                    'tarif.tujuan',
                    'container.keterangan as container_id',
                    'tarif.nominal',
                    'parameter.text as statusaktif',
                    'tarif.tujuanasal',
                    'tarif.sistemton',
                    'kota.kodekota as kota_id',
                    'zona.zona as zona_id',
                    'tarif.nominalton',
                    'tarif.tglberlaku',
                    'p.text as statuspenyesuaianharga',
                    'tarif.modifiedby',
                    'tarif.created_at',
                    'tarif.updated_at'
                )
                    ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
                    ->leftJoin('container', 'tarif.container_id', '=', 'container.id')
                    ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
                    ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
                    ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('tarif.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Tarif())->getTable())->select(
                    'tarif.id',
                    'tarif.tujuan',
                    'container.keterangan as container_id',
                    'tarif.nominal',
                    'parameter.text as statusaktif',
                    'tarif.tujuanasal',
                    'tarif.sistemton',
                    'kota.kodekota as kota_id',
                    'zona.zona as zona_id',
                    'tarif.nominalton',
                    'tarif.tglberlaku',
                    'p.text as statuspenyesuaianharga',
                    'tarif.modifiedby',
                    'tarif.created_at',
                    'tarif.updated_at'
                )
                    ->leftJoin('parameter', 'tarif.statusaktif', '=', 'parameter.id')
                    ->leftJoin('container', 'tarif.container_id', '=', 'container.id')
                    ->leftJoin('kota', 'tarif.kota_id', '=', 'kota.id')
                    ->leftJoin('zona', 'tarif.zona_id', '=', 'zona.id')
                    ->leftJoin('parameter AS p', 'tarif.statuspenyesuaianharga', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('tarif.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('tarif.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('tarif.'.$search['field'], 'LIKE', "%$search[data]%");
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

        $tarif = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $tarif,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }
 /**
     * @ClassName 
     */
    public function store(StoreTarifRequest $request)
    {
        DB::beginTransaction();

        try {
            $tarif = new Tarif();
            $tarif->tujuan = $request->tujuan;
            $tarif->container_id = $request->container_id;
            $tarif->nominal = $request->nominal;
            $tarif->statusaktif = $request->statusaktif;
            $tarif->tujuanasal = $request->tujuanasal;
            $tarif->sistemton = $request->sistemton;
            $tarif->kota_id = $request->kota_id;
            $tarif->zona_id = $request->zona_id;
            $tarif->nominalton = $request->nominalton;
            $tarif->tglberlaku = date('Y-m-d', strtotime($request->tglberlaku));
            $tarif->statuspenyesuaianharga = $request->statuspenyesuaianharga;
            $tarif->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($tarif->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'ENTRY TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($tarif->id, $request, $del);
            $tarif->position = $data->row;

            if (isset($request->limit)) {
                $tarif->page = ceil($tarif->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tarif
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Tarif $tarif)
    {
        return response([
            'status' => true,
            'data' => $tarif
        ]);
    }

    public function edit(Tarif $tarif)
    {
        //
    }
 /**
     * @ClassName 
     */
    public function update(StoreTarifRequest $request, Tarif $tarif)
    {
        try {
            $tarif = Tarif::findOrFail($tarif->id);
            $tarif->tujuan = $request->tujuan;
            $tarif->container_id = $request->container_id;
            $tarif->nominal = $request->nominal;
            $tarif->statusaktif = $request->statusaktif;
            $tarif->tujuanasal = $request->tujuanasal;
            $tarif->sistemton = $request->sistemton;
            $tarif->kota_id = $request->kota_id;
            $tarif->zona_id = $request->zona_id;
            $tarif->nominalton = $request->nominalton;
            $tarif->tglberlaku = date('Y-m-d', strtotime($request->tglberlaku));
            $tarif->statuspenyesuaianharga = $request->statuspenyesuaianharga;
            $tarif->modifiedby = auth('api')->user()->name;

            if ($tarif->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'EDIT TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'EDIT',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $tarif->position = $this->getid($tarif->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $tarif->page = ceil($tarif->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $tarif
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
    public function destroy(Tarif $tarif, Request $request)
    {
        $delete = Tarif::destroy($tarif->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($tarif->getTable()),
                'postingdari' => 'DELETE TARIF',
                'idtrans' => $tarif->id,
                'nobuktitrans' => $tarif->id,
                'aksi' => 'DELETE',
                'datajson' => $tarif->toArray(),
                'modifiedby' => $tarif->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($tarif->id, $request, $del);
            $tarif->position = $data->row  ?? 0;
            $tarif->id = $data->id  ?? 0;
            if (isset($request->limit)) {
                $tarif->page = ceil($tarif->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $tarif
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tarif')->getColumns();

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
            'container' => Container::all(),
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'statusaktif' => Parameter::where(['grp'=>'status aktif'])->get(),
            'statuspenyesuaianharga' => Parameter::where(['grp'=>'status penyesuaian harga'])->get(),
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
            $table->string('tujuan', 50)->default('');
            $table->string('container_id', 50)->default('');
            $table->string('nominal', 50)->default('0');
            $table->string('statusaktif', 50)->default('');
            $table->string('tujuanasal', 50)->default('');
            $table->string('sistemton', 50)->default('');
            $table->string('kota_id', 50)->default('');
            $table->string('zona_id', 50)->default('');
            $table->string('nominalton', 50)->default('0');
            $table->date('tglberlaku', 50)->default('1900/1/1');
            $table->string('statuspenyesuaianharga', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });
        
        if ($params['sortname'] == 'id') {
            $query = DB::table((new Tarif())->getTable())->select(
                'tarif.id as id_',
                'tarif.tujuan',
                'tarif.container_id',
                'tarif.nominal',
                'tarif.statusaktif',
                'tarif.tujuanasal',
                'tarif.sistemton',
                'tarif.kota_id',
                'tarif.zona_id',
                'tarif.nominalton',
                'tarif.tglberlaku',
                'tarif.statuspenyesuaianharga',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at'
            )
                ->orderBy('tarif.id', $params['sortorder']);
        } else if ($params['sortname'] == 'tujuan' or $params['sortname'] == 'container_id') {
            $query = DB::table((new Tarif())->getTable())->select(
                'tarif.id as id_',
                'tarif.tujuan',
                'tarif.container_id',
                'tarif.nominal',
                'tarif.statusaktif',
                'tarif.tujuanasal',
                'tarif.sistemton',
                'tarif.kota_id',
                'tarif.zona_id',
                'tarif.nominalton',
                'tarif.tglberlaku',
                'tarif.statuspenyesuaianharga',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('tarif.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Tarif())->getTable())->select(
                    'tarif.id as id_',
                    'tarif.tujuan',
                    'tarif.container_id',
                    'tarif.nominal',
                    'tarif.statusaktif',
                    'tarif.tujuanasal',
                    'tarif.sistemton',
                    'tarif.kota_id',
                    'tarif.zona_id',
                    'tarif.nominalton',
                    'tarif.tglberlaku',
                    'tarif.statuspenyesuaianharga',
                    'tarif.modifiedby',
                    'tarif.created_at',
                    'tarif.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('tarif.id', $params['sortorder']);
            } else {
                $query = DB::table((new Tarif())->getTable())->select(
                    'tarif.id as id_',
                    'tarif.tujuan',
                    'tarif.container_id',
                    'tarif.nominal',
                    'tarif.statusaktif',
                    'tarif.tujuanasal',
                    'tarif.sistemton',
                    'tarif.kota_id',
                    'tarif.zona_id',
                    'tarif.nominalton',
                    'tarif.tglberlaku',
                    'tarif.statuspenyesuaianharga',
                    'tarif.modifiedby',
                    'tarif.created_at',
                    'tarif.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('tarif.id', 'asc');
            }
        }

        DB::table($temp)->insertUsing(['id_', 'tujuan', 'container_id','nominal', 'statusaktif','tujuanasal','sistemton','kota_id','zona_id','nominalton','tglberlaku','statuspenyesuaianharga', 'modifiedby', 'created_at', 'updated_at'], $query);


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
