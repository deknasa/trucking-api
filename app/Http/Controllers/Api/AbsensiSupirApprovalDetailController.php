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
        $params = [
            'id' => $request->id,
            'absensisupirapproval_id' => $request->absensisupirapproval_id,
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
            $query = AbsensiSupirApprovalDetail::from('absensisupirapprovaldetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['absensisupirapproval_id'])) {
                $query->where('detail.absensisupirapproval_id', $params['absensisupirapproval_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('absensisupirapproval_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.absensisupirapproval_id',
                    'detail.nobukti',
                    'detail.trado_id',
                    'detail.supir_id',
                    'detail.supirserap_id',
                    'detail.modifiedby',
                    'trado.keterangan as trado',
                    'supirutama.namasupir as supir',
                    'supirserap.namasupir as supirserap',

                )

                ->leftJoin('absensisupirapprovalheader', 'detail.absensisupirapproval_id', 'absensisupirapprovalheader.id')
                ->leftJoin('trado', 'detail.trado_id', 'trado.id')
                ->leftJoin('supir as supirutama', 'detail.supir_id', 'supirutama.id')
                ->leftJoin('supir as supirserap', 'detail.supirserap_id', 'supirserap.id');
                $absensiSupirApprovalDetail = $query->get();
            } else {
                $query->select(
                    'detail.absensisupirapproval_id',
                    'detail.nobukti',
                    'detail.trado_id',
                    'detail.supir_id',
                    'detail.supirserap_id',
                    'detail.modifiedby',
                    'trado.keterangan as trado',
                    'supirutama.namasupir as supir',
                    'supirserap.namasupir as supirserap',

                )

                ->leftJoin('absensisupirapprovalheader', 'detail.absensisupirapproval_id', 'absensisupirapprovalheader.id')
                ->leftJoin('trado', 'detail.trado_id', 'trado.id')
                ->leftJoin('supir as supirutama', 'detail.supir_id', 'supirutama.id')
                ->leftJoin('supir as supirserap', 'detail.supirserap_id', 'supirserap.id');
                $totalRows =  $query->count();
                $query->skip($params['offset'])->take($params['limit']);
                $absensiSupirApprovalDetail = $query->get();
            }
            
            return response([
                'data' => $absensiSupirApprovalDetail,
                'total' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1,
                "records" =>$totalRows ?? 0,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
        
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
