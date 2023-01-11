<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceExtraDetail;
use App\Http\Requests\StoreInvoiceExtraDetailRequest;
use App\Http\Requests\UpdateInvoiceExtraDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InvoiceExtraDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'invoiceextra_id' => $request->invoiceextra_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
            'offset' => $request->offset ?? (($request->page - 1) * $request->limit),
            'limit' => $request->limit ?? 10,
        ];
        $totalRows = 0;
        try {
            $query = InvoiceExtraDetail::from('invoiceextradetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['invoiceextra_id'])) {
                $query->where('detail.invoiceextra_id', $params['invoiceextra_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('invoiceextra_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.invoiceextra_id',
                    'detail.nobukti',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.modifiedby'

                )

                ->leftJoin('invoiceextraheader', 'detail.invoiceextra_id', 'invoiceextraheader.id');
                $invoiceExtraDetail = $query->get();
            } else {
                $query->select(
                    'detail.invoiceextra_id',
                    'detail.nobukti',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.modifiedby'

                )

                ->leftJoin('invoiceextraheader', 'detail.invoiceextra_id', 'invoiceextraheader.id');
                $totalRows =  $query->count();
                $query->skip($params['offset'])->take($params['limit']);
                $invoiceExtraDetail = $query->get();
            }

            return response([
                'data' => $invoiceExtraDetail,
                'attributes' => [
                    'totalRows' => $totalRows ?? 0,
                    'totalPages' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1
                ]
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceExtraDetail  $invoiceExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceExtraDetail $invoiceExtraDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceExtraDetail  $invoiceExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceExtraDetail $invoiceExtraDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceExtraDetailRequest  $request
     * @param  \App\Models\InvoiceExtraDetail  $invoiceExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceExtraDetailRequest $request, InvoiceExtraDetail $invoiceExtraDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceExtraDetail  $invoiceExtraDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceExtraDetail $invoiceExtraDetail)
    {
        //
    }
}
