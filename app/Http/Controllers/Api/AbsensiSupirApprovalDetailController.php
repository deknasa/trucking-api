<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AbsensiSupirApprovalDetail;
use App\Http\Requests\StoreAbsensiSupirApprovalDetailRequest;
use App\Http\Requests\UpdateAbsensiSupirApprovalDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AbsensiSupirApprovalDetailController extends Controller
{
    public function index(Request $request)
    {
        $absensiSupirApprovalDetail = new AbsensiSupirApprovalDetail();

        return response()->json([
            'data' => $absensiSupirApprovalDetail->get(),
            'attributes' => [
                'totalRows' => $absensiSupirApprovalDetail->totalRows,
                'totalPages' => $absensiSupirApprovalDetail->totalPages,
                'totalNominal' => $absensiSupirApprovalDetail->totalNominal
            ]
            ]);
        
        
    }

    
    public function store(StoreAbsensiSupirApprovalDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            "absensisupirapproval_id"=>"required",
            "nobukti"=>"required",
            "trado_id"=>"required",
            "supir_id"=>"required",
            "modifiedby"=>"required",
         ], [
            "absensisupirapproval_id.required"=>':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "nobukti.required"=>':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "trado_id.required"=>':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "supir_id.required"=>':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "modifiedby.required"=>':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
           
            "absensisupirapproval_id" => "absensisupirapproval",
            "nobukti" => "nobukti",
            "trado_id" => "trado",
            "supir_id" => "supir",
            "modifiedby" => "modifiedby",
            ],
         );
         if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {
            $absensiSupirApprovalDetail = new AbsensiSupirApprovalDetail();
            $absensiSupirApprovalDetail->absensisupirapproval_id = $request->absensisupirapproval_id;
            $absensiSupirApprovalDetail->nobukti = $request->nobukti;
            $absensiSupirApprovalDetail->trado_id = $request->trado_id;
            $absensiSupirApprovalDetail->supir_id = $request->supir_id;
            $absensiSupirApprovalDetail->modifiedby = $request->modifiedby;
            
            if ($absensiSupirApprovalDetail->save()) {
                DB::commit();
                return [
                    'error' => false,
                    'id' => $absensiSupirApprovalDetail->id,
                    'tabel' => $absensiSupirApprovalDetail->getTable(),
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
     * @param  \App\Models\AbsensiSupirApprovalDetail  $absensiSupirApprovalDetail
     * @return \Illuminate\Http\Response
     */
    public function show(AbsensiSupirApprovalDetail $absensiSupirApprovalDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AbsensiSupirApprovalDetail  $absensiSupirApprovalDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(AbsensiSupirApprovalDetail $absensiSupirApprovalDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAbsensiSupirApprovalDetailRequest  $request
     * @param  \App\Models\AbsensiSupirApprovalDetail  $absensiSupirApprovalDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAbsensiSupirApprovalDetailRequest $request, AbsensiSupirApprovalDetail $absensiSupirApprovalDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AbsensiSupirApprovalDetail  $absensiSupirApprovalDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(AbsensiSupirApprovalDetail $absensiSupirApprovalDetail)
    {
        //
    }
}
