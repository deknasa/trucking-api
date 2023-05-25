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
use App\Models\PemutihanSupir;
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
    public function approvalBlackListSupir($id)
    {
        
        DB::beginTransaction();
        try{
            $supir = Supir::lockForUpdate()->findOrFail($id);
            $statusBlackList = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'BLACKLIST SUPIR')->where('text', '=', 'SUPIR BLACKLIST')->first();
            $statusBukanBlackList = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'BLACKLIST SUPIR')->where('text', '=', 'BUKAN SUPIR BLACKLIST')->first();
            
            if ($supir->statusblacklist == $statusBlackList->id) {
                $supir->statusblacklist = $statusBukanBlackList->id;
                $aksi = $statusBukanBlackList->text;
            } else {
                $supir->statusblacklist = $statusBlackList->id;
                $aksi = $statusBlackList->text;
            }
    
            if ($supir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'APPROVED BLACKLIST SUPIR',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => $aksi,
                    'datajson' => $supir->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);

        }catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


     /**
     * @ClassName 
     */
    public function approvalSupirLuarKota($id)
    {
        DB::beginTransaction();
        try{
            $supir = Supir::lockForUpdate()->findOrFail($id);
            $statusLuarKota = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS LUAR KOTA')->where('text', '=', 'BOLEH LUAR KOTA')->first();
            $statusBukanLuarKota = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS LUAR KOTA')->where('text', '=', 'TIDAK BOLEH LUAR KOTA')->first();
            
            if ($supir->statusluarkota == $statusLuarKota->id) {
                $supir->statusluarkota = $statusBukanLuarKota->id;
                $aksi = $statusBukanLuarKota->text;
            } else {
                $supir->statusluarkota = $statusLuarKota->id;
                $aksi = $statusLuarKota->text;
            }
    
            if ($supir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'APPROVED SUPIR LUAR KOTA',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => $aksi,
                    'datajson' => $supir->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);

        }catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

     /**
     * @ClassName 
     */
    public function approvalSupirResign(Request $request,$id)
    {
        DB::beginTransaction();
        try{
            $supir = Supir::lockForUpdate()->findOrFail($id);
            $tanggalberhenti = date('Y-m-d', strtotime("1900-01-01"));
            $aksi = "UNAPPROVED SUPIR RESIGN";
            
            if ($request->tanggalberhenti) {
                $tanggalberhenti = date('Y-m-d', strtotime($request->tanggalberhenti));
                $aksi = "APPROVED SUPIR RESIGN";
            }
            
            $supir->tglberhentisupir = $tanggalberhenti;
            $supir->keteranganberhentisupir = ($request->keteranganberhentisupir == null) ? "" : $request->keteranganberhentisupir;
    
            if ($supir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'APPROVED SUPIR RESIGN',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => $aksi,
                    'datajson' => $supir->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
            }

            return response([
                "data"=>[
                    "id"=>$supir->id
                ],
                'message' => 'Berhasil'
            ]);

        }catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
            $status = $supir->cekPemutihan($request->noktp);

            if($status == true){
                $request->validate([
                    'pemutihansupir_nobukti' => 'required'
                ],[
                    'pemutihansupir_nobukti.required' =>'nobukti pemutihan supir ' . app(ErrorController::class)->geterror('WI')->keterangan,
                ]);
            }

            $depositke = str_replace(',', '', $request->depositke);
            $supir->namasupir = $request->namasupir;
            $supir->alamat = $request->alamat;
            $supir->namaalias = $request->namaalias;
            $supir->kota = $request->kota;
            $supir->telp = $request->telp;
            $supir->statusaktif = $request->statusaktif;
            $supir->nominaldepositsa = str_replace(',', '', $request->nominaldepositsa) ?? 0;
            $supir->depositke = str_replace('.', '', $depositke) ?? 0;
            $supir->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $supir->nominalpinjamansaldoawal = str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0;
            $supir->pemutihansupir_nobukti = $request->pemutihansupir_nobukti ?? '';
            $supir->supirold_id = $request->supirold_id ?? 0;
            $supir->tglexpsim = date('Y-m-d', strtotime($request->tglexpsim));
            $supir->nosim = $request->nosim;
            $supir->keterangan = $request->keterangan ?? '';
            $supir->noktp = $request->noktp;
            $supir->nokk = $request->nokk;
            $supir->angsuranpinjaman = str_replace(',', '', $request->angsuranpinjaman) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $request->plafondeposito) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($request->tgllahir));
            $supir->tglterbitsim = date('Y-m-d', strtotime($request->tglterbitsim));
            $supir->modifiedby = auth('api')->user()->name;

            $statusAdaUpdateGambar = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS ADA UPDATE GAMBAR')->where('default', 'YA')->first();
            $statusLuarKota = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LUAR KOTA')->where('default', 'YA')->first();
            $statusZonaTertentu = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ZONA TERTENTU')->where('default', 'YA')->first();
            $statusBlackList = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BLACKLIST SUPIR')->where('default', 'YA')->first();
            $supir->statusadaupdategambar = $statusAdaUpdateGambar->id;
            $supir->statusluarkota = $statusLuarKota->id;
            $supir->statuszonatertentu = $statusZonaTertentu->id;
            $supir->statusblacklist = $statusBlackList->id;

            $supir->photosupir = $this->storeFiles($request->photosupir, 'supir');
            $supir->photoktp = $this->storeFiles($request->photoktp, 'ktp');
            $supir->photosim = $this->storeFiles($request->photosim, 'sim');
            $supir->photokk = $this->storeFiles($request->photokk, 'kk');
            $supir->photoskck = $this->storeFiles($request->photoskck, 'skck');
            $supir->photodomisili = $this->storeFiles($request->photodomisili, 'domisili');
            $supir->photovaksin = $this->storeFiles($request->photovaksin, 'vaksin');
            $supir->pdfsuratperjanjian = $this->storePdfFiles($request->pdfsuratperjanjian, 'suratperjanjian');

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
            $supirNew = new Supir();
            $status = $supirNew->cekPemutihan($request->noktp);

            if($status == true){
                $request->validate([
                    'pemutihansupir_nobukti' => 'required'
                ],[
                    'pemutihansupir_nobukti.required' =>'nobukti pemutihan supir ' . app(ErrorController::class)->geterror('WI')->keterangan,
                ]);
            }
            $depositke = str_replace(',', '', $request->depositke);
            $supir->namasupir = $request->namasupir;
            $supir->namaalias = $request->namaalias;
            $supir->alamat = $request->alamat;
            $supir->kota = $request->kota;
            $supir->telp = $request->telp;
            $supir->statusaktif = $request->statusaktif;
            $supir->pemutihansupir_nobukti = $request->pemutihansupir_nobukti ?? '';
            $supir->nominaldepositsa = str_replace(',', '', $request->nominaldepositsa) ?? 0;
            $supir->depositke = str_replace('.00', '', $depositke) ?? 0;
            $supir->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $supir->nominalpinjamansaldoawal = str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0;
            $supir->supirold_id = $request->supirold_id ?? 0;
            $supir->tglexpsim = date('Y-m-d', strtotime($request->tglexpsim));
            $supir->nosim = $request->nosim;
            $supir->keterangan = $request->keterangan ?? '';
            $supir->noktp = $request->noktp;
            $supir->nokk = $request->nokk;
            $supir->angsuranpinjaman = str_replace(',', '', $request->angsuranpinjaman) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $request->plafondeposito) ?? 0;
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
            $supir->photovaksin = $this->storeFiles($request->photovaksin, 'vaksin');
            $supir->pdfsuratperjanjian = $this->storePdfFiles($request->pdfsuratperjanjian, 'suratperjanjian');

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

    public function getImage(string $field, string $filename, string $type, string $aksi)
    {
        if($field == 'supir') {
            $field = 'profil';
        }
        if (Storage::exists("supir/$field/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/supir/$field/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("supir/$field/$filename")) {
                return response()->file(storage_path("app/supir/$field/$filename"));
            }else{
                if ($aksi == 'show') {
                    return response()->file(storage_path("app/no-image.jpg"));
                }else{
                    return response('no-image');
                }
            }
        }
    }
    public function getPdf(string $field, string $filename)
    {
        if (Storage::exists("supir/$field/$filename")) {
            return response()->file(storage_path("app/supir/$field/$filename"));
        }else{
            return response(['data'=>'']);
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];
        if($destinationFolder == 'supir') {
            $destinationFolder = 'profil';
        }
        foreach ($files as $file) {
            $originalFileName = "$destinationFolder-".$file->hashName();
            $storedFile = Storage::putFileAs('supir/'. $destinationFolder, $file,$originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/supir/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }
    private function storePdfFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = "SURAT-".$file->hashName();
            $storedFile = Storage::putFileAs('supir/'.$destinationFolder, $file, $originalFileName);
            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Supir $supir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoSupir = [];
        $relatedPhotoKtp = [];
        $relatedPhotoSim = [];
        $relatedPhotoKk = [];
        $relatedPhotoSkck = [];
        $relatedPhotoDomisili = [];
        $relatedPhotoVaksin = [];
        $relatedPdfSuratPerjanjian = [];

        $photoSupir = json_decode($supir->photosupir, true);
        $photoKtp = json_decode($supir->photoktp, true);
        $photoSim = json_decode($supir->photosim, true);
        $photoKk = json_decode($supir->photokk, true);
        $photoSkck = json_decode($supir->photoskck, true);
        $photoDomisili = json_decode($supir->photodomisili, true);
        $photoVaksin = json_decode($supir->photoVaksin, true);
        $pdfSuratPerjanjian = json_decode($supir->pdfsuratperjanjian, true);

        if ($photoSupir != '') {
            foreach ($photoSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSupir[] = "supir/profil/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoSupir);
        }

        if ($photoKtp != '') {
            foreach ($photoKtp as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoKtp[] = "supir/ktp/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoKtp);
        }

        if ($photoSim != '') {
            foreach ($photoSim as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSim[] = "supir/sim/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoSim);
        }

        if ($photoKk != '') {
            foreach ($photoKk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoKk[] = "supir/kk/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoKk);
        }

        if ($photoSkck != '') {
            foreach ($photoSkck as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSkck[] = "supir/skck/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoSkck);
        }

        if ($photoDomisili != '') {
            foreach ($photoDomisili as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoDomisili[] = "supir/domisili/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoDomisili);
        }
        if ($photoVaksin != '') {
            foreach ($photoVaksin as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoVaksin[] = "supir/vaksin/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoVaksin);
        }
        if ($pdfSuratPerjanjian != '') {
            foreach ($pdfSuratPerjanjian as $path) {
                $relatedPdfSuratPerjanjian[] = "supir/suratperjanjian/$path";
            }
            Storage::delete($relatedPdfSuratPerjanjian);
        }
    }
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $supirs = $decodedResponse['data'];


        $i = 0;
        foreach ($supirs as $index => $params) {

            $statusaktif = $params['statusaktif'];
            $statusLuarKota = $params['statusluarkota'];
            $statusZonaTertentu = $params['statuszonatertentu'];
            $statusBlacklist = $params['statusblacklist'];
            $statusUpdateGambar = $params['statusadaupdategambar'];

            $result = json_decode($statusaktif, true);
            $resultLuarKota = json_decode($statusLuarKota, true);
            $resultZonaTertentu = json_decode($statusZonaTertentu, true);
            $resultBlacklist = json_decode($statusBlacklist, true);
            $resultUpdateGambar = json_decode($statusUpdateGambar, true);

            $statusaktif = $result['MEMO'];
            $statusLuarKota = $resultLuarKota['MEMO'];
            $statusZonaTertentu = $resultZonaTertentu['MEMO'];
            $statusBlacklist = $resultBlacklist['MEMO'];
            $statusUpdateGambar = $resultUpdateGambar['MEMO'];


            $supirs[$i]['statusaktif'] = $statusaktif;
            $supirs[$i]['statusluarkota'] = $statusLuarKota;
            $supirs[$i]['statuszonatertentu'] = $statusZonaTertentu;
            $supirs[$i]['statusblacklist'] = $statusBlacklist;
            $supirs[$i]['statusadaupdategambar'] = $statusUpdateGambar;

        
            $i++;


        }
      
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Nama Supir',
                'index' => 'namasupir',
            ],
            [
                'label' => 'Nama Alias',
                'index' => 'namaalias',
            ],
            [
                'label' => 'Tgl Lahir',
                'index' => 'tgllahir',
            ],
            [
                'label' => 'Alamat',
                'index' => 'alamat',
            ],
            [
                'label' => 'Kota',
                'index' => 'kota',
            ],
            [
                'label' => 'Telepon',
                'index' => 'telp',
            ],
            [
                'label' => 'Pemutihan Supir No Bukti',
                'index' => 'pemutihansupir_nobukti',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
            [
                'label' => 'Nominal Deposit SA',
                'index' => 'nominaldepositsa',
            ],
            [
                'label' => 'Deposit Ke',
                'index' => 'depositke',
            ],
            [
                'label' => 'Nominal Pinjaman Saldo Awal',
                'index' => 'nominalpinjamansaldoawal',
            ],
            [
                'label' => 'Supir Rold',
                'index' => 'supirold_id',
            ],
            [
                'label' => 'No Sim',
                'index' => 'nosim',
            ],
            [
                'label' => 'Tgl Terbit Sim',
                'index' => 'tglterbitsim',
            ],
            [
                'label' => 'Tgl Exp Sim',
                'index' => 'tglexpsim',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'No KTP',
                'index' => 'noktp',
            ],
            [
                'label' => 'No KK',
                'index' => 'nokk',
            ],
            [
                'label' => 'Status Ada Update Gambar',
                'index' => 'statusadaupdategambar',
            ],
            [
                'label' => 'Status Luar Kota',
                'index' => 'statusluarkota',
            ],
            [
                'label' => 'Status Zona Tertentu',
                'index' => 'statuszonatertentu',
            ],
            [
                'label' => 'Zona',
                'index' => 'zona_id',
            ],
            [
                'label' => 'Keterangan Resign',
                'index' => 'keteranganresign',
            ],
            [
                'label' => 'Status Blacklist',
                'index' => 'statusblacklist',
            ],
            [
                'label' => 'Tgl Berhenti Supir',
                'index' => 'tglberhentisupir',
            ],
           
        ];

        $this->toExcel('Supir', $supirs, $columns);
    }
}
