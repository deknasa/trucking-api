<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantarApprovalInputTrip;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSuratPengantarApprovalInputTripRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateSuratPengantarApprovalInputTripRequest;
use Illuminate\Support\Facades\DB;

class SuratPengantarApprovalInputTripController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip();

        return response([
            'data' => $suratPengantarApprovalInputTrip->get(),
            'attributes' => [
                'totalRows' => $suratPengantarApprovalInputTrip->totalRows,
                'totalPages' => $suratPengantarApprovalInputTrip->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreSuratPengantarApprovalInputTripRequest $request)
    {
        DB::beginTransaction();
        try {
            $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip();
            $suratPengantarApprovalInputTrip->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratPengantarApprovalInputTrip->jumlahtrip = $request->jumlahtrip;
            $suratPengantarApprovalInputTrip->modifiedby = auth('api')->user()->name;


            if ($suratPengantarApprovalInputTrip->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantarApprovalInputTrip->getTable()),
                    'postingdari' => 'ENTRY SURAT PENGANTAR APPROVAL',
                    'idtrans' => $suratPengantarApprovalInputTrip->id,
                    'nobuktitrans' => $suratPengantarApprovalInputTrip->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratPengantarApprovalInputTrip->toArray(),
                    'modifiedby' => $suratPengantarApprovalInputTrip->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($suratPengantarApprovalInputTrip, $suratPengantarApprovalInputTrip->getTable());
            $suratPengantarApprovalInputTrip->position = $selected->position;
            $suratPengantarApprovalInputTrip->page = ceil($suratPengantarApprovalInputTrip->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $suratPengantarApprovalInputTrip,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName
     */
    public function show(SuratPengantarApprovalInputTrip $suratPengantarApprovalInputTrip, $id)
    {
        $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip();
        return response([
            'data' => $suratPengantarApprovalInputTrip->findOrFail($id),
            'attributes' => [
                'totalRows' => $suratPengantarApprovalInputTrip->totalRows,
                'totalPages' => $suratPengantarApprovalInputTrip->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateSuratPengantarApprovalInputTripRequest $request, SuratPengantarApprovalInputTrip $suratPengantarApprovalInputTrip, $id)
    {
        DB::beginTransaction();
        try {
            $suratPengantarApprovalInputTrip = SuratPengantarApprovalInputTrip::findOrFail($id);
            $suratPengantarApprovalInputTrip->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratPengantarApprovalInputTrip->jumlahtrip = $request->jumlahtrip;
            $suratPengantarApprovalInputTrip->modifiedby = auth('api')->user()->name;


            if ($suratPengantarApprovalInputTrip->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantarApprovalInputTrip->getTable()),
                    'postingdari' => 'EDIT SURAT PENGANTAR APPROVAL',
                    'idtrans' => $suratPengantarApprovalInputTrip->id,
                    'nobuktitrans' => $suratPengantarApprovalInputTrip->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratPengantarApprovalInputTrip->toArray(),
                    'modifiedby' => $suratPengantarApprovalInputTrip->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($suratPengantarApprovalInputTrip, $suratPengantarApprovalInputTrip->getTable());
            $suratPengantarApprovalInputTrip->position = $selected->position;
            $suratPengantarApprovalInputTrip->page = ceil($suratPengantarApprovalInputTrip->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $suratPengantarApprovalInputTrip,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName
     */
    public function destroy(SuratPengantarApprovalInputTrip $suratPengantarApprovalInputTrip, $id)
    {
        DB::beginTransaction();
        try {
            $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip;
            $suratPengantarApprovalInputTrip = $suratPengantarApprovalInputTrip->lockAndDestroy($id);

            if ($suratPengantarApprovalInputTrip) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantarApprovalInputTrip->getTable()),
                    'postingdari' => 'DELETE SURAT PENGANTAR APPROVAL',
                    'idtrans' => $suratPengantarApprovalInputTrip->id,
                    'nobuktitrans' => $suratPengantarApprovalInputTrip->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratPengantarApprovalInputTrip->toArray(),
                    'modifiedby' => $suratPengantarApprovalInputTrip->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($suratPengantarApprovalInputTrip, $suratPengantarApprovalInputTrip->getTable());
            $suratPengantarApprovalInputTrip->position = $selected->position;
            $suratPengantarApprovalInputTrip->page = ceil($suratPengantarApprovalInputTrip->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $suratPengantarApprovalInputTrip,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName
     */
    public function isTanggalAvaillable()
    {
        $suratPengantarApprovalInputTrip = new SuratPengantarApprovalInputTrip;
        return response([
            'status' => true,
            'message' => 'Berhasil disimpan',
            'data' => $suratPengantarApprovalInputTrip->isTanggalAvaillable()
        ], 201);
        return $suratPengantarApprovalInputTrip->isTanggalAvaillable();
    }
}
