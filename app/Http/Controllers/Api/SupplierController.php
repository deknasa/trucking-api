<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\DestroySupplierRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\UpdateSupirRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\Request;

use App\Models\Parameter;
use DateTime;
use App\Models\Error;
use App\Models\MyModel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ApprovalSupplierRequest;
use Illuminate\Support\Facades\Http;

class SupplierController extends Controller
{

    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $supplier = new Supplier();

        $rows = $supplier->get();

        $baseUrl = asset('');

        return response([
            'data' => $supplier->get(),
            'attributes' => [
                'totalRows' => $supplier->totalRows,
                'totalPages' => $supplier->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $supplier = new Supplier();
        $cekdata = $supplier->cekvalidasihapus($id);
        $dataMaster = Supplier::where('id',$id)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
      
        $aksi = strtoupper(request()->aksi) ?? '';
        $aksi =strtoupper($aksi);
        if( $aksi == 'EDIT'){
            $cekdata['kondisi'] = false;
        }
        if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->updateEditingBy('supplier', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->namasupplier . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            

            } else if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL')
            //     ->get();
            // $keterangan = $query['0'];
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'Data <b>' . $dataMaster->namasupplier . '</b><br>' . $keteranganerror . ' <b> <br> ' . $keterangantambahanerror;

            $data = [
                // 'status' => false,
                // 'message' => $keterangan,
                // 'errors' => '',
                // 'kondisi' => $cekdata['kondisi'],
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',

            ];

            return response($data);

        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('supplier', $id, $aksi);
            }else{
                if ($dataMaster->statusapproval == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
                    $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                    $keterror = 'supplier <b>' . $dataMaster->namasupplier . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
                    $data = [
                        'message' => $keterror,
                        'error' => true,
                        'kodeerror' => 'SAP',
                        'statuspesan' => 'warning',
                    ];
        
                    return response($data);
                }
            }       
            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',                
                // 'status' => false,
                // 'message' => '',
                // 'errors' => '',
                // 'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
    
    public function default()
    {
        $supplier = new Supplier();
        return response([
            'status' => true,
            'data' => $supplier->default()
        ]);
    }

    public function show($id)
    {

        $data = (new Supplier())->getAll($id);
        // $detail = ServiceInDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);

        // return response([
        //     'status' => true,
        //     'data' => $supplier
        // ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'namasupplier' => $request->namasupplier,
                'namakontak' => $request->namakontak,
                'alamat' => $request->alamat,
                'kota' => $request->kota,
                'top' => $request->top,
                'keterangan' => $request->keterangan,
                'statusapproval' => $request->statusapproval,
                'kodepos' => $request->kodepos,
                'notelp1' => $request->notelp1,
                'notelp2' => $request->notelp2 ?? '',
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'web' => $request->web,
                'namapemilik' => $request->namapemilik,
                'jenisusaha' => $request->jenisusaha,
                // 'top' => $request->top,
                'bank' => $request->bank,
                'coa' => $request->coa,
                'rekeningbank' => $request->rekeningbank,
                'namarekening' => $request->namarekening,
                'jabatan' => $request->jabatan,
                'statusdaftarharga' => $request->statusdaftarharga,
                'statuspostingtnl' => $request->statuspostingtnl,
                'kategoriusaha' => $request->kategoriusaha,
                'tas_id' => $request->tas_id,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            // $supplier = (new Supplier())->processStore($data);
            $supplier = new Supplier();
            $supplier->processStore($data, $supplier);            
            if ($request->from == '') {
                $supplier->position = $this->getPosition($supplier, $supplier->getTable())->position;
                if ($request->limit == 0) {
                    $supplier->page = ceil($supplier->position / (10));
                } else {
                    $supplier->page = ceil($supplier->position / ($request->limit ?? 10));
                }
            }


            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $supplier->id;


            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('supplier', 'add', $data);
            }
            // if ($data['statuspostingtnl'] == $statusTnl->id) {
            //     $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
            //     // posting ke tnl
            //     $data['statuspostingtnl'] = $statusBukanTnl->id;

            //     $postingTNL = (new Supplier())->postingTnl($data);
            // }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $supplier
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateSupplierRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'namasupplier' => $request->namasupplier,
                'namakontak' => $request->namakontak,
                'alamat' => $request->alamat,
                'kota' => $request->kota,
                'top' => $request->top,
                'kodepos' => $request->kodepos,
                'keterangan' => $request->keterangan,
                'statusapproval' => $request->statusapproval,
                'notelp1' => $request->notelp1,
                'notelp2' => $request->notelp2 ?? '',
                'email' => $request->email,
                'statusaktif' => $request->statusaktif,
                'web' => $request->web,
                'namapemilik' => $request->namapemilik,
                'jenisusaha' => $request->jenisusaha,
                // 'top' => $request->top,
                'bank' => $request->bank,
                'coa' => $request->coa,
                'rekeningbank' => $request->rekeningbank,
                'namarekening' => $request->namarekening,
                'jabatan' => $request->jabatan,
                'statusdaftarharga' => $request->statusdaftarharga,
                'kategoriusaha' => $request->kategoriusaha,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];

            // $supplier = (new Supplier())->processUpdate($supplier, $data);

            $supplier = new Supplier();
            $suppliers = $supplier->findOrFail($id);
            $supplier = $supplier->processUpdate($suppliers, $data);            

            if ($request->from == '') {
                $supplier->position = $this->getPosition($supplier, $supplier->getTable())->position;
                if ($request->limit == 0) {
                    $supplier->page = ceil($supplier->position / (10));
                } else {
                    $supplier->page = ceil($supplier->position / ($request->limit ?? 10));
                }
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $supplier->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('supplier', 'edit', $data);
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $supplier
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
    public function destroy(DestroySupplierRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            // $supplier = (new Supplier())->processDestroy($id);
            $supplier = new Supplier();
            $suppliers = $supplier->findOrFail($id);
            $supplier = $supplier->processDestroy($suppliers);            
            if ($request->from == '') {
                $selected = $this->getPosition($supplier, $supplier->getTable(), true);
                $supplier->position = $selected->position;
                $supplier->id = $selected->id;
                if ($request->limit == 0) {
                    $supplier->page = ceil($supplier->position / (10));
                } else {
                    $supplier->page = ceil($supplier->position / ($request->limit ?? 10));
                }
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $id;

            $data["accessTokenTnl"] = $request->accessTokenTnl ?? '';

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->SaveTnlNew('supplier', 'delete', $data);
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('supplier')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan APRROVAL DATA
     */
    public function approval(ApprovalSupplierRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Supplier())->processApproval($data);

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
    public function approvalnonaktif(ApprovalSupplierRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Supplier())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function approvalTNL(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
                'nama' => $request->nama
            ];
            (new Supplier())->processApprovalTnl($data);

            DB::commit();
            return response([
                'message' => 'Berhasil approval TNL'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $suppliers = $decodedResponse['data'];

            $judulLaporan = $suppliers[0]['judulLaporan'];

            $i = 0;
            foreach ($suppliers as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusDaftarHarga = $params['statusdaftarharga'];

                $result = json_decode($statusaktif, true);
                $resultDaftarHarga = json_decode($statusDaftarHarga, true);

                $statusaktif = $result['MEMO'];
                $statusDaftarHarga = $resultDaftarHarga['MEMO'];

                $suppliers[$i]['statusaktif'] = $statusaktif;
                $suppliers[$i]['statusdaftarharga'] = $statusDaftarHarga;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Supplier',
                    'index' => 'namasupplier',
                ],
                [
                    'label' => 'Nama Kontak',
                    'index' => 'namakontak',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'Kota',
                    'index' => 'kota',
                ],
                [
                    'label' => 'Kode Pos',
                    'index' => 'kodepos',
                ],
                [
                    'label' => 'No Telp 1',
                    'index' => 'notelp1',
                ],
                [
                    'label' => 'No Telp 2',
                    'index' => 'notelp2',
                ],
                [
                    'label' => 'Email',
                    'index' => 'email',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Web',
                    'index' => 'web',
                ],
                [
                    'label' => 'Nama Pemilik',
                    'index' => 'namapemilik',
                ],
                [
                    'label' => 'Jenis Usaha',
                    'index' => 'jenisusaha',
                ],
                // [
                //     'label' => 'TOP',
                //     'index' => 'top',
                // ],
                [
                    'label' => 'Bank',
                    'index' => 'bank',
                ],
                [
                    'label' => 'Rekening Bank',
                    'index' => 'rekeningbank',
                ],
                [
                    'label' => 'Nama Rekening',
                    'index' => 'namarekening',
                ],
                [
                    'label' => 'Jabatan',
                    'index' => 'jabatan',
                ],
                [
                    'label' => 'Status Daftar Harga',
                    'index' => 'statusdaftarharga',
                ],
                [
                    'label' => 'Kategori Usaha',
                    'index' => 'kategoriusaha',
                ],

            ];

            $this->toExcel($judulLaporan, $suppliers, $columns);
        }
    }
}
