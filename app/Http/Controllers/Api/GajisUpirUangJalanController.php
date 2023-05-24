<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\GajisUpirUangJalan;
use App\Http\Requests\StoreGajisUpirUangJalanRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateGajisUpirUangJalanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajisUpirUangJalanController extends Controller
{
    
    public function index()
    {
        //
    }

    public function store(StoreGajisUpirUangJalanRequest $request)
    {
        DB::beginTransaction();
        
        try{
            $uangJalan = new GajisUpirUangJalan();
            $uangJalan->gajisupir_id = $request->gajisupir_id;
            $uangJalan->gajisupir_nobukti = $request->gajisupir_nobukti;
            $uangJalan->absensisupir_nobukti = $request->absensisupir_nobukti;
            $uangJalan->supir_id = $request->supir_id;
            $uangJalan->nominal = $request->nominal;
            $uangJalan->save();

            $logTrail = [
                'namatabel' => strtoupper($uangJalan->getTable()),
                'postingdari' => 'ENTRY GAJI SUPIR UANG JALAN',
                'idtrans' => $uangJalan->id,
                'nobuktitrans' => $uangJalan->id,
                'aksi' => 'ENTRY',
                'datajson' => $uangJalan->toArray(),
                'modifiedby' => $uangJalan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $uangJalan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(UpdateGajisUpirUangJalanRequest $request, GajisUpirUangJalan $gajisupiruangjalan)
    {
        DB::beginTransaction();

        try {
            GajisUpirUangJalan::where('gajisupir_nobukti', $gajisupiruangjalan->gajisupir_nobukti)->delete();

            $uangJalan = new GajisUpirUangJalan();
            $uangJalan->gajisupir_id = $request->gajisupir_id;
            $uangJalan->gajisupir_nobukti = $request->gajisupir_nobukti;
            $uangJalan->absensisupir_nobukti = $request->absensisupir_nobukti;
            $uangJalan->supir_id = $request->supir_id;
            $uangJalan->nominal = $request->nominal;
            $uangJalan->save();

            $logTrail = [
                'namatabel' => strtoupper($uangJalan->getTable()),
                'postingdari' => 'EDIT GAJI SUPIR UANG JALAN',
                'idtrans' => $uangJalan->id,
                'nobuktitrans' => $uangJalan->id,
                'aksi' => 'EDIT',
                'datajson' => $uangJalan->toArray(),
                'modifiedby' => $uangJalan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $uangJalan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function destroy(Request $request, $id)
    {
        
        DB::beginTransaction();

        $gajisupir = new GajisUpirUangJalan();
        $gajisupir = $gajisupir->lockAndDestroy($id);

        if ($gajisupir) {
            $logTrail = [
                'namatabel' => strtoupper($gajisupir->getTable()),
                'postingdari' => 'DELETE GAJI SUPIR UANG JALAN',
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
