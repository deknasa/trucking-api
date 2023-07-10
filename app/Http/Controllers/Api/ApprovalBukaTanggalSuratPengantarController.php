<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalBukaTanggalSuratPengantar;
use App\Http\Requests\StoreApprovalBukaTanggalSuratPengantarRequest;
use App\Http\Requests\UpdateApprovalBukaTanggalSuratPengantarRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalBukaTanggalSuratPengantarController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $approvalBukaTanggal = new ApprovalBukaTanggalSuratPengantar();
        return response([
            'data' => $approvalBukaTanggal->get(),
            'attributes' => [
                'totalRows' => $approvalBukaTanggal->totalRows,
                'totalPages' => $approvalBukaTanggal->totalPages
            ]
        ]);
    }

    public function default()
    {

        $approvalBukaTanggal = new ApprovalBukaTanggalSuratPengantar();
        return response([
            'status' => true,
            'data' => $approvalBukaTanggal->default(),
        ]);
    }


    /**
     * @ClassName 
     */
    public function store(StoreApprovalBukaTanggalSuratPengantarRequest $request)
    {
       
        DB::beginTransaction();

        try {
           
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show(ApprovalBukaTanggalSuratPengantar $approvalBukaTanggalSP)
    {
        return response([
            'status' => true,
            'data' => $approvalBukaTanggalSP
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateApprovalBukaTanggalSuratPengantarRequest $request, ApprovalBukaTanggalSuratPengantar $approvalBukaTanggalSP): JsonResponse
    {
        $data = [
            'tglbukti' => $request->tglbukti,
            'jumlah' => $request->jumlah,
            'statusapproval' => $request->statusapproval,
        ];
        DB::beginTransaction();

        try {
            $approvalBukaTanggal = (new ApprovalBukaTanggalSuratPengantar())->processUpdate($approvalBukaTanggalSP, $data);
            $approvalBukaTanggal->position = $this->getPosition($approvalBukaTanggal, $approvalBukaTanggal->getTable())->position;
            $approvalBukaTanggal->page = ceil($approvalBukaTanggal->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah.',
                'data' => $approvalBukaTanggal
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $approvalBukaTanggal = (new ApprovalBukaTanggalSuratPengantar())->processDestroy($id);
            $selected = $this->getPosition($approvalBukaTanggal, $approvalBukaTanggal->getTable(), true);
            $approvalBukaTanggal->position = $selected->position;
            $approvalBukaTanggal->id = $selected->id;
            $approvalBukaTanggal->page = ceil($approvalBukaTanggal->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $approvalBukaTanggal
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('approvalbukatanggalsuratpengantar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
