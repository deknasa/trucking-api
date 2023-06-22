<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Http\Requests\StoreUpahRitasiRequest;
use App\Http\Requests\UpdateUpahRitasiRequest;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;
use App\Http\Requests\StoreLogTrailRequest;
use Illuminate\Http\JsonResponse;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpahRitasiController extends Controller
{
   /**
     * @ClassName 
     * UpahRitasi
     * @Detail1 UpahRitasiRincianController
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

    public function listpivot(GetUpahSupirRangeRequest $request)
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
                'statusaktif' => $request->statusaktif,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'container_id' => $request->container_id,
                'nominalsupir' => $request->nominalsupir,
                'liter' => $request->liter ?? 0,
            ];
            $upahritasi = (new upahritasi())->processStore($data);
            $upahritasi->position = $this->getPosition($upahritasi, $upahritasi->getTable())->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($upahritasi->upahritasiRincian());
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
                'statusaktif' => $request->statusaktif,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'container_id' => $request->container_id,
                'nominalsupir' => $request->nominalsupir,
                'liter' => $request->liter ?? 0,
            ];

            $upahritasi = (new UpahRitasi())->processUpdate($upahritasi, $data);
            $upahritasi->position = $this->getPosition($upahritasi, $upahritasi->getTable())->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

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
     */
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();


        try {
            $upahritasi = (new UpahRitasi())->processDestroy($id);
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable(), true);
            $upahritasi->position = $selected->position;
            $upahritasi->id = $selected->id;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

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
                    'kolom1' => $sheet->getCell($this->kolomexcel(5)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(6)  . $row)->getValue(),
                    'kolom3' => $sheet->getCell($this->kolomexcel(7)  . $row)->getValue(),
                    'liter1' => $sheet->getCell($this->kolomexcel(8)  . $row)->getValue(),
                    'liter2' => $sheet->getCell($this->kolomexcel(9)  . $row)->getValue(),
                    'liter3' => $sheet->getCell($this->kolomexcel(10)  . $row)->getValue(),
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
}
