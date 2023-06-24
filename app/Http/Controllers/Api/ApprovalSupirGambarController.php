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
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "statusapproval" => $request->statusapproval,
                "tglbatas" => $request->tglbatas,
            ];
            /* Store header */
            $approvalSupirGambar = (new ApprovalSupirGambar())->processStore($data);
            /* Set position and page */
            $approvalSupirGambar->position = $this->getPosition($approvalSupirGambar, $approvalSupirGambar->getTable())->position;
            $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirGambar
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "statusapproval" => $request->statusapproval,
                "tglbatas" => $request->tglbatas,
            ];
            /* Store header */
            $approvalSupirGambar = ApprovalSupirGambar::findOrFail($id);
            $approvalSupirGambar = (new ApprovalSupirGambar())->processUpdate($approvalSupirGambar,$data);
            /* Set position and page */
            $approvalSupirGambar->position = $this->getPosition($approvalSupirGambar, $approvalSupirGambar->getTable())->position;
            $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirGambar
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName
     */
    public function destroy(ApprovalSupirGambar $approvalSupirGambar,$id)
    {
        DB::beginTransaction();
        try {
            // dd($approvalSupirGambar);
            $approvalSupirGambar = (new ApprovalSupirGambar())->processDestroy($id);
            /* Set position and page */
            $approvalSupirGambar->position = $this->getPosition($approvalSupirGambar, $approvalSupirGambar->getTable())->position;
            $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $approvalSupirGambar->page = ceil($approvalSupirGambar->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirGambar
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
