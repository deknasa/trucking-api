<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApprovalPendapatanSupirRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\ApprovalPendapatanSupir;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\PendapatanSupirHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalPendapatanSupirController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $approvalPendapatanSupir = new ApprovalPendapatanSupir();
        return response([
            'data' => $approvalPendapatanSupir->get(),
            'attributes' => [
                'totalRows' => $approvalPendapatanSupir->totalRows,
                'totalPages' => $approvalPendapatanSupir->totalPages
            ]
        ]);
    }
    
    public function default()
    {
        $approvalPendapatan = new ApprovalPendapatanSupir();
        return response([
            'status' => true,
            'data' => $approvalPendapatan->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalPendapatanSupirRequest $request)
    {
        DB::BeginTransaction();
        try {

            for ($i = 0; $i < count($request->pendapatanId); $i++) {

                $approvePendapatan = PendapatanSupirHeader::lockForUpdate()->findOrFail($request->pendapatanId[$i]);

                $approvePendapatan->statusapproval = $request->approve;
                $approvePendapatan->userapproval = auth('api')->user()->name;
                $approvePendapatan->tglapproval = date('Y-m-d h:i:s');

                $approvePendapatan->save();
                
                $statusApp = Parameter::where('id',$request->approve)->first();
                
                $logTrail = [
                    'namatabel' => strtoupper($approvePendapatan->getTable()),
                    'postingdari' => 'APPROVAL PENDAPATAN',
                    'idtrans' => $approvePendapatan->id,
                    'nobuktitrans' => $approvePendapatan->nobukti,
                    'aksi' => $statusApp->text,
                    'datajson' => $approvePendapatan->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedlogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedlogTrail);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approvePendapatan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
