<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendapatanSupirDetail;
use App\Http\Requests\StorePendapatanSupirDetailRequest;
use App\Http\Requests\UpdatePendapatanSupirDetailRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanSupirDetailController extends Controller
{
    public function index(): JsonResponse
    {
        $pendapatanSupir = new PendapatanSupirDetail();

        return response()->json([
            'data' => $pendapatanSupir->get(),
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages,
                'totalNominal' => $pendapatanSupir->totalNominal
            ]
        ]);
    }

   
    public function store(StorePendapatanSupirDetailRequest $request)
    {
        DB::beginTransaction();
       
        try {
            $pendapatanSupirDetail = new PendapatanSupirDetail();
            
            $pendapatanSupirDetail->pendapatansupir_id = $request->pendapatansupir_id;
            $pendapatanSupirDetail->nobukti = $request->nobukti;
            $pendapatanSupirDetail->supir_id = $request->supir_id;
            $pendapatanSupirDetail->nominal = $request->nominal;
            $pendapatanSupirDetail->keterangan = $request->keterangan;
            $pendapatanSupirDetail->modifiedby = auth('api')->user()->name;
            
            $pendapatanSupirDetail->save();
           
            DB::commit();
            return [
                'error' => false,
                'detail' => $pendapatanSupirDetail,
                'id' => $pendapatanSupirDetail->id,
                'tabel' => $pendapatanSupirDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }   
    }

    
}
