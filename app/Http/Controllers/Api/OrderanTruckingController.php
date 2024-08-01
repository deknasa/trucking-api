<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Agen;
use App\Models\Error;
use App\Models\Tarif;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Container;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\JenisOrder;
use App\Models\TarifRincian;
use Illuminate\Http\Request;
use App\Models\SuratPengantar;
use App\Models\OrderanTrucking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\StoreOrderanTruckingRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Http\Requests\UpdateOrderanTruckingRequest;
use App\Http\Requests\DestroyOrderanTruckingRequest;
use App\Http\Requests\ValidasiApprovalOrderanTruckingRequest;
use App\Models\Locking;

class OrderanTruckingController extends Controller
{
    /**
     * @ClassName 
     * orderantruckingcontroller
     * @Detail JobTruckingController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $orderanTrucking = new OrderanTrucking();
        return response([
            'data' => $orderanTrucking->get(),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages
            ]
        ]);
    }

    public function getForLookup()
    {
        $orderanTrucking = new OrderanTrucking();
        return response([
            'data' => $orderanTrucking->getForLookup(),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages
            ]
        ]);
    }

    public function cekValidasi($id, $aksi,Request $request)
    {

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        $nobuktilist=$request->nobukti ?? '';


        $querysp=DB::table('orderantrucking')->from(
            DB::raw("orderantrucking a with (readuncommitted)")
        )
        ->select('a.id')
        ->where('a.nobukti',$nobuktilist)
        ->first();
        if (isset($querysp)) {
            goto validasilanjut;
        } else {
    
            $data1 = [
                'kondisi' => true,
                'keterangan' => '',
            ];

            $edit = true;
            $keteranganerror = $error->cekKeteranganError('BMS') ?? '';
            $keterror = 'No Bukti <b>' . $nobuktilist . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

        //     $query = DB::table('error')
        //     ->select(
        //         DB::raw("'No Bukti ". $nobuktilist ." '+ltrim(rtrim(keterangan)) as keterangan")
        //     )
        //     ->where('kodeerror', '=', 'BMS')
        //     ->get();
        // $keterangan = $query['0'];
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'BMS',
                'statuspesan' => 'warning',
            ];

            return response($data);
        }


        validasilanjut:;
        $orderanTrucking = new OrderanTrucking();
        $nobukti = OrderanTrucking::from(DB::raw("orderantrucking"))->where('id', $id)->first();
        $cekdata = $orderanTrucking->cekvalidasihapus($nobukti->nobukti, $aksi);

        $isEditAble = OrderanTrucking::isEditAble($nobukti->id);
        $edit = true;
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('orderantrucking', $id);
        $useredit = $getEditing->editing_by ?? '';



        if (!$isEditAble) {
            $edit = false;
        }
        // if (!$isEditAble) {
        //     $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'BAED')->get();
        //     $keterangan = $query['0'];
        //     $data = [
        //         'status' => false,
        //         'message' => $keterangan,
        //         'errors' => '',
        //         'kondisi' => true,
        //     ];
        //     $passes = false;
        // }

        // $todayValidation = OrderanTrucking::todayValidation($nobukti->id);
        // if (!$todayValidation) {
        //     $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
        //     // $keterangan = $query['0'];
        //     $keterangan = ['keterangan' => 'transaksi Sudah beda tanggal']; //$query['0'];
        //     $data = [
        //         'message' => $keterangan,
        //         'errors' => 'Tidak bisa edit di hari yang berbeda',
        //         'kodestatus' => '1',
        //         'kodenobukti' => '1'
        //     ];
        //     $passes = false;
        //     // return response($data);
        // }
        

        if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL2')
            //     ->get();
            // $keterangan = $query['0'];
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            // $keterror = 'No Bukti <b>' . $nobukti->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $keterror = $cekdata['keterangan'];


            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',
            ];

            $passes = false;
        } else if ($tgltutup >= $nobukti->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti->nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);            
        } else if ($useredit != '' && $useredit != $user) {
           
            $waktu = (new Parameter())->cekBatasWaktuEdit('Nota Kredit Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'orderantrucking',$useredit);  
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                // return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {
            (new MyModel())->createLockEditing($id, 'orderantrucking',$useredit);  

            $data = [
                'message' => '',
                'error' => false,
                'kodestatus' => '0',
                'edit' => $edit,
                'kodenobukti' => '1'
            ];
        }
        return response($data);
    }
    public function default()
    {
        $orderanTrucking = new OrderanTrucking();
        return response([
            'status' => true,
            'data' => $orderanTrucking->default()
        ]);
    }



    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreOrderanTruckingRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'container_id' => $request->container_id,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'pelanggan_id' => $request->pelanggan_id,
                'tarifrincian_id' => $request->tarifrincian_id,
                'nojobemkl' => $request->nojobemkl,
                'nocont' => $request->nocont,
                'noseal' => $request->noseal,
                'nojobemkl2' => $request->nojobemkl2,
                'nocont2' => $request->nocont2,
                'noseal2' => $request->noseal2,
                'statuslangsir' => $request->statuslangsir,
                'statusperalihan' => $request->statusperalihan,
            ];
            $orderanTrucking = (new OrderanTrucking())->processStore($data);
            $orderanTrucking->position = $this->getPosition($orderanTrucking, $orderanTrucking->getTable())->position;
            if ($request->limit==0) {
                $orderanTrucking->page = ceil($orderanTrucking->position / (10));
            } else {
                $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $orderanTrucking
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $orderanTrucking = (new OrderanTrucking)->findAll($id);
        $hideCol = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'LOOKUP ORDERAN EMKL')->where('subgrp','PELANGGAN')->first()->text ?? 'YA';
        return response([
            'status' => true,
            'data' => $orderanTrucking,
            'orderemklshipper' => $hideCol
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateOrderanTruckingRequest $request, OrderanTrucking $orderantrucking): JsonResponse
    {
        DB::beginTransaction();

        try {
            $orderan=$request->jenisorderemkl ?? $request->jenisorder ;
            $jenisorderemkl_id=db::table("jenisorder")->from(
                db::raw("jenisorder a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
            ->where ('a.keterangan','=', $orderan)
            ->first();
            $data = [
                'tglbukti' => $request->tglbukti,
                'container_id' => $request->container_id,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'jenisorderemkl_id' => $request->jenisorder_id,
                'pelanggan_id' => $request->pelanggan_id,
                'tarifrincian_id' => $request->tarifrincian_id,
                'gandengan_id' => $request->gandengan_id,
                'nojobemkl' => $request->nojobemkl,
                'nocont' => $request->nocont,
                'noseal' => $request->noseal,
                'nojobemkl2' => $request->nojobemkl2,
                'nocont2' => $request->nocont2,
                'noseal2' => $request->noseal2,
                'statuslangsir' => $request->statuslangsir,
                'statusperalihan' => $request->statusperalihan,
            ];
            $orderanTrucking = (new OrderanTrucking())->processUpdate($orderantrucking, $data);
            $orderanTrucking->position = $this->getPosition($orderanTrucking, $orderanTrucking->getTable())->position;
            if ($request->limit==0) {
                $orderanTrucking->page = ceil($orderanTrucking->position / (10));
            } else {
                $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL TANPA JOB EMKL
     */
    public function updateNoContainer(UpdateOrderanTruckingRequest $request, OrderanTrucking $orderantrucking): JsonResponse{
        DB::beginTransaction();

        try {
            $orderan=$request->jenisorderemkl ?? $request->jenisorder ;
            $jenisorderemkl_id=db::table("jenisorder")->from(
                db::raw("jenisorder a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
            ->where ('a.keterangan','=', $orderan)
            ->first();
            $data = [
                'tglbukti' => $request->tglbukti,
                'container_id' => $request->container_id,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'jenisorderemkl_id' => $jenisorderemkl_id->id,
                'pelanggan_id' => $request->pelanggan_id,
                'tarifrincian_id' => $request->tarifrincian_id,
                'nojobemkl' => $request->nojobemkl,
                'nocont' => $request->nocont,
                'noseal' => $request->noseal,
                'nojobemkl2' => $request->nojobemkl2,
                'nocont2' => $request->nocont2,
                'noseal2' => $request->noseal2,
                'statuslangsir' => $request->statuslangsir,
                'statusperalihan' => $request->statusperalihan,
            ];
            $orderanTrucking = (new OrderanTrucking())->processUpdateNoContainer($orderantrucking, $data);
            $orderanTrucking->position = $this->getPosition($orderanTrucking, $orderanTrucking->getTable())->position;
            if ($request->limit==0) {
                $orderanTrucking->page = ceil($orderanTrucking->position / (10));
            } else {
                $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $orderanTrucking
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
    public function destroy(DestroyOrderanTruckingRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $orderanTrucking = (new OrderanTrucking())->processDestroy($id);
            $selected = $this->getPosition($orderanTrucking, $orderanTrucking->getTable(), true);
            $orderanTrucking->position = $selected->position;
            $orderanTrucking->id = $selected->id;
            if ($request->limit==0) {
                $orderanTrucking->page = ceil($orderanTrucking->position / (10));
            } else {
                $orderanTrucking->page = ceil($orderanTrucking->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('orderantrucking')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])
            ->get(config('app.api_url') . "jobemkl/combo");

        $data = [
            'container' => Container::all(),
            'agen' => Agen::all(),
            'jenisorder' => JenisOrder::all(),
            'pelanggan' => Pelanggan::all(),
            'tarif' => Tarif::all(),
            'statuslangsir' => Parameter::where(['grp' => 'status langsir'])->get(),
            'statusperalihan' => Parameter::where(['grp' => 'status peralihan'])->get(),
            'jobemkl' => $response['data']['jobemkl'],
        ];

        return response([
            'data' => $data
        ]);
    }

    public function getOrderanTrip(Request $request)
    {
        $idinvoice = $request->idInvoice ?? 0;
        $orderanTrucking = new OrderanTrucking();
        $agen = $request->agen;
        $tglbukti = date('Y-m-d', strtotime($request->tglbukti));
        return response([
            'data' => $orderanTrucking->getOrderanTrip($tglbukti, $agen, $idinvoice),
            'attributes' => [
                'totalRows' => $orderanTrucking->totalRows,
                'totalPages' => $orderanTrucking->totalPages,
                'totalNominal' => $orderanTrucking->totalNominal,
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ValidasiApprovalOrderanTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $orderanTrucking = (new OrderanTrucking())->processApproval($request->all());

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * @ClassName
     * @Keterangan APPROVAL EDIT DATA
     */
    public function approvaledit(ValidasiApprovalOrderanTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $orderanTrucking = (new OrderanTrucking())->processApprovalEdit($request->all());

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getagentas($id)
    {

        $orderantrucking = new OrderanTrucking();
        return response([
            "data" => $orderantrucking->getagentas($id)
        ]);
    }
    public function getcont($id)
    {

        $orderantrucking = new OrderanTrucking();
        return response([
            "data" => $orderantrucking->getcont($id)
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
    public function export(GetUpahSupirRangeRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $orderanTrucking = new OrderanTrucking();
        return response([
            'data' => $orderanTrucking->getExport($dari, $sampai),
        ]);
    }
    /**
     * @ClassName
     * @Keterangan APPROVAL TANPA JOB EMKL
     */
    public function approvaltanpajobemkl(ValidasiApprovalOrderanTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'id' => $request->orderanTruckingId
            ];
            $orderanTrucking = (new OrderanTrucking())->processApprovalTanpaJob($data);

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $orderanTrucking
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}
