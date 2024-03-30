<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahSupir;
use App\Models\UpahSupirRincian;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Http\Requests\StoreUpahSupirRequest;
use App\Http\Requests\UpdateUpahSupirRequest;
use App\Http\Requests\StoreUpahSupirRincianRequest;
use App\Http\Requests\UpdateUpahSupirRincianRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalUpahSupirRequest;

use App\Helpers\App;
use App\Http\Requests\DestroyUpahSupirRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpahSupirController extends Controller
{
    private $upahsupir;
    /**
     * @ClassName 
     * UpahSupir
     * @Detail UpahSupirRincianController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $upahsupir = new UpahSupir();

        return response([
            'data' => $upahsupir->get(),
            'attributes' => [
                'totalRows' => $upahsupir->totalRows,
                'totalPages' => $upahsupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */

    public function export(GetUpahSupirRangeRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $upahsupirrincian = new UpahSupirRincian();

        $cekData = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
            ->whereBetween('tglmulaiberlaku', [$dari, $sampai])
            ->first();

        if ($cekData != null) {

            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select(
                    'text',
                    DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
                )
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

            return response([
                'status' => true,
                'data' => $upahsupirrincian->listpivot($dari, $sampai),
                'judul' => $getJudul
            ]);
        } else {
            return response([
                'errors' => [
                    "export" => "tidak ada data"
                ],
                'message' => "The given data was invalid.",
            ], 422);
        }
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreUpahSupirRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kotadari' => $request->kotadari,
                'kotadari_id' => $request->kotadari_id,
                'parent_id' => $request->parent_id ?? 0,
                'parent' => $request->parent,
                'tarif_id' => $request->tarif_id ?? 0,
                'tarifmuatan_id' => $request->tarifmuatan_id ?? 0,
                'tarifbongkaran_id' => $request->tarifbongkaran_id ?? 0,
                'tarifimport_id' => $request->tarifimport_id ?? 0,
                'tarifexport_id' => $request->tarifexport_id ?? 0,
                'tarif' => $request->tarif,
                'kotasampai' => $request->kotasampai,
                'kotasampai_id' => $request->kotasampai_id,
                'penyesuaian' => $request->penyesuaian,
                'jarak' => $request->jarak,
                'jarakfullempty' => $request->jarakfullempty,
                'zona' => $request->zona,
                'zona_id' => ($request->zona_id == null) ? 0 : $request->zona_id ?? 0,
                'statusaktif' => $request->statusaktif,

                'tglmulaiberlaku' => $request->tglmulaiberlaku,

                'statusupahzona' => $request->statusupahzona,
                'statuspostingtnl' => $request->statuspostingtnl,
                'zonadari_id' => $request->zonadari_id,
                'zonasampai_id' => $request->zonasampai_id,
                'statussimpankandang' => $request->statussimpankandang,
                'statusluarkota' => $request->statusluarkota,
                'keterangan' => $request->keterangan,
                'from' => $request->from ?? '',

                'container_id' => $request->container_id,
                'container' => $request->container,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statuscontainer' => $request->statuscontainer,
                'nominalsupir' => $request->nominalsupir,
                'nominalkenek' => $request->nominalkenek ?? 0,
                'nominalkomisi' => $request->nominalkomisi ?? 0,
                'nominaltol' =>  $request->nominaltol ?? 0,
                'liter' => $request->liter ?? 0,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',

            ];
            if ($request->from != '') {
                $data['gambar'] = $request->gambar ?? [];
            } else {
                $data['gambar'] = $request->file('gambar') ?? [];
            }
            $upahsupir = (new UpahSupir())->processStore($data);
            if ($request->from == '') {
                $upahsupir->position = $this->getPosition($upahsupir, $upahsupir->getTable())->position;
                if ($request->limit == 0) {
                    $upahsupir->page = ceil($upahsupir->position / (10));
                } else {
                    $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));
                }
            }

            $statusTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'POSTING TNL')->first();
            if ($data['statuspostingtnl'] == $statusTnl->id) {
                $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
                $data['statuspostingtnl'] = $statusBukanTnl->id;

                (new UpahSupir())->postingTnl($data, $upahsupir->gambar);
            }
            $this->upahsupir = $upahsupir;
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = upahSupir::findAll($id);
        $detail = UpahSupirRincian::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateUpahSupirRequest $request, UpahSupir $upahsupir): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kotadari_id' => $request->kotadari_id,
                'parent_id' => $request->parent_id ?? 0,
                'tarif_id' => $request->tarif_id ?? 0,
                'tarifmuatan_id' => $request->tarifmuatan_id ?? 0,
                'tarifbongkaran_id' => $request->tarifbongkaran_id ?? 0,
                'tarifimport_id' => $request->tarifimport_id ?? 0,
                'tarifexport_id' => $request->tarifexport_id ?? 0,
                'kotasampai_id' => $request->kotasampai_id,
                'penyesuaian' => $request->penyesuaian,
                'jarak' => $request->jarak,
                'jarakfullempty' => $request->jarakfullempty,
                'zona_id' => ($request->zona_id == null) ? 0 : $request->zona_id ?? 0,
                'statusaktif' => $request->statusaktif,

                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'statusupahzona' => $request->statusupahzona,
                'zonadari_id' => $request->zonadari_id,
                'zonasampai_id' => $request->zonasampai_id,
                'statussimpankandang' => $request->statussimpankandang,
                'statusluarkota' => $request->statusluarkota,
                'keterangan' => $request->keterangan,
                'gambar' => $request->gambar ?? [],

                'container_id' => $request->container_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'nominalsupir' => $request->nominalsupir,
                'nominalkenek' => $request->nominalkenek ?? 0,
                'nominalkomisi' => $request->nominalkomisi ?? 0,
                'nominaltol' =>  $request->nominaltol ?? 0,
                'liter' => $request->liter ?? 0,

            ];
            $upahsupir = (new UpahSupir())->processUpdate($upahsupir, $data);
            $upahsupir->position = $this->getPosition($upahsupir, $upahsupir->getTable())->position;
            if ($request->limit == 0) {
                $upahsupir->page = ceil($upahsupir->position / (10));
            } else {
                $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
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
    public function destroy(DestroyUpahSupirRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $upahsupir = (new upahsupir())->processDestroy($id);
            $selected = $this->getPosition($upahsupir, $upahsupir->getTable(), true);
            $upahsupir->position = $selected->position;
            $upahsupir->id = $selected->id;
            if ($request->limit == 0) {
                $upahsupir->page = ceil($upahsupir->position / (10));
            } else {
                $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $upahsupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function default()
    {

        $UpahSupir = new UpahSupir();
        return response([
            'status' => true,
            'data' => $UpahSupir->default(),
        ]);
    }


    public function combo(Request $request)
    {
        $data = [
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'container' => Container::all(),
            'statuscontainer' => StatusContainer::all(),
            'statusaktif' => Parameter::where('grp', 'STATUS AKTIF')->get(),
            'statusluarkota' => Parameter::where('grp', 'UPAH SUPIR LUAR KOTA')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('upahsupir')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan IMPORT DATA DARI KE EXCEL  KE SYSTEM
     */
    public function import(Request $request)
    {
        $request->validate(
            [
                'fileImport' => 'required|file|mimes:xls,xlsx'
            ],
            [
                'fileImport.mimes' => 'file import ' . app(ErrorController::class)->geterror('FXLS')->keterangan,
            ]
        );

        $the_file = $request->file('fileImport');
        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range    = range(4, $row_limit);
            $column_range = range('A', $column_limit);
            $startcount = 4;
            $data = array();
            $a = 0;
            foreach ($row_range as $row) {

                $data[] = [
                    'kotadari' => $sheet->getCell($this->kolomexcel(1) . $row)->getValue(),
                    'kotasampai' => $sheet->getCell($this->kolomexcel(2) . $row)->getValue(),
                    'penyesuaian' => $sheet->getCell($this->kolomexcel(3) . $row)->getValue(),
                    'jarak' => $sheet->getCell($this->kolomexcel(4) . $row)->getValue(),
                    'tglmulaiberlaku' => date('Y-m-d', strtotime($sheet->getCell($this->kolomexcel(5) . $row)->getFormattedValue())),
                    'kolom1' => $sheet->getCell($this->kolomexcel(6)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(7)  . $row)->getValue(),
                    'kolom3' => $sheet->getCell($this->kolomexcel(8)  . $row)->getValue(),
                    'kolom4' => $sheet->getCell($this->kolomexcel(9)  . $row)->getValue(),
                    'kolom5' => $sheet->getCell($this->kolomexcel(10)  . $row)->getValue(),
                    'kolom6' => $sheet->getCell($this->kolomexcel(11)  . $row)->getValue(),
                    'kolom7' => $sheet->getCell($this->kolomexcel(12)  . $row)->getValue(),
                    'kolom8' => $sheet->getCell($this->kolomexcel(13)  . $row)->getValue(),
                    'kolom9' => $sheet->getCell($this->kolomexcel(14)  . $row)->getValue(),
                    'liter1' => $sheet->getCell($this->kolomexcel(15)  . $row)->getValue(),
                    'liter2' => $sheet->getCell($this->kolomexcel(16)  . $row)->getValue(),
                    'liter3' => $sheet->getCell($this->kolomexcel(17)  . $row)->getValue(),
                    'liter4' => $sheet->getCell($this->kolomexcel(18)  . $row)->getValue(),
                    'liter5' => $sheet->getCell($this->kolomexcel(19)  . $row)->getValue(),
                    'liter6' => $sheet->getCell($this->kolomexcel(20)  . $row)->getValue(),
                    'liter7' => $sheet->getCell($this->kolomexcel(21)  . $row)->getValue(),
                    'liter8' => $sheet->getCell($this->kolomexcel(22)  . $row)->getValue(),
                    'liter9' => $sheet->getCell($this->kolomexcel(23)  . $row)->getValue(),
                    'modifiedby' => auth('api')->user()->name
                ];


                $startcount++;
            }
            $upahSupirRincian = new UpahSupirRincian();
            $cekdata = $upahSupirRincian->cekupdateharga($data);
            if ($cekdata == true) {
                $query = DB::table('error')
                    ->select('keterangan')
                    ->where('kodeerror', '=', 'SPI')
                    ->get();
                $keterangan = $query['0'];

                $data = [
                    'message' => $keterangan,
                    'errors' => '',
                    'kondisi' => $cekdata
                ];

                return response($data);
            } else {
                return response([
                    'status' => true,
                    'keterangan' => 'harga berhasil di update',
                    'data' => $upahSupirRincian->updateharga($data),
                    'kondisi' => $cekdata
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function kolomexcel($kolom)
    {
        if ($kolom >= 27 and $kolom <= 52) {
            $hasil = 'A' . chr(38 + $kolom);
        } else {
            $hasil = chr(64 + $kolom);
        }
        return $hasil;
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

    private function deleteFiles(UpahSupir $upahsupir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];
        dd('here');;
        $relatedPhotoUpahSupir = [];
        $photoUpahSupir = json_decode($upahsupir->gambar, true);
        if ($photoUpahSupir) {
            foreach ($photoUpahSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoUpahSupir[] = "upahsupir/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoUpahSupir);
        }
    }

    public function getImage(string $filename, string $type)
    {
        if (Storage::exists("upahsupir/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/upahsupir/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("upahsupir/$filename")) {
                return response()->file(storage_path("app/upahsupir/$filename"));
            } else {
                if ($type == 'small') {
                    return response()->file(storage_path("app/no-image.jpg"));
                } else {
                    return response('no-image');
                }
            }
        }
    }
    // public function export()
    // {
    //     $response = $this->index();
    //     $decodedResponse = json_decode($response->content(), true);
    //     $upahSupirs = $decodedResponse['data'];

    //     // dd($upahSupirs);


    //     $i = 0;
    //     foreach ($tarifs as $index => $params) {

    //         $statusaktif = $params['statusaktif'];
    //         $statusSistemTon = $params['statussistemton'];
    //         $statusPenyesuaianHarga = $params['statuspenyesuaianharga'];

    //         $result = json_decode($statusaktif, true);
    //         $resultSistemTon = json_decode($statusSistemTon, true);
    //         $resultPenyesuaianHarga = json_decode($statusPenyesuaianHarga, true);

    //         $statusaktif = $result['MEMO'];
    //         $statusSistemTon = $resultSistemTon['MEMO'];
    //         $statusPenyesuaianHarga = $resultPenyesuaianHarga['MEMO'];


    //         $tarifs[$i]['statusaktif'] = $statusaktif;
    //         $tarifs[$i]['statussistemton'] = $statusSistemTon;
    //         $tarifs[$i]['statuspenyesuaianharga'] = $statusPenyesuaianHarga;


    //         $i++;
    //     }

    //     $columns = [
    //         [
    //             'label' => 'No',
    //         ],
    //         [
    //             'label' => 'Parent',
    //             'index' => 'parent_id',
    //         ],
    //         [
    //             'label' => 'Upah Supir',
    //             'index' => 'upahsupir_id',
    //         ],
    //         [
    //             'label' => 'Tujuan',
    //             'index' => 'tujuan',
    //         ],
    //         [
    //             'label' => 'Status Aktif',
    //             'index' => 'statusaktif',
    //         ],
    //         [
    //             'label' => 'Status Sistem Ton',
    //             'index' => 'statussistemton',
    //         ],
    //         [
    //             'label' => 'Kota',
    //             'index' => 'kota_id',
    //         ],
    //         [
    //             'label' => 'Zona',
    //             'index' => 'zona_id',
    //         ],
    //         [
    //             'label' => 'Tgl Mulai Berlaku',
    //             'index' => 'tglmulaiberlaku',
    //         ],
    //         [
    //             'label' => 'Status Penyesuaian Harga',
    //             'index' => 'statuspenyesuaianharga',
    //         ],
    //         [
    //             'label' => 'Keterangan',
    //             'index' => 'keterangan',
    //         ],
    //     ];

    //     $this->toExcel('Tarif', $tarifs, $columns);
    // }

    public function cekValidasi($id)
    {
        $upahSupir = new UpahSupir();
        $cekdata = $upahSupir->cekValidasi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
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

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalUpahSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new UpahSupir())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function getRincian(Request $request)
    {

        $statuskandang = $request->statuskandang ?? '';
        $upah_id = $request->upah_id ?? '';
        $container_id = $request->container_id ?? '';
        $statuscontainer_id = $request->statuscontainer_id ?? '';

        return response([
            'data' => (new UpahSupir())->getRincian($statuskandang, $upah_id, $container_id, $statuscontainer_id)
        ]);
    }
}
