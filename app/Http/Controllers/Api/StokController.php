<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\Stok;
use App\Http\Requests\StoreStokRequest;
use App\Http\Requests\UpdateStokRequest;
use Illuminate\Http\Request;
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

    public function cekValidasi($id) {
        $stok= new Stok();
        $cekdata=$stok->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
                )
            ->where('kodeerror', '=', 'SATL')
            ->get();
        $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
         
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data); 
        }
    }

    public function default()
    {
        $stok = new Stok();
        return response([
            'status' => true,
            'data' => $stok->default()
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
            $stok->merk_id = $request->merk_id ?? 0;
            $stok->jenistrado_id = $request->jenistrado_id ?? 0;
            $stok->keterangan = $request->keterangan ?? '';
            $stok->qtymin = $request->qtymin ?? 0;
            $stok->qtymax = $request->qtymax ?? 0;
            $stok->modifiedby = auth('api')->user()->name;

            if ($request->gambar) {
                $stok->gambar = $this->storeFiles($request->gambar, 'stok');
            } else {
                $stok->gambar = '';
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
            $stok->merk_id =  $request->merk_id ?? 0;
            $stok->jenistrado_id = $request->jenistrado_id ?? 0;
            $stok->keterangan = $request->keterangan ?? '';
            $stok->qtymin = $request->qtymin ?? 0;
            $stok->qtymax = $request->qtymax ?? 0;
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $stok = new Stok;
        $stok = $stok->lockAndDestroy($id);
        if ($stok) {
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
            $this->deleteFiles($stok);
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($stok, $stok->getTable(), true);
            $stok->position = $selected->position;
            $stok->id = $selected->id;
            $stok->page = ceil($stok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $stok
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Stok $stok)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoStok = [];
        $photoStok = json_decode($stok->gambar, true);
        if ($photoStok) {
            foreach ($photoStok as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStok[] = "stok/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStok);
        }
    }

    public function getImage(string $filename, string $type)
    {
        if(Storage::exists("stok/$type" . '_' . "$filename")){
            return response()->file(storage_path("app/stok/$type" . '_' . "$filename"));
        }else{
            return response()->file(storage_path("app/stok/$filename"));
        }
    }
}
