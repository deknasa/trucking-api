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
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
    }
    /**
     * @ClassName 
     * inputtripcontroller
     * @Detail SuratPengantarApprovalInputTripController
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMandorTripRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = [
                'jobtrucking' => $request->jobtrucking,
                'upah_id' => $request->upah_id ?? '',
                'container_id' => $request->container_id ?? '',
                'statuscontainer_id' => $request->statuscontainer_id ?? '',
                'statuskandang_id' => $request->statuskandang,
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'statusgandengan' => $request->statusgandengan,
                'statusupahzona' => $request->statusupahzona,
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id ?? '',
                'pelanggan_id' => $request->pelanggan_id,
                'tarifrincian_id' => $request->tarifrincian_id ?? '',
                'triptangki_id' => $request->triptangki_id ?? '',
                'nojobemkl' => $request->nojobemkl,
                'nocont' => $request->nocont,
                'noseal' => $request->noseal,
                'nojobemkl2' => $request->nojobemkl2,
                'nocont2' => $request->nocont2,
                'noseal2' => $request->noseal2,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'penyesuaian' => $request->penyesuaian,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'gandengan_id' => $request->gandengan_id,
                'gandenganasal_id' => $request->gandenganasal_id,
                'statuslongtrip' => $request->statuslongtrip,
                'statuslangsir' => $request->statuslangsir,
                'statusgudangsama' => $request->statusgudangsama,
                'gudang' => $request->gudang,
                'lokasibongkarmuat' => $request->lokasibongkarmuat,
                'jenisritasi_id' => $request->jenisritasi_id,
                'ritasidari_id' => $request->ritasidari_id,
                'ritasike_id' => $request->ritasike_id,
                'nobukti_tripasal' => $request->nobukti_tripasal ?? '',
                'statuspenyesuaian' => $request->statuspenyesuaian,
            ];
            // dd($data);
            $inputTrip = (new InputTrip())->processStore($data);
            // $inputTrip->position = $this->getPosition($inputTrip, $inputTrip->getTable())->position;
            // $inputTrip->page = ceil($inputTrip->position / ($request->limit ?? 10));

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

    public function getKotaRitasi(Request $request)
    {
        return response([
            'data' => (new InputTrip())->getKotaRitasi($request->dataritasi_id)
        ]);
    }

    public function getInfoTrado(Request $request)
    {
        return response([
            'data' => (new InputTrip())->getInfo($request->trado_id, $request->upah_id, $request->statuscontainer_id, $request->id),
        ]);
    }
}
