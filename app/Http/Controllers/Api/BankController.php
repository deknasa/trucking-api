<?php

namespace App\Http\Controllers\Api;

use App\Models\Bank;
use App\Models\AkunPusat;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\UpdateBankRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Bank::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = Bank::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = Bank::select(
                'bank.id',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'parameter.text as statusaktif',
                'bank.kodepenerimaan',
                'bank.kodepengeluaran',
                'bank.modifiedby',
                'bank.created_at',
                'bank.updated_at'
            )
                ->leftJoin('parameter', 'bank.statusaktif', '=', 'parameter.id')
                ->orderBy('bank.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodebank' or $params['sortIndex'] == 'namabank') {
            $query = Bank::select(
                'bank.id',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'parameter.text as statusaktif',
                'bank.kodepenerimaan',
                'bank.kodepengeluaran',
                'bank.modifiedby',
                'bank.created_at',
                'bank.updated_at'
            )
                ->leftJoin('parameter', 'bank.statusaktif', '=', 'parameter.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('bank.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Bank::select(
                    'bank.id',
                    'bank.kodebank',
                    'bank.namabank',
                    'bank.coa',
                    'bank.tipe',
                    'parameter.text as statusaktif',
                    'bank.kodepenerimaan',
                    'bank.kodepengeluaran',
                    'bank.modifiedby',
                    'bank.created_at',
                    'bank.updated_at'
                )
                    ->leftJoin('parameter', 'bank.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('bank.id', $params['sortOrder']);
            } else {
                $query = Bank::select(
                    'bank.id',
                    'bank.kodebank',
                    'bank.namabank',
                    'bank.coa',
                    'bank.tipe',
                    'parameter.text as statusaktif',
                    'bank.kodepenerimaan',
                    'bank.kodepengeluaran',
                    'bank.modifiedby',
                    'bank.created_at',
                    'bank.updated_at'
                )
                    ->leftJoin('parameter', 'bank.statusaktif', '=', 'parameter.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('bank.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
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

        $banks = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $banks,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreBankRequest $request)
    {
        DB::beginTransaction();
        try {
            $bank = new Bank();
            $bank->kodebank = $request->kodebank;
            $bank->namabank = $request->namabank;
            $bank->coa = $request->coa;
            $bank->tipe = $request->tipe;
            $bank->statusaktif = $request->statusaktif;
            $bank->kodepenerimaan = $request->kodepenerimaan;
            $bank->kodepengeluaran = $request->kodepengeluaran;
            $bank->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($bank->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bank->getTable()),
                    'postingdari' => 'ENTRY BANK',
                    'idtrans' => $bank->id,
                    'nobuktitrans' => $bank->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $bank->toArray(),
                    'modifiedby' => $bank->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($bank->id, $request, $del);
            $bank->position = $data->row;

            if (isset($request->limit)) {
                $bank->page = ceil($bank->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bank
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Bank $bank)
    {
        return response([
            'status' => true,
            'data' => $bank
        ]);
    }

    public function edit(Bank $bank)
    {
        //
    }
    /**
     * @ClassName 
     */
    public function update(StoreBankRequest $request, Bank $bank)
    {
        try {
            $bank = Bank::findOrFail($bank->id);
            $bank->kodebank = $request->kodebank;
            $bank->namabank = $request->namabank;
            $bank->coa = $request->coa;
            $bank->tipe = $request->tipe;
            $bank->statusaktif = $request->statusaktif;
            $bank->kodepenerimaan = $request->kodepenerimaan;
            $bank->kodepengeluaran = $request->kodepengeluaran;
            $bank->modifiedby = $request->modifiedby;

            if ($bank->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bank->getTable()),
                    'postingdari' => 'EDIT BANK',
                    'idtrans' => $bank->id,
                    'nobuktitrans' => $bank->id,
                    'aksi' => 'EDIT',
                    'datajson' => $bank->toArray(),
                    'modifiedby' => $bank->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $bank->position = $this->getid($bank->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $bank->page = ceil($bank->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $bank
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
    public function destroy(Bank $bank, Request $request)
    {
        $delete = Bank::destroy($bank->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($bank->getTable()),
                'postingdari' => 'DELETE BANK',
                'idtrans' => $bank->id,
                'nobuktitrans' => $bank->id,
                'aksi' => 'DELETE',
                'datajson' => $bank->toArray(),
                'modifiedby' => $bank->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($bank->id, $request, $del);
            $bank->position = $data->row;
            $bank->id = $data->id;
            if (isset($request->limit)) {
                $bank->page = ceil($bank->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $bank
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
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),
            'akunpusat' => AkunPusat::all(),
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
            $table->string('coa', 50)->default('');
            $table->string('tipe', 50)->default('');
            $table->string('statusaktif', 300)->default('')->nullable();
            $table->string('kodepenerimaan', 50)->default('');
            $table->string('kodepengeluaran', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Bank::select(
                'bank.id as id_',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'bank.statusaktif',
                'bank.kodepenerimaan',
                'bank.kodepengeluaran',
                'bank.modifiedby',
                'bank.created_at',
                'bank.updated_at'
            )
                ->orderBy('bank.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodebank' or $params['sortname'] == 'namabank') {
            $query = Bank::select(
                'bank.id as id_',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'bank.statusaktif',
                'bank.kodepenerimaan',
                'bank.kodepengeluaran',
                'bank.modifiedby',
                'bank.created_at',
                'bank.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('bank.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Bank::select(
                    'bank.id as id_',
                    'bank.kodebank',
                    'bank.namabank',
                    'bank.coa',
                    'bank.tipe',
                    'bank.statusaktif',
                    'bank.kodepenerimaan',
                    'bank.kodepengeluaran',
                    'bank.modifiedby',
                    'bank.created_at',
                    'bank.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('bank.id', $params['sortorder']);
            } else {
                $query = Bank::select(
                    'bank.id as id_',
                    'bank.kodebank',
                    'bank.namabank',
                    'bank.coa',
                    'bank.tipe',
                    'bank.statusaktif',
                    'bank.kodepenerimaan',
                    'bank.kodepengeluaran',
                    'bank.modifiedby',
                    'bank.created_at',
                    'bank.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('bank.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodebank', 'namabank', 'coa', 'tipe', 'statusaktif', 'kodepenerimaan', 'kodepengeluaran', 'modifiedby', 'created_at', 'updated_at'], $query);


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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('bank')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
