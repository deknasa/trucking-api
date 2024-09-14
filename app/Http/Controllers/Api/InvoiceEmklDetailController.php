<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceEmklDetail;
use App\Models\PiutangDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceEmklDetailController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(): JsonResponse
    {
        $invoiceEmklDetail = new InvoiceEmklDetail();

        return response()->json([
            'data' => $invoiceEmklDetail->get(),
            'attributes' => [
                'totalRows' => $invoiceEmklDetail->totalRows,
                'totalPages' => $invoiceEmklDetail->totalPages,
                'totalNominal' => $invoiceEmklDetail->totalNominal
            ]
        ]);
    }
    
    public function piutang(): JsonResponse
    {
        $piutangDetail = new PiutangDetail();

        return response()->json([
            'data' => $piutangDetail->getPiutangFromInvoice(request()->nobukti_piutang),
            'attributes' => [
                'totalRows' => $piutangDetail->totalRows,
                'totalPages' => $piutangDetail->totalPages,
                'totalNominal' => $piutangDetail->totalNominal
            ]
        ]);
    }

}
