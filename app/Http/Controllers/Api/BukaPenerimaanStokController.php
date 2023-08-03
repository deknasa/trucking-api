<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\BukaPenerimaanStok;
use App\Http\Requests\StoreBukaPenerimaanStokRequest;
use App\Http\Requests\UpdateBukaPenerimaanStokRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BukaPenerimaanStokController extends Controller
{
    /**
     * @ClassName
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
     */
    public function updateTanggalBatas($id)
    {
        DB::beginTransaction();
        try {
            $bukaPenerimaanStok = (new BukaPenerimaanStok())->processTanggalBatasUpdate($id);
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
