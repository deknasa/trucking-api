<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\BukaPengeluaranStok;
use App\Http\Requests\StoreBukaPengeluaranStokRequest;
use App\Http\Requests\UpdateBukaPengeluaranStokRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BukaPengeluaranStokController extends Controller
{
     /**
     * @ClassName
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
     */
    public function updateTanggalBatas($id)
    {
        DB::beginTransaction();
        try {
            $bukaPengeluaranStok = (new BukaPengeluaranStok())->processTanggalBatasUpdate($id);
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
}
