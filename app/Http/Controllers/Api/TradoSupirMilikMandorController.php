<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TradoSupirMilikMandor;
use App\Http\Requests\StoreTradoSupirMilikMandorRequest;
use App\Http\Requests\UpdateTradoSupirMilikMandorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradoSupirMilikMandorController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $tradoSupirMilikMandor = new TradoSupirMilikMandor();

        return response([
            'data' => $tradoSupirMilikMandor->get(),
            'attributes' => [
                'totalRows' => $tradoSupirMilikMandor->totalRows,
                'totalPages' => $tradoSupirMilikMandor->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTradoSupirMilikMandorRequest $request)
    {
        DB::beginTransaction();
        try {
            
            $data =[
                "mandor_id" => $request->mandor_id,
                "supir_id" => $request->supir_id,
                "trado_id" => $request->trado_id,
            ];
            /* Store header */
            $tradoSupirMilikMandor = (new TradoSupirMilikMandor())->processStore($data);
            /* Set position and page */
            $tradoSupirMilikMandor->position = $this->getPosition($tradoSupirMilikMandor, $tradoSupirMilikMandor->getTable())->position;
            if ($request->limit==0) {
                $tradoSupirMilikMandor->page = ceil($tradoSupirMilikMandor->position / (10));
            } else {
                $tradoSupirMilikMandor->page = ceil($tradoSupirMilikMandor->position / ($request->limit ?? 10));
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $tradoSupirMilikMandor
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function show(TradoSupirMilikMandor $tradoSupirMilikMandor,$id)
    {
        $tradoSupirMilikMandor = new TradoSupirMilikMandor();
        return response([
            'data' => $tradoSupirMilikMandor->find($id),
            'attributes' => [
                'totalRows' => $tradoSupirMilikMandor->totalRows,
                'totalPages' => $tradoSupirMilikMandor->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTradoSupirMilikMandorRequest $request, TradoSupirMilikMandor $tradoSupirMilikMandor,$id)
    {
        DB::beginTransaction();
        try {
            $data =[
                "mandor_id" => $request->mandor_id,
                "supir_id" => $request->supir_id,
                "trado_id" => $request->trado_id,
            ];
            /* Store header */
            $tradoSupirMilikMandor = TradoSupirMilikMandor::findOrFail($id);
            $tradoSupirMilikMandor = (new TradoSupirMilikMandor())->processUpdate($tradoSupirMilikMandor,$data);
            /* Set position and page */
            $tradoSupirMilikMandor->position = $this->getPosition($tradoSupirMilikMandor, $tradoSupirMilikMandor->getTable())->position;
            if ($request->limit==0) {
                $tradoSupirMilikMandor->page = ceil($tradoSupirMilikMandor->position / (10));
            } else {
                $tradoSupirMilikMandor->page = ceil($tradoSupirMilikMandor->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $tradoSupirMilikMandor
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(TradoSupirMilikMandor $tradoSupirMilikMandor,$id, Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($tradoSupirMilikMandor);
            $tradoSupirMilikMandor = (new TradoSupirMilikMandor())->processDestroy($id);
            /* Set position and page */
            $tradoSupirMilikMandor->position = $this->getPosition($tradoSupirMilikMandor, $tradoSupirMilikMandor->getTable())->position;
            if ($request->limit==0) {
                $tradoSupirMilikMandor->page = ceil($tradoSupirMilikMandor->position / (10));
            } else {
                $tradoSupirMilikMandor->page = ceil($tradoSupirMilikMandor->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $tradoSupirMilikMandor
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
