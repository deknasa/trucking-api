<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApprovalNotaHeaderRequest;
use App\Models\ApprovalNotaHeader;
use App\Models\LogTrail;
use App\Models\NotaDebetHeader;
use App\Models\NotaKreditHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalNotaHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $approvalNota = new ApprovalNotaHeader();
        return response([
            'data' => $approvalNota->get(),
            'attributes' => [
                'totalRows' => $approvalNota->totalRows,
                'totalPages' => $approvalNota->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreApprovalNotaHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $tabel = $request->tabel;
            
            for ($i = 0; $i < count($request->notaId); $i++) {

                if($tabel == 'notakreditheader'){
                    $approveNota = NotaKreditHeader::lockForUpdate()->findOrFail($request->notaId[$i]);
                }else{
                    $approveNota = NotaDebetHeader::lockForUpdate()->findOrFail($request->notaId[$i]);
                }
               
                $approveNota->statusapproval = $request->approve;
                $approveNota->userapproval = auth('api')->user()->name;
                $approveNota->tglapproval = date('Y-m-d H:i:s');

                $approveNota->save();
            }
           
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $approveNota
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


}
