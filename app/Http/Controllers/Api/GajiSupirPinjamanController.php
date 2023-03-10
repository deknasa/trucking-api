<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\GajiSupirPelunasanPinjaman;
use App\Http\Requests\StoreGajiSupirPinjamanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateGajiSupirPinjamanRequest;
use App\Models\GajiSupirPinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiSupirPinjamanController extends Controller
{

    public function index()
    {
        //
    }

    public function store(StoreGajiSupirPinjamanRequest $request)
    {
        DB::beginTransaction();

        try {
            $gajiSupir = new GajiSupirPinjaman();
            $gajiSupir->gajisupir_id = $request->gajisupir_id;
            $gajiSupir->gajisupir_nobukti = $request->gajisupir_nobukti;
            $gajiSupir->penerimaantrucking_nobukti = $request->penerimaantrucking_nobukti ?? '';
            $gajiSupir->pengeluarantrucking_nobukti = $request->pengeluarantrucking_nobukti ?? '';
            $gajiSupir->supir_id = $request->supir_id;
            $gajiSupir->nominal = $request->nominal;
            $gajiSupir->modifiedby = auth('api')->user()->name;

            $gajiSupir->save();
            $logTrail = [
                'namatabel' => strtoupper($gajiSupir->getTable()),
                'postingdari' => 'ENTRY GAJI SUPIR PINJAMAN',
                'idtrans' => $gajiSupir->id,
                'nobuktitrans' => $gajiSupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $gajiSupir->toArray(),
                'modifiedby' => $gajiSupir->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gajiSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function update(UpdateGajiSupirPinjamanRequest $request, GajiSupirPinjaman $gajisupirpinjaman)
    {
        DB::beginTransaction();

        try {
            $gajisupirpinjaman->supir_id = $request->supir_id;
            $gajisupirpinjaman->nominal = $request->nominal;
            $gajisupirpinjaman->modifiedby = auth('api')->user()->name;

            $gajisupirpinjaman->save();
            $logTrail = [
                'namatabel' => strtoupper($gajisupirpinjaman->getTable()),
                'postingdari' => 'EDIT GAJI SUPIR PINJAMAN',
                'idtrans' => $gajisupirpinjaman->id,
                'nobuktitrans' => $gajisupirpinjaman->id,
                'aksi' => 'EDIT',
                'datajson' => $gajisupirpinjaman->toArray(),
                'modifiedby' => $gajisupirpinjaman->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gajisupirpinjaman
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy(Request $request, $id)
    {
        
        DB::beginTransaction();

        $gajisupir = new GajiSupirPinjaman();
        $gajisupir = $gajisupir->lockAndDestroy($id);

        if ($gajisupir) {
            $logTrail = [
                'namatabel' => strtoupper($gajisupir->getTable()),
                'postingdari' => 'DELETE GAJI SUPIR PINJAMAN',
                'idtrans' => $gajisupir->id,
                'nobuktitrans' => $gajisupir->id,
                'aksi' => 'DELETE',
                'datajson' => $gajisupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
           
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gajisupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
}
