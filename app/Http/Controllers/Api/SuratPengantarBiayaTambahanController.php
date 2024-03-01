<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalSuratPengantarBiayaTambahanRequest;
use App\Models\SuratPengantarBiayaTambahan;
use App\Http\Requests\StoreSuratPengantarBiayaTambahanRequest;
use App\Http\Requests\UpdateSuratPengantarBiayaTambahanRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class SuratPengantarBiayaTambahanController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $suratPengantarBiayaTambahan = new SuratPengantarBiayaTambahan();

        return response()->json([
            'data' => $suratPengantarBiayaTambahan->get(),
            'attributes' => [
                'totalRows' => $suratPengantarBiayaTambahan->totalRows,
                'totalPages' => $suratPengantarBiayaTambahan->totalPages,
                'totalNominal' => $suratPengantarBiayaTambahan->totalNominal,
                'totalNominalTagih' => $suratPengantarBiayaTambahan->totalNominalTagih
            ]
        ]);
    }
    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalSuratPengantarBiayaTambahanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'id' => $request->id
            ];
            $suratpengantar = (new SuratPengantarBiayaTambahan())->processApproval($data);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $suratpengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function deleteRow()
    {
        DB::beginTransaction();

        try {
            $data = [
                'id' => request()->id
            ];
            $suratpengantar = (new SuratPengantarBiayaTambahan())->deleteRow($data);

            DB::commit();

            return response()->json([
                'data' => $suratpengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
