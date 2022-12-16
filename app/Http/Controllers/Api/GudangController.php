<?php

namespace App\Http\Controllers\Api;

use App\Models\Gudang;
use App\Http\Requests\StoreGudangRequest;
use App\Http\Requests\UpdateGudangRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\StokPersediaan;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class GudangController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $gudang = new Gudang();
        return response([
            'data' => $gudang->get(),
            'attributes' => [
                'totalRows' => $gudang->totalRows,
                'totalPages' => $gudang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreGudangRequest $request)
    {
        DB::beginTransaction();

        try {
            $gudang = new Gudang();
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($gudang->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'ENTRY GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $gudang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Gudang $gudang)
    {
        return response([
            'status' => true,
            'data' => $gudang
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(StoreGudangRequest $request, Gudang $gudang)
    {
        DB::beginTransaction();
        try {
            $gudang->gudang = $request->gudang;
            $gudang->statusaktif = $request->statusaktif;
            $gudang->modifiedby = auth('api')->user()->name;

            if ($gudang->save()) {


                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'EDIT GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'EDIT',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                $param1 = $gudang->id;
                $param2 = $gudang->modifiedby;
                $stokgudang = Stok::select(DB::raw(
                    "stok.id as stok_id,"
                        . $param1 . "  as gudang_id,
                    0 as trado_id,
                    0 as gandengan_id,
                    0 as qty,'"
                        . $param2 . "' as modifiedby"
                ))
                    ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                        $join->on('stokpersediaan.stok_id', '=', 'stok.id');
                        $join->on('stokpersediaan.gudang_id', '=', DB::raw("'" . $param1 . "'"));
                    })
                    ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);



                $datadetail = json_decode($stokgudang->get(), true);

                $dataexist = $stokgudang->exists();
                $detaillogtrail = [];
                foreach ($datadetail as $item) {


                    $stokpersediaan = new StokPersediaan();
                    $stokpersediaan->stok_id = $item['stok_id'];
                    $stokpersediaan->gudang_id = $item['gudang_id'];
                    $stokpersediaan->trado_id = $item['trado_id'];
                    $stokpersediaan->gandengan_id = $item['gandengan_id'];
                    $stokpersediaan->qty = $item['qty'];
                    $stokpersediaan->modifiedby = $item['modifiedby'];
                    $stokpersediaan->save();
                    $detaillogtrail[] = $stokpersediaan->toArray();
                }

                if ($dataexist == true) {

                    $logTrail = [
                        'namatabel' => strtoupper($stokpersediaan->getTable()),
                        'postingdari' => 'EDIT STOK PERSEDIAAN',
                        'idtrans' => $gudang->id,
                        'nobuktitrans' => $gudang->id,
                        'aksi' => 'EDIT',
                        'datajson' => json_encode($detaillogtrail),
                        'modifiedby' => $gudang->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    app(LogTrailController::class)->store($validatedLogTrail);
                }



                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $gudang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Gudang $gudang, Request $request)
    {
        DB::beginTransaction();
        try {
            $delete = Gudang::destroy($gudang->id);
            $del = 1;
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($gudang->getTable()),
                    'postingdari' => 'DELETE GUDANG',
                    'idtrans' => $gudang->id,
                    'nobuktitrans' => $gudang->id,
                    'aksi' => 'DELETE',
                    'datajson' => $gudang->toArray(),
                    'modifiedby' => $gudang->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($gudang, $gudang->getTable());
            $gudang->position = $selected->position;
            $gudang->id = $selected->id;
            $gudang->page = ceil($gudang->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $gudang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('gudang')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'statusgudang' => Parameter::where(['grp' => 'status gudang'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }
}
