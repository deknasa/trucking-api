<?php

namespace App\Http\Controllers\Api;

use App\Models\ApprovalKirimBerkas;
use App\Http\Requests\StoreApprovalKirimBerkasRequest;
use App\Http\Requests\UpdateApprovalKirimBerkasRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKirimBerkasRequest;
use Illuminate\Http\Request;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PenerimaanHeader;
use App\Models\PengeluaranHeader;
use App\Rules\ApprovalKirimBerkas as RulesApprovalKirimberkas;

class ApprovalKirimBerkasController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $parameter = new Parameter();
        $dataKirimBerkas = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSKIRIMBERKAS')->where('text', 'KIRIM BERKAS')->first();

        $request['statuskirimberkas'] = $dataKirimBerkas->id;

        $parameter = new Parameter();
        $dataKirimBerkas = $parameter->getcombodata('KIRIMBERKAS', 'KIRIMBERKAS');
        $dataKirimBerkas = json_decode($dataKirimBerkas, true);
        foreach ($dataKirimBerkas as $item) {
            $statusKirimBerkas[] = $item['text'];
        }

        $request->validate([
            'table' => ['required', Rule::in($statusKirimBerkas)],
            'periode' => ['required', new RulesApprovalKirimBerkas()],
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
    public function store(StoreApprovalKirimBerkasRequest $request)
    {
        DB::beginTransaction();

        try {
            $approvalKirimBerkas = (new ApprovalKirimBerkas())->processStore([
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
    
    public function kirimBerkas($id, $table)
    {
        
        DB::beginTransaction();
        try {
            $backSlash = " \ ";

            $model = 'App\Models' . trim($backSlash) . $table->text;
            $data = app($model)->findOrFail($id);
            $statusKirimBerkas = Parameter::where('grp', '=', 'STATUSKIRIMBERKAS')->where('text', '=', 'KIRIM BERKAS')->first();
            $statusBelumKirimBerkas = Parameter::where('grp', '=', 'STATUSKIRIMBERKAS')->where('text', '=', 'BELUM KIRIM BERKAS')->first();

            if ($data->statuskirimberkas == $statusKirimBerkas->id) {
                $data->statuskirimberkas = $statusBelumKirimBerkas->id;
            } else {
                $data->statuskirimberkas = $statusKirimBerkas->id;
            }

            $data->tglbukacetak = date('Y-m-d', time());
            $data->userbukacetak = auth('api')->user()->name;
            if ($data->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($data->getTable()),
                    'postingdari' => "KIRIM BERKAS/BELUM KIRIM BERKAS $table->text",
                    'idtrans' => $data->id,
                    'nobuktitrans' => $data->id,
                    'aksi' => 'KIRIM BERKAS/BELUM KIRIM BERKAS',
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
