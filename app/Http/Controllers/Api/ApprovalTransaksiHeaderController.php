<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PengeluaranHeader;
use App\Http\Requests\StoreApprovalTransaksiHeaderRequest;

class ApprovalTransaksiHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        if($request->periode){
            $periode = explode("-",$request->periode);
            $request->merge([
                'year' => $periode[1],
                'month'=> $periode[0]
            ]);
        }
        
        if ($request->transaksi == 'PENERIMAAN BANK' && $request->approve){
            $penerimaan = new PenerimaanHeader();

            return response([
                'data' => $penerimaan->get(),
                'attributes' => [
                    'totalRows' => $penerimaan->totalRows,
                    'totalPages' => $penerimaan->totalPages
                ]
            ]);
        } else if ($request->transaksi == 'PENGELUARAN BANK' && $request->approve){
            $pengeluaran = new PengeluaranHeader();

            return response([
                'data' => $pengeluaran->get(),
                'attributes' => [
                    'totalRows' => $pengeluaran->totalRows,
                    'totalPages' => $pengeluaran->totalPages
                ]
            ]);
        } else{
            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
        
       
    }
    /**
     * @ClassName 
     */
    public function store(StoreApprovalTransaksiHeaderRequest $request)
    {

        if ($request->transaksi == 'PENERIMAAN BANK' && $request->approve){
            if ($request->transaksiId) {
                
                for ($i = 0; $i < count($request->transaksiId); $i++) {
                    $penerimaanHeader = app(PenerimaanHeaderController::class)->approval($request->transaksiId[$i]);
                }
            }
        }else if ($request->transaksi == 'PENGELUARAN BANK' && $request->approve){
            if ($request->transaksiId) {
                
                for ($i = 0; $i < count($request->transaksiId); $i++) {
                    $pengeluaranHeader = app(PengeluaranHeaderController::class)->approval($request->transaksiId[$i]);
                    // return response($pengeluaranHeader, 422);
                }
            }
        }
        return response([
            'message' => 'Berhasil'
        ]);

    }
    public function combo(Request $request)
    {
        $parameters = Parameter::select('kelompok')->whereIn('kelompok', ['PENERIMAAN BANK','PENGELUARAN BANK'])
            ->groupBy('kelompok')
            ->get();

        return response([
            'data' => $parameters
        ]);
    }
}
