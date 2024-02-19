<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\BukaPenerimaanStok;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreBukaPenerimaanStokRequest;
use App\Http\Requests\UpdateBukaPenerimaanStokRequest;

class BukaPenerimaanStokController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $bukaPenerimaanStok = new BukaPenerimaanStok();

        return response([
            'data' => $bukaPenerimaanStok->get(),
            'attributes' => [
                'totalRows' => $bukaPenerimaanStok->totalRows,
                'totalPages' => $bukaPenerimaanStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBukaPenerimaanStokRequest $request)
    {
        DB::beginTransaction();
        try {

            $data =[
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'penerimaanstok_id' => $request->penerimaanstok_id
            ];
            /* Store header */
            $bukaPenerimaanStok = (new BukaPenerimaanStok())->processStore($data);
            /* Set position and page */
            $bukaPenerimaanStok->position = $this->getPosition($bukaPenerimaanStok, $bukaPenerimaanStok->getTable())->position;
            $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaPenerimaanStok
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function show(BukaPenerimaanStok $bukaPenerimaanStok,$id)
    {
        $bukaPenerimaanStok = new BukaPenerimaanStok();
        return response([
            'data' => $bukaPenerimaanStok->findAll($id),
            'attributes' => [
                'totalRows' => $bukaPenerimaanStok->totalRows,
                'totalPages' => $bukaPenerimaanStok->totalPages
            ]
        ]);
    }
    /**
     * @ClassName
     * @Keterangan PERBARUI BATAS TANGGAL
     */
    public function updateTanggalBatas(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->Id as $id) {
                $bukaPenerimaanStok = (new BukaPenerimaanStok())->processTanggalBatasUpdate($id);
            }
            // /* Set position and page */
            // $bukaPenerimaanStok->position = $this->getPosition($bukaPenerimaanStok, $bukaPenerimaanStok->getTable())->position;
            // $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
            // if (isset($request->limit)) {
            //     $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
            // }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaPenerimaanStok
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    // public function updateTanggalBatas($id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $bukaPenerimaanStok = (new BukaPenerimaanStok())->processTanggalBatasUpdate($id);
    //         /* Set position and page */
    //         $bukaPenerimaanStok->position = $this->getPosition($bukaPenerimaanStok, $bukaPenerimaanStok->getTable())->position;
    //         $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
    //         if (isset($request->limit)) {
    //             $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
    //         }

    //         DB::commit();
    //         return response()->json([
    //             'message' => 'Berhasil disimpan',
    //             'data' => $bukaPenerimaanStok
    //         ], 201);    
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($bukaPenerimaanStok);
            $bukaPenerimaanStok = (new BukaPenerimaanStok())->processDestroy($id);
            /* Set position and page */
            $bukaPenerimaanStok->position = $this->getPosition($bukaPenerimaanStok, $bukaPenerimaanStok->getTable())->position;
            $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaPenerimaanStok->page = ceil($bukaPenerimaanStok->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaPenerimaanStok
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function isTanggalAvaillable($id)
    {
        $bukaPenerimaanStok = new BukaPenerimaanStok;
        return response([
            'status' => true,
            'data' => $bukaPenerimaanStok->isTanggalAvaillable($id)
        ], 201);
    }
}
