<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StokPusat;
use App\Http\Requests\StoreStokPusatRequest;
use App\Http\Requests\UpdateStokPusatRequest;
use App\Models\StokPusatRincian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StokPusatController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->get(),
            'attributes' => [
                'totalRows' => $stokPusat->totalRows,
                'totalPages' => $stokPusat->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreStokPusatRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'namaterpusat' => str_replace('"', "''", strtoupper($request->namastok)),
                'kelompok' => $request->kelompok,
                'kelompok_id' => $request->kelompok_id,
                'stok_idmdn' => $request->stok_idmdn,
                'namastokmdn' => str_replace('"', "''", strtoupper($request->namastokmdn)),
                'gambarmdn' => $request->gambarmdn,
                'stok_idjkt' => $request->stok_idjkt,
                'namastokjkt' => str_replace('"', "''", strtoupper($request->namastokjkt)),
                'gambarjkt' => $request->gambarjkt,
                'stok_idjkttnl' => $request->stok_idjkttnl,
                'namastokjkttnl' => str_replace('"', "''", strtoupper($request->namastokjkttnl)),
                'gambarjkttnl' => $request->gambarjkttnl,
                'stok_idmks' => $request->stok_idmks,
                'namastokmks' => str_replace('"', "''", strtoupper($request->namastokmks)),
                'gambarmks' => $request->gambarmks,
                'stok_idsby' => $request->stok_idsby,
                'namastoksby' => str_replace('"', "''", strtoupper($request->namastoksby)),
                'gambarsby' => $request->gambarsby,
                'stok_idbtg' => $request->stok_idbtg,
                'namastokbtg' => str_replace('"', "''", strtoupper($request->namastokbtg)),
                'gambarbtg' => $request->gambarbtg,
                'stok_idmdndel' => $request->stok_idmdndel,
                'stok_idjktdel' => $request->stok_idjktdel,
                'stok_idjkttnldel' => $request->stok_idjkttnldel,
                'stok_idmksdel' => $request->stok_idmksdel,
                'stok_idsbydel' => $request->stok_idsbydel,
                'stok_idbtgdel' => $request->stok_idbtgdel, 
            ];
            $stok = (new StokPusat())->processStore($data);
            $stok->position = $this->getPosition($stok, $stok->getTable())->position;
            if ($request->limit == 0) {
                $stok->page = ceil($stok->position / (10));
            } else {
                $stok->page = ceil($stok->position / ($request->limit ?? 10));
            }
            $this->stok = $stok;
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $stok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function dataJkt(Request $request)
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->dataJkt($request->kelompok_id),
        ]);
    }
    public function dataMdn(Request $request)
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->dataMdn($request->kelompok_id),
            'attributes' => [
                'totalRows' => $stokPusat->totalRows,
                'totalPages' => $stokPusat->totalPages
            ]
        ]);
    }
    public function dataSby(Request $request)
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->dataSby($request->kelompok_id),
            'attributes' => [
                'totalRows' => $stokPusat->totalRows,
                'totalPages' => $stokPusat->totalPages
            ]
        ]);
    }
    public function dataJktTnl(Request $request)
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->dataJktTnl($request->kelompok_id),
        ]);
    }
    public function dataMks(Request $request)
    {
        try {
            $stokPusat = new StokPusat();
            return response([
                'data' => $stokPusat->dataMks($request->kelompok_id),
                'attributes' => [
                    'totalRows' => $stokPusat->totalRows,
                    'totalPages' => $stokPusat->totalPages
                ]
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function dataMnd(Request $request)
    {
        $stokPusat = new StokPusat();
        return response([
            'data' => $stokPusat->dataMnd($request->kelompok_id),
            'attributes' => [
                'totalRows' => $stokPusat->totalRows,
                'totalPages' => $stokPusat->totalPages
            ]
        ]);
    }
    public function getData(Request $request)
    {
        $stokPusat = new StokPusat();

        return response([
            'data' => $stokPusat->getData($request->kelompok_id)
        ]);
    }

    public function show($id)
    {
        $data = (new StokPusat())->findAll($id);
        $mdn = (new StokPusatRincian())->findMdn($id);
        $jkt = (new StokPusatRincian())->findJkt($id);
        $tnl = (new StokPusatRincian())->findJktTnl($id);
        $mks = (new StokPusatRincian())->findMks($id);
        $sby = (new StokPusatRincian())->findSby($id);
        $btg = (new StokPusatRincian())->findBtg($id);

        return response([
            'data' => $data,
            'mdn' => $mdn,
            'jkt' => $jkt,
            'tnl' => $tnl,
            'sby' => $sby,
            'mks' => $mks,
            'btg' => $btg,
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateStokPusatRequest $request, StokPusat $stokpusat): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'namaterpusat' => str_replace('"', "''", strtoupper($request->namastok)),
                'kelompok' => $request->kelompok,
                'kelompok_id' => $request->kelompok_id,
                'stok_idmdn' => $request->stok_idmdn,
                'namastokmdn' => str_replace('"', "''", strtoupper($request->namastokmdn)),
                'gambarmdn' => $request->gambarmdn,
                'stok_idjkt' => $request->stok_idjkt,
                'namastokjkt' => str_replace('"', "''", strtoupper($request->namastokjkt)),
                'gambarjkt' => $request->gambarjkt,
                'stok_idjkttnl' => $request->stok_idjkttnl,
                'namastokjkttnl' => str_replace('"', "''", strtoupper($request->namastokjkttnl)),
                'gambarjkttnl' => $request->gambarjkttnl,
                'stok_idmks' => $request->stok_idmks,
                'namastokmks' => str_replace('"', "''", strtoupper($request->namastokmks)),
                'gambarmks' => $request->gambarmks,
                'stok_idsby' => $request->stok_idsby,
                'namastoksby' => str_replace('"', "''", strtoupper($request->namastoksby)),
                'gambarsby' => $request->gambarsby,
                'stok_idbtg' => $request->stok_idbtg,
                'namastokbtg' => str_replace('"', "''", strtoupper($request->namastokbtg)),
                'gambarbtg' => $request->gambarbtg,
                'stok_idmdndel' => $request->stok_idmdndel,
                'stok_idjktdel' => $request->stok_idjktdel,
                'stok_idjkttnldel' => $request->stok_idjkttnldel,
                'stok_idmksdel' => $request->stok_idmksdel,
                'stok_idsbydel' => $request->stok_idsbydel,
                'stok_idbtgdel' => $request->stok_idbtgdel, 
            ];
            $stokPusat = (new StokPusat())->processUpdate($stokpusat, $data);
            $stokPusat->position = $this->getPosition($stokPusat, $stokPusat->getTable())->position;
            if ($request->limit == 0) {
                $stokPusat->page = ceil($stokPusat->position / (10));
            } else {
                $stokPusat->page = ceil($stokPusat->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $stokPusat
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
            $stokPusat = (new StokPusat())->processDestroy($id, 'DELETE STOK PUSAT');
            $selected = $this->getPosition($stokPusat, $stokPusat->getTable(), true);
            $stokPusat->position = $selected->position;
            $stokPusat->id = $selected->id;
            if ($request->limit == 0) {
                $stokPusat->page = ceil($stokPusat->position / (10));
            } else {
                $stokPusat->page = ceil($stokPusat->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $stokPusat
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getImage(string $cabang, string $filename, string $type)
    {
        if (Storage::exists("stokpusat/$cabang/$filename")) {
            return response()->file(storage_path("app/stokpusat/$cabang/$filename"));
        } else {
            return response()->file(storage_path("app/no-image.jpg"));
        }
    }
}
