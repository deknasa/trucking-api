<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\Stok;
use App\Http\Requests\StoreStokRequest;
use App\Http\Requests\UpdateStokRequest;
use App\Models\StokPersediaan;
use App\Models\Gudang;
use App\Models\Gandengan;
use App\Models\Trado;


use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $stok = new stok();

        return response([
            'data' => $stok->get(),
            'attributes' => [
                'totalRows' => $stok->totalRows,
                'totalPages' => $stok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreStokRequest $request)
    {
        DB::beginTransaction();
        try {
            $stok = new stok();
            $stok->keterangan = $request->keterangan;
            $stok->namastok = $request->namastok;
            $stok->namaterpusat = $request->namaterpusat;
            $stok->statusaktif = $request->statusaktif;
            $stok->kelompok_id = $request->kelompok_id;
            $stok->subkelompok_id = $request->subkelompok_id;
            $stok->kategori_id = $request->kategori_id;
            $stok->merk_id = $request->merk_id;
            $stok->jenistrado_id = $request->jenistrado_id;
            $stok->keterangan = $request->keterangan;
            $stok->qtymin = $request->qtymin;
            $stok->qtymax = $request->qtymax;
            $stok->modifiedby = auth('api')->user()->name;

            if ($request->gambar) {
                $stok->gambar = $this->storeFiles($request->gambar, 'stok');
            }
            $stok->save();

            $logTrail = [
                'namatabel' => strtoupper($stok->getTable()),
                'postingdari' => 'ENTRY STOK',
                'idtrans' => $stok->id,
                'nobuktitrans' => $stok->id,
                'aksi' => 'ENTRY',
                'datajson' => $stok->toArray(),
                'modifiedby' => $stok->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $param1 = $stok->id;
            $param2 = $stok->modifiedby;

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('stok_id')->default(0);
                $table->unsignedBigInteger('gudang_id')->default(0);
                $table->unsignedBigInteger('trado_id')->default(0);
                $table->unsignedBigInteger('gandengan_id')->default(0);
                $table->float('qty')->default(0);
                $table->string('modifiedby', 500)->default();
            });


            $stokgandengan = Gandengan::select(DB::raw(
                $param1 . " as  stok_id,
                       0  as gudang_id,
                   0 as trado_id,
                   gandengan.id as gandengan_id,
                   0 as qty,'"
                    . $param2 . "' as modifiedby"
            ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.gandengan_id', '=', 'gandengan.id');
                    $join->on('stokpersediaan.stok_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            DB::table($temp)->insertUsing(['stok_id', 'gudang_id', 'trado_id', 'gandengan_id', 'qty', 'modifiedby'], $stokgandengan);

            $stoktrado = Trado::select(DB::raw(
                $param1 . " as stok_id,
                           0  as gudang_id,
                       trado.id as trado_id,
                       0 as gandengan_id,
                       0 as qty,'"
                    . $param2 . "' as modifiedby"
            ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.trado_id', '=', 'trado.id');
                    $join->on('stokpersediaan.stok_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            DB::table($temp)->insertUsing(['stok_id', 'gudang_id', 'trado_id', 'gandengan_id', 'qty', 'modifiedby'], $stoktrado);


            $stokgudang = Gudang::select(DB::raw(
                $param1 . " as  stok_id,
                        gudang.id  as gudang_id,
                    0 as trado_id,
                    0 as gandengan_id,
                    0 as qty,'"
                    . $param2 . "' as modifiedby"
            ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.gudang_id', '=', 'gudang.id');
                    $join->on('stokpersediaan.stok_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            DB::table($temp)->insertUsing(['stok_id', 'gudang_id', 'trado_id', 'gandengan_id', 'qty', 'modifiedby'], $stokgudang);

            $query = DB::table($temp);


            // dd($stokgudang->get()->toSql());
            $datadetail = json_decode($query->get(), true);

            $dataexist = $query->exists();
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
                    'postingdari' => 'STOK PERSEDIAAN',
                    'idtrans' => $stok->id,
                    'nobuktitrans' => $stok->id,
                    'aksi' => 'EDIT',
                    'datajson' => json_encode($detaillogtrail),
                    'modifiedby' => $stok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($stok, $stok->getTable());
            $stok->position = $selected->position;
            $stok->page = ceil($stok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $stok
            ], 201);
        } catch (\Throwable $th) {
            $this->deleteFiles($stok);
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    public function show($id)
    {
        $stok = Stok::findAll($id);

        return response([
            'status' => true,
            'data' => $stok
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateStokRequest $request, $id)
    {
        $stok = Stok::find($id);

        DB::beginTransaction();
        try {
            $stok->keterangan = $request->keterangan;
            $stok->namastok = $request->namastok;
            $stok->namaterpusat = $request->namaterpusat;
            $stok->namaterpusat = $request->namaterpusat;
            $stok->statusaktif = $request->statusaktif;
            $stok->kelompok_id = $request->kelompok_id;
            $stok->subkelompok_id = $request->subkelompok_id;
            $stok->kategori_id = $request->kategori_id;
            $stok->merk_id = $request->merk_id;
            $stok->jenistrado_id = $request->jenistrado_id;
            $stok->keterangan = $request->keterangan;
            $stok->qtymin = $request->qtymin;
            $stok->qtymax = $request->qtymax;
            $stok->modifiedby = auth('api')->user()->name;

            $this->deleteFiles($stok);
            if ($request->gambar) {
                $stok->gambar = $this->storeFiles($request->gambar, 'stok');
            } else {
                $stok->gambar = '';
            }

            $stok->save();

            $logTrail = [
                'namatabel' => strtoupper($stok->getTable()),
                'postingdari' => 'EDIT STOK',
                'idtrans' => $stok->id,
                'nobuktitrans' => $stok->id,
                'aksi' => 'ENTRY',
                'datajson' => $stok->toArray(),
                'modifiedby' => $stok->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $param1 = $stok->id;
            $param2 = $stok->modifiedby;

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('stok_id')->default(0);
                $table->unsignedBigInteger('gudang_id')->default(0);
                $table->unsignedBigInteger('trado_id')->default(0);
                $table->unsignedBigInteger('gandengan_id')->default(0);
                $table->float('qty')->default(0);
                $table->string('modifiedby', 500)->default();
            });


            $stokgandengan = Gandengan::select(DB::raw(
                $param1 . " as  stok_id,
                       0  as gudang_id,
                   0 as trado_id,
                   gandengan.id as gandengan_id,
                   0 as qty,'"
                    . $param2 . "' as modifiedby"
            ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.gandengan_id', '=', 'gandengan.id');
                    $join->on('stokpersediaan.stok_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            DB::table($temp)->insertUsing(['stok_id', 'gudang_id', 'trado_id', 'gandengan_id', 'qty', 'modifiedby'], $stokgandengan);

            $stoktrado = Trado::select(DB::raw(
                $param1 . " as stok_id,
                           0  as gudang_id,
                       trado.id as trado_id,
                       0 as gandengan_id,
                       0 as qty,'"
                    . $param2 . "' as modifiedby"
            ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.trado_id', '=', 'trado.id');
                    $join->on('stokpersediaan.stok_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            DB::table($temp)->insertUsing(['stok_id', 'gudang_id', 'trado_id', 'gandengan_id', 'qty', 'modifiedby'], $stoktrado);


            $stokgudang = Gudang::select(DB::raw(
                $param1 . " as  stok_id,
                        gudang.id  as gudang_id,
                    0 as trado_id,
                    0 as gandengan_id,
                    0 as qty,'"
                    . $param2 . "' as modifiedby"
            ))
                ->leftjoin('stokpersediaan', function ($join) use ($param1) {
                    $join->on('stokpersediaan.gudang_id', '=', 'gudang.id');
                    $join->on('stokpersediaan.stok_id', '=', DB::raw("'" . $param1 . "'"));
                })
                ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            DB::table($temp)->insertUsing(['stok_id', 'gudang_id', 'trado_id', 'gandengan_id', 'qty', 'modifiedby'], $stokgudang);

            $query = DB::table($temp);


            // dd($stokgudang->get()->toSql());
            $datadetail = json_decode($query->get(), true);

            $dataexist = $query->exists();
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
                    'postingdari' => 'STOK PERSEDIAAN',
                    'idtrans' => $stok->id,
                    'nobuktitrans' => $stok->id,
                    'aksi' => 'EDIT',
                    'datajson' => json_encode($detaillogtrail),
                    'modifiedby' => $stok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($stok, $stok->getTable());
            $stok->position = $selected->position;
            $stok->page = ceil($stok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $stok
            ], 201);
        } catch (\Throwable $th) {
            $this->deleteFiles($stok);
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $stok = Stok::find($id);
            if ($stok->lockForUpdate()->delete()) {
                $logTrail = [
                    'namatabel' => strtoupper($stok->getTable()),
                    'postingdari' => 'DELETE STOK',
                    'idtrans' => $stok->id,
                    'nobuktitrans' => $stok->id,
                    'aksi' => 'DELETE',
                    'datajson' => $stok->toArray(),
                    'modifiedby' => $stok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            $this->deleteFiles($stok);
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($stok, $stok->getTable(), true);
            $stok->position = $selected->position;
            $stok->id = $selected->id;
            $stok->page = ceil($stok->position / ($stok->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $stok
            ]);
        } catch (\Throwable $th) {
            $this->deleteFiles($stok);
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, 'ori-' . $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Stok $stok)
    {
        $sizeTypes = ['ori', 'medium', 'small'];

        $relatedPhotoStok = [];
        $photoStok = json_decode($stok->gambar, true);
        foreach ($photoStok as $path) {
            foreach ($sizeTypes as $sizeType) {
                $relatedPhotoStok[] = "stok/$sizeType-$path";
            }
        }
        Storage::delete($relatedPhotoStok);
    }

    public function getImage(string $filename, string $type)
    {
        return response()->file(storage_path("app/stok/$type-$filename"));
    }
}
