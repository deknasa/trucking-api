<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parameter;
use App\Models\InvoiceHeader;
use App\Models\InvoiceExtraHeader;
use App\Http\Requests\StoreApprovalInvoiceHeaderRequest;
use App\Models\ApprovalInvoiceHeader;

class ApprovalInvoiceHeaderController extends Controller
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
        
        if ($request->invoice == 85 && $request->approve) { //if invoice utama
            $penerimaan = new InvoiceHeader();

            return response([
                'data' => $penerimaan->get(),
                'attributes' => [
                    'totalRows' => $penerimaan->totalRows,
                    'totalPages' => $penerimaan->totalPages
                ]
            ]);
        } else if ($request->invoice == 86 && $request->approve) { //if invoice extra
            $pengeluaran = new InvoiceExtraHeader();

            return response([
                'data' => $pengeluaran->get(),
                'attributes' => [
                    'totalRows' => $pengeluaran->totalRows,
                    'totalPages' => $pengeluaran->totalPages
                ]
            ]);
        } else {
            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function default()
    {
        $approvalInvoice = new ApprovalInvoiceHeader();
        return response([
            'status' => true,
            'data' => $approvalInvoice->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalInvoiceHeaderRequest $request)
    {
        if ($request->invoice == 85 && $request->approve) { //if invoice utama
            if ($request->invoiceId) {

                for ($i = 0; $i < count($request->invoiceId); $i++) {
                    $penerimaanHeader = app(InvoiceHeaderController::class)->approval($request->invoiceId[$i]);
                }
            }
        } else if ($request->invoice == 86 && $request->approve) { //if invoice extra
            if ($request->invoiceId) {

                for ($i = 0; $i < count($request->invoiceId); $i++) {
                    // return response($request->invoiceId[$i], 422);
                    $pengeluaranHeader = app(InvoiceExtraHeaderController::class)->approval($request->invoiceId[$i]);
                }
            }
        }
        return response([
            'message' => 'Berhasil'
        ]);
    }
}
