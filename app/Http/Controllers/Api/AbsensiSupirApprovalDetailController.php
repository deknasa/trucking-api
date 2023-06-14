<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AbsensiSupirApprovalDetail;
use App\Http\Requests\StoreAbsensiSupirApprovalDetailRequest;
use App\Http\Requests\UpdateAbsensiSupirApprovalDetailRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AbsensiSupirApprovalDetailController extends Controller
{
    public function index(Request $request)
    {
        $absensiSupirApprovalDetail = new AbsensiSupirApprovalDetail();

        $idUser = auth('api')->user()->id;
        $getuser = User::select('name')
            ->where('user.id', $idUser)->first();

        return response()->json([
            'data' => $absensiSupirApprovalDetail->get(),
            'user' => $getuser,
            'attributes' => [
                'totalRows' => $absensiSupirApprovalDetail->totalRows,
                'totalPages' => $absensiSupirApprovalDetail->totalPages,
                'totalNominal' => $absensiSupirApprovalDetail->totalNominal
            ]
        ]);
    }


    public function store(StoreAbsensiSupirApprovalDetailRequest $request)
    {
        $absensiSupirApprovalDetail = new AbsensiSupirApprovalDetail();
        $absensiSupirApprovalDetail->absensisupirapproval_id = $request->absensisupirapproval_id;
        $absensiSupirApprovalDetail->nobukti = $request->nobukti;
        $absensiSupirApprovalDetail->trado_id = $request->trado_id;
        $absensiSupirApprovalDetail->supir_id = $request->supir_id ?? '';
        $absensiSupirApprovalDetail->modifiedby = $request->modifiedby;

        if (!$absensiSupirApprovalDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi supir detail.");
        }

        return [
            'error' => false,
            'id' => $absensiSupirApprovalDetail->id,
            'tabel' => $absensiSupirApprovalDetail->getTable(),
        ];
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
