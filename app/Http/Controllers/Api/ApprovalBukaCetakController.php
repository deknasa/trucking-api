<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalBukaCetakRequest;
use Illuminate\Http\Request;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PenerimaanHeader;
use App\Models\PengeluaranHeader;
use App\Http\Requests\StoreApprovalBukuCetakHeaderRequest;
use App\Models\ApprovalBukaCetak;
use App\Rules\ApprovalBukaCetak as RulesApprovalBukaCetak;

class ApprovalBukaCetakController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $parameter = new Parameter();
        $dataCetak = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $request['statuscetak'] = $dataCetak->id;

        $parameter = new Parameter();
        $dataCetakUlang = $parameter->getcombodata('CETAKULANG', 'CETAKULANG');
        $dataCetakUlang = json_decode($dataCetakUlang, true);
        foreach ($dataCetakUlang as $item) {
            $statusCetakUlang[] = $item['text'];
        }
        $request->validate([
            'table' => ['required', Rule::in($statusCetakUlang)],
            'periode' => ['required', new RulesApprovalBukaCetak()],
        ]);

        if ($request->periode) {
            $periode = explode("-", $request->periode);
            $request->merge([
                'year' => $periode[1],
                'month' => $periode[0],
                'statuscetak' => $dataCetak->id
            ]);
        }

        $table = Parameter::where('text', $request->table)->first();
        $backSlash = " \ ";
        $model = 'App\Models' . trim($backSlash) . $table->text;
        $data = app($model);
        return response([
            'data' => $data->get($request),
            'attributes' => [
                'totalRows' => $data->totalRows,
                'totalPages' => $data->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalBukuCetakHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $approvalBukaCetak = (new ApprovalBukaCetak())->processStore([
                "tableId"=>$request->tableId,
                "periode"=>$request->periode,
                "table"=>$request->table,
            ]);
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalBukaCetak
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    
    public function bukaCetak($id, $table)
    {
        
        DB::beginTransaction();
        try {
            $backSlash = " \ ";

            $model = 'App\Models' . trim($backSlash) . $table->text;
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
