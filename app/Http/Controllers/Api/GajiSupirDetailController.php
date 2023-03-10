<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GajiSupirDetail;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\UpdateGajiSupirDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GajiSupirDetailController extends Controller
{
    public function index(): JsonResponse
    {
        $pelunasanPiutang = new GajiSupirDetail();

        return response()->json([
            'data' => $pelunasanPiutang->get(),
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages,
                'totalGajiSupir' => $pelunasanPiutang->totalGajiSupir,
                'totalGajiKenek' => $pelunasanPiutang->totalGajiKenek,
                'totalKomisiSupir' => $pelunasanPiutang->totalKomisiSupir,
            ]
        ]);
    }
    
    public function store(StoreGajiSupirDetailRequest $request)
    {
        DB::beginTransaction();

        
        try {
            $gajisupirdetail = new GajiSupirDetail();
            
            $gajisupirdetail->gajisupir_id = $request->gajisupir_id;
            $gajisupirdetail->nobukti = $request->nobukti;
            $gajisupirdetail->nominaldeposito = $request->nominaldeposito;
            $gajisupirdetail->nourut = $request->nourut;
            $gajisupirdetail->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $gajisupirdetail->komisisupir = $request->komisisupir;
            $gajisupirdetail->tolsupir = $request->tolsupir;
            $gajisupirdetail->voucher = $request->voucher;
            $gajisupirdetail->novoucher = $request->novoucher;
            $gajisupirdetail->gajisupir = $request->gajisupir;
            $gajisupirdetail->gajikenek = $request->gajikenek;
            $gajisupirdetail->gajiritasi = $request->gajiritasi;
            $gajisupirdetail->nominalpengembalianpinjaman = $request->nominalpengembalianpinjaman;
            
            $gajisupirdetail->modifiedby = auth('api')->user()->name;
            
            $gajisupirdetail->save();
           
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $gajisupirdetail,
                'id' => $gajisupirdetail->id,
                'tabel' => $gajisupirdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }



}
