<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderanTrucking;
use App\Http\Requests\StoreOrderanTruckingRequest;
use App\Http\Requests\UpdateOrderanTruckingRequest;
use App\Http\Requests\DestroyOrderanTruckingRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Models\Container;
use App\Models\Agen;
use App\Models\JenisOrder;
use App\Models\Pelanggan;
use App\Models\SuratPengantar;
use App\Models\Tarif;
use App\Models\TarifRincian;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

class OrderanTruckingController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(GetIndexRangeRequest $request)
    {
        $orderanTrucking = new OrderanTrucking();
        return response([
            'data' => $orderanTrucking->get(),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages
            ]
        ]);
    }

    public function cekValidasi($id,$aksi)
    {
        $orderanTrucking = new OrderanTrucking();
        $nobukti = OrderanTrucking::from(DB::raw("orderantrucking"))->where('id', $id)->first();
        $cekdata = $orderanTrucking->cekvalidasihapus($nobukti->nobukti,$aksi);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
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
                'status' => true,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
    public function default()
    {
        $orderanTrucking = new OrderanTrucking();
        return response([
            'status' => true,
            'data' => $orderanTrucking->default()
        ]);
    }



    /**
     * @ClassName 
     */
    public function store(StoreOrderanTruckingRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'container_id' => $request->container_id,
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
                'statuslangsir' => $request->statuslangsir,
                'statusperalihan' => $request->statusperalihan,
            ];
            $orderanTrucking = (new OrderanTrucking())->processStore($data);
            $orderanTrucking->position = $this->getPosition($orderanTrucking, $orderanTrucking->getTable())->position;
            $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $orderanTrucking
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $orderanTrucking = (new OrderanTrucking)->findAll($id);

        return response([
            'status' => true,
            'data' => $orderanTrucking
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateOrderanTruckingRequest $request, OrderanTrucking $orderantrucking): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'container_id' => $request->container_id,
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
                'statuslangsir' => $request->statuslangsir,
                'statusperalihan' => $request->statusperalihan,
            ];
            $orderanTrucking = (new OrderanTrucking())->processUpdate($orderantrucking, $data);
            $orderanTrucking->position = $this->getPosition($orderanTrucking, $orderanTrucking->getTable())->position;
            $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyOrderanTruckingRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $orderanTrucking = (new OrderanTrucking())->processDestroy($id);
            $selected = $this->getPosition($orderanTrucking, $orderanTrucking->getTable(), true);
            $orderanTrucking->position = $selected->position;
            $orderanTrucking->id = $selected->id;
            $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('orderantrucking')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])
            ->get(config('app.api_url') . "jobemkl/combo");

        $data = [
            'container' => Container::all(),
            'agen' => Agen::all(),
            'jenisorder' => JenisOrder::all(),
            'pelanggan' => Pelanggan::all(),
            'tarif' => Tarif::all(),
            'statuslangsir' => Parameter::where(['grp' => 'status langsir'])->get(),
            'statusperalihan' => Parameter::where(['grp' => 'status peralihan'])->get(),
            'jobemkl' => $response['data']['jobemkl'],
        ];

        return response([
            'data' => $data
        ]);
    }

    public function getOrderanTrip(Request $request)
    {
        $orderanTrucking = new OrderanTrucking();
        $agen = $request->agen;
        $tglbukti = date('Y-m-d',strtotime($request->tglbukti));
        return response([
            'data' => $orderanTrucking->getOrderanTrip($tglbukti,$agen),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages
            ]
        ]);
    }

    public function getagentas($id)
    {

        $orderantrucking = new OrderanTrucking();
        return response([
            "data" => $orderantrucking->getagentas($id)
        ]);
    }
    public function getcont($id)
    {

        $orderantrucking = new OrderanTrucking();
        return response([
            "data" => $orderantrucking->getcont($id)
        ]);
    }

    public function export(GetUpahSupirRangeRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $orderanTrucking = new OrderanTrucking();
        return response([
            'data' => $orderanTrucking->getExport($dari, $sampai),
        ]);
    }
}
