<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Helpers\App;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Container;
use App\Models\Parameter;
use App\Models\UpahSupir;
use Illuminate\Http\Request;
use App\Models\StatusContainer;
use App\Models\UpahSupirRincian;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreUpahSupirRequest;
use App\Http\Requests\UpdateUpahSupirRequest;
use App\Http\Requests\DestroyUpahSupirRequest;
use App\Http\Requests\ApprovalUpahSupirRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Http\Requests\StoreUpahSupirRincianRequest;
use App\Http\Requests\UpdateUpahSupirRincianRequest;

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

    public function export()
    {
       
        $upahsupirrincian = new UpahSupirRincian();
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            $decodedResponse = json_decode($upahsupirrincian->listpivot(), true);
            $upahsupirArray = $decodedResponse;
            $judulLaporan = "LAPORAN UPAH SUPIR";
            $upahsupirArray[0]["judul"] = $getJudul->text;
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    "index"=>"dari",
                    "label"=>"dari",
                ],
                [
                    "index"=>"tujuan",
                    "label"=>"tujuan",
                ],
                [
                    "index"=>"penyesuaian",
                    "label"=>"penyesuaian",
                ],
                [
                    "index"=>"jarak",
                    "label"=>"jarak",
                ],
                // [
                //     "index"=>"upahsupir_id",
                //     "label"=>"upah supir",
                // ],
                [
                    "index"=>"20_EMPTY",
                    "label"=>"20_EMPTY",
                ],
                [
                    "index"=>"20_FULL",
                    "label"=>"20_FULL",
                ],
                [
                    "index"=>"20_FULL EMPTY",
                    "label"=>"20_FULL EMPTY",
                ],
                [
                    "index"=>"2X20_EMPTY",
                    "label"=>"2X20_EMPTY",
                ],
                [
                    "index"=>"2X20_FULL",
                    "label"=>"2X20_FULL",
                ],
                [
                    "index"=>"2X20_FULL EMPTY",
                    "label"=>"2X20_FULL EMPTY",
                ],
                [
                    "index"=>"40_EMPTY",
                    "label"=>"40_EMPTY",
                ],
                [
                    "index"=>"40_FULL",
                    "label"=>"40_FULL",
                ],
                [
                    "index"=>"40_FULL EMPTY",
                    "label"=>"40_FULL EMPTY",
                ],
                [
                    "index"=>"Liter_20_EMPTY",
                    "label"=>"Liter_20_EMPTY",
                ],
                [
                    "index"=>"Liter_20_FULL",
                    "label"=>"Liter_20_FULL",
                ],
                [
                    "index"=>"Liter_20_FULL EMPTY",
                    "label"=>"Liter_20_FULL EMPTY",
                ],
                [
                    "index"=>"Liter_2X20_EMPTY",
                    "label"=>"Liter_2X20_EMPTY",
                ],
                [
                    "index"=>"Liter_2X20_FULL",
                    "label"=>"Liter_2X20_FULL",
                ],
                [
                    "index"=>"Liter_2X20_FULL EMPTY",
                    "label"=>"Liter_2X20_FULL EMPTY",
                ],
                [
                    "index"=>"Liter_40_EMPTY",
                    "label"=>"Liter_40_EMPTY",
                ],
                [
                    "index"=>"Liter_40_FULL",
                    "label"=>"Liter_40_FULL",
                ],
                [
                    "index"=>"Liter_40_FULL EMPTY",
                    "label"=>"Liter_40_FULL EMPTY",
                ],
            ];
            $this->toExcel($judulLaporan, $upahsupirArray, $columns);
      
    }
    public function export2()
    {
        // $dari = date('Y-m-d', strtotime($request->dari));
        // $sampai = date('Y-m-d', strtotime($request->sampai));

        $upahsupirrincian = new UpahSupirRincian();

        // $cekData = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
        //     ->whereBetween('tglmulaiberlaku', [$dari, $sampai])
        //     ->first();

        // if ($cekData != null) {

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
                'data' => $upahsupirrincian->listpivot(),
                'judul' => $getJudul
            ]);
        // } else {
        //     return response([
        //         'errors' => [
        //             "export" => "tidak ada data"
        //         ],
        //         'message' => "The given data was invalid.",
        //     ], 422);
        // }
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreUpahSupirRequest $request): JsonResponse
    {
        DB::beginTransaction();
        $parameter = new Parameter();
        $tglsaldo = $parameter->cekText('SALDO', 'SALDO') ?? '1900-01-01';

        try {
            $data = [
                'kotadari' => $request->kotadari,
                'kotadari_id' => $request->kotadari_id,
                'parent_id' => $request->parent_id ?? 0,
                'parent' => $request->parent,
                'pelabuhan_id' => $request->pelabuhan_id ?? 1,
                'pelabuhan' => $request->pelabuhan,
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
                'tglmulaiberlaku' => date('Y-m-d', strtotime($tglsaldo)),

                // 'tglmulaiberlaku' => $request->tglmulaiberlaku,

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
                'statuslangsir' => $request->statuslangsir,
                'nominalsupir' => $request->nominalsupir,
                'nominalkenek' => $request->nominalkenek ?? 0,
                'nominalkomisi' => $request->nominalkomisi ?? 0,
                'nominaltol' =>  $request->nominaltol ?? 0,
                'liter' => $request->liter ?? 0,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'tas_id' => $request->tas_id
            ];
            if ($request->from != '') {
                $data['gambar'] = $request->gambar ?? [];
            } else {
                $data['gambar'] = $request->file('gambar') ?? [];
            }
            // $upahsupir = (new UpahSupir())->processStore($data);
            $upahsupir = new UpahSupir();
            $dataupahsupir=$upahsupir->processStore($data, $upahsupir);                
            if ($request->from == '') {
                $dataupahsupir->position = $this->getPosition($dataupahsupir, $dataupahsupir->getTable())->position;
                if ($request->limit == 0) {
                    $dataupahsupir->page = ceil($dataupahsupir->position / (10));
                } else {
                    $dataupahsupir->page = ceil($dataupahsupir->position / ($request->limit ?? 10));
                }
            }

            // $statusTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'POSTING TNL')->first();
            // if ($data['statuspostingtnl'] == $statusTnl->id) {
            //     $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
            //     $data['statuspostingtnl'] = $statusBukanTnl->id;

            //     (new UpahSupir())->postingTnl($data, $upahsupir->gambar);
            // }
            // $this->upahsupir = $upahsupir;

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $dataupahsupir->id;
            $data['detail_tas_id'] = $dataupahsupir->detailTasId;
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $dataupahsupirtnl=$this->SaveTnlMasterDetail('upahsupir', 'add', $data);
            } 
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $dataupahsupir
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
    public function update(UpdateUpahSupirRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kotadari_id' => $request->kotadari_id,
                'parent_id' => $request->parent_id ?? 0,
                'pelabuhan_id' => $request->pelabuhan_id ?? 1,
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

                // 'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'statusupahzona' => $request->statusupahzona,
                'zonadari_id' => $request->zonadari_id,
                'zonasampai_id' => $request->zonasampai_id,
                'statussimpankandang' => $request->statussimpankandang,
                'statusluarkota' => $request->statusluarkota,
                'keterangan' => $request->keterangan,
                'gambar' => $request->gambar ?? [],

                'container_id' => $request->container_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statuslangsir' => $request->statuslangsir,
                'nominalsupir' => $request->nominalsupir,
                'nominalkenek' => $request->nominalkenek ?? 0,
                'nominalkomisi' => $request->nominalkomisi ?? 0,
                'nominaltol' =>  $request->nominaltol ?? 0,
                'liter' => $request->liter ?? 0,
                'tas_id' => $request->tas_id

            ];
            // $upahsupir = (new UpahSupir())->processUpdate($upahsupir, $data);
            $upahsupir = new UpahSupir();
            $upahsupirs = $upahsupir->findOrFail($id);
            $dataupahsupir = $upahsupir->processUpdate($upahsupirs, $data);            
            // UpahSupirRincian::where('upahsupir_id', $id)->delete();

            if ($request->from == '') {

                $dataupahsupir->position = $this->getPosition($dataupahsupir, $dataupahsupir->getTable())->position;
                if ($request->limit == 0) {
                    $dataupahsupir->page = ceil($dataupahsupir->position / (10));
                } else {
                    $dataupahsupir->page = ceil($dataupahsupir->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();

            $data['tas_id'] = $dataupahsupir->id;
            $data['detail_tas_id'] = $dataupahsupir->detailTasId;
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {

                $dataupahsupirtnl=$this->SaveTnlMasterDetail('upahsupir', 'edit', $data);
            } 

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $dataupahsupir
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
            // $upahsupir = (new upahsupir())->processDestroy($id);
            $upahsupir = new UpahSupir();
            $upahsupirs = $upahsupir->findOrFail($id);
            $upahsupir = $upahsupir->processDestroy($upahsupirs); 

            if ($request->from == '') {

            $selected = $this->getPosition($upahsupir, $upahsupir->getTable(), true);
            $upahsupir->position = $selected->position;
            $upahsupir->id = $selected->id;
            if ($request->limit == 0) {
                $upahsupir->page = ceil($upahsupir->position / (10));
            } else {
                $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));
            }
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
                    'kolom1' => $sheet->getCell($this->kolomexcel(5)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(6)  . $row)->getValue(),
                    'kolom3' => $sheet->getCell($this->kolomexcel(7)  . $row)->getValue(),
                    'kolom4' => $sheet->getCell($this->kolomexcel(8)  . $row)->getValue(),
                    'kolom5' => $sheet->getCell($this->kolomexcel(9)  . $row)->getValue(),
                    'kolom6' => $sheet->getCell($this->kolomexcel(10)  . $row)->getValue(),
                    'kolom7' => $sheet->getCell($this->kolomexcel(11)  . $row)->getValue(),
                    'kolom8' => $sheet->getCell($this->kolomexcel(12)  . $row)->getValue(),
                    'kolom9' => $sheet->getCell($this->kolomexcel(13)  . $row)->getValue(),
                    'liter1' => $sheet->getCell($this->kolomexcel(14)  . $row)->getValue(),
                    'liter2' => $sheet->getCell($this->kolomexcel(15)  . $row)->getValue(),
                    'liter3' => $sheet->getCell($this->kolomexcel(16)  . $row)->getValue(),
                    'liter4' => $sheet->getCell($this->kolomexcel(17)  . $row)->getValue(),
                    'liter5' => $sheet->getCell($this->kolomexcel(18)  . $row)->getValue(),
                    'liter6' => $sheet->getCell($this->kolomexcel(19)  . $row)->getValue(),
                    'liter7' => $sheet->getCell($this->kolomexcel(20)  . $row)->getValue(),
                    'liter8' => $sheet->getCell($this->kolomexcel(21)  . $row)->getValue(),
                    'liter9' => $sheet->getCell($this->kolomexcel(22)  . $row)->getValue(),
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
        $dataMaster = $upahSupir->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $cekdata = $upahSupir->cekValidasi($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
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
            goto selesai;
            // return response($data);
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('upahSupir', $id, $aksi);
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
                $keterror = 'Data tujuan <b>' . $dataMaster->tujuan . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];
                
                return response($data);
            }
        } else {

            (new MyModel())->updateEditingBy('upahSupir', $id, $aksi);

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
        selesai:
        return response($data);
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
    /**
     * @ClassName 
     * @Keterangan APRROVAL AKTIF
     */
    public function approvalaktif(ApprovalUpahSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new UpahSupir())->processApprovalaktif($data);

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
