<?php

namespace App\Http\Controllers\Api;

use App\Models\BankPelanggan;
use App\Http\Requests\StoreBankPelangganRequest;
use App\Http\Requests\UpdateBankPelangganRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankPelangganController extends Controller
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
        
        $totalRows = BankPelanggan::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = BankPelanggan::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = BankPelanggan::select(
                'bankpelanggan.id',
                'bankpelanggan.kodebank',
                'bankpelanggan.namabank',
                'bankpelanggan.keterangan',
                'parameter.text as statusaktif',
                'bankpelanggan.modifiedby',
                'bankpelanggan.created_at',
                'bankpelanggan.updated_at'
            )
            ->leftJoin('parameter', 'bankpelanggan.statusaktif', '=', 'parameter.id')
            ->orderBy('bankpelanggan.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodebank' or $params['sortIndex'] == 'namabank') {
            $query = BankPelanggan::select(
                'bankpelanggan.id',
                'bankpelanggan.kodebank',
                'bankpelanggan.namabank',
                'bankpelanggan.keterangan',
                'parameter.text as statusaktif',
                'bankpelanggan.modifiedby',
                'bankpelanggan.created_at',
                'bankpelanggan.updated_at'
            )
                ->leftJoin('parameter', 'bankpelanggan.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('bankpelanggan.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = BankPelanggan::select(
                    'bankpelanggan.id',
                    'bankpelanggan.kodebank',
                    'bankpelanggan.namabank',
                    'bankpelanggan.keterangan',
                    'parameter.text as statusaktif',
                    'bankpelanggan.modifiedby',
                    'bankpelanggan.created_at',
                    'bankpelanggan.updated_at'
                )
                    ->leftJoin('parameter', 'bankpelanggan.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('bankpelanggan.id', $params['sortOrder']);
            } else {
                $query = BankPelanggan::select(
                    'bankpelanggan.id',
                    'bankpelanggan.kodebank',
                    'bankpelanggan.namabank',
                    'bankpelanggan.keterangan',
                    'bankpelanggan.text as statusaktif',
                    'bankpelanggan.modifiedby',
                    'bankpelanggan.created_at',
                    'bankpelanggan.updated_at'
                )
                    ->leftJoin('parameter', 'bankpelanggan.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('bankpelanggan.id', 'asc');
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
                            $query = $query->where('bankpelanggan.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('bankpelanggan.'.$search['field'], 'LIKE', "%$search[data]%");
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

        $bankpelanggan = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $bankpelanggan,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }

    public function store(StoreBankPelangganRequest $request)
    {
        DB::beginTransaction();
        try {
            $bankpelanggan = new BankPelanggan();
            $bankpelanggan->kodebank = $request->kodebank;
            $bankpelanggan->namabank = $request->namabank;
            $bankpelanggan->keterangan = $request->keterangan;
            $bankpelanggan->statusaktif = $request->statusaktif;
            $bankpelanggan->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($bankpelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bankpelanggan->getTable()),
                    'postingdari' => 'ENTRY BANK PELANGGAN',
                    'idtrans' => $bankpelanggan->id,
                    'nobuktitrans' => $bankpelanggan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $bankpelanggan->toArray(),
                    'modifiedby' => $bankpelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($bankpelanggan->id, $request, $del);
            $bankpelanggan->position = $data->row;

            if (isset($request->limit)) {
                $bankpelanggan->page = ceil($bankpelanggan->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bankpelanggan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(BankPelanggan $bankpelanggan)
    {
        return response([
            'status' => true,
            'data' => $bankpelanggan
        ]);
    }

    public function edit(BankPelanggan $bankPelanggan)
    {
        //
    }

    public function update(StoreBankPelangganRequest $request, BankPelanggan $bankpelanggan)
    {
        try {
            $bankpelanggan = BankPelanggan::findOrFail($bankpelanggan->id);
            $bankpelanggan->kodebank = $request->kodebank;
            $bankpelanggan->namabank = $request->namabank;
            $bankpelanggan->keterangan = $request->keterangan;
            $bankpelanggan->statusaktif = $request->statusaktif;
            $bankpelanggan->modifiedby = $request->modifiedby;

            if ($bankpelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bankpelanggan->getTable()),
                    'postingdari' => 'EDIT BankPelangganController',
                    'idtrans' => $bankpelanggan->id,
                    'nobuktitrans' => $bankpelanggan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $bankpelanggan->toArray(),
                    'modifiedby' => $bankpelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $bankpelanggan->position = $this->getid($bankpelanggan->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $bankpelanggan->page = ceil($bankpelanggan->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $bankpelanggan
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

    public function destroy(BankPelanggan $bankpelanggan, Request $request)
    {
        $delete = BankPelanggan::destroy($bankpelanggan->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($bankpelanggan->getTable()),
                'postingdari' => 'DELETE BANKPELANGGAN',
                'idtrans' => $bankpelanggan->id,
                'nobuktitrans' => $bankpelanggan->id,
                'aksi' => 'DELETE',
                'datajson' => $bankpelanggan->toArray(),
                'modifiedby' => $bankpelanggan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($bankpelanggan->id, $request, $del);
            $bankpelanggan->position = $data->row ?? 0;
            $bankpelanggan->id = $data->id ?? 0;
            if (isset($request->limit)) {
                $bankpelanggan->page = ceil($bankpelanggan->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $bankpelanggan
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
            $table->string('kodebank', 50)->default('');
            $table->string('namabank', 50)->default('');
            $table->longtext('keterangan')->default('');
            $table->string('statusaktif',300)->default('')->nullable();
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = BankPelanggan::select(
                'bankpelanggan.id as id_',
                'bankpelanggan.kodebank',
                'bankpelanggan.namabank',
                'bankpelanggan.keterangan',
                'bankpelanggan.statusaktif',
                'bankpelanggan.modifiedby',
                'bankpelanggan.created_at',
                'bankpelanggan.updated_at'
            )
                ->orderBy('bankpelanggan.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodebank' or $params['sortname'] == 'namabank') {
            $query = BankPelanggan::select(
                'bankpelanggan.id as id_',
                'bankpelanggan.kodebank',
                'bankpelanggan.namabank',
                'bankpelanggan.keterangan',
                'bankpelanggan.statusaktif',
                'bankpelanggan.modifiedby',
                'bankpelanggan.created_at',
                'bankpelanggan.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('bankpelanggan.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = BankPelanggan::select(
                    'bankpelanggan.id as id_',
                    'bankpelanggan.kodebank',
                    'bankpelanggan.namabank',
                    'bankpelanggan.keterangan',
                    'bankpelanggan.statusaktif',
                    'bankpelanggan.modifiedby',
                    'bankpelanggan.created_at',
                    'bankpelanggan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('bankpelanggan.id', $params['sortorder']);
            } else {
                $query = BankPelanggan::select(
                    'bankpelanggan.id as id_',
                    'bankpelanggan.kodebank',
                    'bankpelanggan.namabank',
                    'bankpelanggan.keterangan',
                    'bankpelanggan.statusaktif',
                    'bankpelanggan.modifiedby',
                    'bankpelanggan.created_at',
                    'bankpelanggan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('bankpelanggan.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodebank', 'namabank', 'keterangan', 'statusaktif','modifiedby', 'created_at', 'updated_at'], $query);


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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('bankpelanggan')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

}
