<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Exception;
use App\Helpers\App;

use App\Models\Zona;
use App\Models\Error;
use App\Models\Supir;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Support\Js;
use Illuminate\Http\Request;
use App\Models\PemutihanSupir;
use Illuminate\Http\JsonResponse;
use App\Models\ApprovalSupirTanpa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\HistorySupirMilikMandor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreSupirRequest;
use App\Http\Requests\UpdateSupirRequest;
use App\Http\Requests\ApprovalSupirRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\ApprovalSupirKacabRequest;
use App\Http\Requests\ApprovalSupirLuarKotaRequest;
use App\Http\Requests\ApprovalSupirNonAktifRequest;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Requests\HistorySupirMilikMandorRequest;
use App\Http\Requests\StoreApprovalSupirTanpaRequest;

class SupirController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan APPROVAL BLACK LIST SUPIR
     */
    public function approvalBlackListSupir(ApprovalSupirRequest $request)
    {

        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Supir())->processApprovalBlackListSupir($data);

            DB::commit();
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
     * @Keterangan APPROVAL Kacab
     */
    public function approval(ApprovalSupirKacabRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Supir())->processApproval($data);

            DB::commit();
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
     * @Keterangan APPROVAL SUPIR LUAR KOTA
     */
    public function approvalSupirLuarKota(ApprovalSupirLuarKotaRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'id' => $request->id,
                'nama' => $request->namasupir,
                'tglbatas' => $request->tglbatas,
                'statusluarkota' => $request->statusluarkota,
                'keterangan' => $request->keterangan

            ];
            (new Supir())->processApprovalSupirLuarKota($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function approvalLuarKota(Request $request)
    {
        $id = $request->supir_id;
        return response([
            'data' => (new Supir())->getApprovalLuarKota($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL SUPIR RESIGN
     */
    public function approvalSupirResign(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $supir = Supir::lockForUpdate()->findOrFail($id);
            $cekValidasi = (new Supir())->validationSupirResign($supir->noktp, $id);
            if ($cekValidasi == false) {
                $query = DB::table('error')
                    ->select('keterangan')
                    ->where('kodeerror', '=', 'SPI')
                    ->first();
                $getSupir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('namasupir')
                    ->where('noktp', $supir->noktp)
                    ->whereRaw("isnull(tglberhentisupir,'1900-01-01') = '1900-01-01'")
                    ->first();
                return response([
                    'errors' => true,
                    'statuspesan' => 'warning',
                    'message' => 'NO KTP SUPIR ' . $query->keterangan . " ($getSupir->namasupir)"
                ], 500);
            } else {
                $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
                $statusapproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
                
                $cabang = DB::table('parameter')->where('grp', 'CABANG')->where('subgrp', 'CABANG')->first();

                if ($request->action == "approve") {
                    $supir->tglberhentisupir = date('Y-m-d', strtotime($request->tglberhentisupir));
                    $supir->statusaktif = $statusaktif->id;
                    if ($cabang->text == "SURABAYA") {
                        $supir->statusapproval = $statusapproval->id;
                    }

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
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function cekValidasi($id)
    {
        $supir = new Supir();

        $dataMaster = $supir->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        if (request()->aksi == "ApprovalTanpa") {
            $approvalSupirTanpa = (new ApprovalSupirTanpa())->cekApproval($supir->find($id));
            // dd($approvalSupirTanpa);
            $data = [
                'error' => (!$approvalSupirTanpa['gambar'] && !$approvalSupirTanpa['keterangan']),
                'statusapproval' => $approvalSupirTanpa,
                'message' => '',
                'statuspesan' => 'success',
            ];
            return response($data);
        }
        $cekdata = $supir->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            if (request()->aksi == 'EDIT') {

                $query = DB::table('error')
                    ->select(
                        DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                    )
                    ->where('kodeerror', '=', 'SR')
                    ->first();
                $keterangan = $query->keterangan;
            } else {

                $query = DB::table('error')
                    ->select(
                        DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                    )
                    ->where('kodeerror', '=', 'SATL')
                    ->first();
                $keterangan = $query->keterangan;
            }

            $data = [
                'error' => true,
                'message' => $keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('supir', $id, $aksi);
                }

                $data = [
                    'error' => false,
                    'message' => '',
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->namasupir . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('supir', $id, $aksi);
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
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
     * @Keterangan TAMBAH DATA
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
                'statuspostingtnl' => $request->statuspostingtnl,
                'nominaldepositsa' => str_replace(',', '', $request->nominaldepositsa) ?? 0,
                'depositke' => str_replace('.', '', $depositke) ?? 0,
                'tglmasuk' => $request->tglmasuk,
                'nominalpinjamansaldoawal' => str_replace(',', '', $request->nominalpinjamansaldoawal) ?? 0,
                'pemutihansupir_nobukti' => $request->pemutihansupir_nobukti ?? '',
                'supirold_id' => $request->supirold_id ?? 0,
                'tglexpsim' => $request->tglexpsim,
                'nosim' => $request->nosim,
                'keterangan' => $request->keterangan ?? '',
                'noktp' => $request->noktp,
                'nokk' => $request->nokk,
                'angsuranpinjaman' => str_replace(',', '', $request->angsuranpinjaman) ?? 0,
                'plafondeposito' => str_replace(',', '', $request->plafondeposito) ?? 0,
                'tgllahir' => $request->tgllahir,
                'tglterbitsim' => $request->tglterbitsim,
                'modifiedby' => auth('api')->user()->name,
                'mandor_id' => $request->mandor_id ?? 0,

                'photosupir' => $request->photosupir ?? [],
                'photoktp' => $request->photoktp ?? [],
                'photosim' => $request->photosim ?? [],
                'photokk' => $request->photokk ?? [],
                'photoskck' => $request->photoskck ?? [],
                'photodomisili' => $request->photodomisili ?? [],
                'photovaksin' => $request->photovaksin ?? [],
                'pdfsuratperjanjian' => $request->pdfsuratperjanjian ?? [],
                'from' => $request->from ?? '',
            ];
            $supir = (new supir())->processStore($data);
            if ($request->from == '') {
                $supir->position = $this->getPosition($supir, $supir->getTable())->position;
                if ($request->limit == 0) {
                    $supir->page = ceil($supir->position / (10));
                } else {
                    $supir->page = ceil($supir->position / ($request->limit ?? 10));
                }
            }

            $statusTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'POSTING TNL')->first();
            if ($data['statuspostingtnl'] == $statusTnl->id) {
                $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
                // posting ke tnl
                $data['statuspostingtnl'] = $statusBukanTnl->id;
                $gambar = [
                    'supir' => $supir->photosupir,
                    'ktp' => $supir->photoktp,
                    'sim' => $supir->photosim,
                    'kk' => $supir->photokk,
                    'skck' => $supir->photoskck,
                    'domisili' => $supir->photodomisili,
                    'vaksin' => $supir->photovaksin,
                    'pdfsuratperjanjian' => $supir->pdfsuratperjanjian,
                ];
                $postingTNL = (new supir())->postingTnl($data, $gambar);
            }

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
     * @Keterangan EDIT DATA
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
            if ($request->limit == 0) {
                $supir->page = ceil($supir->position / (10));
            } else {
                $supir->page = ceil($supir->position / ($request->limit ?? 10));
            }

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
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $supir = (new Supir())->processDestroy($id);
            $selected = $this->getPosition($supir, $supir->getTable(), true);
            $supir->position = $selected->position;
            $supir->id = $selected->id;
            if ($request->limit == 0) {
                $supir->page = ceil($supir->position / (10));
            } else {
                $supir->page = ceil($supir->position / ($request->limit ?? 10));
            }

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

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(RangeExportReportRequest $request)
    {
        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
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
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'No KTP',
                    'index' => 'noktp',
                ],
                [
                    'label' => 'Status Blacklist',
                    'index' => 'statusblacklist',
                ],
                [
                    'label' => 'Tgl Berhenti Supir',
                    'index' => 'tglberhentisupir',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Supir Lama',
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

            ];

            $this->toExcel($judulLaporan, $supirs, $columns);
        }
    }

    public function getSupirResign(Request $request)
    {
        $supir = new Supir();
        $noktp = $request->noktp;
        return response([
            'data' => $supir->getSupirResignModel($noktp)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan HISTORY SUPIR MILIK MANDOR
     */
    public function historySupirMandor(HistorySupirMilikMandorRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = [
                'id' => $request->id,
                'mandorbaru_id' => $request->mandorbaru_id,
                'mandor_id' => $request->mandor_id,
                'tglberlaku' => $request->tglberlaku,
            ];

            $supir = (new Supir())->processHistorySupirMilikMandor($data);

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

    public function getHistoryMandor($id)
    {
        return response([
            'data' => (new Supir())->getHistoryMandor($id),
        ]);
    }

    public function getListHistoryMandor($id)
    {
        return response([
            'data' => (new HistorySupirMilikMandor())->get($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalSupirNonAktifRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id
            ];
            (new Supir())->processApprovalnonaktif($data);

            DB::commit();
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
     * @Keterangan APPROVAL AKTIF
     */
    public function approvalaktif(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id
            ];
            (new Supir())->processApprovalaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function approvalSupirTanpa()
    {
        $approvalSupirTanpa = new ApprovalSupirTanpa();

        if (isset(request()->supir_id)) {
            $data = $approvalSupirTanpa->firstOrFind(request()->supir_id);
        }
        return response([
            'data' => $data,

        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL SUPIR TANPA KETERANGAN / GAMBAR
     */
    public function StoreApprovalSupirTanpa(StoreApprovalSupirTanpaRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = [
                "supir_id" => $request->supir_id,
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "keterangan_id" => $request->keterangan_id,
                "keterangan_statusapproval" => $request->keterangan_statusapproval,
                "gambar_id" => $request->gambar_id,
                "gambar_statusapproval" => $request->gambar_statusapproval,
                "tglbatas" => $request->tglbatas,
            ];

            $approvalSupirTanpa = (new ApprovalSupirTanpa())->processStore($data);

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalSupirTanpa
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekvalidasihistory($id)
    {
        $supir = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.namasupir',
                'a.mandor_id'
            )
            ->where('a.id', $id)
            ->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $aksi = request()->aksi ?? '';
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        // dd($aksi);
        if ($aksi == 'historyMandor') {
            $query = DB::table('supir')->from(db::raw("supir a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.namasupir'
                )
                ->where('a.id', $id)
                ->where('a.statusapprovalhistorysupirmilikmandor', $statusApproval->id)
                ->first();

            if (!isset($query)) {
                if ($supir != '') {
                    if ($supir->mandor_id == '' || $supir->mandor_id == 0) {
                        $data = [
                            'error' => false,
                            'message' => '',
                            'statuspesan' => 'success',
                        ];

                        return response($data);
                    }
                }
                $keteranganerror = $error->cekKeteranganError('BAP') ?? '';
                $keterror = 'Supir <b>' . $supir->namasupir  . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'BAP',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        }



        $data = [
            'error' => false,
            'message' => '',
            'statuspesan' => 'success',
        ];

        return response($data);
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL HISTORY SUPIR MILIK MANDOR
     */
    public function approvalhistorysupirmilikmandor(ApprovalSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id
            ];
            $trado = (new Supir())->processApprovalHistoryTradoMilikMandor($data);

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL SUPIR TANPA KETERANGAN
     */
    public function approvalsupirketerangan()
    {
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL SUPIR TANPA GAMBAR
     */
    public function approvalsupirgambar()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA USER
     */
    public function updateuser()
    {
    }
}
