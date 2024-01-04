<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInvoiceChargeGandenganDetailRequest;
use App\Models\InvoiceChargeGandenganDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;


class InvoiceChargeGandenganDetailController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(): JsonResponse
    {
        $invoiceChargeGandengan = new InvoiceChargeGandenganDetail();
        return response()->json([
            'data' => $invoiceChargeGandengan->get(),
            'attributes' => [
                'totalRows' => $invoiceChargeGandengan->totalRows,
                'totalPages' => $invoiceChargeGandengan->totalPages,
                'totalNominal' => $invoiceChargeGandengan->totalNominal,
            ]
        ]);
    }
    public function store(StoreInvoiceChargeGandenganDetailRequest $request)
    {

        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            // 'jobtrucking_detail' => 'required',
            // 'tgltrip_detail' => 'required',
            // 'jumlahhari_detail' => 'required',
            // 'nopolisi_detail' => 'required',
            'nominal_detail' => 'required',
            'keterangan_detail' => 'required',
        ], [
            // 'jobtrucking_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            // 'tgltrip_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            // 'jumlahhari_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            // 'nopolisi_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nominal_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'keterangan_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            // 'jobtrucking_detail' => 'Job Trucking',
            // 'tgltrip_detail' => 'Tgl Trip',
            // 'jumlahhari_detail' => 'Jumlah Hari',
            // 'nopolisi_detail' => 'No Polisi',
            'nominal_detail' => 'Nominal',
            'keterangan_detail' => 'Keterangan',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $invoiceChargeGandenganDetail = new InvoiceChargeGandenganDetail();
            $invoiceChargeGandenganDetail->invoicechargegandengan_id = $request->invoicechargegandengan_id;
            $invoiceChargeGandenganDetail->nobukti = $request->nobukti;
            $invoiceChargeGandenganDetail->jobtrucking = $request->jobtrucking_detail;
            $invoiceChargeGandenganDetail->trado_id = $request->trado_id;
            $invoiceChargeGandenganDetail->tgltrip = $request->tgltrip_detail;
            $invoiceChargeGandenganDetail->jumlahhari = $request->jumlahhari_detail;
            $invoiceChargeGandenganDetail->nominal = $request->nominal_detail;
            $invoiceChargeGandenganDetail->keterangan = $request->keterangan_detail;

            $invoiceChargeGandenganDetail->save();
            DB::commit();
            return [
                'error' => false,
                'id' => $invoiceChargeGandenganDetail->id,
                'tabel' => $invoiceChargeGandenganDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
