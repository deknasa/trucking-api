<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Error;
use App\Models\Tarif;
use App\Models\MyModel;
use App\Models\Container;
use App\Models\Parameter;
use App\Models\TarifRincian;
use App\Models\LogTrail;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Requests\StoreTarifRequest;
use App\Http\Requests\UpdateTarifRequest;
use App\Http\Requests\DestroyTarifRequest;
use App\Http\Requests\ApprovalTarifRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Http\Requests\StoreTarifRincianRequest;


class TarifController extends Controller
{
    /**
     * @ClassName 
     * Tarif
     * @Detail TarifRincianController
     * @Keterangan TAMPILKAN DATA
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
        $dataMaster = $tarif->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $cekdata = $tarif->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
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
            goto selesai;
            
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('tarif', $id, $aksi);
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
            (new MyModel())->updateEditingBy('tarif', $id, $aksi);
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
                'data' => $tarifrincian->listpivot($dari, $sampai),
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
    public function store(StoreTarifRequest $request): JsonResponse
    {
        // dd('test');
        DB::beginTransaction();

        try {
            $data = [
                'parent_id' => $request->parent_id ?? '',
                'parent' => $request->parent ?? '',
                'upahsupir_id' => $request->upah_id ?? 0,
                'tujuan' => $request->tujuan,
                'penyesuaian' => $request->penyesuaian,
                'statusaktif' => $request->statusaktif,
                'statussistemton' => $request->statussistemton,
                'kota' => $request->kota,
                'kota_id' => $request->kota_id,
                'zona_id' => $request->zona_id ?? '',
                'zona' => $request->zona ?? '',
                'from' => $request->from ?? '',
                'jenisorder_id' => $request->jenisorder_id ?? 0,
                'tglmulaiberlaku' => $request->tglmulaiberlaku,
                'statuspenyesuaianharga' => $request->statuspenyesuaianharga,
                'statuspostingtnl' => $request->statuspostingtnl,
                'keterangan' => $request->keterangan,
                'container' => $request->container,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
                'detail_id' => $request->detail_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'tas_id' => $request->tas_id,

            ];

            // $tarif = (new Tarif())->processStore($data);
            $tarif = new Tarif();
            $datatarif=$tarif->processStore($data, $tarif);            
            if ($request->from == '') {
                $tarif->position = $this->getPosition($tarif, $tarif->getTable())->position;
                if ($request->limit == 0) {
                    $tarif->page = ceil($tarif->position / (10));
                } else {
                    $tarif->page = ceil($tarif->position / ($request->limit ?? 10));
                }
            }

            // $statusTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'POSTING TNL')->first();
            // if ($data['statuspostingtnl'] == $statusTnl->id) {
            //     $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
            //     // posting ke tnl
            //     $data['statuspostingtnl'] = $statusBukanTnl->id;

            //     $postingTNL = (new Tarif())->postingTnl($data);
            // }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $tarif->id;
            $data['detail_tas_id'] = $tarif->detailTasId;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $datatariftnl=$this->SaveTnlMasterDetail('tarif', 'add', $data);
            }            
            /**
             * 
             //detail
                $json=json_decode(json_encode($datatariftnl),true);
                $datatariftnlid=$json['original']['id'];
                // dd($json['original']['id']);
                // dd($datatariftnl);
                if (is_iterable($data['container_id'])) {
                    $tarifDetails = [];
                    for ($i = 0; $i < count($data['container_id']); $i++) {
                        $datadetail = [
                            'container_id' => $data['container_id'][$i],
                            'nominal' => $data['nominal'][$i],
                            'tarif_id' => $datatarif->id,
                            'tas_id' => 0,
                        ];

                            $tarifRincian = new TarifRincian();
                            $tarifRincian->processStore($datadetail, $tarifRincian);

                        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
                        $datadetail['tas_id'] = $tarifRincian->id;
                        $datadetail['tarif_id'] =$datatariftnlid;
                        
                        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                            $controller = new Controller;
                            $controller->SaveTnlNew('tarifrincian', 'add', $datadetail);
                        }

                        $tarifRincians[] = $tarifRincian->toArray();
                    }

                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper($tarifRincian->getTable()),
                        'postingdari' => 'ENTRY tarif RINCIAN',
                        'idtrans' =>  $tarif->id,
                        'nobuktitrans' => $tarif->id,
                        'aksi' => 'ENTRY',
                        'datajson' => $tarifRincians,
                        'modifiedby' => auth('api')->user()->user,
                    ]);
                }
                        // 
            * 
            */
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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateTarifRequest $request, $id): JsonResponse
    {

        DB::beginTransaction();
        try {
            $data = [
                'parent_id' => $request->parent_id ?? '',
                'parent' => $request->parent ?? '',                
                'upahsupir_id' => $request->upah_id ?? 0,
                'tujuan' => $request->tujuan,
                'penyesuaian' => $request->penyesuaian,
                'statusaktif' => $request->statusaktif,
                'statussistemton' => $request->statussistemton,
                'kota' => $request->kota ?? '',
                'kota_id' => $request->kota_id ?? 0 ,
                'zona_id' => $request->zona_id ?? '',
                'zona' => $request->zona ?? '',
                'jenisorder_id' => $request->jenisorder_id ?? 0,
                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),
                'statuspenyesuaianharga' => $request->statuspenyesuaianharga,
                'keterangan' => $request->keterangan,
                'container' => $request->container,
                'container_id' => $request->container_id,
                'nominal' => $request->nominal,
                'detail_id' => $request->detail_id,
                'tas_id' => $request->tas_id,
            ];
            // $tarif = (new Tarif())->processUpdate($tarif, $data);
            $tarif = new Tarif();
            $tarifs = $tarif->findOrFail($id);
            $tarif = $tarif->processUpdate($tarifs, $data);            
            
            if ($request->from == '') {
            $tarif->position = $this->getPosition($tarif, $tarif->getTable())->position;
            if ($request->limit == 0) {
                $tarif->page = ceil($tarif->position / (10));
            } else {
                $tarif->page = ceil($tarif->position / ($request->limit ?? 10));
            }
        }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $tarif->id;
            $data['detail_tas_id'] = $tarif->detailTasId;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $datatariftnl=$this->SaveTnlMasterDetail('tarif', 'edit', $data);
            }
        /**
         * 
         //detail
         // dd($data);
         $json=json_decode(json_encode($datatariftnl),true);
         $datatariftnlid=$json['original']['id'];
         // dd($json['original']['id']);
         // dd($datatariftnl);
         if (is_iterable($data['container_id'])) {
             $tarifDetails = [];
             for ($i = 0; $i < count($data['container_id']); $i++) {
                 $datadetail = [
                    'container_id' => $data['container_id'][$i],
                    'nominal' => $data['nominal'][$i],
                    'tarif_id' => $id,
                     'tas_id' => 0,
                 ];
         
                     $tarifRincian = new TarifRincian();
                     $tarifRincian->processStore($datadetail, $tarifRincian);
         
                 $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
                 $datadetail['tas_id'] = $tarifRincian->id;
                 $datadetail['tarif_id'] =$datatariftnlid;
                 
                 if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                     $controller = new Controller;
                     $controller->SaveTnlNew('tarifrincian', 'add', $datadetail);
                 }
         
                 $tarifRincians[] = $tarifRincian->toArray();
             }
         
             (new LogTrail())->processStore([
                 'namatabel' => strtoupper($tarifRincian->getTable()),
                 'postingdari' => 'ENTRY tarif RINCIAN',
                 'idtrans' =>  $tarif->id,
                 'nobuktitrans' => $tarif->id,
                 'aksi' => 'ENTRY',
                 'datajson' => $tarifRincians,
                 'modifiedby' => auth('api')->user()->user,
             ]);
         }
         * 
         */


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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyTarifRequest $request, $id)
    {

        DB::beginTransaction();

        try {
            // $tarif = (new Tarif())->processDestroy($id);
            $tarif = new Tarif();
            $tarifs = $tarif->findOrFail($id);
            $tarif = $tarif->processDestroy($tarifs);   
            if ($request->from == '') {
                $selected = $this->getPosition($tarif, $tarif->getTable(), true);
                $tarif->position = $selected->position;
                $tarif->id = $selected->id;
                if ($request->limit == 0) {
                    $tarif->page = ceil($tarif->position / (10));
                } else {
                    $tarif->page = ceil($tarif->position / ($request->limit ?? 10));
                }
    
            }

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
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
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

         /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalTarifRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Tarif())->processApprovalnonaktif($data);

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
