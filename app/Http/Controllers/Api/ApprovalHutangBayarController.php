<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApprovalHutangBayarRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\ApprovalHutangBayar;
use App\Models\HutangBayarHeader;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalHutangBayarController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $approvalHutangBayar = new ApprovalHutangBayar();
        return response([
            'data' => $approvalHutangBayar->get(),
            'attributes' => [
                'totalRows' => $approvalHutangBayar->totalRows,
                'totalPages' => $approvalHutangBayar->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreApprovalHutangBayarRequest $request)
    {
        DB::BeginTransaction();
        try {

            for ($i = 0; $i < count($request->hutangbayarId); $i++) {

                $approveHutangBayar = HutangBayarHeader::lockForUpdate()->findOrFail($request->hutangbayarId[$i]);

                $statusApp = Parameter::where('id',$request->approve)->first();
                $approveHutangBayar->statusapproval = $request->approve;
                $approveHutangBayar->userapproval = auth('api')->user()->name;
                $approveHutangBayar->tglapproval = date('Y-m-d H:i:s');

                $approveHutangBayar->save();
                
                $logTrail = [
                    'namatabel' => strtoupper($approveHutangBayar->getTable()),
                    'postingdari' => 'APPROVED HUTANG',
                    'idtrans' => $approveHutangBayar->id,
                    'nobuktitrans' => $approveHutangBayar->nobukti,
                    'aksi' => $statusApp->text,
                    'datajson' => $approveHutangBayar->toArray(),
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
                'data' => $approveHutangBayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }
}
