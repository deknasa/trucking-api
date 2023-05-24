<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalTradoGambar;
use App\Http\Requests\StoreApprovalTradoGambarRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateApprovalTradoGambarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalTradoGambarController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $approvalTradoGambar = new ApprovalTradoGambar();
        return response([
            'data' => $approvalTradoGambar->get(),
            'attributes' => [
                'totalRows' => $approvalTradoGambar->totalRows,
                'totalPages' => $approvalTradoGambar->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreApprovalTradoGambarRequest $request)
    {
        DB::beginTransaction();
        try{
            $approvalTradoGambar = new ApprovalTradoGambar();
            $approvalTradoGambar->kodetrado = $request->kodetrado;
            $approvalTradoGambar->tglbatas = date('Y-m-d', strtotime($request->tglbatas));
            $approvalTradoGambar->statusapproval = $request->statusapproval;
            $approvalTradoGambar->modifiedby = auth('api')->user()->name;

            if ($approvalTradoGambar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($approvalTradoGambar->getTable()),
                    'postingdari' => 'ENTRY APPROVAL TRADO GAMBAR',
                    'idtrans' => $approvalTradoGambar->id,
                    'nobuktitrans' => $approvalTradoGambar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $approvalTradoGambar->toArray(),
                    'modifiedby' => $approvalTradoGambar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            }

            DB::commit();
            $selected = $this->getPosition($approvalTradoGambar, $approvalTradoGambar->getTable());
            $approvalTradoGambar->position = $selected->position;
            $approvalTradoGambar->page = ceil($approvalTradoGambar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvalTradoGambar
            ], 201);
        }catch (\Throwable $th){
            DB::rollBack();
            throw $th;
        }
    }

    public function show(ApprovalTradoGambar $approvaltradogambar)
    {
        return response([
            'status' => true,
            'data' => $approvaltradogambar
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateApprovalTradoGambarRequest $request, ApprovalTradoGambar $approvaltradogambar)
    {
        DB::beginTransaction();
        try{
            $approvaltradogambar->kodetrado = $request->kodetrado;
            $approvaltradogambar->tglbatas = date('Y-m-d', strtotime($request->tglbatas));
            $approvaltradogambar->statusapproval = $request->statusapproval;
            $approvaltradogambar->modifiedby = auth('api')->user()->name;

            if ($approvaltradogambar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($approvaltradogambar->getTable()),
                    'postingdari' => 'EDIT APPROVAL TRADO GAMBAR',
                    'idtrans' => $approvaltradogambar->id,
                    'nobuktitrans' => $approvaltradogambar->id,
                    'aksi' => 'EDIT',
                    'datajson' => $approvaltradogambar->toArray(),
                    'modifiedby' => $approvaltradogambar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            }

            DB::commit();
            $selected = $this->getPosition($approvaltradogambar, $approvaltradogambar->getTable());
            $approvaltradogambar->position = $selected->position;
            $approvaltradogambar->page = ceil($approvaltradogambar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvaltradogambar
            ], 201);

        }catch(\Throwable $th){
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
        $approvalTradoGambar = new ApprovalTradoGambar();
        $approvalTradoGambar = $approvalTradoGambar->lockAndDestroy($id);
        if ($approvalTradoGambar) {
            $logTrail = [
                'namatabel' => strtoupper($approvalTradoGambar->getTable()),
                'postingdari' => 'DELETE APPROVAL TRADO GAMBAR',
                'idtrans' => $approvalTradoGambar->id,
                'nobuktitrans' => $approvalTradoGambar->id,
                'aksi' => 'DELETE',
                'datajson' => $approvalTradoGambar->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($approvalTradoGambar, $approvalTradoGambar->getTable(), true);
            $approvalTradoGambar->position = $selected->position;
            $approvalTradoGambar->id = $selected->id;
            $approvalTradoGambar->page = ceil($approvalTradoGambar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $approvalTradoGambar
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('approvaltradogambar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
