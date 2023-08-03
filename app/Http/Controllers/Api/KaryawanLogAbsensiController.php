<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\KaryawanLogAbsensi;
use App\Http\Requests\StoreKaryawanLogAbsensiRequest;
use App\Http\Requests\UpdateKaryawanLogAbsensiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanLogAbsensiController extends Controller
{

    /**
     * @ClassName
     * 
     */
    public function index()
    {
        $logAbsensi = new KaryawanLogAbsensi();
        return response([
            'data' => $logAbsensi->get(),
            'attributes' => [
                'totalRows' => $logAbsensi->totalRows,
                'totalPages' => $logAbsensi->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     * 
     */
    public function store(StoreKaryawanLogAbsensiRequest $request)
    {
        //
    }


    public function show($id)
    {
        return response([
            'data' => (new KaryawanLogAbsensi())->findAll($id),
        ]);
    }


    /**
     * @ClassName
     * 
     */
    public function update(UpdateKaryawanLogAbsensiRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglresign' => $request->tglresign,
                'statusaktif' => $request->statusaktif
            ];
            $karyawanLogAbsensi = (new KaryawanLogAbsensi())->processUpdate($id, $data);
            $karyawanLogAbsensi->id =  $id;
            $getPosition = (new KaryawanLogAbsensi())->createTemp($id);
            $karyawanLogAbsensi->position =  $getPosition->position;
            if ($request->limit == 0) {
                $karyawanLogAbsensi->page = ceil($karyawanLogAbsensi->position / (10));
            } else {
                $karyawanLogAbsensi->page = ceil($karyawanLogAbsensi->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $karyawanLogAbsensi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName
     * 
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $karyawanLogAbsensi = (new KaryawanLogAbsensi())->processDestroy($id, 'DELETE KARYAWAN LOG ABSENSI');
            // $selected = $this->getPosition($karyawanLogAbsensi, $karyawanLogAbsensi->getTable(), true);

            $selected =  (new KaryawanLogAbsensi())->createTemp($id, true);
            $karyawanLogAbsensi->position = $selected->position;
            $karyawanLogAbsensi->id = $id;
            if ($request->limit == 0) {
                $karyawanLogAbsensi->page = ceil($karyawanLogAbsensi->position / (10));
            } else {
                $karyawanLogAbsensi->page = ceil($karyawanLogAbsensi->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $karyawanLogAbsensi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
