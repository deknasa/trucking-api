<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreSupirRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSupirRequest;
use App\Models\Supir;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\Zona;
use Exception;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class SupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $supir = new Supir();

        return response([
            'data' => $supir->get(),
            'attributes' => [
                'totalRows' => $supir->totalRows,
                'totalPages' => $supir->totalPages
            ]
        ]);
    }

     /**
     * @ClassName 
     */
    public function approvalBlackListSupir()
    {

    }

     /**
     * @ClassName 
     */
    public function approvalSupirLuarKota()
    {

    }

     /**
     * @ClassName 
     */
    public function approvalSupirResign()
    {

    }

    
    public function cekValidasi($id) {
        $supir= new Supir();
        $cekdata=$supir->cekvalidasihapus($id);
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

        $supir = new Supir();
        return response([
            'status' => true,
            'data' => $supir->default(),
        ]);
    }

    public function show($id)
    {
        $data = Supir::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $supir = new Supir();
            $depositke = str_replace(',', '', $request->depositke);
            $supir->namasupir = $request->namasupir;
            $supir->alamat = $request->alamat;
            $supir->kota = $request->kota;
            $supir->telp = $request->telp;
            $supir->statusaktif = $request->statusaktif;
            $supir->nominaldepositsa = str_replace(',', '', $request->nominaldepositsa) ?? 0;
            $supir->depositke = str_replace('.', '', $depositke) ?? 0;
            $supir->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $supir->nominalpinjamansaldoawal = str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0;
            $supir->supirold_id = $request->supirold_id ?? 0;
            $supir->tglexpsim = date('Y-m-d', strtotime($request->tglexpsim));
            $supir->nosim = $request->nosim;
            $supir->keterangan = $request->keterangan;
            $supir->noktp = $request->noktp;
            $supir->nokk = $request->nokk;
            $supir->statusadaupdategambar = $request->statusadaupdategambar ?? 0;
            $supir->statusluarkota = $request->statusluarkota;
            $supir->statuszonatertentu = $request->statuszonatertentu;
            $supir->zona_id = $request->zona_id;
            $supir->angsuranpinjaman = str_replace(',', '', $request->angsuranpinjaman) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $request->plafondeposito) ?? 0;
            $supir->keteranganresign = $request->keteranganresign ?? '';
            $supir->statusblacklist = $request->statusblacklist;
            $supir->tglberhentisupir = date('Y-m-d', strtotime($request->tglberhentisupir)) ?? '';
            $supir->tgllahir = date('Y-m-d', strtotime($request->tgllahir));
            $supir->tglterbitsim = date('Y-m-d', strtotime($request->tglterbitsim));
            $supir->modifiedby = auth('api')->user()->name;
            $supir->photosupir = $this->storeFiles($request->photosupir, 'supir');
            $supir->photoktp = $this->storeFiles($request->photoktp, 'ktp');
            $supir->photosim = $this->storeFiles($request->photosim, 'sim');
            $supir->photokk = $this->storeFiles($request->photokk, 'kk');
            $supir->photoskck = $this->storeFiles($request->photoskck, 'skck');
            $supir->photodomisili = $this->storeFiles($request->photodomisili, 'domisili');

            $supir->save();

            $logTrail = [
                'namatabel' => strtoupper($supir->getTable()),
                'postingdari' => 'ENTRY SUPIR',
                'idtrans' => $supir->id,
                'nobuktitrans' => $supir->id,
                'aksi' => 'ENTRY',
                'datajson' => $supir->toArray(),
                'modifiedby' => $supir->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($supir, $supir->getTable());
            $supir->position = $selected->position;
            $supir->page = ceil($supir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $supir
            ], 201);
        } catch (\Throwable $th) {
            $this->deleteFiles($supir);

            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSupirRequest $request, Supir $supir)
    {
        DB::beginTransaction();

        try {

            $depositke = str_replace(',', '', $request->depositke);
            $supir->namasupir = $request->namasupir;
            $supir->alamat = $request->alamat;
            $supir->kota = $request->kota;
            $supir->telp = $request->telp;
            $supir->statusaktif = $request->statusaktif;
            $supir->nominaldepositsa = str_replace(',', '', $request->nominaldepositsa) ?? 0;
            $supir->depositke = str_replace('.00', '', $depositke) ?? 0;
            $supir->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $supir->nominalpinjamansaldoawal = str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0;
            $supir->supirold_id = $request->supirold_id ?? 0;
            $supir->tglexpsim = date('Y-m-d', strtotime($request->tglexpsim));
            $supir->nosim = $request->nosim;
            $supir->keterangan = $request->keterangan;
            $supir->noktp = $request->noktp;
            $supir->nokk = $request->nokk;
            $supir->statusadaupdategambar = $request->statusadaupdategambar ?? 0;
            $supir->statusluarkota = $request->statusluarkota;
            $supir->statuszonatertentu = $request->statuszonatertentu;
            $supir->zona_id = $request->zona_id;
            $supir->angsuranpinjaman = str_replace(',', '', $request->angsuranpinjaman) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $request->plafondeposito) ?? 0;
            $supir->keteranganresign = $request->keteranganresign ?? '';
            $supir->statusblacklist = $request->statusblacklist;
            $supir->tglberhentisupir = date('Y-m-d', strtotime($request->tglberhentisupir)) ?? '';
            $supir->tgllahir = date('Y-m-d', strtotime($request->tgllahir));
            $supir->tglterbitsim = date('Y-m-d', strtotime($request->tglterbitsim));
            $supir->modifiedby = auth('api')->user()->name;

            $this->deleteFiles($supir);

            $supir->photosupir = $this->storeFiles($request->photosupir, 'supir');
            $supir->photoktp = $this->storeFiles($request->photoktp, 'ktp');
            $supir->photosim = $this->storeFiles($request->photosim, 'sim');
            $supir->photokk = $this->storeFiles($request->photokk, 'kk');
            $supir->photoskck = $this->storeFiles($request->photoskck, 'skck');
            $supir->photodomisili = $this->storeFiles($request->photodomisili, 'domisili');
            $supir->save();

            if ($supir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'EDIT SUPIR',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => 'EDIT',
                    'datajson' => $supir->toArray(),
                    'modifiedby' => $supir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($supir, $supir->getTable());
            $supir->position = $selected->position;
            $supir->page = ceil($supir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $supir
            ]);
        } catch (\Throwable $th) {
            $this->deleteFiles($supir);

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

        $supir = new Supir();
        $supir = $supir->lockAndDestroy($id);

        if ($supir) {
            $logTrail = [
                'namatabel' => strtoupper($supir->getTable()),
                'postingdari' => 'DELETE SUPIR',
                'idtrans' => $supir->id,
                'nobuktitrans' => $supir->id,
                'aksi' => 'DELETE',
                'datajson' => $supir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);
            $this->deleteFiles($supir);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($supir, $supir->getTable(), true);
            $supir->position = $selected->position;
            $supir->id = $selected->id;
            $supir->page = ceil($supir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $supir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('supir')->getColumns();

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
            'status' => Parameter::where(['grp' => 'status aktif'])->get(),
            'updategambar' => Parameter::where(['grp' => 'status ada update gambar'])->get(),
            'luarkota' => Parameter::where(['grp' => 'status luar kota'])->get(),
            'zonatertentu' => Parameter::where(['grp' => 'status zona tertentu'])->get(),
            'pameran' => Parameter::where(['grp' => 'status pameran'])->get(),
            'blacklist' => Parameter::where(['grp' => 'status blacklist'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function getImage(string $field, string $filename, string $type)
    {
        return response()->file(storage_path("app/$field/$type-$filename"));
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

    private function deleteFiles(Supir $supir)
    {
        $sizeTypes = ['ori', 'medium', 'small'];

        $relatedPhotoSupir = [];
        $relatedPhotoKtp = [];
        $relatedPhotoSim = [];
        $relatedPhotoKk = [];
        $relatedPhotoSkck = [];
        $relatedPhotoDomisili = [];

        $photoSupir = json_decode($supir->photosupir, true);
        $photoKtp = json_decode($supir->photoktp, true);
        $photoSim = json_decode($supir->photosim, true);
        $photoKk = json_decode($supir->photokk, true);
        $photoSkck = json_decode($supir->photoskck, true);
        $photoDomisili = json_decode($supir->photodomisili, true);

        if ($photoSupir != '') {
            foreach ($photoSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSupir[] = "supir/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoSupir);
        }

        if ($photoKtp != '') {
            foreach ($photoKtp as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoKtp[] = "ktp/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoKtp);
        }

        if ($photoSim != '') {
            foreach ($photoSim as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSim[] = "sim/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoSim);
        }

        if ($photoKk != '') {
            foreach ($photoKk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoKk[] = "kk/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoKk);
        }

        if ($photoSkck != '') {
            foreach ($photoSkck as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSkck[] = "skck/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoSkck);
        }

        if ($photoDomisili != '') {
            foreach ($photoDomisili as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoDomisili[] = "domisili/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoDomisili);
        }
    }
}
