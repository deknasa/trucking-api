<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GajiSupirDetail;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Http\Requests\UpdateGajiSupirDetailRequest;
use App\Models\ProsesGajiSupirDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProsesGajiSupirDetailController extends Controller
{
    public function index(): JsonResponse
    {
        $prosesGajiSupir = new ProsesGajiSupirDetail();

        return response()->json([
            'data' => $prosesGajiSupir->get(),
            'attributes' => [
                'totalRows' => $prosesGajiSupir->totalRows,
                'totalPages' => $prosesGajiSupir->totalPages,
                'totalNominal' => $prosesGajiSupir->totalNominal
            ]
        ]);
    }


    
    public function store(StoreProsesGajiSupirDetailRequest $request)
    {
        DB::beginTransaction();
        try {
            $gajisupirdetail = new ProsesGajiSupirDetail();
            
            $gajisupirdetail->prosesgajisupir_id = $request->prosesgajisupir_id;
            $gajisupirdetail->nobukti = $request->nobukti;
            $gajisupirdetail->gajisupir_nobukti = $request->gajisupir_nobukti;
            $gajisupirdetail->supir_id = $request->supir_id;
            $gajisupirdetail->trado_id = $request->trado_id;
            $gajisupirdetail->nominal = $request->nominal;
            $gajisupirdetail->keterangan = $request->keterangan;
            
            $gajisupirdetail->modifiedby = auth('api')->user()->name;
            
            $gajisupirdetail->save();
           
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $gajisupirdetail,
                'id' => $gajisupirdetail->id,
                'tabel' => $gajisupirdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }



}
