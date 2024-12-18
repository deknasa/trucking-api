<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PengeluaranHeader;
use App\Http\Requests\StoreApprovalTransaksiHeaderRequest;
use App\Models\ApprovalTransaksiHeader;
use App\Models\PenerimaanGiroHeader;
use Illuminate\Support\Facades\DB;

class ApprovalTransaksiHeaderController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        if ($request->periode) {
            $periode = explode("-", $request->periode);
            $request->merge([
                'year' => $periode[1],
                'month' => $periode[0]
            ]);
        }

        if ($request->approve == 3) {
            $request->approve = 4;
        }else{
            $request->approve = 3;
        }
        
        if ($request->transaksi == 'PENERIMAAN BANK' && $request->approve) {
            $penerimaan = new PenerimaanHeader();
            $data = $penerimaan->get();
            $totalRows = $penerimaan->totalRows;
            $totalPages = $penerimaan->totalPages;
        } else if ($request->transaksi == 'PENGELUARAN BANK' && $request->approve) {
            $pengeluaran = new PengeluaranHeader();
            $data = $pengeluaran->get();
            $totalRows = $pengeluaran->totalRows;
            $totalPages = $pengeluaran->totalPages;
        } else if ($request->transaksi == 'PENERIMAAN GIRO' && $request->approve) {
            $penerimaanGiro = new PenerimaanGiroHeader();
            $data = $penerimaanGiro->get();
            $totalRows = $penerimaanGiro->totalRows;
            $totalPages = $penerimaanGiro->totalPages;
        } else {
            $data = [];
            $totalRows = 0;
            $totalPages = 0;
        }

        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $totalRows,
                'totalPages' => $totalPages
            ]
        ]);
    }
    
    public function default()
    {
        $approvalTransaksi = new ApprovalTransaksiHeader();
        return response([
            'status' => true,
            'data' => $approvalTransaksi->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalTransaksiHeaderRequest $request)
    {

        if ($request->transaksiId != '') {
            if ($request->transaksi == 'PENERIMAAN BANK' && $request->approve) {
                if ($request->transaksiId) {

                    for ($i = 0; $i < count($request->transaksiId); $i++) {
                        $penerimaanHeader = app(PenerimaanHeaderController::class)->approval($request->transaksiId[$i]);
                    }
                }
            } else if ($request->transaksi == 'PENGELUARAN BANK' && $request->approve) {
                if ($request->transaksiId) {

                    for ($i = 0; $i < count($request->transaksiId); $i++) {
                        $pengeluaranHeader = app(PengeluaranHeaderController::class)->approval($request->transaksiId[$i]);
                        // return response($pengeluaranHeader, 422);
                    }
                }
            } else if ($request->transaksi == 'PENERIMAAN GIRO' && $request->approve) {
                if ($request->transaksiId) {

                    for ($i = 0; $i < count($request->transaksiId); $i++) {
                        $penerimaanGiro = app(PenerimaanGiroHeaderController::class)->approval($request->transaksiId[$i]);
                        // return response($pengeluaranHeader, 422);
                    }
                }
            }
            return response([
                'message' => 'Berhasil'
            ]);
        }else {
            $query = DB::table('error')->from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'WP')
                ->first();
            return response([
                'errors' => [
                    'transaksiId' => "transaksi $query->keterangan"
                ],
                'message' => "transaksi $query->keterangan",
            ], 422);
        }
    }
    public function combo(Request $request)
    {
        $parameters = Parameter::select('kelompok')->whereIn('kelompok', ['PENERIMAAN BANK', 'PENGELUARAN BANK', 'PENERIMAAN GIRO'])
            ->groupBy('kelompok')
            ->get();

        return response([
            'data' => $parameters
        ]);
    }
}
