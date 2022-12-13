<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\PenerimaanHeader;
use App\Models\PengeluaranHeader;
use App\Http\Requests\StoreApprovalBukuCetakHeaderRequest;

class ApprovalBukaCetakController extends Controller
{
    public function index(Request $request)
    {
        
        if($request->periode){
            $periode = explode("-",$request->periode);
            $request->merge([
                'year' => $periode[1],
                'month'=> $periode[0]
            ]);
        }
        if ($request->table && $request->cetak){
            $table = Parameter::where('text',$request->table)->first();
            $backSlash = " \ ";
            $model = 'App\Models'.trim($backSlash).$table->text;
            $data = app($model);
            return response([
                'data' => $data->get(),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages
                ]
            ]);
        }
    }

    public function store(StoreApprovalBukuCetakHeaderRequest $request)
    {
        if ($request->table && $request->cetak){
            if ($request->tableId) {
                $table = Parameter::where('text',$request->table)->first();
                $backSlash = " \ ";
                for ($i = 0; $i < count($request->tableId); $i++) {
                    $this->bukaCetak($request->tableId[$i],$table);
                }
                
            }
        }
        return response([
            'message' => 'Berhasil'
        ]);
    }

    public function bukaCetak($id,$table)
    {
        DB::beginTransaction();
        try {
            $backSlash = " \ ";
    
            $model = 'App\Models'.trim($backSlash).$table->text;
            $data = app($model)->findOrFail($id);
            $statusCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();
            
            if ($data->statuscetak == $statusCetak->id) {
                $data->statuscetak = $statusBelumCetak->id;
            } else {
                $data->statuscetak = $statusCetak->id;
            }
    
            $data->tglbukacetak = date('Y-m-d', time());
            $data->userbukacetak = auth('api')->user()->name;

            if ($data->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($data->getTable()),
                    'postingdari' => "BUKA/BELUM CETAK $table->text",
                    'idtrans' => $data->id,
                    'nobuktitrans' => $data->id,
                    'aksi' => 'BUKA/BELUM CETAK',
                    'datajson' => $data->toArray(),
                    'modifiedby' => $data->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
