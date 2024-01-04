<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApprovalNotaHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\ApprovalNotaHeader;
use App\Models\LogTrail;
use App\Models\NotaDebetHeader;
use App\Models\NotaKreditHeader;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalNotaHeaderController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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

    public function default()
    {
        $approvalNota = new ApprovalNotaHeader();
        return response([
            'status' => true,
            'data' => $approvalNota->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreApprovalNotaHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $tabel = ($request->tabel == 'NOTA DEBET') ? 'notadebetheader' : 'notakreditheader';

            
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
                
                $statusApp = Parameter::where('id',$request->approve)->first();
                $logTrail = [
                    'namatabel' => strtoupper($approveNota->getTable()),
                    'postingdari' => 'APPROVED NOTA',
                    'idtrans' => $approveNota->id,
                    'nobuktitrans' => $approveNota->nobukti,
                    'aksi' => $statusApp->text,
                    'datajson' => $approveNota->toArray(),
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
                'data' => $approveNota
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }
    
    public function combo(Request $request)
    {
        $parameters = Parameter::select('kelompok')->whereIn('kelompok', ['NOTA DEBET','NOTA KREDIT'])
            ->groupBy('kelompok')
            ->get();

        return response([
            'data' => $parameters
        ]);
    }


}
