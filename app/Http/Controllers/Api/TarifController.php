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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TarifController extends Controller
{
    /**
     * @ClassName 
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
    public function cekValidasi($id) {
        $tarif= new Tarif();
        $cekdata=$tarif->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
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

    public function listpivot()
    {

        $tarifrincian = new TarifRincian();

        return response([
            'status' => true,
            'data' => $tarifrincian->listpivot(),
        ]);
    }


    /**
     * @ClassName 
     */
    public function store(StoreTarifRequest $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            $tarif = new Tarif();
            $tarif->parent_id = $request->parent_id ?? '';
            $tarif->upahsupir_id = $request->upahsupir_id ?? '';
            $tarif->tujuan = $request->tujuan;
            $tarif->statusaktif = $request->statusaktif;
            $tarif->statussistemton = $request->statussistemton;
            $tarif->kota_id = $request->kota_id;
            $tarif->zona_id = $request->zona_id ?? '';
            $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $tarif->statuspenyesuaianharga = $request->statuspenyesuaianharga;
            $tarif->keterangan = $request->keterangan;
            $tarif->modifiedby = auth('api')->user()->name;

            if ($tarif->save()) {

              
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'ENTRY TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];
               
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
                // dd(count($request->container_id));
                $detaillog = [];
                for ($i = 0; $i < count($request->container_id); $i++) {

                    $datadetail = [
                        'tarif_id' => $tarif->id,
                        'container_id' => $request->container_id[$i],
                        'nominal' => $request->nominal[$i],
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreTarifRincianRequest($datadetail);
                    $datadetails = app(TarifRincianController::class)->store($data);
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
                    'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($tarif, $tarif->getTable());
            $tarif->position = $selected->position;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            return response([
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
    public function update(UpdateTarifRequest $request, Tarif $tarif)
    {
        DB::beginTransaction();

        try {
            $tarif->parent_id = $request->parent_id ?? '';
            $tarif->upahsupir_id = $request->upahsupir_id ?? '';
            $tarif->tujuan = $request->tujuan;
            $tarif->statusaktif = $request->statusaktif;
            $tarif->statussistemton = $request->statussistemton;
            $tarif->kota_id = $request->kota_id;
            $tarif->zona_id = $request->zona_id ?? '';
            $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $tarif->statuspenyesuaianharga = $request->statuspenyesuaianharga;
            $tarif->keterangan = $request->keterangan;
            $tarif->modifiedby = auth('api')->user()->name;

            if ($tarif->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($tarif->getTable()),
                    'postingdari' => 'EDIT TARIF',
                    'idtrans' => $tarif->id,
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'EDIT',
                    'datajson' => $tarif->toArray(),
                    'modifiedby' => $tarif->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // TarifRincian::where('tarif_id', $tarif->id)->delete();

                $detaillog = [];
                for ($i = 0; $i < count($request->container_id); $i++) {

                    $datadetail = [
                        'tarif_id' => $tarif->id,
                        'detail_id' => $request->detail_id[$i],
                        'container_id' => $request->container_id[$i],
                        'nominal' => $request->nominal[$i],
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreTarifRincianRequest($datadetail);
                    $datadetails = app(TarifRincianController::class)->update($data);
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }


                    $detaillog[] = $datadetails['detail']->toArray();
                    // $detaillog[] = $data->all();
                }
                // return response($detaillog,422);
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $tarif->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($tarif, $tarif->getTable());
            $tarif->position = $selected->position;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            return response([
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
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        $tarif = new Tarif();
        $tarif = $tarif->lockAndDestroy($id);
        if ($tarif) {
            $logTrail = [
                'namatabel' => strtoupper($tarif->getTable()),
                'postingdari' => 'DELETE TARIF',
                'idtrans' => $tarif->id,
                'nobuktitrans' => $tarif->id,
                'aksi' => 'DELETE',
                'datajson' => $tarif->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($tarif, $tarif->getTable(), true);
            $tarif->position = $selected->position;
            $tarif->id = $selected->id;
            $tarif->page = ceil($tarif->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $tarif
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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
            $row_range    = range(2, $row_limit);
            $column_range = range('A', $column_limit);
            $startcount = 2;
            $data = array();
            
            $a=0;
            foreach ($row_range as $row) {
              
                $data[] = [
                    'tujuan' => $sheet->getCell($this->kolomexcel(1) . $row)->getValue(),
                    'tglmulaiberlaku' => date('Y-m-d',strtotime($sheet->getCell($this->kolomexcel(2) . $row)->getFormattedValue())),
                    'kota' => $sheet->getCell($this->kolomexcel(3) . $row)->getValue(),
                    'kolom1' => $sheet->getCell($this->kolomexcel(4)  . $row)->getValue(),
                    'kolom2' => $sheet->getCell($this->kolomexcel(5)  . $row)->getValue(),
                    'modifiedby' => auth('api')->user()->name
                ];
                $startcount++;
            }

            $tarifrincian = new TarifRincian();

            $cekdata=$tarifrincian->cekupdateharga($data);
            if ($cekdata==true) {
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
        if ($kolom>=27 and $kolom<=52) {
            $hasil='A'.chr(38+$kolom);
        } else  {
            $hasil=chr(64+$kolom);
        }
        return $hasil;
    }

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
