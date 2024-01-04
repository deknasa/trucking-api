<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceExtraDetail;
use App\Http\Requests\StoreInvoiceExtraDetailRequest;
use App\Http\Requests\UpdateInvoiceExtraDetailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InvoiceExtraDetailController extends Controller
{

   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(): JsonResponse
    {
        $invoiceExtra = new InvoiceExtraDetail();
        return response()->json([
            'data' => $invoiceExtra->get(),
            'attributes' => [
                'totalRows' => $invoiceExtra->totalRows,
                'totalPages' => $invoiceExtra->totalPages,
                'totalNominal' => $invoiceExtra->totalNominal,
            ]
        ]);
    }
    public function store(StoreInvoiceExtraDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'nominal_detail' => 'required',
            'keterangan_detail' => 'required',
        ], [
            'nominal_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'keterangan_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
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
            $invoiceExtraDetail = new InvoiceExtraDetail();

            $invoiceExtraDetail->invoiceextra_id = $request->invoiceextra_id;
            $invoiceExtraDetail->nobukti = $request->nobukti;
            $invoiceExtraDetail->nominal = $request->nominal_detail;
            $invoiceExtraDetail->keterangan = $request->keterangan_detail;
            $invoiceExtraDetail->save();
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $invoiceExtraDetail->id,
                    'tabel' => $invoiceExtraDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function addrow(StoreInvoiceExtraDetailRequest $request)
    {
        return true;
    }
    public function export()
    {
    }
}
