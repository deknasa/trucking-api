<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\PengajuanTripInap;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengajuanTripInapRequest;
use App\Http\Requests\UpdatePengajuanTripInapRequest;

class PengajuanTripInapController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pengajuanTripInap = new PengajuanTripInap();
        return response([
            'data' => $pengajuanTripInap->get(),
            'attributes' => [
                'totalRows' => $pengajuanTripInap->totalRows,
                'totalPages' => $pengajuanTripInap->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePengajuanTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" =>$request->absensi_id,
                "tglabsensi" =>$request->tglabsensi,
                "trado_id" =>$request->trado_id,
                "supir_id" =>$request->supir_id,
            ];

            $pengajuanTripInap = (new PengajuanTripInap())->processStore($data);
            $pengajuanTripInap->position = $this->getPosition($pengajuanTripInap, $pengajuanTripInap->getTable())->position;
            if ($request->limit==0) {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / (10));
            } else {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengajuanTripInap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function show(PengajuanTripInap $pengajuantripinap)
    {
        return response([
            'data' => (new PengajuanTripInap())->findAll($pengajuantripinap),
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePengajuanTripInapRequest $request, PengajuanTripInap $pengajuantripinap)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" =>$request->absensi_id,
                "tglabsensi" =>$request->tglabsensi,
                "trado_id" =>$request->trado_id,
                "supir_id" =>$request->supir_id,
                "trado" =>$request->trado,
            ];
            $pengajuanTripInap = (new PengajuanTripInap())->processUpdate($pengajuantripinap,$data);
            $pengajuanTripInap->position = $this->getPosition($pengajuanTripInap, $pengajuanTripInap->getTable())->position;
            if ($request->limit==0) {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / (10));
            } else {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengajuanTripInap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(Request $request,PengajuanTripInap $pengajuantripinap)
    {
        DB::beginTransaction();

        try {
            $pengajuanTripInap = (new PengajuanTripInap())->processDestroy($pengajuantripinap->id, 'DELETE Trip Inap');
            $selected = $this->getPosition($pengajuanTripInap, $pengajuanTripInap->getTable(), true);
            $pengajuanTripInap->position = $selected->position;
            $pengajuanTripInap->id = $selected->id;
            if ($request->limit == 0) {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / (10));
            } else {
                $pengajuanTripInap->page = ceil($pengajuanTripInap->position / ($request->limit ?? 10));
            }
            $pengajuanTripInap->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengajuanTripInap->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengajuanTripInap
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function approval(PengajuanTripInap $id)
    {
        DB::beginTransaction();
        try {
            $pengajuanTripInap =$id;
            $pengajuanTripInap = (new PengajuanTripInap())->processApprove($pengajuanTripInap);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
