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
    
       /**
     * @ClassName 
     */
    public function index()
    {
        $bankpelanggan = new BankPelanggan();

        return response([
            'data' => $bankpelanggan->get(),
            'attributes' => [
                'totalRows' => $bankpelanggan->totalRows,
                'totalPages' => $bankpelanggan->totalPages
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
    public function store(StoreBankPelangganRequest $request)
    {
        DB::beginTransaction();
        try {
            $bankpelanggan = new BankPelanggan();
            $bankpelanggan->kodebank = $request->kodebank;
            $bankpelanggan->namabank = $request->namabank;
            $bankpelanggan->keterangan = $request->keterangan;
            $bankpelanggan->statusaktif = $request->statusaktif;
            $bankpelanggan->modifiedby = auth('api')->user()->name;
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
            $selected = $this->getPosition($bankpelanggan, $bankpelanggan->getTable());
            $bankpelanggan->position = $selected->position;
            $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10 ));

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
   /**
     * @ClassName 
     */
    public function update(StoreBankPelangganRequest $request, BankPelanggan $bankpelanggan)
    {
        try {
            $bankpelanggan = BankPelanggan::findOrFail($bankpelanggan->id);
            $bankpelanggan->kodebank = $request->kodebank;
            $bankpelanggan->namabank = $request->namabank;
            $bankpelanggan->keterangan = $request->keterangan;
            $bankpelanggan->statusaktif = $request->statusaktif;
            $bankpelanggan->modifiedby = auth('api')->user()->name;

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
               
                $selected = $this->getPosition($bankpelanggan, $bankpelanggan->getTable());
                $bankpelanggan->position = $selected->position;
                $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10 ));

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
   /**
     * @ClassName 
     */
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

            
            $selected = $this->getPosition($bankpelanggan, $bankpelanggan->getTable(), true);
            $bankpelanggan->position = $selected->position;
            $bankpelanggan->id = $selected->id;
            $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10 ));
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
