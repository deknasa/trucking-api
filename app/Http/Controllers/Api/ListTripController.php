<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantar;
use App\Models\UpahSupir;
use App\Models\Tarifrincian;
use App\Models\UpahSupirRincian;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyListTripRequest;
use App\Http\Requests\GetIndexRangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMandorTripRequest;
use App\Http\Requests\UpdateListTripRequest;
use App\Models\ListTrip;

class ListTripController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request) //list history 
    {
        $suratPengantar = new SuratPengantar();
        return response([
            'data' => $suratPengantar->getListTrip(),
            'attributes' => [
                'totalRows' => $suratPengantar->totalRows,
                'totalPages' => $suratPengantar->totalPages
            ]
        ]);
    }

    public function show($id)
    {
        return response([
            'data' => (new ListTrip())->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateListTripRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $data = [
                'nobukti' => $request->nobukti,
                'tglbukti' => $request->tglbukti,
                'statusupahzona' => $request->statusupahzona,
                'statuslongtrip' => $request->statuslongtrip,
                'statuslangsir' => $request->statuslangsir,
                'statuskandang' => $request->statuskandang,
                'statusgudangsama' => $request->statusgudangsama,
                'nobukti_tripasal' => $request->nobukti_tripasal,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'container_id' => $request->container_id,
                'upah_id' => $request->upah_id,
                'penyesuaian' => $request->penyesuaian,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'tarifrincian_id' => $request->tarifrincian_id,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'pelanggan_id' => $request->pelanggan_id,
                'statusgandengan' => $request->statusgandengan,
                'gandengan_id' => $request->gandengan_id,
                'gandenganasal_id' => $request->gandenganasal_id,
                'jobtrucking' => $request->jobtrucking,
                'gudang' => $request->gudang,
                'lokasibongkarmuat' => $request->lokasibongkarmuat,
                'jenisritasi_id' => $request->jenisritasi_id,
                'ritasidari_id' => $request->ritasidari_id,
                'ritasike_id' => $request->ritasike_id,
            ];
            $trip = (new ListTrip())->processUpdate($id, $data);
            $suratPengantar = (new SuratPengantar());
            $trip->position = $this->getPosition($suratPengantar, $suratPengantar->getTable())->position;
            if ($request->limit == 0) {
                $trip->page = ceil($trip->position / (10));
            } else {
                $trip->page = ceil($trip->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $trip
            ]);
        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyListTripRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $trip = (new ListTrip())->processDestroy($id);
            $suratPengantar = (new SuratPengantar());
            $selected = $this->getPosition($suratPengantar, $suratPengantar->getTable(), true);
            $trip->position = $selected->position;
            $trip->id = $selected->id;
            if ($request->limit == 0) {
                $trip->page = ceil($trip->position / (10));
            } else {
                $trip->page = ceil($trip->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $trip
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekValidasi($id)
    {
        $listTrip = new ListTrip();
        $cekdata = $listTrip->cekValidasi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
}
