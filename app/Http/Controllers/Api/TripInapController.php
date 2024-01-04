<?php

namespace App\Http\Controllers\Api;

use App\Models\TripInap;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripInapRequest;
use App\Http\Requests\UpdateTripInapRequest;

class TripInapController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tripInap = new TripInap();
        return response([
            'data' => $tripInap->get(),
            'attributes' => [
                'totalRows' => $tripInap->totalRows,
                'totalPages' => $tripInap->totalPages
            ]
        ]);
    }

   /**
     * @ClassName
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTripInapRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" =>$request->absensi_id,
                "tglabsensi" =>$request->tglabsensi,
                "trado_id" =>$request->trado_id,
                "trado" =>$request->trado,
                "suratpengantar_nobukti" =>$request->suratpengantar_nobukti,
                "jammasukinap" =>$request->jammasukinap,
                "jamkeluarinap" =>$request->jamkeluarinap,
            ];

            $tripInap = (new TripInap())->processStore($data);
            $tripInap->position = $this->getPosition($tripInap, $tripInap->getTable())->position;
            if ($request->limit==0) {
                $tripInap->page = ceil($tripInap->position / (10));
            } else {
                $tripInap->page = ceil($tripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tripInap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function show(TripInap $tripinap)
    {
        return response([
            'data' => (new TripInap())->findAll($tripinap),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTripInapRequest $request, TripInap $tripinap)
    {
        DB::beginTransaction();
        try {
            $data = [
                "absensi_id" =>$request->absensi_id,
                "tglabsensi" =>$request->tglabsensi,
                "trado_id" =>$request->trado_id,
                "trado" =>$request->trado,
                "suratpengantar_nobukti" =>$request->suratpengantar_nobukti,
                "jammasukinap" =>$request->jammasukinap,
                "jamkeluarinap" =>$request->jamkeluarinap,
            ];
            $tripInap = (new TripInap())->processUpdate($tripinap,$data);
            $tripInap->position = $this->getPosition($tripInap, $tripInap->getTable())->position;
            if ($request->limit==0) {
                $tripInap->page = ceil($tripInap->position / (10));
            } else {
                $tripInap->page = ceil($tripInap->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tripInap
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request,TripInap $tripinap)
    {
        DB::beginTransaction();

        try {
            $tripInap = (new TripInap())->processDestroy($tripinap->id, 'DELETE Trip Inap');
            $selected = $this->getPosition($tripInap, $tripInap->getTable(), true);
            $tripInap->position = $selected->position;
            $tripInap->id = $selected->id;
            if ($request->limit == 0) {
                $tripInap->page = ceil($tripInap->position / (10));
            } else {
                $tripInap->page = ceil($tripInap->position / ($request->limit ?? 10));
            }
            $tripInap->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $tripInap->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $tripInap
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(TripInap $id)
    {
        DB::beginTransaction();
        try {
            $tripInap =$id;
            $tripInap = (new TripInap())->processApprove($tripInap);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
