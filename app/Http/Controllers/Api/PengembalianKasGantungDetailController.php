<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\PengembalianKasGantungDetail;
use App\Http\Requests\StorePengembalianKasGantungDetailRequest;
use App\Http\Requests\UpdatePengembalianKasGantungDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PengembalianKasGantungDetailController extends Controller
{
    public function index(Request $request)
    {
            $pengembalianKasGantungDetail = new PengembalianKasGantungDetail();
            return response([
                'data' => $pengembalianKasGantungDetail->get(),
                'attributes' => [
                    'totalRows' => $pengembalianKasGantungDetail->totalRows,
                    'totalPages' => $pengembalianKasGantungDetail->totalPages
                ]
            ]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePengembalianKasGantungDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePengembalianKasGantungDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'coadetail' => 'required',
            'keterangandetail' => 'required',
         ], [
             'keterangandetail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'coadetail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
              ], [
             'coadetail' => 'coa detail',
             'keterangandetail' => 'keterangandetail Detail',
            ],
         );         
         if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {

            $pengembalianKasGantungDetail = new PengembalianKasGantungDetail();
            $pengembalianKasGantungDetail->pengembaliankasgantung_id = $request->pengembaliankasgantung_id;
            $pengembalianKasGantungDetail->nobukti = $request->nobukti;
            $pengembalianKasGantungDetail->nominal = $request->nominal;
            $pengembalianKasGantungDetail->coa = $request->coadetail;
            $pengembalianKasGantungDetail->keterangan = $request->keterangandetail;
            $pengembalianKasGantungDetail->kasgantung_nobukti = $request->kasgantung_nobukti;
            $pengembalianKasGantungDetail->modifiedby = auth('api')->user()->name;
            
            DB::commit();
            if ($pengembalianKasGantungDetail->save()) {
                return [
                    'error' => false,
                    'id' => $pengembalianKasGantungDetail->id,
                    'tabel' => $pengembalianKasGantungDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PengembalianKasGantungDetail  $pengembalianKasGantungDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PengembalianKasGantungDetail $pengembalianKasGantungDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengembalianKasGantungDetail  $pengembalianKasGantungDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(PengembalianKasGantungDetail $pengembalianKasGantungDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengembalianKasGantungDetailRequest  $request
     * @param  \App\Models\PengembalianKasGantungDetail  $pengembalianKasGantungDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengembalianKasGantungDetailRequest $request, PengembalianKasGantungDetail $pengembalianKasGantungDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengembalianKasGantungDetail  $pengembalianKasGantungDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengembalianKasGantungDetail $pengembalianKasGantungDetail)
    {
        //
    }
}
