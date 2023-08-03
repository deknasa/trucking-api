<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BukaAbsensi;
use App\Http\Requests\StoreBukaAbsensiRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Requests\UpdateBukaAbsensiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukaAbsensiController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $bukaAbsensi = new BukaAbsensi();

        return response([
            'data' => $bukaAbsensi->get(),
            'attributes' => [
                'totalRows' => $bukaAbsensi->totalRows,
                'totalPages' => $bukaAbsensi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreBukaAbsensiRequest $request)
    {
        DB::beginTransaction();
        try {


            $data =[
                'tglabsensi' => date('Y-m-d', strtotime($request->tglabsensi))
            ];
            /* Store header */
            $bukaAbsensi = (new BukaAbsensi())->processStore($data);
            /* Set position and page */
            $bukaAbsensi->position = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable())->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function show(BukaAbsensi $bukaAbsensi,$id)
    {
        $bukaAbsensi = new BukaAbsensi();
        return response([
            'data' => $bukaAbsensi->findOrFail($id),
            'attributes' => [
                'totalRows' => $bukaAbsensi->totalRows,
                'totalPages' => $bukaAbsensi->totalPages
            ]
        ]);
    }

    
    public function update(UpdateBukaAbsensiRequest $request, BukaAbsensi $bukaAbsensi,$id)
    {
        DB::beginTransaction();
        try {
            $data =[
                'tglabsensi' => date('Y-m-d', strtotime($request->tglabsensi))
            ];
            /* Store header */
            $bukaAbsensi = (new BukaAbsensi())->processStore($data);
            /* Set position and page */
            $bukaAbsensi->position = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable())->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName
     */
    public function updateTanggalBatas($id)
    {
        DB::beginTransaction();
        try {
            $bukaAbsensi = (new BukaAbsensi())->processTanggalBatasUpdate($id);
            /* Set position and page */
            $bukaAbsensi->position = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable())->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($bukaAbsensi);
            $bukaAbsensi = (new BukaAbsensi())->processDestroy($id);
            /* Set position and page */
            $bukaAbsensi->position = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable())->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}