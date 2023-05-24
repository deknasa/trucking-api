<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalSupirGambar;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreApprovalSupirGambarRequest;
use App\Http\Requests\UpdateApprovalSupirGambarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalSupirGambarController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $approvalSupirGambar = new ApprovalSupirGambar();

        return response([
            'data' => $approvalSupirGambar->get(),
            'attributes' => [
                'totalRows' => $approvalSupirGambar->totalRows,
                'totalPages' => $approvalSupirGambar->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreApprovalSupirGambarRequest $request)
    {
        DB::beginTransaction();
        try {
            $approvalSupirGambar = new ApprovalSupirGambar();
            $approvalSupirGambar->namasupir = ($request->namasupir == null) ? "" : $request->namasupir;
            $approvalSupirGambar->noktp = ($request->noktp == null) ? "" : $request->noktp;
            $approvalSupirGambar->statusapproval = ($request->statusapproval == null) ? "" : $request->statusapproval;
            $approvalSupirGambar->tglbatas = date('Y-m-d', strtotime($request->tglbatas));
            // $approvalSupirGambar->modifiedby = auth('api')->user()->name;

            
            if ($approvalSupirGambar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($approvalSupirGambar->getTable()),
                    'postingdari' => 'ENTRY BUKA ABSENSI',
                    'idtrans' => $approvalSupirGambar->id,
                    'nobuktitrans' => $approvalSupirGambar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $approvalSupirGambar->toArray(),
                    'modifiedby' => $approvalSupirGambar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($approvalSupirGambar, $approvalSupirGambar->getTable());
            $approvalSupirGambar->position = $selected->position;
            $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirGambar,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

   /**
    * @ClassName
    */
    public function show(ApprovalSupirGambar $approvalSupirGambar,$id)
    {
        $approvalSupirGambar = new ApprovalSupirGambar();
        return response([
            'data' => $approvalSupirGambar->findOrFail($id),
            'attributes' => [
                'totalRows' => $approvalSupirGambar->totalRows,
                'totalPages' => $approvalSupirGambar->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateApprovalSupirGambarRequest $request, ApprovalSupirGambar $approvalSupirGambar,$id)
    {
        DB::beginTransaction();
        try {
            $approvalSupirGambar = ApprovalSupirGambar::findOrFail($id);
            $approvalSupirGambar->tglbatas = date('Y-m-d', strtotime($request->tglbatas));
            // $approvalSupirGambar->modifiedby = auth('api')->user()->name;

            
            if ($approvalSupirGambar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($approvalSupirGambar->getTable()),
                    'postingdari' => 'EDIT BUKA ABSENSI',
                    'idtrans' => $approvalSupirGambar->id,
                    'nobuktitrans' => $approvalSupirGambar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $approvalSupirGambar->toArray(),
                    'modifiedby' => $approvalSupirGambar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($approvalSupirGambar, $approvalSupirGambar->getTable());
            $approvalSupirGambar->position = $selected->position;
            $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirGambar,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName
     */
    public function destroy(ApprovalSupirGambar $approvalSupirGambar,$id)
    {
        DB::beginTransaction();
        try {
            $approvalSupirGambar = new ApprovalSupirGambar;
            $approvalSupirGambar = $approvalSupirGambar->lockAndDestroy($id);
            
            if ($approvalSupirGambar) {
                $logTrail = [
                    'namatabel' => strtoupper($approvalSupirGambar->getTable()),
                    'postingdari' => 'DELETE BUKA ABSENSI',
                    'idtrans' => $approvalSupirGambar->id,
                    'nobuktitrans' => $approvalSupirGambar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $approvalSupirGambar->toArray(),
                    'modifiedby' => $approvalSupirGambar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            $selected = $this->getPosition($approvalSupirGambar, $approvalSupirGambar->getTable());
            $approvalSupirGambar->position = $selected->position;
            $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirGambar,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
    public function default()
    {

        $approvalSupirGambar = new ApprovalSupirGambar();
        return response([
            'status' => true,
            'data' => $approvalSupirGambar->default(),
        ]);
    }
}
