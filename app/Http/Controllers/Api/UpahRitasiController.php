<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function listpivot(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $cekData = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))
        ->whereBetween('tglmulaiberlaku', [$dari, $sampai])
        ->first();

        if($cekData != null){

            $upahritasirincian = new UpahRitasiRincian();

            return response([
                'status' => true,
                'data' => $upahritasirincian->listpivot($dari, $sampai)
            ]);
        }else{
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
    public function store(StoreUpahRitasiRequest $request)
    {
        DB::beginTransaction();

        try {
            $upahritasi = new UpahRitasi();

            $upahritasi->parent_id = $request->parent_id ?? 0;
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = $request->jarak;
            $upahritasi->statusaktif = $request->statusaktif;
            $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));

            $upahritasi->modifiedby = auth('api')->user()->name;

            if ($upahritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'ENTRY UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => $upahritasi->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahritasi->modifiedby,
                    ];
                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }


                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY UPAH RITASI RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $upahritasi->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable());
            $upahritasi->position = $selected->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

            return response([
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
    public function update(UpdateUpahRitasiRequest $request, UpahRitasi $upahritasi)
    {
        DB::beginTransaction();

        try {
            $upahritasi->parent_id = $request->parent_id ?? 0;
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = $request->jarak;
            $upahritasi->statusaktif = $request->statusaktif;
            $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));

            $upahritasi->modifiedby = auth('api')->user()->name;

            if ($upahritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'EDIT UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => $upahritasi->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                UpahRitasiRincian::where('upahritasi_id', $upahritasi->id)->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahritasi->modifiedby,
                    ];

                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }

                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT UPAH RITASI RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $upahritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $upahritasi->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable());
            $upahritasi->position = $selected->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

            return response([
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

        $getDetail = UpahRitasiRincian::where('upahritasi_id', $id)->get();
        $upahRitasi = new UpahRitasi();
        $upahRitasi = $upahRitasi->lockAndDestroy($id);
        if ($upahRitasi) {
            $logTrail = [
                'namatabel' => strtoupper($upahRitasi->getTable()),
                'postingdari' => 'DELETE UPAH RITASI',
                'idtrans' => $upahRitasi->id,
                'nobuktitrans' => $upahRitasi->id,
                'aksi' => 'DELETE',
                'datajson' => $upahRitasi->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE UPAH RITASI RINCIAN
            $logTrailUpahRitasiRincian = [
                'namatabel' => 'UPAHRITASIRINCIAN',
                'postingdari' => 'DELETE UPAH RITASI RINCIAN',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $upahRitasi->id,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailUpahRitasiRincian = new StoreLogTrailRequest($logTrailUpahRitasiRincian);
            app(LogTrailController::class)->store($validatedLogTrailUpahRitasiRincian);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($upahRitasi, $upahRitasi->getTable(), true);
            $upahRitasi->position = $selected->position;
            $upahRitasi->id = $selected->id;
            $upahRitasi->page = ceil($upahRitasi->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $upahRitasi
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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
            $row_range    = range(2, $row_limit);
            $column_range = range('A', $column_limit);
            $startcount = 2;
            $data = array();
            
            $a=0;
            foreach ($row_range as $row) {
                $data[] = [
                    'kotadari' => $sheet->getCell('A' . $row)->getValue(),
                    'kotasampai' => $sheet->getCell('B' . $row)->getValue(),
                    'jarak' => $sheet->getCell('C' . $row)->getValue(),
                    'tglmulaiberlaku' => date('Y-m-d',strtotime($sheet->getCell($this->kolomexcel(4) . $row)->getFormattedValue())),
                    'kolom1' => $sheet->getCell($this->kolomexcel(5)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(6)  . $row)->getValue(),
                    'liter1' => $sheet->getCell($this->kolomexcel(7)  . $row)->getValue(),
                    'liter2' => $sheet->getCell($this->kolomexcel(8)  . $row)->getValue(),
                    'modifiedby' => auth('api')->user()->name
                ];
                $startcount++;
            }
 
            $upahRitasiRincian = new UpahRitasiRincian();

            return response([
                'status' => true,
                'data' => $upahRitasiRincian->updateharga($data),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    
    private function kolomexcel($kolom)
    {
        if ($kolom>=27 and $kolom<=52) {
            $hasil='A'.chr(38+$kolom);
        } else  {
            $hasil=chr(64+$kolom);
        }
        return $hasil;
    }
}
