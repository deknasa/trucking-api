<?php

namespace App\Http\Controllers\Api;

use App\Models\Tarif;
use App\Models\TarifRincian;
use App\Http\Requests\StoreTarifRequest;
use App\Http\Requests\StoreTarifRincianRequest;
use App\Http\Requests\UpdateTarifRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Container;
use App\Models\Kota;
use App\Models\Zona;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyTarifRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Requests\GetUpahSupirRangeRequest;
use Illuminate\Http\JsonResponse;


class TarifController extends Controller
{
    /**
     * @ClassName 
     * Tarif
     * @Detail1 TarifRincianController
     */
    public function index()
    {

        $tarif = new Tarif();

        return response([
            'data' => $tarif->get(),
            'attributes' => [
                'totalRows' => $tarif->totalRows,
                'totalPages' => $tarif->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $tarif = new Tarif();
        $cekdata = $tarif->cekvalidasihapus($id);
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

        $tarif = new Tarif();
        $tarifrincian = new TarifRincian();

        return response([
            'status' => true,
            'data' => $tarif->default(),
            'detail' => $tarifrincian->getAll(0),
        ]);
    }

    public function listpivot(GetUpahSupirRangeRequest $request)
    {

        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $tarifrincian = new TarifRincian();

        $cekData = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
            ->whereBetween('tglmulaiberlaku', [$dari, $sampai])
            ->first();

        if ($cekData != null) {

            $tarifrincian = new TarifRincian();

            return response([
                'status' => true,
                'data' => $tarifrincian->listpivot($dari, $sampai)
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
    public function store(StoreTarifRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'parent_id' => $request->parent_id ?? '',
                'upahsupir_id' => $request->upahsupir_id ?? '',
                'tujuan' => $request->tujuan,
                'penyesuaian' => $request->penyesuaian,
                'statusaktif' => $request->statusaktif,
                'statussistemton' => $request->statussistemton,
                'kota_id' => $request->kota_id,
                'zona_id' => $request->zona_id ?? '',
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),
                'statuspenyesuaianharga' => $request->statuspenyesuaianharga,
                'keterangan' => $request->keterangan,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
                'detail_id' => $request->detail_id
            ];

            $tarif = (new Tarif())->processStore($data);
            $tarif->position = $this->getPosition($tarif, $tarif->getTable())->position;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $tarif
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = Tarif::findAll($id);
        $detail = TarifRincian::getAll($id);

        // dump($data);
        // dd($detail);


        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateTarifRequest $request, Tarif $tarif): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'parent_id' => $request->parent_id ?? '',
                'upahsupir_id' => $request->upahsupir_id ?? '',
                'tujuan' => $request->tujuan,
                'penyesuaian' => $request->penyesuaian,
                'statusaktif' => $request->statusaktif,
                'statussistemton' => $request->statussistemton,
                'kota_id' => $request->kota_id,
                'zona_id' => $request->zona_id ?? '',
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),
                'statuspenyesuaianharga' => $request->statuspenyesuaianharga,
                'keterangan' => $request->keterangan,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
                'detail_id' => $request->detail_id
            ];
            $tarif = (new Tarif())->processUpdate($tarif, $data);
            $tarif->position = $this->getPosition($tarif, $tarif->getTable())->position;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $tarif
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName
     */
    public function destroy(DestroyTarifRequest $request, $id)
    {

        DB::beginTransaction();

        try {
            $tarif = (new Tarif())->processDestroy($id);
            $selected = $this->getPosition($tarif, $tarif->getTable(), true);
            $tarif->position = $selected->position;
            $tarif->id = $selected->id;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $tarif
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('tarif')->getColumns();

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
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'statuspenyesuaianharga' => Parameter::where(['grp' => 'status penyesuaian harga'])->get(),
            'statussistemton' => Parameter::where(['grp' => 'sistem ton'])->get(),
        ];

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
                    'tujuan' => $sheet->getCell($this->kolomexcel(1) . $row)->getValue(),
                    'penyesuaian' => $sheet->getCell($this->kolomexcel(2) . $row)->getValue(),
                    'tglmulaiberlaku' => date('Y-m-d', strtotime($sheet->getCell($this->kolomexcel(3) . $row)->getFormattedValue())),
                    'kota' => $sheet->getCell($this->kolomexcel(4) . $row)->getValue(),
                    'kolom1' => $sheet->getCell($this->kolomexcel(5)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(6)  . $row)->getValue(),
                    'kolom3' => $sheet->getCell($this->kolomexcel(7)  . $row)->getValue(),
                    'modifiedby' => auth('api')->user()->name
                ];


                $startcount++;
            }

            $tarifrincian = new TarifRincian();

            $cekdata = $tarifrincian->cekupdateharga($data);


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
                    'data' => $tarifrincian->updateharga($data),
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

    /**
     * @ClassName 
     */
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $tarifs = $decodedResponse['data'];

        $i = 0;
        foreach ($tarifs as $index => $params) {

            // $tarifRincian = new TarifRincian();

            $statusaktif = $params['statusaktif'];
            $statusSistemTon = $params['statussistemton'];
            $statusPenyesuaianHarga = $params['statuspenyesuaianharga'];

            $result = json_decode($statusaktif, true);
            $resultSistemTon = json_decode($statusSistemTon, true);
            $resultPenyesuaianHarga = json_decode($statusPenyesuaianHarga, true);

            $statusaktif = $result['MEMO'];
            $statusSistemTon = $resultSistemTon['MEMO'];
            $statusPenyesuaianHarga = $resultPenyesuaianHarga['MEMO'];


            $tarifs[$i]['statusaktif'] = $statusaktif;
            $tarifs[$i]['statussistemton'] = $statusSistemTon;
            $tarifs[$i]['statuspenyesuaianharga'] = $statusPenyesuaianHarga;

            // $tarifs[$i]['rincian'] = json_decode($tarifRincian->getAll($tarifs[$i]['id']), true);


            $i++;
        }




        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Parent',
                'index' => 'parent_id',
            ],
            [
                'label' => 'Upah Supir',
                'index' => 'upahsupir_id',
            ],
            [
                'label' => 'Tujuan',
                'index' => 'tujuan',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
            [
                'label' => 'Status Sistem Ton',
                'index' => 'statussistemton',
            ],
            [
                'label' => 'Kota',
                'index' => 'kota_id',
            ],
            [
                'label' => 'Zona',
                'index' => 'zona_id',
            ],
            [
                'label' => 'Tgl Mulai Berlaku',
                'index' => 'tglmulaiberlaku',
            ],
            [
                'label' => 'Status Penyesuaian Harga',
                'index' => 'statuspenyesuaianharga',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],

        ];

        $this->toExcel('Tarif', $tarifs, $columns);
    }
}
