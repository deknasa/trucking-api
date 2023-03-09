<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTruckingDetail;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;
use App\Http\Requests\UpdatePenerimaanTruckingDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanTruckingDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $penerimaanTruckingDetail = new PenerimaanTruckingDetail();
        return response([
            'data' => $penerimaanTruckingDetail->get(),
            'attributes' => [
                'totalRows' => $penerimaanTruckingDetail->totalRows,
                'totalPages' => $penerimaanTruckingDetail->totalPages,
                'totalNominal' => $penerimaanTruckingDetail->totalNominal
            ]
        ]);
    }


    
    public function store(StorePenerimaanTruckingDetailRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $penerimaantruckingDetail = new PenerimaanTruckingDetail();
            
            $penerimaantruckingDetail->penerimaantruckingheader_id = $request->penerimaantruckingheader_id;
            $penerimaantruckingDetail->nobukti = $request->nobukti;
            $penerimaantruckingDetail->supir_id = $request->supir_id;
            $penerimaantruckingDetail->pengeluarantruckingheader_nobukti = $request->pengeluarantruckingheader_nobukti ?? '';
            $penerimaantruckingDetail->nominal = $request->nominal;
            $penerimaantruckingDetail->modifiedby = auth('api')->user()->name;
            
            $penerimaantruckingDetail->save();
           
            DB::commit();
            return [
                'error' => false,
                'detail' => $penerimaantruckingDetail,
                'id' => $penerimaantruckingDetail->id,
                'tabel' => $penerimaantruckingDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }


}
