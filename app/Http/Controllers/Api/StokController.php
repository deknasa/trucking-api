<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Helpers\App;

use App\Models\Stok;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreStokRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateStokRequest;
use App\Http\Requests\ApprovalSupirRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;
use Intervention\Image\ImageManagerStatic as Image;

class StokController extends Controller
{
    private $stok;
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan EDIT DATA USER
     */
    public function updateuser()
    {
    }


    public function cekValidasi($id, request $request)
    {
        $stok = new Stok();
        $dataMaster = $stok->where('id', $id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        $cekdata = $stok->cekvalidasihapus($id);


        $aksi = $request->aksi ?? '';
        $acoid = db::table('acos')->from(db::raw("acos a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.class', 'kategori')
            ->where('a.method', 'update')
            ->first()->id ?? 0;
        $userid = auth('api')->user()->id;

        $data = (new MyModel())->hakuser($userid, $acoid);
        if ($data == true) {
            $hakutama = 1;
        } else {
            $hakutama = 0;
        }
        if ($aksi == 'edit') {
            if ($cekdata['kondisi'] == true) {
                if ($hakutama == 1) {
                    $cekdata['kondisi'] = false;
                }
            }
        }

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
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('stok', $id, $aksi);
                }

                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->keterangan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;

                $data = [
                    'status' => true,
                    'message' => ["keterangan" => $keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];

                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('stok', $id, $aksi);

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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreStokRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'keterangan' => $request->keterangan,
                'namastok' => $request->namastok,
                'namaterpusat' => $request->namaterpusat,
                'statusaktif' => $request->statusaktif,
                'kelompok_id' => $request->kelompok_id,
                'subkelompok_id' => $request->subkelompok_id,
                'kategori_id' => $request->kategori_id,
                'merk_id' => $request->merk_id ?? 0,
                'jenistrado_id' => $request->jenistrado_id ?? 0,
                'keterangan' => $request->keterangan ?? '',
                'qtymin' => $request->qtymin ?? 0,
                'qtymax' => $request->qtymax ?? 0,
                'statusreuse' => $request->statusreuse,
                'statusban' => $request->statusban,
                'statusservicerutin' => $request->statusservicerutin,
                'satuan_id' => $request->satuan_id,
                'totalvulkanisir' => $request->totalvulkanisir,
                'vulkanisirawal' => $request->vulkanisirawal,
                'hargabelimin' => $request->hargabelimin,
                'hargabelimax' => $request->hargabelimax,
                'gambar' => $request->gambar,
                'tas_id' => $request->tas_id

            ];
            $stok = (new Stok())->processStore($data);
            if ($request->from == '') {
                $stok->position = $this->getPosition($stok, $stok->getTable())->position;
                if ($request->limit == 0) {
                    $stok->page = ceil($stok->position / (10));
                } else {
                    $stok->page = ceil($stok->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $stok->id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $gambar = json_decode($stok->gambar);
                if ($gambar != '') {
                    $supirBase64 = [];
                    foreach ($gambar as $imagePath) {
                        $gambarBase64[] = base64_encode(file_get_contents(storage_path("app/stok/" . $imagePath)));
                    }
                    $data['gambar'] = $gambarBase64;
                }
                $this->saveToTnl('stok', 'add', $data);
            }
            // $this->stok = $stok;
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


    public function show($id)
    {
        $stok = Stok::findAll($id);
        $gambar = json_decode($stok->gambar);

        $countGambar = 0;
        if ($gambar != null) {
            foreach ($gambar as $g) {
                if (Storage::exists("stok/$g")) {
                    $countGambar++;
                }
            }
        }
        $statusPakai = (new Stok)->cekvalidasihapus($id);
        return response([
            'status' => true,
            'statuspakai' => $statusPakai['kondisi'],
            'data' => $stok,
            'count' => $countGambar
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateStokRequest $request, $id)
    {
        $stok = Stok::find($id);

        DB::beginTransaction();
        try {
            $data = [
                'keterangan' => $request->keterangan,
                'namastok' => $request->namastok,
                'namaterpusat' => $request->namaterpusat,
                'statusaktif' => $request->statusaktif,
                'kelompok_id' => $request->kelompok_id,
                'subkelompok_id' => $request->subkelompok_id,
                'kategori_id' => $request->kategori_id,
                'merk_id' => $request->merk_id ?? 0,
                'jenistrado_id' => $request->jenistrado_id ?? 0,
                'keterangan' => $request->keterangan ?? '',
                'qtymin' => $request->qtymin ?? 0,
                'qtymax' => $request->qtymax ?? 0,
                'statusreuse' => $request->statusreuse,
                'statusban' => $request->statusban,
                'statusservicerutin' => $request->statusservicerutin,
                'satuan_id' => $request->satuan_id,
                'totalvulkanisir' => $request->totalvulkanisir,
                'vulkanisirawal' => $request->vulkanisirawal,
                'hargabelimin' => $request->hargabelimin,
                'hargabelimax' => $request->hargabelimax,
                'gambar' => $request->gambar,

            ];

            $stok = (new Stok())->processUpdate($stok, $data);
            if ($request->from == '') {
                $stok->position = $this->getPosition($stok, $stok->getTable())->position;
                if ($request->limit == 0) {
                    $stok->page = ceil($stok->position / (10));
                } else {
                    $stok->page = ceil($stok->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $stok->id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $gambar = json_decode($stok->gambar);
                if ($gambar != '') {
                    $supirBase64 = [];
                    foreach ($gambar as $imagePath) {
                        $gambarBase64[] = base64_encode(file_get_contents(storage_path("app/stok/" . $imagePath)));
                    }
                    $data['gambar'] = $gambarBase64;
                }
                $this->saveToTnl('stok', 'edit', $data);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $stok
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
            $stok = (new Stok())->processDestroy($id);
            if ($request->from == '') {
                $selected = $this->getPosition($stok, $stok->getTable(), true);
                $stok->position = $selected->position;
                $stok->id = $selected->id;
                if ($request->limit == 0) {
                    $stok->page = ceil($stok->position / (10));
                } else {
                    $stok->page = ceil($stok->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;
            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('stok', 'delete', $data);
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $stok
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL TANPA KLAIM
     */
    public function approvalklaim(ApprovalSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'id' => $request->Id,
                'nama' => $request->nama,
            ];
            $stok = new Stok();
            $stok->processApprovalklaim($data);

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
     * @Keterangan APPROVAL STOK REUSE
     */
    public function approvalReuse(ApprovalSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'id' => $request->Id,
                'nama' => $request->nama,
            ];
            $stok = new Stok();
            $stok->processApprovalReuse($data);

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
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Stok())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
        if (Storage::exists("stok/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/stok/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("stok/$filename")) {
                return response()->file(storage_path("app/stok/$filename"));
            } else {
                return response()->file(storage_path("app/no-image.jpg"));
            }
        }
    }

    public function fieldLength(Type $var = null)
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('stok')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
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
            $stoks = $decodedResponse['data'];

            $judulLaporan = $stoks[0]['judulLaporan'];

            $i = 0;
            foreach ($stoks as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $stoks[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Stok',
                    'index' => 'namastok',
                ],
                [
                    'label' => 'Nama Terpusat',
                    'index' => 'namaterpusat',
                ],
                [
                    'label' => 'Kelompok',
                    'index' => 'kelompok',
                ],
                [
                    'label' => 'Qty Min',
                    'index' => 'qtymin',
                ],
                [
                    'label' => 'Qty Max',
                    'index' => 'qtymax',
                ],
                [
                    'label' => 'Sub Kelompok',
                    'index' => 'subkelompok',
                ],
                [
                    'label' => 'Kategori',
                    'index' => 'kategori',
                ],
                [
                    'label' => 'Jenis Trado',
                    'index' => 'jenistrado',
                ],
                [
                    'label' => 'Merk',
                    'index' => 'merk',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
            ];
            $this->toExcel($judulLaporan, $stoks, $columns);
        }
    }

    public function getGambar()
    {
        $stok = (new stok())->getGambarName(request()->id);
        if ($stok->gambar != '') {
            $gambar = json_decode($stok->gambar);
            $filename = $gambar[0];
            if (Storage::exists("stok/ori" . '_' . "$filename")) {

                return response([
                    'gambar' => $filename
                ]);
            } else {
                if (Storage::exists("stok/$filename")) {
                    return response([
                        'gambar' => "$filename"
                    ]);
                } else {
                    return response([
                        'gambar' => "no-image"
                    ]);
                }
            }
        }
        return response([
            'gambar' => "no-image"
        ]);
    }

    public function getvulkan(Stok $stok)
    {
        return response([
            'data' => $stok->getvulkanisir($stok->id),
        ]);
        // dd($stok->getvulkanisir($stok->id));
    }
    public function updatekonsolidasi(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'namaterpusat' => $request->namaterpusat,
                'kelompok' => $request->kelompok,
                'kelompok_id' => $request->kelompok_id,
                'stok_idmdn' => $request->stok_idmdn,
                'namastokmdn' => $request->namastokmdn,
                'gambarmdn' => $request->gambarmdn,
                'stok_idjkt' => $request->stok_idjkt,
                'namastokjkt' => $request->namastokjkt,
                'gambarjkt' => $request->gambarjkt,
                'stok_idjkttnl' => $request->stok_idjkttnl,
                'namastokjkttnl' => $request->namastokjkttnl,
                'gambarjkttnl' => $request->gambarjkttnl,
                'stok_idmks' => $request->stok_idmks,
                'namastokmks' => $request->namastokmks,
                'gambarmks' => $request->gambarmks,
                'stok_idsby' => $request->stok_idsby,
                'namastoksby' => $request->namastoksby,
                'gambarsby' => $request->gambarsby,
                'stok_idbtg' => $request->stok_idbtg,
                'namastokbtg' => $request->namastokbtg,
                'gambarbtg' => $request->gambarbtg,
                'cekKoneksi' => $request->cekKoneksi,
                'stok_idmdndel' => $request->stok_idmdndel,
                'stok_idjktdel' => $request->stok_idjktdel,
                'stok_idjkttnldel' => $request->stok_idjkttnldel,
                'stok_idmksdel' => $request->stok_idmksdel,
                'stok_idsbydel' => $request->stok_idsbydel,
                'stok_idbtgdel' => $request->stok_idbtgdel,
            ];
            $stokPusat = (new Stok())->processKonsolidasi($data);

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
     * @Keterangan APPROVAL AKTIF
     */
    public function approvalaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id
            ];
            (new Stok())->processApprovalaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
