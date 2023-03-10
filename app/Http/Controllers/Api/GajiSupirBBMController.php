<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\GajiSupirPelunasanPinjaman;
use App\Http\Requests\StoreGajiSupirBBMRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateGajiSupirBBMRequest;
use App\Models\GajiSupirBBM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiSupirBBMController extends Controller
{

    public function index()
    {
        //
    }

    public function store(StoreGajiSupirBBMRequest $request)
    {
        DB::beginTransaction();

        try {
            $gajiSupir = new GajiSupirBBM();
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
                'postingdari' => 'ENTRY GAJI SUPIR BBM',
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


    public function update(UpdateGajiSupirBBMRequest $request, GajiSupirBBM $gajisupirdeposito)
    {
        DB::beginTransaction();

        try {
            $gajisupirdeposito->supir_id = $request->supir_id;
            $gajisupirdeposito->nominal = $request->nominal;
            $gajisupirdeposito->modifiedby = auth('api')->user()->name;

            $gajisupirdeposito->save();
            $logTrail = [
                'namatabel' => strtoupper($gajisupirdeposito->getTable()),
                'postingdari' => 'EDIT GAJI SUPIR BBM',
                'idtrans' => $gajisupirdeposito->id,
                'nobuktitrans' => $gajisupirdeposito->id,
                'aksi' => 'EDIT',
                'datajson' => $gajisupirdeposito->toArray(),
                'modifiedby' => $gajisupirdeposito->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gajisupirdeposito
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy(Request $request, $id)
    {
        
        DB::beginTransaction();

        $gajisupir = new GajiSupirBBM();
        $gajisupir = $gajisupir->lockAndDestroy($id);

        if ($gajisupir) {
            $logTrail = [
                'namatabel' => strtoupper($gajisupir->getTable()),
                'postingdari' => 'DELETE GAJI SUPIR BBM',
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
