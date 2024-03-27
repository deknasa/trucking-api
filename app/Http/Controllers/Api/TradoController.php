<?php

namespace App\Http\Controllers\Api;

use stdClass;
use App\Helpers\App;

use App\Models\Stok;
use App\Models\Trado;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\StokPersediaan;
use Illuminate\Http\JsonResponse;
use App\Models\ApprovalTradoTanpa;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\TradoRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\HistoryTradoMilikSupir;
use Illuminate\Support\Facades\Schema;
use App\Models\HistoryTradoMilikMandor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreTradoRequest;
use App\Http\Requests\UpdateTradoRequest;
use App\Http\Requests\DestroyTradoRequest;
use App\Http\Requests\ApprovalTradoRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Requests\HistoryTradoMilikSupirRequest;
use App\Http\Requests\HistoryTradoMilikMandorRequest;
use App\Http\Requests\StoreApprovalTradoTanpaRequest;
use App\Models\Error;

class TradoController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $trado = new Trado();


        return response([


            'data' => $trado->get(),
            'attributes' => [
                'totalRows' => $trado->totalRows,
                'totalPages' => $trado->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $trado = new Trado();

        if (request()->aksi == "ApprovalTanpa") {
            $approvalTradoTanpa = (new ApprovalTradoTanpa())->cekApproval($trado->find($id));
            // dd($approvalTradoTanpa);
            $data = [
                'error' => (!$approvalTradoTanpa['gambar'] && !$approvalTradoTanpa['keterangan']),
                'statusapproval' => $approvalTradoTanpa,
                'message' => '',
                'statuspesan' => 'success',
            ];
            return response($data);
        }

        $cekdata = $trado->cekvalidasihapus($id);
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

        $trado = new Trado();
        return response([
            'status' => true,
            'data' => $trado->default(),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreTradoRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'keterangan' => $request->keterangan ?? '',
                'kodetrado' => $request->kodetrado,
                'statusaktif' => $request->statusaktif,
                'tahun' => $request->tahun,
                'merek' => $request->merek,
                'norangka' => $request->norangka,
                'nomesin' => $request->nomesin,
                'nama' => $request->nama,
                'nostnk' => $request->nostnk,
                'alamatstnk' => $request->alamatstnk,
                'statusjenisplat' => $request->statusjenisplat,
                'tglpajakstnk' => date('Y-m-d', strtotime($request->tglpajakstnk)),
                'tglstnkmati' => $request->tglstnkmati,
                'tglspeksimati' => $request->tglspeksimati,
                'tglasuransimati' => $request->tglasuransimati,
                'tipe' => $request->tipe,
                'jenis' => $request->jenis,
                'isisilinder' => $request->isisilinder,
                'warna' => $request->warna,
                'jenisbahanbakar' => $request->jenisbahanbakar,
                'jumlahsumbu' => $request->jumlahsumbu,
                'jumlahroda' => $request->jumlahroda,
                'model' => $request->model,
                'nobpkb' => $request->nobpkb,
                'mandor_id' => $request->mandor_id ?? 0,
                'supir_id' => $request->supir_id ?? 0,
                'jumlahbanserap' => $request->jumlahbanserap,
                'statusgerobak' => $request->statusgerobak,
                'statusabsensisupir' => $request->statusabsensisupir,
                'nominalplusborongan' => str_replace(',', '', $request->nominalplusborongan) ?? 0,
                'photostnk' => ($request->photostnk) ? $this->storeFiles($request->photostnk, 'stnk') : '',
                'photobpkb' => ($request->photobpkb) ? $this->storeFiles($request->photobpkb, 'bpkb') : '',
                'phototrado' => ($request->phototrado) ? $this->storeFiles($request->phototrado, 'trado') : '',
                'tas_id' => $request->tas_id,
            ];


            $trado = (new Trado())->processStore($data);
            $selected = $this->getPosition($trado, $trado->getTable());
            $trado->position = $selected->position;
            if ($request->limit == 0) {
                $trado->page = ceil($trado->position / (10));
            } else {
                $trado->page = ceil($trado->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $trado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function cekvalidasihistory($id)
    {
        $trado = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.kodetrado'
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
            $query = DB::table('trado')->from(db::raw("trado a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodetrado'
                )
                ->where('a.id', $id)
                ->where('a.statusapprovalhistorytradomilikmandor', $statusApproval->id)
                ->first();

            if (!isset($query)) {
                $keteranganerror = $error->cekKeteranganError('BAP') ?? '';
                $keterror = 'No Polisi <b>' . $trado->kodetrado  . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'BAP',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        }

        if ($aksi == 'historySupir') {

            $query = DB::table('trado')->from(db::raw("trado a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodetrado'
                )
                ->where('a.id', $id)
                ->where('a.statusapprovalhistorytradomiliksupir', $statusApproval->id)
                ->first();


            if (!isset($query)) {
                $keteranganerror = $error->cekKeteranganError('BAP') ?? '';
                $keterror = 'No Polisi <b>' . $trado->kodetrado  . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTradoRequest $request, Trado $trado): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'keterangan' => $request->keterangan ?? '',
                'kodetrado' => $request->kodetrado,
                'statusaktif' => $request->statusaktif,
                'tahun' => $request->tahun,
                'merek' => $request->merek,
                'norangka' => $request->norangka,
                'nomesin' => $request->nomesin,
                'nama' => $request->nama,
                'nostnk' => $request->nostnk,
                'alamatstnk' => $request->alamatstnk,
                'statusjenisplat' => $request->statusjenisplat,
                'tglpajakstnk' => date('Y-m-d', strtotime($request->tglpajakstnk)),
                'tglstnkmati' => $request->tglstnkmati,
                'tglspeksimati' => $request->tglspeksimati,
                'tglasuransimati' => $request->tglasuransimati,
                'tipe' => $request->tipe,
                'jenis' => $request->jenis,
                'isisilinder' => $request->isisilinder,
                'warna' => $request->warna,
                'jenisbahanbakar' => $request->jenisbahanbakar,
                'jumlahsumbu' => $request->jumlahsumbu,
                'jumlahroda' => $request->jumlahroda,
                'model' => $request->model,
                'nobpkb' => $request->nobpkb,
                // 'mandor_id' => $request->mandor_id ?? 0,
                // 'supir_id' => $request->supir_id ?? 0,
                'jumlahbanserap' => $request->jumlahbanserap,
                'statusgerobak' => $request->statusgerobak,
                'statusabsensisupir' => $request->statusabsensisupir,
                'nominalplusborongan' => str_replace(',', '', $request->nominalplusborongan) ?? 0,
                'photostnk' => ($request->photostnk) ? $this->storeFiles($request->photostnk, 'stnk') : '',
                'photobpkb' => ($request->photobpkb) ? $this->storeFiles($request->photobpkb, 'bpkb') : '',
                'phototrado' => ($request->phototrado) ? $this->storeFiles($request->phototrado, 'trado') : '',
            ];


            $trado = (new Trado())->processUpdate($trado, $data);
            $trado->position = $this->getPosition($trado, $trado->getTable())->position;
            if ($request->limit == 0) {
                $trado->page = ceil($trado->position / (10));
            } else {
                $trado->page = ceil($trado->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $dataTrado = Trado::findAll($id);
        return response([
            'status' => true,
            'data' => $dataTrado
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalTradoRequest $request)
    {

        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Trado())->processApprovalnonaktif($data);

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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyTradoRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $trado = (new Trado())->processDestroy($id);
            $selected = $this->getPosition($trado, $trado->getTable(), true);
            $trado->position = $selected->position;
            $trado->id = $selected->id;
            if ($request->limit == 0) {
                $trado->page = ceil($trado->position / (10));
            } else {
                $trado->page = ceil($trado->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('trado')->getColumns();

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
            'standarisasi' => Parameter::where(['grp' => 'status standarisasi'])->get(),
            'plat' => Parameter::where(['grp' => 'jenis plat'])->get(),
            'mutasi' => Parameter::where(['grp' => 'status mutasi'])->get(),
            'validasikendaraan' => Parameter::where(['grp' => 'status validasi kendaraan'])->get(),
            'mobilstoring' => Parameter::where(['grp' => 'status mobil storing'])->get(),
            'appeditban' => Parameter::where(['grp' => 'status app edit ban'])->get(),
            'lewatvalidasi' => Parameter::where(['grp' => 'status lewat validasi'])->get(),
            'mandor' => DB::table('mandor')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function getImage(string $field, string $filename, string $type, string $aksi)
    {
        if (Storage::exists("trado/$field/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/trado/$field/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("trado/$field/$filename")) {
                return response()->file(storage_path("app/trado/$field/$filename"));
            } else {
                if ($aksi == 'show') {
                    return response()->file(storage_path("app/no-image.jpg"));
                } else {
                    return response('no-image');
                }
            }
        }
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs("trado/" . $destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/trado/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Trado $trado)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoTrado = [];
        $relatedPhotoStnk = [];
        $relatedPhotoBpkb = [];

        $photoTrado = json_decode($trado->phototrado, true);
        $photoStnk = json_decode($trado->photostnk, true);
        $photoBpkb = json_decode($trado->photobpkb, true);

        if ($photoTrado != '') {
            foreach ($photoTrado as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoTrado[] = "trado/trado/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoTrado);
        }

        if ($photoStnk != '') {
            foreach ($photoStnk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStnk[] = "trado/stnk/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStnk);
        }

        if ($photoBpkb != '') {
            foreach ($photoBpkb as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoBpkb[] = "trado/bpkb/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoBpkb);
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
            header('Access-Control-Allow-Origin: *');

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $trados = $decodedResponse['data'];

            $judulLaporan = $trados[0]['judulLaporan'];

            $i = 0;
            foreach ($trados as $index => $params) {


                $statusaktif = $params['statusaktif'];
                $statusStandarisasi = $params['statusstandarisasi'];
                $statusJenisPlat = $params['statusjenisplat'];
                $statusMutasi = $params['statusmutasi'];
                $statusValidasiKendaraan = $params['statusvalidasikendaraan'];
                $statusMobilStoring = $params['statusmobilstoring'];
                $statusAppEditBan = $params['statusappeditban'];
                $statusLewatValidasi = $params['statuslewatvalidasi'];


                $result = json_decode($statusaktif, true);
                $resultStandarisasi = json_decode($statusStandarisasi, true);
                $resultJenisPlat = json_decode($statusJenisPlat, true);
                $resultMutasi = json_decode($statusMutasi, true);
                $resultValidasiKendaraan = json_decode($statusValidasiKendaraan, true);
                $resultMobilStoring = json_decode($statusMobilStoring, true);
                $resultAppEditBan = json_decode($statusAppEditBan, true);
                $resultLewatValidasi = json_decode($statusLewatValidasi, true);

                $statusaktif = $result['MEMO'];
                $statusStandarisasi = $resultStandarisasi['MEMO'];
                $statusJenisPlat = $resultJenisPlat['MEMO'];
                $statusMutasi = $resultMutasi['MEMO'];
                $statusValidasiKendaraan = $resultValidasiKendaraan['MEMO'];
                $statusMobilStoring = $resultMobilStoring['MEMO'];
                $statusAppEditBan = $resultAppEditBan['MEMO'];
                $statusLewatValidasi = $resultLewatValidasi['MEMO'];


                $trados[$i]['statusaktif'] = $statusaktif;
                $trados[$i]['statusstandarisasi'] = $statusStandarisasi;
                $trados[$i]['statusjenisplat'] = $statusJenisPlat;
                $trados[$i]['statusmutasi'] = $statusMutasi;
                $trados[$i]['statusvalidasikendaraan'] = $statusValidasiKendaraan;
                $trados[$i]['statusmobilstoring'] = $statusMobilStoring;
                $trados[$i]['statusappeditban'] = $statusAppEditBan;
                $trados[$i]['statuslewatvalidasi'] = $statusLewatValidasi;

                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'No Polisi',
                    'index' => 'kodetrado',
                ],
                [
                    'label' => 'KM Awal',
                    'index' => 'kmawal',
                ],
                [
                    'label' => 'KM Ganti Oli',
                    'index' => 'kmakhirgantioli',
                ],
                [
                    'label' => 'Tgl Asuransi Mati',
                    'index' => 'tglasuransimati',
                ],
                [
                    'label' => 'Merk',
                    'index' => 'merek',
                ],
                [
                    'label' => 'No Rangka',
                    'index' => 'norangka',
                ],
                [
                    'label' => 'No Mesin',
                    'index' => 'nomesin',
                ],
                [
                    'label' => 'Nama',
                    'index' => 'nama',
                ],
                [
                    'label' => 'No STNK',
                    'index' => 'nostnk',
                ],
                [
                    'label' => 'Alamat STNK',
                    'index' => 'alamatstnk',
                ],
                [
                    'label' => 'Tgl Service Opname',
                    'index' => 'tglserviceopname',
                ],
                [
                    'label' => 'Ket Progress Standarisasi',
                    'index' => 'keteranganprogressstandarisasi',
                ],
                [
                    'label' => 'Tgl Pajak STNK',
                    'index' => 'tglpajakstnk',
                ],
                [
                    'label' => 'Tgl Aki Terakhir',
                    'index' => 'tglgantiakiterakhir',
                ],
                [
                    'label' => 'Jumlah Sumbu',
                    'index' => 'jumlahsumbu',
                ],
                [
                    'label' => 'Jumlah Roda',
                    'index' => 'jumlahroda',
                ],
                [
                    'label' => 'Model',
                    'index' => 'model',
                ],
                [
                    'label' => 'Plus Borongan',
                    'index' => 'nominalplusborongan',
                ],
                [
                    'label' => 'No BPKB',
                    'index' => 'nobpkb',
                ],
                [
                    'label' => 'Status Validasi Kendaraan',
                    'index' => 'statusvalidasikendaraan',
                ],
                [
                    'label' => 'Tipe',
                    'index' => 'tipe',
                ],
                [
                    'label' => 'Jenis',
                    'index' => 'jenis',
                ],
                [
                    'label' => 'Isi Silinder',
                    'index' => 'isisilinder',
                ],
                [
                    'label' => 'Jumlah Ban Serap',
                    'index' => 'jumlahbanserap',
                ],
                [
                    'label' => 'Warna',
                    'index' => 'warna',
                ],
                [
                    'label' => 'Jenis Bahan Bakar',
                    'index' => 'jenisbahanbakar',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Status Standarisasi',
                    'index' => 'statusstandarisasi',
                ],
                [
                    'label' => 'Status Jenis Plat',
                    'index' => 'statusjenisplat',
                ],
                [
                    'label' => 'Status Mutasi',
                    'index' => 'statusmutasi',
                ],
                [
                    'label' => 'Status Mobil Storing',
                    'index' => 'statusmobilstoring',
                ],
                [
                    'label' => 'Status App Edit Ban',
                    'index' => 'statusappeditban',
                ],
                [
                    'label' => 'Status Lewat Validasi',
                    'index' => 'statuslewatvalidasi',
                ],
                [
                    'label' => 'Mandor',
                    'index' => 'mandor_id',
                ],
                [
                    'label' => 'Supir',
                    'index' => 'supir_id',
                ],

            ];

            // foreach ($parameters as $index => $params) {
            //     $data = $params['statusaktif'];


            //     $result = json_decode($data, true);

            //     $statusaktif = $result['MEMO'];



            //     // Memperbarui nilai 'statusaktif' pada $columns
            //     $columns[$index + 4]['index'] = $statusaktif;
            // }

            $this->toExcel($judulLaporan, $trados, $columns);
        }
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL REMINDER OLI MESIN
     */
    public function approvalmesin(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        try {
            $data = [
                'tradoId' => $request->Id
            ];
            $trado = (new Trado())->processApprovalMesin($data);

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
     * @Keterangan APPROVAL REMINDER OLI PESNELING
     */
    public function approvalpersneling(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tradoId' => $request->Id
            ];
            $trado = (new Trado())->processApprovalPersneling($data);

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function approvalTradoTanpa()
    {
        $approvalTradoTanpa = new ApprovalTradoTanpa();

        if (isset(request()->trado_id)) {
            $data = $approvalTradoTanpa->firstOrFind(request()->trado_id);
        }
        return response([
            'data' => $data,

        ]);
    }


    /**
     * @ClassName 
     * @Keterangan APPROVAL TRADO TANPA KETERANGAN / GAMBAR
     */
    public function StoreApprovalTradoTanpa(StoreApprovalTradoTanpaRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = [
                "trado_id" => $request->trado_id,
                "kodetrado" => $request->kodetrado,
                "keterangan_id" => $request->keterangan_id,
                "keterangan_statusapproval" => $request->keterangan_statusapproval,
                "gambar_id" => $request->gambar_id,
                "gambar_statusapproval" => $request->gambar_statusapproval,
                "tglbatas" => $request->tglbatas,
            ];
            // dd($data);

            $approvalTradoTanpa = (new ApprovalTradoTanpa())->processStore($data);

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $approvalTradoTanpa
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL REMINDER OLI GARDAN
     */
    public function approvalgardan(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tradoId' => $request->Id
            ];
            $trado = (new Trado())->processApprovalGardan($data);

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
     * @Keterangan APPROVAL REMINDER SARINGAN HAWA
     */
    public function approvalsaringanhawa(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tradoId' => $request->Id
            ];
            $trado = (new Trado())->processApprovalSaringanHawa($data);

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
     * @Keterangan APPROVAL HISTORY TRADO MILIK MANDOR
     */
    public function approvalhistorytradomilikmandor(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tradoId' => $request->Id
            ];
            $trado = (new Trado())->processApprovalHistoryTradoMilikMandor($data);

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
     * @Keterangan APPROVAL HISTORY TRADO MILIK SUPIR
     */
    public function approvalhistorytradomiliksupir(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tradoId' => $request->Id
            ];
            $trado = (new Trado())->processApprovalHistoryTradoMilikSupir($data);

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
     * @Keterangan HISTORY TRADO MILIK MANDOR
     */
    public function historyTradoMandor(HistoryTradoMilikMandorRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = [
                'id' => $request->id,
                'mandorbaru_id' => $request->mandorbaru_id,
                'mandor_id' => $request->mandor_id,
                'tglberlaku' => $request->tglberlaku,
            ];

            $trado = (new Trado())->processHistoryTradoMilikMandor($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getHistoryMandor($id)
    {
        return response([
            'data' => (new Trado())->getHistoryMandor($id),
        ]);
    }

    public function getListHistoryMandor($id)
    {
        return response([
            'data' => (new HistoryTradoMilikMandor())->get($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan HISTORY TRADO MILIK SUPIR
     */
    public function historyTradoSupir(HistoryTradoMilikSupirRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = [
                'id' => $request->id,
                'supirbaru_id' => $request->supirbaru_id,
                'supir_id' => $request->supir_id,
                'tglberlaku' => $request->tglberlaku,
            ];

            $trado = (new Trado())->processHistoryTradoMilikSupir($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $trado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function getHistorySupir($id)
    {
        return response([
            'data' => (new Trado())->getHistorySupir($id),
        ]);
    }

    public function getListHistorySupir($id)
    {
        return response([
            'data' => (new HistoryTradoMilikSupir())->get($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL TRADO TANPA GAMBAR
     */
    public function approvaltradogambar()
    {
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL TRADO TANPA KETERANGAN
     */
    public function approvaltradoketerangan()
    {
    }
}
