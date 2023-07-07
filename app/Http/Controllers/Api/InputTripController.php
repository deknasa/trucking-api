<?php

namespace App\Http\Controllers\Api;


use App\Models\SuratPengantar;
use App\Models\UpahSupir;
use App\Models\Tarifrincian;
use App\Models\UpahSupirRincian;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMandorTripRequest;
use App\Http\Requests\StoreOrderantruckingRequest;
use App\Http\Requests\StoreRitasiRequest;
use App\Models\InputTrip;
use Illuminate\Http\JsonResponse;

class InputTripController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
    }
    /**
     * @ClassName 
     * inputtripcontroller
     * @Detail1 SuratPengantarApprovalInputTripController
    */
    public function store(StoreMandorTripRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = [
                'jobtrucking' => $request->jobtrucking,
                'upah_id' => $request->upah_id,
                'container_id' => $request->container_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statusgandengan' => $request->statusgandengan,
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'pelanggan_id' => $request->pelanggan_id,
                'tarifrincian_id' => $request->tarifrincian_id,
                'nojobemkl' => $request->nojobemkl,
                'nocont' => $request->nocont,
                'noseal' => $request->noseal,
                'nojobemkl2' => $request->nojobemkl2,
                'nocont2' => $request->nocont2,
                'noseal2' => $request->noseal2,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'gandengan_id' => $request->gandengan_id,
                'gandenganasal_id' => $request->gandenganasal_id,
                'statuslongtrip' => $request->statuslongtrip,
                'statusgudangsama' => $request->statusgudangsama,
                'gudang' => $request->gudang,
                'jenisritasi_id' => $request->jenisritasi_id,
                'ritasidari_id' => $request->ritasidari_id,
                'ritasike_id' => $request->ritasike_id,
            ];
            $inputTrip = (new InputTrip())->processStore($data);
            $inputTrip->position = $this->getPosition($inputTrip, $inputTrip->getTable())->position;
            $inputTrip->page = ceil($inputTrip->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $inputTrip
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
