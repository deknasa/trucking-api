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
use Illuminate\Database\QueryException;

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

    public function cekValidasi($id) {
        $bankPelanggan= new BankPelanggan();
        $cekdata=$bankPelanggan->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
                )
            ->where('kodeerror', '=', 'SATL')
            ->get();
        $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
         
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data); 
        }
    }
    public function default()
    {
        $bankPelanggan = new BankPelanggan();
        return response([
            'status' => true,
            'data' => $bankPelanggan->default()
        ]);
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
            $bankpelanggan->keterangan = $request->keterangan ?? '';
            $bankpelanggan->statusaktif = $request->statusaktif;
            $bankpelanggan->modifiedby = auth('api')->user()->name;

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
            $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bankpelanggan
            ], 201);
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

    /**
     * @ClassName 
     */
    public function update(StoreBankPelangganRequest $request, BankPelanggan $bankpelanggan)
    {
        DB::beginTransaction();
        try {
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

                DB::commit();

                /* Set position and page */

                $selected = $this->getPosition($bankpelanggan, $bankpelanggan->getTable());
                $bankpelanggan->position = $selected->position;
                $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $bankpelanggan
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $bankPelanggan = new BankPelanggan();
        $bankPelanggan = $bankPelanggan->lockAndDestroy($id);
        if ($bankPelanggan) {
            $logTrail = [
                'namatabel' => strtoupper($bankPelanggan->getTable()),
                'postingdari' => 'DELETE BANKPELANGGAN',
                'idtrans' => $bankPelanggan->id,
                'nobuktitrans' => $bankPelanggan->id,
                'aksi' => 'DELETE',
                'datajson' => $bankPelanggan->toArray(),
                'modifiedby' => $bankPelanggan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            $selected = $this->getPosition($bankPelanggan, $bankPelanggan->getTable(), true);
            $bankPelanggan->position = $selected->position;
            $bankPelanggan->id = $selected->id;
            $bankPelanggan->page = ceil($bankPelanggan->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $bankPelanggan
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
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
