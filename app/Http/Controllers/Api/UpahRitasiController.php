<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Container;
use App\Models\Parameter;
use App\Models\UpahRitasi;
use Illuminate\Http\Request;
use App\Models\StatusContainer;
use App\Models\UpahRitasiRincian;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreUpahRitasiRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\UpdateUpahRitasiRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;

class UpahRitasiController extends Controller
{
    /**
     * @ClassName 
     * UpahRitasi
     * @Detail UpahRitasiRincianController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $upahritasi = new UpahRitasi();

        return response([
            'data' => $upahritasi->get(),
            'attributes' => [
                'totalRows' => $upahritasi->totalRows,
                'totalPages' => $upahritasi->totalPages
            ]
        ]);
    }

    public function default()
    {
        $upahRitasi = new UpahRitasi();
        return response([
            'status' => true,
            'data' => $upahRitasi->default()
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

        $cekData = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))
            ->whereBetween('tglmulaiberlaku', [$dari, $sampai])
            ->first();

        if ($cekData != null) {

            $upahritasirincian = new UpahRitasiRincian();

            return response([
                'status' => true,
                'data' => $upahritasirincian->listpivot($dari, $sampai)
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
    public function store(StoreUpahRitasiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'parent_id' => $request->parent_id ?? 0,
                'kotadari_id' => $request->kotadari_id,
                'kotasampai_id' => $request->kotasampai_id,
                'jarak' => $request->jarak,
                'nominalsupir' => $request->nominalsupir,
                'statusaktif' => $request->statusaktif,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'container_id' => $request->container_id,
                'liter' => $request->liter ?? 0,
            ];
            $upahritasi = (new upahritasi())->processStore($data);
            $upahritasi->position = $this->getPosition($upahritasi, $upahritasi->getTable())->position;
            if ($request->limit == 0) {
                $upahritasi->page = ceil($upahritasi->position / (10));
            } else {
                $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = UpahRitasi::findAll($id);
        $detail = UpahRitasiRincian::getAll($id);

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
    public function update(UpdateUpahRitasiRequest $request, UpahRitasi $upahritasi): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'parent_id' => $request->parent_id ?? 0,
                'kotadari_id' => $request->kotadari_id,
                'kotasampai_id' => $request->kotasampai_id,
                'jarak' => $request->jarak,
                'nominalsupir' => $request->nominalsupir,
                'statusaktif' => $request->statusaktif,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'container_id' => $request->container_id,
                'liter' => $request->liter ?? 0,
            ];

            $upahritasi = (new UpahRitasi())->processUpdate($upahritasi, $data);
            $upahritasi->position = $this->getPosition($upahritasi, $upahritasi->getTable())->position;
            if ($request->limit == 0) {
                $upahritasi->page = ceil($upahritasi->position / (10));
            } else {
                $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
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
            $upahritasi = (new UpahRitasi())->processDestroy($id);
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable(), true);
            $upahritasi->position = $selected->position;
            $upahritasi->id = $selected->id;
            if ($request->limit == 0) {
                $upahritasi->page = ceil($upahritasi->position / (10));
            } else {
                $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $upahritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function triplookup()
    {

        $upahritasi = new UpahRitasi();

        return response([
            'data' => $upahritasi->triplookup(),
            'attributes' => [
                'totalRows' => $upahritasi->totalRows,
                'totalPages' => $upahritasi->totalPages
            ]
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
            'statusluarkota' => Parameter::where('grp', 'STATUS LUAR KOTA')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function comboLuarKota(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('upahritasi')->getColumns();

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
                    'jarak' => $sheet->getCell($this->kolomexcel(3) . $row)->getValue(),
                    'tglmulaiberlaku' => date('Y-m-d', strtotime($sheet->getCell($this->kolomexcel(4) . $row)->getFormattedValue())),
                    'nominalsupir' => $sheet->getCell($this->kolomexcel(5)  . $row)->getValue(),
                    'kolom1' => $sheet->getCell($this->kolomexcel(6)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(7)  . $row)->getValue(),
                    'kolom3' => $sheet->getCell($this->kolomexcel(8)  . $row)->getValue(),
                    'modifiedby' => auth('api')->user()->name
                ];


                $startcount++;
            }

            $upahRitasiRincian = new UpahRitasiRincian();
            $cekdata = $upahRitasiRincian->cekupdateharga($data);

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
                    'data' => $upahRitasiRincian->updateharga($data),
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

    /**
     * @ClassName 
     */
    public function report()
    {
    }


    public function cekValidasi($id)
    {
        $upahRitasi = new UpahRitasi();
        $dataMaster = $upahRitasi->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        
        $cekdata = $upahRitasi->cekValidasi($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' =>  $query->keterangan,
                'statuspesan' => 'warning',
            ];
            goto selesai;
            // return response($data);
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('upahritasi', $id, $aksi);
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

            (new MyModel())->updateEditingBy('upahritasi', $id, $aksi);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
        selesai:
        return response($data);
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
            (new UpahRitasi())->processApprovalnonaktif($data);

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
    public function approvalaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new UpahRitasi())->processApprovalaktif($data);

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
