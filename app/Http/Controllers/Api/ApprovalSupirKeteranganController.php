<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalSupirKeterangan;
use App\Http\Requests\StoreApprovalSupirKeteranganRequest;
use App\Http\Requests\UpdateApprovalSupirKeteranganRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalSupirKeteranganController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $approvalSupirKeterangan = new ApprovalSupirKeterangan();

        return response([
            'data' => $approvalSupirKeterangan->get(),
            'attributes' => [
                'totalRows' => $approvalSupirKeterangan->totalRows,
                'totalPages' => $approvalSupirKeterangan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreApprovalSupirKeteranganRequest $request)
    {
        DB::beginTransaction();
        try {
            
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "statusapproval" => $request->statusapproval,
                "tglbatas" => $request->tglbatas,
            ];
            /* Store header */
            $approvalSupirKeterangan = (new ApprovalSupirKeterangan())->processStore($data);
            /* Set position and page */
            $approvalSupirKeterangan->position = $this->getPosition($approvalSupirKeterangan, $approvalSupirKeterangan->getTable())->position;
            $approvalSupirKeterangan->page = ceil($approvalSupirKeterangan->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $approvalSupirKeterangan->page = ceil($approvalSupirKeterangan->position / ($request->limit ?? 10));
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirKeterangan
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function show(ApprovalSupirKeterangan $approvalSupirKeterangan,$id)
    {
        $approvalSupirKeterangan = new ApprovalSupirKeterangan();
        return response([
            'data' => $approvalSupirKeterangan->findOrFail($id),
            'attributes' => [
                'totalRows' => $approvalSupirKeterangan->totalRows,
                'totalPages' => $approvalSupirKeterangan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateApprovalSupirKeteranganRequest $request, ApprovalSupirKeterangan $approvalSupirKeterangan, $id)
    {
        DB::beginTransaction();
        try {
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "statusapproval" => $request->statusapproval,
                "tglbatas" => $request->tglbatas,
            ];
            /* Store header */
            $approvalSupirKeterangan = ApprovalSupirKeterangan::findOrFail($id);
            $approvalSupirKeterangan = (new ApprovalSupirKeterangan())->processUpdate($approvalSupirKeterangan,$data);
            /* Set position and page */
            $approvalSupirKeterangan->position = $this->getPosition($approvalSupirKeterangan, $approvalSupirKeterangan->getTable())->position;
            $approvalSupirKeterangan->page = ceil($approvalSupirKeterangan->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $approvalSupirKeterangan->page = ceil($approvalSupirKeterangan->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirKeterangan
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

   /**
    * @ClassName 
    */
    public function destroy(ApprovalSupirKeterangan $approvalSupirKeterangan,$id)
    {
        DB::beginTransaction();
        try {
            // dd($approvalSupirKeterangan);
            $approvalSupirKeterangan = (new ApprovalSupirKeterangan())->processDestroy($id);
            /* Set position and page */
            $approvalSupirKeterangan->position = $this->getPosition($approvalSupirKeterangan, $approvalSupirKeterangan->getTable())->position;
            $approvalSupirKeterangan->page = ceil($approvalSupirKeterangan->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $approvalSupirKeterangan->page = ceil($approvalSupirKeterangan->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirKeterangan
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}