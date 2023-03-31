<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceDetail;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Http\Requests\UpdateInvoiceDetailRequest;
use App\Models\JurnalUmumDetail;
use App\Models\PiutangDetail;
use App\Models\PiutangHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceDetailController extends Controller
{

    public function index(): JsonResponse
    {
        $invoice = new InvoiceDetail();

        return response()->json([
            'data' => $invoice->get(),
            'attributes' => [
                'totalRows' => $invoice->totalRows,
                'totalPages' => $invoice->totalPages,
                'totalNominal' => $invoice->totalNominal,
                'totalRetribusi' => $invoice->totalRetribusi,
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
    
    public function jurnal(): JsonResponse
    {
        $jurnalDetail = new JurnalUmumDetail();

        return response()->json([
            'data' => $jurnalDetail->getJurnalFromAnotherTable(request()->nobukti),
            'attributes' => [
                'totalRows' => $jurnalDetail->totalRows,
                'totalPages' => $jurnalDetail->totalPages,
                'totalNominalDebet' => $jurnalDetail->totalNominalDebet,
                'totalNominalKredit' => $jurnalDetail->totalNominalKredit,
            ]
        ]);
    }


    
    public function store(StoreInvoiceDetailRequest $request)
    {
        DB::beginTransaction();

        
        try {
            $invoiceDetail = new InvoiceDetail();
            
            $invoiceDetail->invoice_id = $request->invoice_id;
            $invoiceDetail->nobukti = $request->nobukti;
            $invoiceDetail->nominal = $request->nominal;
            $invoiceDetail->nominalretribusi = $request->nominalretribusi;
            $invoiceDetail->total = $request->total;
            $invoiceDetail->keterangan = $request->keterangan;
            $invoiceDetail->orderantrucking_nobukti = $request->orderantrucking_nobukti;
            $invoiceDetail->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            
            $invoiceDetail->modifiedby = auth('api')->user()->name;
            
            $invoiceDetail->save();
           
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $invoiceDetail,
                'id' => $invoiceDetail->id,
                'tabel' => $invoiceDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }      
    }

    
}
