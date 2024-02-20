<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\BukaPengeluaranStok;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\StoreBukaPengeluaranStokRequest;
use App\Http\Requests\UpdateBukaPengeluaranStokRequest;

class BukaPengeluaranStokController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $bukaPengeluaranStok = new BukaPengeluaranStok();

        return response([
            'data' => $bukaPengeluaranStok->get(),
            'attributes' => [
                'totalRows' => $bukaPengeluaranStok->totalRows,
                'totalPages' => $bukaPengeluaranStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreBukaPengeluaranStokRequest $request)
    {
        DB::beginTransaction();
        try {

            $data =[
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pengeluaranstok_id' => $request->pengeluaranstok_id
            ];
            /* Store header */
            $bukaPengeluaranStok = (new BukaPengeluaranStok())->processStore($data);
            /* Set position and page */
            $bukaPengeluaranStok->position = $this->getPosition($bukaPengeluaranStok, $bukaPengeluaranStok->getTable())->position;
            $bukaPengeluaranStok->page = ceil($bukaPengeluaranStok->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaPengeluaranStok->page = ceil($bukaPengeluaranStok->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaPengeluaranStok
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function show(BukaPengeluaranStok $bukaPengeluaranStok,$id)
    {
        $bukaPengeluaranStok = new BukaPengeluaranStok();
        return response([
            'data' => $bukaPengeluaranStok->findAll($id),
            'attributes' => [
                'totalRows' => $bukaPengeluaranStok->totalRows,
                'totalPages' => $bukaPengeluaranStok->totalPages
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
                $bukaPengeluaranStok = (new BukaPengeluaranStok())->processTanggalBatasUpdate($id);
            }
            // /* Set position and page */
            // $bukaPengeluaranStok->position = $this->getPosition($bukaPengeluaranStok, $bukaPengeluaranStok->getTable())->position;
            // $bukaPengeluaranStok->page = ceil($bukaPengeluaranStok->position / ($request->limit ?? 10));
            // if (isset($request->limit)) {
            //     $bukaPengeluaranStok->page = ceil($bukaPengeluaranStok->position / ($request->limit ?? 10));
            // }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaPengeluaranStok
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($bukaPengeluaranStok);
            $bukaPengeluaranStok = (new BukaPengeluaranStok())->processDestroy($id);
            /* Set position and page */
            $bukaPengeluaranStok->position = $this->getPosition($bukaPengeluaranStok, $bukaPengeluaranStok->getTable())->position;
            $bukaPengeluaranStok->page = ceil($bukaPengeluaranStok->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $bukaPengeluaranStok->page = ceil($bukaPengeluaranStok->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $bukaPengeluaranStok
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
        $BukaPengeluaranStok = new BukaPengeluaranStok();
        return response([
            'status' => true,
            'data' => $BukaPengeluaranStok->isTanggalAvaillable($id)
        ], 201);
    }
}
