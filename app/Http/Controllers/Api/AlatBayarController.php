<?php

namespace App\Http\Controllers\Api;

use App\Models\AlatBayar;
use App\Models\Bank;
use App\Http\Requests\StoreAlatBayarRequest;
use App\Http\Requests\UpdateAlatBayarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AlatBayarController extends Controller
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

        $totalRows = DB::table((new AlatBayar)->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new AlatBayar)->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new AlatBayar)->getTable())->select(
                'alatbayar.id',
                'alatbayar.kodealatbayar',
                'alatbayar.namaalatbayar',
                'alatbayar.keterangan',
                'alatbayar.statuslangsunggcair',
                'alatbayar.statusdefault',
                'alatbayar.bank_id',
                'alatbayar.modifiedby',
                'alatbayar.created_at',
                'alatbayar.updated_at'
            )->orderBy('alatbayar.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodebank' or $params['sortIndex'] == 'namabank') {
            $query = DB::table((new AlatBayar)->getTable())->select(
                'alatbayar.id',
                'alatbayar.kodealatbayar',
                'alatbayar.namaalatbayar',
                'alatbayar.keterangan',
                'alatbayar.statuslangsunggcair',
                'alatbayar.statusdefault',
                'alatbayar.bank_id',
                'alatbayar.modifiedby',
                'alatbayar.created_at',
                'alatbayar.updated_at'
            )
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('alatbayar.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new AlatBayar)->getTable())->select(
                    'alatbayar.id',
                    'alatbayar.kodealatbayar',
                    'alatbayar.namaalatbayar',
                    'alatbayar.keterangan',
                    'alatbayar.statuslangsunggcair',
                    'alatbayar.statusdefault',
                    'alatbayar.bank_id',
                    'alatbayar.modifiedby',
                    'alatbayar.created_at',
                    'alatbayar.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('alatbayar.id', $params['sortOrder']);
            } else {
                $query = DB::table((new AlatBayar)->getTable())->select(
                    'alatbayar.id',
                    'alatbayar.kodealatbayar',
                    'alatbayar.namaalatbayar',
                    'alatbayar.keterangan',
                    'alatbayar.statuslangsunggcair',
                    'alatbayar.statusdefault',
                    'alatbayar.bank_id',
                    'alatbayar.modifiedby',
                    'alatbayar.created_at',
                    'alatbayar.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('alatbayar.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
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

        $alatbayar = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $alatbayar,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreAlatBayarRequest $request)
    {
        DB::beginTransaction();
        try {
            $alatbayar = new AlatBayar();
            $alatbayar->kodealatbayar = $request->kodealatbayar;
            $alatbayar->namaalatbayar = $request->namaalatbayar;
            $alatbayar->keterangan = $request->keterangan;
            $alatbayar->statuslangsunggcair = $request->statuslangsungcair;
            $alatbayar->statusdefault = $request->statusdefault;
            $alatbayar->bank_id = $request->bank_id;
            $alatbayar->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($alatbayar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($alatbayar->getTable()),
                    'postingdari' => 'ENTRY ALATBAYAR',
                    'idtrans' => $alatbayar->id,
                    'nobuktitrans' => $alatbayar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $alatbayar->toArray(),
                    'modifiedby' => $alatbayar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($alatbayar->id, $request, $del);
            $alatbayar->position = $data->row;

            if (isset($request->limit)) {
                $alatbayar->page = ceil($alatbayar->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $alatbayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(AlatBayar $alatbayar)
    {
        return response([
            'status' => true,
            'data' => $alatbayar
        ]);
    }

    public function edit(AlatBayar $alatBayar)
    {
        //
    }
    /**
     * @ClassName 
     */
    public function update(StoreAlatBayarRequest $request, AlatBayar $alatbayar)
    {
        try {
            $alatbayar = DB::table((new AlatBayar)->getTable())->findOrFail($alatbayar->id);
            $alatbayar->kodealatbayar = $request->kodealatbayar;
            $alatbayar->namaalatbayar = $request->namaalatbayar;
            $alatbayar->keterangan = $request->keterangan;
            $alatbayar->statuslangsunggcair = $request->statuslangsungcair;
            $alatbayar->statusdefault = $request->statusdefault;
            $alatbayar->bank_id = $request->bank_id;
            $alatbayar->modifiedby = auth('api')->user()->name;

            if ($alatbayar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($alatbayar->getTable()),
                    'postingdari' => 'EDIT ALATBAYAR',
                    'idtrans' => $alatbayar->id,
                    'nobuktitrans' => $alatbayar->id,
                    'aksi' => 'EDIT',
                    'datajson' => $alatbayar->toArray(),
                    'modifiedby' => $alatbayar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $alatbayar->position = $this->getid($alatbayar->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $alatbayar->page = ceil($alatbayar->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $alatbayar
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
    public function destroy(AlatBayar $alatbayar, Request $request)
    {
        $delete = AlatBayar::destroy($alatbayar->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($alatbayar->getTable()),
                'postingdari' => 'DELETE ALATBAYAR',
                'idtrans' => $alatbayar->id,
                'nobuktitrans' => $alatbayar->id,
                'aksi' => 'DELETE',
                'datajson' => $alatbayar->toArray(),
                'modifiedby' => $alatbayar->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($alatbayar->id, $request, $del);
            $alatbayar->position = $data->row;
            $alatbayar->id = $data->id;
            if (isset($request->limit)) {
                $alatbayar->page = ceil($alatbayar->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $alatbayar
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
            'langsungcair' => Parameter::where(['grp' => 'status langsung cair'])->get(),
            'statusdefault' => Parameter::where(['grp' => 'status default'])->get(),
            'bank' => Bank::all(),
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
            $table->string('kodealatbayar', 50)->default('');
            $table->string('namaalatbayar', 50)->default('');
            $table->longtext('keterangan')->default('');
            $table->string('statuslangsunggcair', 300)->default('')->nullable();
            $table->string('statusdefault', 300)->default('')->nullable();
            $table->string('bank_id', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new AlatBayar)->getTable())->select(
                'alatbayar.id as id_',
                'alatbayar.kodealatbayar',
                'alatbayar.namaalatbayar',
                'alatbayar.keterangan',
                'alatbayar.statuslangsunggcair',
                'alatbayar.statusdefault',
                'alatbayar.bank_id',
                'alatbayar.modifiedby',
                'alatbayar.created_at',
                'alatbayar.updated_at'
            )
                ->orderBy('alatbayar.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodealatbayar' or $params['sortname'] == 'namabank') {
            $query = DB::table((new AlatBayar)->getTable())->select(
                'alatbayar.id as id_',
                'alatbayar.kodealatbayar',
                'alatbayar.namaalatbayar',
                'alatbayar.keterangan',
                'alatbayar.statuslangsunggcair',
                'alatbayar.statusdefault',
                'alatbayar.bank_id',
                'alatbayar.modifiedby',
                'alatbayar.created_at',
                'alatbayar.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('alatbayar.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new AlatBayar)->getTable())->select(
                    'alatbayar.id as id_',
                    'alatbayar.kodealatbayar',
                    'alatbayar.namaalatbayar',
                    'alatbayar.keterangan',
                    'alatbayar.statuslangsunggcair',
                    'alatbayar.statusdefault',
                    'alatbayar.bank_id',
                    'alatbayar.modifiedby',
                    'alatbayar.created_at',
                    'alatbayar.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('alatbayar.id', $params['sortorder']);
            } else {
                $query = DB::table((new AlatBayar)->getTable())->select(
                    'alatbayar.id as id_',
                    'alatbayar.kodealatbayar',
                    'alatbayar.namaalatbayar',
                    'alatbayar.keterangan',
                    'alatbayar.statuslangsunggcair',
                    'alatbayar.statusdefault',
                    'alatbayar.bank_id',
                    'alatbayar.modifiedby',
                    'alatbayar.created_at',
                    'alatbayar.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('alatbayar.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodealatbayar', 'namaalatbayar', 'keterangan', 'statuslangsunggcair', 'statusdefault', 'bank_id', 'modifiedby', 'created_at', 'updated_at'], $query);


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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('alatbayar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
