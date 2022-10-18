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
    public function index()
    {
        $bank = new Bank();
        return response([
            'data' => $bank->get(),
            'attributes' => [
                'totalRows' => $bank->totalRows,
                'totalPages' => $bank->totalPages
            ]
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
            $bank->statusformatpenerimaan = $request->statusformatpenerimaan ?? 0;
            $bank->statusformatpengeluaran = $request->statusformatpengeluaran ?? 0;
            $bank->modifiedby = auth('api')->user()->name;
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
           

            $selected = $this->getPosition($bank, $bank->getTable());
            $bank->position = $selected->position;
            $bank->page = ceil($bank->position / ($request->limit ?? 10));

            
            if (isset($request->limit)) {
                $bank->page = ceil($bank->position / $request->limit);
            }
            
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bank,
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
            $bank->statusformatpenerimaan = $request->statusformatpenerimaan;
            $bank->statusformatpengeluaran = $request->statusformatpengeluaran;
            $bank->modifiedby = auth('api')->user()->name;

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
                
                $selected = $this->getPosition($bank, $bank->getTable());
                $bank->position = $selected->position;
                $bank->page = ceil($bank->position / ($request->limit ?? 10));

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

            $selected = $this->getPosition($bank, $bank->getTable(), true);
            $bank->position = $selected->position;
            $bank->id = $selected->id;
            $bank->page = ceil($bank->position / ($request->limit ?? 10));

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
            // 'statusformatpenerimaan' => Parameter::where(['grp' => 'PENERIMAAN KAS'])->get(),
            // 'statusformatpengeluaran' => Parameter::where(['grp' => 'PENGELUARAN KAS'])->get(),
            // 'akunpusat' => AkunPusat::all(),
        ];

        return response([
            'data' => $data
        ]);
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
