<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Agen;
use App\Models\Kota;
use App\Models\Error;
use App\Models\Supir;
use App\Models\Tarif;
use App\Models\Trado;
use App\Models\MyModel;
use App\Models\Container;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\UpahSupir;
use App\Models\JenisOrder;
use App\Models\TarifRincian;
use Illuminate\Http\Request;
use App\Models\SuratPengantar;
use App\Models\OrderanTrucking;
use App\Models\StatusContainer;
use App\Models\UpahSupirRincian;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoSuratPengantar;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\SuratPengantarBiayaTambahan;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\ApprovalBatalMuatRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Http\Requests\ApprovalGabungJobTruckingRequest;
use App\Http\Requests\ApprovalEditTujuanRequest;
use App\Http\Requests\ApprovalTolakanRequest;
use App\Http\Requests\StoreSuratPengantarRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Http\Requests\DestroySuratPengantarRequest;

use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SuratPengantarController extends Controller
{
    /**
     * @ClassName 
     * SuratPengantar
     * @Detail SuratPengantarBiayaTambahanController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $suratPengantar = new SuratPengantar();

        $suratPengantar->returnUnApprovalEdit();

        return response([
            'data' => $suratPengantar->get(),
            'attributes' => [
                'totalJarak' => $suratPengantar->totalJarak,
                'totalRows' => $suratPengantar->totalRows,
                'totalPages' => $suratPengantar->totalPages
            ]
        ]);
    }

    public function getTripInap()
    {
        $suratPengantar = new SuratPengantar();

        return response([
            'data' => $suratPengantar->get(),
            'attributes' => [
                'totalJarak' => $suratPengantar->totalJarak,
                'totalRows' => $suratPengantar->totalRows,
                'totalPages' => $suratPengantar->totalPages
            ]
        ]);
    }
    public function default()
    {
        $suratPengantar = new SuratPengantar();
        return response([
            'status' => true,
            'data' => $suratPengantar->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreSuratPengantarRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'jobtrucking' => $request->jobtrucking,
                'upah_id' => $request->upah_id,
                'container_id' => $request->container_id,
                'tglbukti' => $request->tglbukti,
                'keterangan' => $request->keterangan,
                'nourutorder' => $request->nourutorder,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statusgandengan' => $request->statusgandengan,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'gandengan_id' => $request->gandengan_id,
                'statuslongtrip' => $request->statuslongtrip,
                'statusperalihan' => $request->statusperalihan,
                'nominalperalihan' => $request->nominalperalihan,
                'biayatambahan_id' => $request->biayatambahan_id,
                'penyesuaian' => $request->penyesuaian,
                'nosp' => $request->nosp,
                'nosptagihlain' => $request->nosptagihlain,
                'qtyton' => $request->qtyton,
                'statusgudangsama' => $request->statusgudangsama,
                'statusbatalmuat' => $request->statusbatalmuat,
                'gudang' => $request->gudang,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal,
                'nominalTagih' => $request->nominalTagih,

            ];
            $suratPengantar = (new SuratPengantar())->processStore($data);
            $suratPengantar->position = $this->getPosition($suratPengantar, $suratPengantar->getTable())->position;
            $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $suratPengantar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {

        $query = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.id', $id)
            ->first();

        if (isset($query)) {
            $data = SuratPengantar::findAll($id);
            $detail = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();
        } else {
            $data = SaldoSuratPengantar::findAll($id);
            $detail = null;
        }

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);

        // $data = SuratPengantar::findAll($id);
        // $detail = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();
        // if (isset($data)) {
        //     return response([
        //         'status' => true,
        //         'data' => $data,
        //         'detail' => $detail
        //     ]);
        // } else {
        //     $data = SaldoSuratPengantar::findAll($id);

        //     return response([
        //         'status' => true,
        //         'data' => $data,
        //         'detail' => $detail
        //     ]);
        // }
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateSuratPengantarRequest $request, SuratPengantar $suratpengantar): JsonResponse
    {

        DB::beginTransaction();
        try {
            $data = [
                'jobtrucking' => $request->jobtrucking,
                'upah_id' => $request->upah_id,
                'triptangki_id' => $request->triptangki_id,
                'tarif_id' => $request->tarifrincian_id,
                'container_id' => $request->container_id,
                'tglbukti' => $request->tglbukti,
                'keterangan' => $request->keterangan,
                'nourutorder' => $request->nourutorder,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'statusgandengan' => $request->statusgandengan,
                'statuslangsir' => $request->statuslangsir,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'penyesuaian' => $request->penyesuaian,
                'gandengan_id' => $request->gandengan_id,
                'statuslongtrip' => $request->statuslongtrip,
                'statusperalihan' => $request->statusperalihan,
                'statuskandang' => $request->statuskandang,
                'nominalperalihan' => $request->nominalperalihan ?? 0,
                'persentaseperalihan' => $request->persentaseperalihan,
                'biayatambahan_id' => $request->biayatambahan_id,
                'nosp' => $request->nosp,
                'nocont' => $request->nocont,
                'noseal' => $request->noseal,
                'nocont2' => $request->nocont2,
                'noseal2' => $request->noseal2,
                'nosptagihlain' => $request->nosptagihlain,
                'qtyton' => $request->qtyton,
                'statusgudangsama' => $request->statusgudangsama,
                'statustolakan' => $request->statustolakan,
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'qtyton' => $request->qtyton,
                'statusbatalmuat' => $request->statusbatalmuat,
                'statusupahzona' => $request->statusupahzona,
                'gudang' => $request->gudang,
                'lokasibongkarmuat' => $request->lokasibongkarmuat,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal,
                'nominalTagih' => $request->nominalTagih,
                'komisisupir' => $request->komisisupir,
                'gajikenek' => $request->gajikenek,
                'gajisupir' => $request->gajisupir,
                'tambahan_id' => $request->tambahan_id,
                'nobukti_tripasal' => $request->nobukti_tripasal ?? '',
                'statuspenyesuaian' => $request->statuspenyesuaian,
            ];
            $suratPengantar = (new SuratPengantar())->processUpdate($suratpengantar, $data);
            $suratPengantar->position = $this->getPosition($suratPengantar, $suratPengantar->getTable())->position;
            if ($request->limit == 0) {
                $suratPengantar->page = ceil($suratPengantar->position / (10));
            } else {
                $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));
            }
            // $suratPengantar->position = $suratpengantar->id;
            // $suratPengantar->page = 1;


            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $suratPengantar
            ]);
        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;
        }
    }

    public function getpelabuhan($id)
    {

        $suratpengantar = new SuratPengantar();
        return response([
            "data" => $suratpengantar->getpelabuhan($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroySuratPengantarRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $suratPengantar = (new SuratPengantar())->processDestroy($id);
            $selected = $this->getPosition($suratPengantar, $suratPengantar->getTable(), true);
            $suratPengantar->position = $selected->position;
            $suratPengantar->id = $selected->id;
            if ($request->limit == 0) {
                $suratPengantar->page = ceil($suratPengantar->position / (10));
            } else {
                $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $suratPengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('suratpengantar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function cekUpahSupir(Request $request)
    {

        $upahSupir =  DB::table('upahsupir')->from(
            DB::raw("upahsupir with (readuncommitted)")
        )
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $request->dari_id)
            ->where('upahsupir.kotasampai_id', $request->sampai_id)
            ->where('upahsupirrincian.container_id', $request->container_id)
            ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
            ->first();

        if (!isset($upahSupir)) {
            $upahSupir =  DB::table('upahsupir')->from(
                DB::raw("upahsupir with (readuncommitted)")
            )
                ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
                ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
                ->where('upahsupir.kotasampai_id', $request->dari_id)
                ->where('upahsupir.kotadari_id', $request->sampai_id)
                ->where('upahsupirrincian.container_id', $request->container_id)
                ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
                ->first();
        }
        if ($upahSupir != null) {
            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '1',
            ];

            return response($data);
        } else {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'USBA')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }

    public function cekValidasi($id, Request $request)
    {

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));


        $nobuktilist = $request->nobukti ?? '';

        $aksi = $request->aksi ?? '';





        $querysp = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.tglbukti'
            )
            ->where('a.nobukti', $nobuktilist)
            ->first();
        if (isset($querysp)) {
            if ($aksi == 'DELETE') {
                // $tglnow=date('Y-m-d');
                // $date1=date_create($querysp->tglbukti);
                // $date2= date_create();
                // $diff=date_diff($date1,$date2);
                // $diff=$diff->days;
                $defaultidnonapproval = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.grp', 'STATUS APPROVAL')
                    ->where('a.subgrp', 'STATUS APPROVAL')
                    ->where('a.text', 'NON APPROVAL')
                    ->first()->id ?? '';

                $defaultidapproval = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.grp', 'STATUS APPROVAL')
                    ->where('a.subgrp', 'STATUS APPROVAL')
                    ->where('a.text', 'APPROVAL')
                    ->first()->id ?? '';

                $queryabsen = db::table("absensisupirheader")->from(db::raw("absensisupirheader a with (readuncommitted)"))
                    ->select(
                        'a.tglbukti',
                        db::raw("isnull(a.statusapprovalfinalabsensi," . $defaultidnonapproval . ") as statusapprovalfinalabsensi"),

                    )
                    ->where('a.tglbukti', $querysp->tglbukti)
                    ->where('a.statusapprovalfinalabsensi', $defaultidapproval)
                    ->first();

                if (isset($queryabsen)) {
                    $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                    $keterror = 'Tgl Absensi <b>' . $querysp->tglbukti . '</b><br>' . $keteranganerror . ' FINAL ABSENSI <br> ' . $keterangantambahanerror;

                    $data = [
                        'error' => true,
                        'message' => $keterror,
                        'kodeerror' => 'BAP',
                        'statuspesan' => 'warning',
                    ];

                    return response($data);
                }
            }
            goto validasilanjut;
        } else {

            $data1 = [
                'kondisi' => true,
                'keterangan' => '',
            ];

            $edit = true;
            $keteranganerror = $error->cekKeteranganError('BMS') ?? '';
            $keterror = 'No Bukti <b>' . $nobuktilist . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("'No Bukti " . $nobuktilist . " '+ltrim(rtrim(keterangan)) as keterangan")
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
        $suratPengantar = new SuratPengantar();
        $nobukti = DB::table('SuratPengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('id', $id)->first();
        //validasi Hari ini
        // $todayValidation = SuratPengantar::todayValidation($nobukti->id);
        $isEditAble = SuratPengantar::isEditAble($nobukti->id);
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('suratpengantar', $id);
        $useredit = $getEditing->editing_by ?? '';

        $edit = true;
        // if (!$todayValidation) {
        //     $edit = false;
        // if ($isEditAble) {
        //     $edit = true;
        // }
        // }
        // else {
        if (!$isEditAble) {
            $edit = false;
        }
        // }

        $cekdata = $suratPengantar->cekvalidasihapus($nobukti->nobukti, $nobukti->jobtrucking, $nobukti);
        if ($cekdata['kondisi'] == true) {
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = $cekdata['keterangan'];
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL')
            //     ->get();
            // $keterangan = $query['0'];

            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',
            ];

            return response($data);
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

            $waktu = (new Parameter())->cekBatasWaktuEdit('surat pengantar BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi == 'DELETE' || $aksi == 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'suratpengantar', $useredit);
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
            if ($aksi == 'DELETE' || $aksi == 'EDIT') {
                (new MyModel())->createLockEditing($id, 'suratpengantar', $useredit);
            }
            $data = [
                'status' => false,
                'message' => '',
                'error' => false,
                'edit' => $edit,
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function getTarifOmset($id)
    {

        $iddata = $id ?? 0;
        $tarifrincian = new TarifRincian();
        $omset = $tarifrincian->getid($iddata);


        return response([
            "dataTarif" => $omset
        ]);
    }

    public function getOrderanTrucking()
    {

        $suratPengantar = new SuratPengantar();
        return response([
            "data" => $suratPengantar->getOrderanTrucking()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL BATAL MUAT
     */
    public function approvalBatalMuat(ApprovalBatalMuatRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'nobukti' => $request->Id,
            ];
            (new SuratPengantar())->approvalBatalMuat($data);

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
     * @Keterangan APPROVAL EDIT TUJUAN
     */
    public function approvalEditTujuan(ApprovalEditTujuanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'nobukti' => $request->Id,
            ];
            (new SuratPengantar())->approvalEditTujuan($data);

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
     * @Keterangan APPROVAL GABUNG JOB TRUCKING
     */
    public function approvalGabungJobTrucking(ApprovalGabungJobTruckingRequest $request)
    {
        // dd('test');


      
        DB::beginTransaction();
        try {

            $data = [
                'nobukti' => $request->Id,
            ];   

    
            (new SuratPengantar())->approvalGabungJobTrucking($data);

            DB::commit();
            // cek sisa belum approval

            $nobuktitrip = $data['nobukti'][0] ?? '';
            $queryutama = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.nocont',
                    'a.nocont2',
                    'a.noseal',
                    'a.noseal2',
                    'a.nojob',
                    'a.nojob2',
                    'a.pelanggan_id',
                    'a.penyesuaian',
                    'a.container_id',
                    'a.trado_id',
                    'a.gandengan_id',
                    'a.agen_id',
                    'a.jenisorder_id',
                    'a.tarif_id',
                    'a.sampai_id',
                    'a.statuslongtrip',
                    'b.statusgerobak'
                )
                ->join(db::raw("trado b with (readuncommitted)"), 'a.trado_id', 'b.id')
                ->where('a.nobukti', $nobuktitrip)
                ->first();

            $pelanggan_idtrip2 = $queryutama->pelanggan_id;
            $penyesuaiantrip2 = $queryutama->penyesuaian;
            $container_idtrip2 = $queryutama->container_id;
            $trado_idtrip2 = $queryutama->trado_id;
            $gandengan_idtrip2 = $queryutama->gandengan_id;
            $agen_idtrip2 = $queryutama->agen_id;
            $jenisorder_idtrip2 = $queryutama->jenisorder_id;
            $tarif_idtrip2 = $queryutama->tarif_id;
            $statusgerobaktrip2 = $queryutama->statusgerobak;
            $noconttrip2 = $queryutama->nocont;
            $nocont2trip2 = $queryutama->nocont2;
            $nosealtrip2 = $queryutama->noseal;
            $noseal2trip2 = $queryutama->noseal2;
            $nojobtrip2 = $queryutama->nojob;
            $nojob2trip2 = $queryutama->nojob2;
            $jobtruckingtrip2 = $queryutama->jobtrucking;
            $statuslongtrip2 = $queryutama->statuslongtrip;
            $sampai_id = $queryutama->sampai_id;

            // dd($pelanggan_idtrip2, $penyesuaiantrip2, $container_idtrip2, $gandengan_idtrip2, $agen_idtrip2, $jenisorder_idtrip2, $tarif_idtrip2);
            $querysuratpengantar = db::table("suratpengantar")->from(db::raw("suratpengantar  with (readuncommitted)"))
                ->select(
                    'suratpengantar.nobukti'
                );

            if ($statuslongtrip2 == 65) {
                $querysuratpengantar->whereRaw("(isnull(suratpengantar.pelanggan_id,0)=" . $pelanggan_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.container_id,0)=" . $container_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.gandengan_id,0)=" . $gandengan_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.agen_id,0)=" . $agen_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.jenisorder_id,0)=" . $jenisorder_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.dari_id,0)=" . $sampai_id . ")  or ( suratpengantar.nobukti='" . $nobuktitrip . "')");
            } else {
                $querysuratpengantar->whereRaw("isnull(suratpengantar.pelanggan_id,0)=" . $pelanggan_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.penyesuaian,'')='" . $penyesuaiantrip2 . "'");
                $querysuratpengantar->whereRaw("isnull(suratpengantar.container_id,0)=" . $container_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.gandengan_id,0)=" . $gandengan_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.agen_id,0)=" . $agen_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.jenisorder_id,0)=" . $jenisorder_idtrip2);
                $querysuratpengantar->whereRaw("isnull(suratpengantar.tarif_id,0)=" . $tarif_idtrip2);
            }
            $querysuratpengantar->whereraw("isnull(suratpengantar.jobtrucking,'')=''");

            $nobukti = $querysuratpengantar->first() ?? '';
            return response([
                'message' => 'Berhasil',
                'nobukti' => $nobukti
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     * @Keterangan APPROVAL TITIPAN EMKL
     */
    public function approvalTitipanEmkl(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'nobukti' => $request->Id,
            ];
            (new SuratPengantar())->approvalTitipanEmkl($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getGaji($dari, $sampai, $container, $statuscontainer)
    {


        $data = DB::table('upahsupir')
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join('upahsupirrincian', 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $dari)
            ->where('upahsupir.kotasampai_id', $sampai)
            ->where('upahsupirrincian.container_id', $container)
            ->where('upahsupirrincian.statuscontainer_id', $statuscontainer)

            //  dd($data->toSql());
            ->first();

        // dd($data);
        if ($data != null) {
            return response([
                'data' => $data
            ]);
        } else {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'USBA')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $suratPengantar = new SuratPengantar();
        $surat_Pengantar = $suratPengantar->getExport();

        if ($request->export == true) {
            $surat_PengantarData = $surat_Pengantar['data'];

            $timeStamp = strtotime($request->tgldari);
            $datetglDari = date('d-m-Y', $timeStamp);
            $periodeDari = $datetglDari;

            $timeStamp = strtotime($request->tglsampai);
            $datetglSampai = date('d-m-Y', $timeStamp);
            $periodeSampai = $datetglSampai;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $surat_Pengantar['parameter']->judul);
            $sheet->setCellValue('A2', $surat_Pengantar['parameter']->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:Z1');
            $sheet->mergeCells('A2:Z2');

            $header_start_row = 4;
            $detail_table_header_row = 7;
            $detail_start_row = $detail_table_header_row + 1;
            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'Periode Dari',
                    'index' => $periodeDari
                ],
                [
                    'label' => 'Periode Sampai',
                    'index' => $periodeSampai
                ]
            ];

            $columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'JOB TRUCKING',
                    'index' => 'jobtrucking',
                ],
                [
                    'label' => 'NO TRIP',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'TANGGAL TRIP',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'NO SP',
                    'index' => 'nosp',
                ],
                [
                    'label' => 'TANGGAL SP',
                    'index' => 'tglsp',
                ],
                [
                    'label' => 'SHIPPER',
                    'index' => 'pelanggan_id',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'NO JOB',
                    'index' => 'nojob',
                ],
                [
                    'label' => 'DARI',
                    'index' => 'dari_id',
                ],
                [
                    'label' => 'SAMPAI',
                    'index' => 'sampai_id',
                ],
                [
                    'label' => 'PENYESUAIAN',
                    'index' => 'penyesuaian',
                ],
                [
                    'label' => 'CUSTOMER',
                    'index' => 'agen_id',
                ],
                [
                    'label' => 'JENIS ORDER',
                    'index' => 'jenisorder_id',
                ],
                [
                    'label' => 'JARAK (KM)',
                    'index' => 'jarak',
                ],
                [
                    'label' => 'NO CONTAINER',
                    'index' => 'nocont',
                ],
                [
                    'label' => 'CONTAINER',
                    'index' => 'container_id',
                ],
                [
                    'label' => 'STATUS CONTAINER',
                    'index' => 'statuscontainer_id',
                ],
                [
                    'label' => 'GUDANG',
                    'index' => 'gudang',
                ],
                [
                    'label' => 'NO POLISI',
                    'index' => 'trado_id',
                ],
                [
                    'label' => 'SUPIR',
                    'index' => 'supir_id',
                ],
                [
                    'label' => 'CHASIS',
                    'index' => 'gandengan_id',
                ],
                [
                    'label' => 'LOKASI BONGKAR MUAT',
                    'index' => 'tarif_id',
                ],
                [
                    'label' => 'MANDOR TRADO',
                    'index' => 'mandortrado_id',
                ],
                [
                    'label' => 'MANDOR SUPIR',
                    'index' => 'mandorsupir_id',
                ],
                [
                    'label' => 'NO SEAL',
                    'index' => 'noseal',
                ],
                [
                    'label' => 'TOTAL OMSET',
                    'index' => 'totalomset',
                    'format' => 'currency'
                ],
                [
                    'label' => 'GAJI SUPIR',
                    'index' => 'gajisupir',
                    'format' => 'currency'
                ],
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $header_column['index']);
            }
            $alphabets = [];
            for ($i = 0; $i < 26; $i++) {
                $alphabets[] = chr(65 + $i);
            }

            for ($i = 0; $i < 26; $i++) {
                for ($j = 0; $j < 26; $j++) {
                    $alphabets[] = chr(65 + $i) . chr(65 + $j);
                }
            }
            foreach ($columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            }
            $styleArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            );

            $style_number = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],

                'borders' => [
                    'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                ]
            ];
            $sheet->getStyle("A$detail_table_header_row:AB$detail_table_header_row")->applyFromArray($styleArray);
            $sheet->getStyle("A$detail_table_header_row:AB$detail_table_header_row")->getFont()->setBold(true);

            if (is_iterable($surat_PengantarData)) {
                $gajisupir = 0;
                foreach ($surat_PengantarData as $response_index => $response_detail) {

                    $response_detail->gajisupirs = number_format((float) $response_detail->gajisupir, '2', '.', ',');

                    $tglTrip = $response_detail->tglbukti;
                    $timeStamp = strtotime($tglTrip);
                    $datetglTrip = date('d-m-Y', $timeStamp);
                    $response_detail->tglbukti = $datetglTrip;

                    $tglSp = $response_detail->tglsp;
                    $timeStamp = strtotime($tglSp);
                    $datetglSp = date('d-m-Y', $timeStamp);
                    $response_detail->tglsp = $datetglSp;

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->jobtrucking);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->nobukti);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->tglbukti);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->nosp);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->tglsp);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->pelanggan_id);
                    $sheet->setCellValue("H$detail_start_row", $response_detail->keterangan);
                    $sheet->setCellValue("I$detail_start_row", $response_detail->nojob);
                    $sheet->setCellValue("J$detail_start_row", $response_detail->dari_id);
                    $sheet->setCellValue("K$detail_start_row", $response_detail->sampai_id);
                    $sheet->setCellValue("L$detail_start_row", $response_detail->penyesuaian);
                    $sheet->setCellValue("M$detail_start_row", $response_detail->agen_id);
                    $sheet->setCellValue("N$detail_start_row", $response_detail->jenisorder_id);
                    $sheet->setCellValue("O$detail_start_row", $response_detail->jarak);
                    $sheet->setCellValue("P$detail_start_row", $response_detail->nocont);
                    $sheet->setCellValue("Q$detail_start_row", $response_detail->container_id);
                    $sheet->setCellValue("R$detail_start_row", $response_detail->statuscontainer_id);
                    $sheet->setCellValue("S$detail_start_row", $response_detail->gudang);
                    $sheet->setCellValue("T$detail_start_row", $response_detail->trado_id);
                    $sheet->setCellValue("U$detail_start_row", $response_detail->supir_id);
                    $sheet->setCellValue("V$detail_start_row", $response_detail->gandengan_id);
                    $sheet->setCellValue("W$detail_start_row", $response_detail->tarif_id);
                    $sheet->setCellValue("X$detail_start_row", $response_detail->mandortrado_id);
                    $sheet->setCellValue("Y$detail_start_row", $response_detail->mandorsupir_id);
                    $sheet->setCellValue("Z$detail_start_row", $response_detail->noseal);
                    $sheet->setCellValue("AA$detail_start_row", $response_detail->totalomset);
                    $sheet->setCellValue("AB$detail_start_row", $response_detail->gajisupir);

                    $sheet->getStyle("H$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('H')->setWidth(50);

                    $sheet->getStyle("A$detail_start_row:Z$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("AA$detail_start_row")->applyFromArray($style_number);
                    $sheet->getStyle("AB$detail_start_row")->applyFromArray($style_number);

                    $sheet->getStyle("AA$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("AB$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $gajisupir += $response_detail->gajisupir;
                    $detail_start_row++;
                }
                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':Z' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':Z' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("AA$total_start_row", "=SUM(AA8:AA" . ($detail_start_row - 1) . ")")->getStyle("AA$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->setCellValue("AB$total_start_row", "=SUM(AB8:AB" . ($detail_start_row - 1) . ")")->getStyle("AB$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $sheet->getStyle("AA$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("AB$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);
            $sheet->getColumnDimension('M')->setAutoSize(true);
            $sheet->getColumnDimension('N')->setAutoSize(true);
            $sheet->getColumnDimension('O')->setAutoSize(true);
            $sheet->getColumnDimension('P')->setAutoSize(true);
            $sheet->getColumnDimension('Q')->setAutoSize(true);
            $sheet->getColumnDimension('R')->setAutoSize(true);
            $sheet->getColumnDimension('S')->setAutoSize(true);
            $sheet->getColumnDimension('T')->setAutoSize(true);
            $sheet->getColumnDimension('U')->setAutoSize(true);
            $sheet->getColumnDimension('V')->setAutoSize(true);
            $sheet->getColumnDimension('W')->setAutoSize(true);
            $sheet->getColumnDimension('X')->setAutoSize(true);
            $sheet->getColumnDimension('Y')->setAutoSize(true);
            $sheet->getColumnDimension('Z')->setAutoSize(true);
            $sheet->getColumnDimension('AA')->setAutoSize(true);
            $sheet->getColumnDimension('AB')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Surat Pengantar' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $surat_Pengantar
            ]);
        }
    }

    public function addrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keterangan_detail.*' => 'required',
            'nominal.*' => 'required|numeric',
            'nominalTagih.*' => 'required|numeric',

        ], [
            'keterangan_detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nominalTagih.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'keterangan_detail' => 'keterangan',
            'nominal' => 'nominal',
            'nominalTagih' => 'nominal Tagih',
            'keterangan_detail.*' => 'keterangan',
            'nominal.*' => 'nominal',
            'nominalTagih.*' => 'nominal Tagih',
        ]);
        if ($validator->fails()) {

            return response()->json([
                "message" => "The given data was invalid.",
                "errors" => $validator->messages()
            ], 422);
        }
        return true;
    }

    public function rekapcustomer(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));
        $suratPengantar = new SuratPengantar();
        return response([
            'data' => $suratPengantar->getRekapCustomer($dari, $sampai),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL BIAYA TAMBAHAN
     */
    public function approvalBiayaTambahan() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL TOLAKAM
     */
    public function approvalTolakan(ApprovalTolakanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'nobukti' => $request->nobuktitrans,
                'statustolakan' => $request->statustolakan,
                'nominalperalihan' => $request->nominalperalihantolakan,
                'persentaseperalihan' => $request->persentaseperalihantolakan,

            ];
            $suratPengantar = (new SuratPengantar())->processTolakan($data);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $suratPengantar
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getTolakan($id)
    {
        return response([
            'status' => true,
            'data' => (new SuratPengantar())->getTolakan($id),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL BIAYA EXTRA
     */
    public function approvalBiayaExtra(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'nobukti' => $request->Id,
            ];
            (new SuratPengantar())->approvalBiayaExtra($data);

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
     * @Keterangan EDIT SP
     */
    public function editSP(Request $request)
    {

        DB::beginTransaction();
        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                'id' => $requestData['id'],
                'nosp' => $requestData['nosp'],
                'nocont' => $requestData['nocont'],
                'nocont2' => $requestData['nocont2'],
            ];
            (new SuratPengantar())->editSP($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getEditSp($id)
    {
        $suratpengantar = new SuratPengantar();
        return response([
            'data' => $suratpengantar->getEditSp($id),
            'attributes' => [
                'totalRows' => $suratpengantar->totalRows,
                'totalPages' => $suratpengantar->totalPages
            ]
        ]);
    }
}
