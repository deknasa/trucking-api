<?php

namespace App\Http\Controllers\Api;

use App\Models\BukaAbsensi;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBukaAbsensiRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Requests\UpdateBukaAbsensiRequest;
use DB;

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
            $bukaAbsensi = new BukaAbsensi();
            $bukaAbsensi->tglabsensi = date('Y-m-d', strtotime($request->tglabsensi));
            $bukaAbsensi->modifiedby = auth('api')->user()->name;

            
            if ($bukaAbsensi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bukaAbsensi->getTable()),
                    'postingdari' => 'ENTRY BUKA ABSENSI',
                    'idtrans' => $bukaAbsensi->id,
                    'nobuktitrans' => $bukaAbsensi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $bukaAbsensi->toArray(),
                    'modifiedby' => $bukaAbsensi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable());
            $bukaAbsensi->position = $selected->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
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

    /**
     * @ClassName
     */
    public function update(UpdateBukaAbsensiRequest $request, BukaAbsensi $bukaAbsensi,$id)
    {
        DB::beginTransaction();
        try {
            $bukaAbsensi = BukaAbsensi::findOrFail($id);
            $bukaAbsensi->tglabsensi = date('Y-m-d', strtotime($request->tglabsensi));
            $bukaAbsensi->modifiedby = auth('api')->user()->name;

            
            if ($bukaAbsensi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($bukaAbsensi->getTable()),
                    'postingdari' => 'EDIT BUKA ABSENSI',
                    'idtrans' => $bukaAbsensi->id,
                    'nobuktitrans' => $bukaAbsensi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $bukaAbsensi->toArray(),
                    'modifiedby' => $bukaAbsensi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable());
            $bukaAbsensi->position = $selected->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName
     */
    public function destroy(BukaAbsensi $bukaAbsensi,$id)
    {
        DB::beginTransaction();
        try {
            $bukaAbsensi = new BukaAbsensi;
            $bukaAbsensi = $bukaAbsensi->lockAndDestroy($id);
            
            if ($bukaAbsensi) {
                $logTrail = [
                    'namatabel' => strtoupper($bukaAbsensi->getTable()),
                    'postingdari' => 'DELETE BUKA ABSENSI',
                    'idtrans' => $bukaAbsensi->id,
                    'nobuktitrans' => $bukaAbsensi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $bukaAbsensi->toArray(),
                    'modifiedby' => $bukaAbsensi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($bukaAbsensi, $bukaAbsensi->getTable());
            $bukaAbsensi->position = $selected->position;
            $bukaAbsensi->page = ceil($bukaAbsensi->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bukaAbsensi,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
