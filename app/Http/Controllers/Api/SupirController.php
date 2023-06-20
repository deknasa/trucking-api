<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreSupirRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Js;

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
        try {
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
        } catch (\Throwable $th) {
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
        try {
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
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function approvalSupirResign(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $supir = Supir::lockForUpdate()->findOrFail($id);

            if ($request->action == "approve") {
                $supir->tglberhentisupir = date('Y-m-d', strtotime($request->tglberhentisupir));
                $aksi = "APPROVED SUPIR RESIGN";
                // $supir->keteranganberhentisupir = ($request->keteranganberhentisupir == null) ? "" : $request->keteranganberhentisupir;
                $supir->keteranganberhentisupir = $request->keteranganberhentisupir;
            } else if ($request->action == "unapprove") {
                $supir->tglberhentisupir = date('Y-m-d', strtotime("1900-01-01"));
                $aksi = "UNAPPROVED SUPIR RESIGN";
                $supir->keteranganberhentisupir = null;
            }

            // $supir->tglberhentisupir = $tanggalberhenti;
            // return response([$supir],422);
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
                "data" => [
                    "id" => $supir->id
                ],
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function cekValidasi($id)
    {
        $supir = new Supir();
        $cekdata = $supir->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
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
    public function store(StoreSupirRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $depositke = str_replace(',', '', $request->depositke);
            $data = [
                'namasupir' => $request->namasupir,
                'alamat' => $request->alamat,
                'namaalias' => $request->namaalias,
                'kota' => $request->kota,
                'telp' => $request->telp,
                'statusaktif' => $request->statusaktif,
                'nominaldepositsa' => str_replace(',', '', $request->nominaldepositsa) ?? 0,
                'depositke' => str_replace('.', '', $depositke) ?? 0,
                'tglmasuk' => date('Y-m-d', strtotime($request->tglmasuk)),
                'nominalpinjamansaldoawal' => str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0,
                'pemutihansupir_nobukti' => $request->pemutihansupir_nobukti ?? '',
                'supirold_id' => $request->supirold_id ?? 0,
                'tglexpsim' => date('Y-m-d', strtotime($request->tglexpsim)),
                'nosim' => $request->nosim,
                'keterangan' => $request->keterangan ?? '',
                'noktp' => $request->noktp,
                'nokk' => $request->nokk,
                'angsuranpinjaman' => str_replace(',', '', $request->angsuranpinjaman) ?? 0,
                'plafondeposito' => str_replace(',', '', $request->plafondeposito) ?? 0,
                'tgllahir' => date('Y-m-d', strtotime($request->tgllahir)),
                'tglterbitsim' => date('Y-m-d', strtotime($request->tglterbitsim)),
                'modifiedby' => auth('api')->user()->name,

                'photosupir' => ($request->photosupir) ? $this->storeFiles($request->photosupir, 'supir') : '',
                'photoktp' => ($request->photoktp) ? $this->storeFiles($request->photoktp, 'ktp') : '',
                'photosim' => ($request->photosim) ? $this->storeFiles($request->photosim, 'sim') : '',
                'photokk' => ($request->photokk) ? $this->storeFiles($request->photokk, 'kk') : '',
                'photoskck' => ($request->photoskck) ? $this->storeFiles($request->photoskck, 'skck') : '',
                'photodomisili' => ($request->photodomisili) ? $this->storeFiles($request->photodomisili, 'domisili') : '',
                'photovaksin' => ($request->photovaksin) ? $this->storeFiles($request->photovaksin, 'vaksin') : '',
                'pdfsuratperjanjian' => ($request->pdfsuratperjanjian) ? $this->storePdfFiles($request->pdfsuratperjanjian, 'suratperjanjian') : ''

            ];
            $supir = (new supir())->processStore($data);
            $supir->position = $this->getPosition($supir, $supir->getTable())->position;
            $supir->page = ceil($supir->position / ($request->limit ?? 10));
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $supir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSupirRequest $request, Supir $supir): JsonResponse
    {
        DB::beginTransaction();

        try {
            $depositke = str_replace(',', '', $request->depositke);
            $data = [
                'namasupir' => $request->namasupir,
                'alamat' => $request->alamat,
                'namaalias' => $request->namaalias,
                'kota' => $request->kota,
                'telp' => $request->telp,
                'statusaktif' => $request->statusaktif,
                'nominaldepositsa' => str_replace(',', '', $request->nominaldepositsa) ?? 0,
                'depositke' => str_replace('.', '', $depositke) ?? 0,
                'tglmasuk' => date('Y-m-d', strtotime($request->tglmasuk)),
                'nominalpinjamansaldoawal' => str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0,
                'pemutihansupir_nobukti' => $request->pemutihansupir_nobukti ?? '',
                'supirold_id' => $request->supirold_id ?? 0,
                'tglexpsim' => date('Y-m-d', strtotime($request->tglexpsim)),
                'nosim' => $request->nosim,
                'keterangan' => $request->keterangan ?? '',
                'noktp' => $request->noktp,
                'nokk' => $request->nokk,
                'angsuranpinjaman' => str_replace(',', '', $request->angsuranpinjaman) ?? 0,
                'plafondeposito' => str_replace(',', '', $request->plafondeposito) ?? 0,
                'tgllahir' => date('Y-m-d', strtotime($request->tgllahir)),
                'tglterbitsim' => date('Y-m-d', strtotime($request->tglterbitsim)),
                'modifiedby' => auth('api')->user()->name,

                'photosupir' => ($request->photosupir) ? $this->storeFiles($request->photosupir, 'supir') : '',
                'photoktp' => ($request->photoktp) ? $this->storeFiles($request->photoktp, 'ktp') : '',
                'photosim' => ($request->photosim) ? $this->storeFiles($request->photosim, 'sim') : '',
                'photokk' => ($request->photokk) ? $this->storeFiles($request->photokk, 'kk') : '',
                'photoskck' => ($request->photoskck) ? $this->storeFiles($request->photoskck, 'skck') : '',
                'photodomisili' => ($request->photodomisili) ? $this->storeFiles($request->photodomisili, 'domisili') : '',
                'photovaksin' => ($request->photovaksin) ? $this->storeFiles($request->photovaksin, 'vaksin') : '',
                'pdfsuratperjanjian' => ($request->pdfsuratperjanjian) ? $this->storePdfFiles($request->pdfsuratperjanjian, 'suratperjanjian') : ''

            ];

            $supir = (new Supir())->processUpdate($supir, $data);
            $supir->position = $this->getPosition($supir, $supir->getTable())->position;
            $supir->page = ceil($supir->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $supir
            ]);
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
            $supir = (new Supir())->processDestroy($id);
            $selected = $this->getPosition($supir, $supir->getTable(), true);
            $supir->position = $selected->position;
            $supir->id = $selected->id;
            $supir->page = ceil($supir->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $supir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
        if ($field == 'supir') {
            $field = 'profil';
        }
        if (Storage::exists("supir/$field/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/supir/$field/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("supir/$field/$filename")) {
                return response()->file(storage_path("app/supir/$field/$filename"));
            } else {
                if ($aksi == 'show') {
                    return response()->file(storage_path("app/no-image.jpg"));
                } else {
                    return response('no-image');
                }
            }
        }
    }
    public function getPdf(string $field, string $filename)
    {
        if (Storage::exists("supir/$field/$filename")) {
            return response()->file(storage_path("app/supir/$field/$filename"));
        } else {
            return response(['data' => '']);
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];
        if ($destinationFolder == 'supir') {
            $destinationFolder = 'profil';
        }
        foreach ($files as $file) {
            $originalFileName = "$destinationFolder-" . $file->hashName();
            $storedFile = Storage::putFileAs('supir/' . $destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/supir/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }
    private function storePdfFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = "SURAT-" . $file->hashName();
            $storedFile = Storage::putFileAs('supir/' . $destinationFolder, $file, $originalFileName);
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
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {
            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $supirs = $decodedResponse['data'];

            $judulLaporan = $supirs[0]['judulLaporan'];

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

            $this->toExcel($judulLaporan, $supirs, $columns);
        }
    }
}
