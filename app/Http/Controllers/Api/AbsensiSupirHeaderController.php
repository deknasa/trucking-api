<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\User;
use App\Models\Error;
use App\Models\MyModel;
// use App\Http\Requests\UpdateAbsensiSupirHeaderRequest;
// use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\AbsensiSupirDetail;
use App\Models\AbsensiSupirHeader;
use App\Models\MandorAbsensiSupir;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Models\AbsensiSupirApprovalHeader;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\AbsensiSupirHeaderRequest;
use App\Http\Requests\ApprovalAbsensiFinalRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;


use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;

use App\Http\Requests\ApprovalValidasiApprovalRequest;
use App\Http\Controllers\Api\PengeluaranHeaderController;
use App\Http\Requests\ApprovalAbsensiFinalAppEditRequest;
use App\Http\Requests\ApprovalPengajuanTripInapAbsensiRequest;

class AbsensiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * AbsensiSupirHeader
     * @Detail AbsensiSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();
        $absensiSupirHeader->returnUnApprovalEdit();

        return response([
            'data' => $absensiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirHeader->totalRows,
                'totalPages' => $absensiSupirHeader->totalPages
            ]
        ]);
    }
    public function getStatusJeniskendaraan()
    {

        return response([
            'activeKolomJenisKendaraan' => (new MandorAbsensiSupir)->activeKolomJenisKendaraan(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan APPROVAL EDIT ABSENSI
     */
    public function approvalEditAbsensi(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'Id' => [
                    'required',
                ],
            ],
            [
                'Id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WP')->keterangan,
            ],
            [
                'Id' => 'Absensi',
            ],
        );
        if (!$validator->passes()) {
            return response([
                'error' => true,
                'errors' => $validator->messages()
            ], 422);
        }
        $data = [
            'Id' => $request->Id,
        ];
        (new AbsensiSupirHeader())->processApprovalEditAbsensi($data);

        DB::commit();
        return response([
            'message' => 'Berhasil'
        ]);
        // DB::beginTransaction();
        // try {
        //     $absensiSupirHeader = AbsensiSupirHeader::lockForUpdate()->findOrFail($id);

        //     $statusBolehEdit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT ABSENSI')->where('text', '=', 'BOLEH EDIT ABSENSI')->first();
        //     $statusTidakBolehEdit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT ABSENSI')->where('text', '=', 'TIDAK BOLEH EDIT ABSENSI')->first();
        //     // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
        //     if ($absensiSupirHeader->statusapprovaleditabsensi == $statusBolehEdit->id) {
        //         $absensiSupirHeader->statusapprovaleditabsensi = $statusTidakBolehEdit->id;
        //         $absensiSupirHeader->tglapprovaleditabsensi = date('Y-m-d', strtotime("1900-01-01"));
        //         $absensiSupirHeader->userapprovaleditabsensi = '';
        //         $absensiSupirHeader->tglbataseditabsensi = null;
        //         $absensiSupirHeader->tglbataseditabsensiadmin = null;
        //         $aksi = $statusTidakBolehEdit->text;
        //     } else {
        //         $jam_batas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'JAMBATASAPPROVAL')->where('subgrp', 'JAMBATASAPPROVAL')->first()->text ?? '23:59:59';
        //         $tglbtas = (new AbsensiSupirHeader())->getTomorrowDate();
        //         $tglbtas = date("Y-m-d H:i:s", strtotime($tglbtas .' '. $jam_batas));
        //         $absensiSupirHeader->tglbataseditabsensi = $tglbtas;
        //         $absensiSupirHeader->tglbataseditabsensiadmin = $tglbtas;
        //         $absensiSupirHeader->statusapprovaleditabsensi = $statusBolehEdit->id;
        //         $aksi = $statusBolehEdit->text;
        //         $absensiSupirHeader->tglapprovaleditabsensi = date("Y-m-d", strtotime('today'));
        //         $absensiSupirHeader->userapprovaleditabsensi = auth('api')->user()->name;
        //     }


        //     if ($absensiSupirHeader->save()) {
        //         $logTrail = [
        //             'namatabel' => strtoupper($absensiSupirHeader->getTable()),
        //             'postingdari' => 'APPROVED EDIT ABSENSI SUPIR',
        //             'idtrans' => $absensiSupirHeader->id,
        //             'nobuktitrans' => $absensiSupirHeader->id,
        //             'aksi' => $aksi,
        //             'datajson' => $absensiSupirHeader->toArray(),
        //             'modifiedby' => auth('api')->user()->name
        //         ];

        //         $validatedLogTrail = new StoreLogTrailRequest($logTrail);
        //         $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

        //         DB::commit();
        //     }

        //     return response([
        //         'message' => 'Berhasil'
        //     ]);
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     throw $th;
        // }
    }
    public function approvalEditAbsensiOld(ApprovalAbsensiFinalAppEditRequest $request, $id)
    {

        dd($request->all());
        DB::beginTransaction();
        try {
            $absensiSupirHeader = AbsensiSupirHeader::lockForUpdate()->findOrFail($id);

            $statusBolehEdit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT ABSENSI')->where('text', '=', 'BOLEH EDIT ABSENSI')->first();
            $statusTidakBolehEdit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT ABSENSI')->where('text', '=', 'TIDAK BOLEH EDIT ABSENSI')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($absensiSupirHeader->statusapprovaleditabsensi == $statusBolehEdit->id) {
                $absensiSupirHeader->statusapprovaleditabsensi = $statusTidakBolehEdit->id;
                $absensiSupirHeader->tglapprovaleditabsensi = date('Y-m-d', strtotime("1900-01-01"));
                $absensiSupirHeader->userapprovaleditabsensi = '';
                $absensiSupirHeader->tglbataseditabsensi = null;
                $absensiSupirHeader->tglbataseditabsensiadmin = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $jam_batas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'JAMBATASAPPROVAL')->where('subgrp', 'JAMBATASAPPROVAL')->first()->text ?? '23:59:59';
                $tglbtas = (new AbsensiSupirHeader())->getTomorrowDate();
                $tglbtas = date("Y-m-d H:i:s", strtotime($tglbtas . ' ' . $jam_batas));
                $absensiSupirHeader->tglbataseditabsensi = $tglbtas;
                $absensiSupirHeader->tglbataseditabsensiadmin = $tglbtas;
                $absensiSupirHeader->statusapprovaleditabsensi = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
                $absensiSupirHeader->tglapprovaleditabsensi = date("Y-m-d", strtotime('today'));
                $absensiSupirHeader->userapprovaleditabsensi = auth('api')->user()->name;
            }


            if ($absensiSupirHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'APPROVED EDIT ABSENSI SUPIR',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

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
     * @Keterangan APPROVAL PENGAJUAN TRIP INAP
     */
    public function approvalTripInap(ApprovalPengajuanTripInapAbsensiRequest $request)
    {
        DB::beginTransaction();
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $id = $request->id[$i];
                $absensiSupirHeader = AbsensiSupirHeader::lockForUpdate()->findOrFail($id);

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusTidakApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
                // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
                if ($absensiSupirHeader->statusapprovalpengajuantripinap == $statusApproval->id) {
                    $absensiSupirHeader->statusapprovalpengajuantripinap = $statusTidakApproval->id;
                    $absensiSupirHeader->userapprovalpengajuantripinap = '';
                    $absensiSupirHeader->tglapprovalpengajuantripinap = date('Y-m-d', strtotime("1900-01-01"));
                    $absensiSupirHeader->tglbataspengajuantripinap = null;
                    $aksi = $statusTidakApproval->text;
                } else {
                    $tglbtas = date("Y-m-d", strtotime('today'));
                    $tglbtas = date("Y-m-d H:i:s", strtotime($tglbtas . ' 23:59:00'));
                    $absensiSupirHeader->statusapprovalpengajuantripinap = $statusApproval->id;
                    $absensiSupirHeader->userapprovalpengajuantripinap = auth('api')->user()->name;
                    $absensiSupirHeader->tglapprovalpengajuantripinap = date("Y-m-d", strtotime('today'));
                    $absensiSupirHeader->tglbataspengajuantripinap = $tglbtas;
                    $aksi = $statusApproval->text;
                }

                if ($absensiSupirHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                        'postingdari' => 'APPROVED PENGAJUAN TRIP INAP',
                        'idtrans' => $absensiSupirHeader->id,
                        'nobuktitrans' => $absensiSupirHeader->id,
                        'aksi' => $aksi,
                        'datajson' => $absensiSupirHeader->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                }
            }

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



    public function default()
    {

        $absensisupirdetail = new AbsensiSupirDetail();

        return response([
            'status' => true,
            'detail' => $absensisupirdetail->getAll(0),
        ]);
    }

    public function show($id)
    {
        $data = AbsensiSupirHeader::findAll($id);
        $detail = (new AbsensiSupirDetail())->getAll($id);
        $mandorabsensisupir = (new MandorAbsensiSupir());


        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail,
            "attributes" => [
                'defaultJenis' => $mandorabsensisupir->defaultJenis(),
            ]
        ]);
    }

    public function detail($id)
    {
        return response([
            'data' => AbsensiSupirDetail::with('trado', 'supir', 'absenTrado')->where('absensi_id', $id)->get()
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan SIMPAN DATA
     */
    public function store(AbsensiSupirHeaderRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" =>  $request->tglbukti,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "supir_id" => $request->supir_id,
                "supirold_id" => $request->supirold_id,
                "supir" => $request->supir,
                "keterangan_detail" => $request->keterangan_detail,
                "absen_id" => $request->absen_id,
                "statusjeniskendaraan" => $request->statusjeniskendaraan,
                "absen" => null,
                "jam" => $request->jam,
                "uangjalan" => $request->uangjalan,
                'tglbataseditabsensi' => $request->tglbataseditabsensi,
            ];


            /* Store header */
            $absensiSupirHeader = (new absensiSupirHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / (10));
            } else {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
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
    public function update(AbsensiSupirHeaderRequest $request, AbsensiSupirHeader $absensiSupirHeader)
    {
        DB::beginTransaction();
        try {
            $data = [
                "nobukti" =>  $request->nobukti,
                "tglbukti" =>  $request->tglbukti,
                "trado_id" => $request->trado_id,
                "trado" => $request->trado,
                "supir_id" => $request->supir_id,
                "supirold_id" => $request->supir_id_old,
                "supir" => $request->supir,
                "keterangan_detail" => $request->keterangan_detail,
                "absen_id" => $request->absen_id,
                "statusjeniskendaraan" => $request->statusjeniskendaraan,
                "absen" => null,
                "jam" => $request->jam,
                "uangjalan" => $request->uangjalan,
                'tglbataseditabsensi' => $request->tglbataseditabsensi,

            ];


            /* Store header */
            $absensiSupirHeader = (new absensiSupirHeader())->processUpdate($absensiSupirHeader, $data);
            /* Set position and page */
            $absensiSupirHeader->position = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / (10));
            } else {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(AbsensiSupirHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($absensiSupirHeader);
            $absensiSupirHeader = (new AbsensiSupirHeader())->processDestroy($id);
            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable(), true);
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->id = $selected->id;
            if ($request->limit == 0) {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / (10));
            } else {
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
            }
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function storeKasGantung($kasGantungHeader, $kasGantungDetail)
    {
        try {


            $kasGantung = new StoreKasGantungHeaderRequest($kasGantungHeader);
            $header = app(KasGantungHeaderController::class)->store($kasGantung);

            $nobukti = $kasGantungHeader['nobukti'];
            $detailLog = [];
            foreach ($kasGantungDetail as $value) {

                $value['kasgantung_id'] = $header->original['data']['id'];
                $value['pengeluaran_nobukti'] = $header->original['data']['pengeluaran_nobukti'];
                $kasGantungDetail = new StoreKasGantungDetailRequest($value);
                $datadetails = app(KasGantungDetailController::class)->store($kasGantungDetail);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY ABSENSI SUPIR',
                'idtrans' =>  $header->original['idlogtrail'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function approval($id)
    {
        return $id;
    }
    public function cekvalidasidelete($id)
    {
        $absensisupir = AbsensiSupirHeader::findOrFail($id);
        $nobukti = $absensisupir->nobukti ?? '';
        $passes = true;
        $keterangan = [];
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        //validasi sudah dipakai di orderantrucking / sp
        $isUsedTrip = AbsensiSupirHeader::isUsedTrip($absensisupir->id);
        if ($isUsedTrip) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
            // // $keterangan = $query['0'];
            // $keterangan = ['keterangan' => $query['0']->keterangan]; //$query['0'];
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',
            ];
            $passes = false;
            return response($data);
        }
        $data = [
            'error' => false,
            'message' => '',
            'statuspesan' => 'success',
        ];

        return response($data);
    }

    public function cekvalidasi($id)
    {
        $absensisupir = AbsensiSupirHeader::findOrFail($id);
        $nobukti = $absensisupir->nobukti ?? '';

        $aksi = request()->aksi ?? '';
        $passes = true;
        $keterangan = [];

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $useredit = $absensisupir->editing_by ?? '';

        $cekgajisupiruangjalan = DB::table("gajisupiruangjalan")->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->where('absensisupir_nobukti', $nobukti)
            ->first();

        if ($cekgajisupiruangjalan != '' && $aksi == 'DELETE') {

            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' no bukti gaji supir <b>' . $cekgajisupiruangjalan->gajisupir_nobukti . '</b> <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $cekAbsensiApproval = DB::table("absensisupirapprovalheader")->from(DB::raw("absensisupirapprovalheader with (readuncommitted)"))
            ->where('absensisupir_nobukti', $nobukti)
            ->first();
        if ($cekAbsensiApproval != '') {

            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' no bukti approval <b>' . $cekAbsensiApproval->nobukti . '</b> <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }
        
        $isUsedTrip = AbsensiSupirHeader::isUsedTrip($absensisupir->id);
        // dd($absensisupir,$absensisupir->nominal);
        if ($aksi == 'DELETE') {
            if ($isUsedTrip  || ($absensisupir->nominal > 0)) {
                $keteranganerror = $error->cekKeteranganError('DTSA') ?? '';
                $keterror = 'No Bukti <b>' . $absensisupir->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'DTSA',
                    'statuspesan' => 'warning',
                ];
                return response($data);
            }
        }

        if ($aksi == 'PRINTER BESAR' || $aksi == 'PRINTER KECIL') {
            //validasi cetak
            $printValidation = AbsensiSupirHeader::printValidation($id);
            if (!$printValidation) {
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDC',
                    'statuspesan' => 'warning',
                ];
                $passes = false;
            } else {
                $data = [
                    'message' => '',
                    'errors' => 'success',
                    'kodestatus' => '0',
                    'kodenobukti' => '1'
                ];
            }
            return response($data);
        } else if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('ABSENSI SUPIR BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($absensisupir->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                // if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('absensisupirheader', $id, $aksi);
                // }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {

            $isDateAllowed = AbsensiSupirHeader::isDateAllowed($id);
            if (!$isDateAllowed) {
                $keteranganerror = $error->cekKeteranganError('TEPT') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'TEPT',
                    'statuspesan' => 'warning',
                ];
                $passes = false;
                // return response($data);
            }

            //validasi status edit
            $passes = true;
            $isEditAble = AbsensiSupirHeader::isEditAble($id);
            if (!$isEditAble) {
                $keteranganerror = $error->cekKeteranganError('BAED') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'BAED',
                    'statuspesan' => 'warning',
                ];
                $passes = false;
                // return response($data);
            }

            //validasi cetak
            $printValidation = AbsensiSupirHeader::printValidation($id);
            if (!$printValidation) {
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDC',
                    'statuspesan' => 'warning',
                ];
                $passes = false;

                // return response($data);
            }

            //validasi Hari ini
            $todayValidation = (new AbsensiSupirHeader())->todayValidation($absensisupir->tglbukti);
            // dd($todayValidation);
            if (!$todayValidation) {
                $jam_batas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'JAMBATASAPPROVAL')->where('subgrp', 'JAMBATASAPPROVAL')->first();
                $batas = date('d-m-Y', strtotime($absensisupir->tglbukti));

                $keteranganerror = $error->cekKeteranganError('SLBE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' ( ' . $batas . ' ' . $jam_batas->text . ' ) <br> ' . $keterangantambahanerror;

                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL2')->get();
                // $keterangan = $query['0'];
                // $keterangan = ['keterangan' => 'transaksi Sudah beda tanggal']; //$query['0'];
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SLBE',
                    'statuspesan' => 'warning',
                ];
                $passes = false;
                // return response($data);
            }

            //validasi approval
            $isApproved = AbsensiSupirHeader::isApproved($absensisupir->nobukti);

            if (!$isApproved) {
                $error = new Error();
                $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
                $parameter = new Parameter();
                $absensisupirapproval = db::table('absensisupirapprovalheader')->from(db::raw("absensisupirapprovalheader a with (readuncommitted)"))
                    ->select(
                        'a.pengeluaran_nobukti',
                        'a.nobukti'
                    )
                    ->where('a.absensisupir_nobukti', $absensisupir->nobukti)
                    ->first();
                $nobukti = $absensisupirapproval->nobukti ?? '';
                $pengeluaran = $absensisupirapproval->pengeluaran_nobukti ?? '';

                $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.nobukti', $pengeluaran)
                    ->first()->id ?? 0;
                // $aksi = request()->aksi ?? '';
                if ($idpengeluaran != 0) {
                    $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
                    $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
                    if ($msg == false) {
                        goto lanjut;
                    } else {
                        return $validasipengeluaran;
                    }

                    lanjut:
                    // dd('test');
                    $keteranganerror = $error->cekKeteranganError('SDP') ?? '';
                    $keterror = 'No Bukti <b>' . $absensisupir->nobukti . '</b><br>' . $keteranganerror . ' No Bukti <b>' . $pengeluaran . '</b> <br> ' . $keterangantambahanerror;

                    // $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
                    // $keterangan = $query['0'];

                    // dd($keterangan);
                    // $keterangan = ['keterangan' => 'transaksi Sudah di approved']; //$query['0'];

                    $data = [
                        'error' => true,
                        'message' => $keterror,
                        'kodeerror' => 'SDP',
                        'statuspesan' => 'warning',
                    ];
                    $passes = false;
                    return response($data);
                }

                // return response($data);
            }
            if ($tgltutup >= $absensisupir->tglbukti) {
                $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'TUTUPBUKU',
                    'statuspesan' => 'warning',
                ];
            }

            if (($todayValidation && $isApproved) || ($isEditAble && $printValidation) || $isDateAllowed) {
                // dd($aksi);
                // if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('absensisupirheader', $id, $aksi);
                // }                
                $data = [
                    'error' => false,
                    'message' => '',
                    'statuspesan' => 'success',
                ];
                return response($data);
            }

            return response($data);
        }
    }
    /**
     * @ClassName 
     * @Keterangan CEK ABSENSI
     */
    public function cekabsensi(Request $request, $id)
    {
        // return $request;
        $absensiSupirDetail = new AbsensiSupirDetail();
        $absensiSupirHeader = new AbsensiSupirHeader();

        return response([
            'status' => true,
            'data' => $absensiSupirHeader->findAll($id),
            'detail' => $absensiSupirDetail->get(),
            'absenTrado' => $absensiSupirHeader->getTradoAbsensi($id),
        ]);
    }

    public function cekValidasiAksi($id)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();
        $nobukti = AbsensiSupirHeader::from(DB::raw("absensisupirheader"))->where('id', $id)->first();
        $cekdata = $absensiSupirHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL')
            //     ->get();
            // $keterangan = $query['0'];

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            (new MyModel())->updateEditingBy('absensisupirheader', $id, 'EDIT');
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absensisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $absensiSupirHeader = new AbsensiSupirHeader();
        return response([
            'data' => $absensiSupirHeader->getExport($id)
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
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $absensiSupirHeader = AbsensiSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($absensiSupirHeader->statuscetak != $statusSudahCetak->id) {
                $absensiSupirHeader->statuscetak = $statusSudahCetak->id;
                // $absensiSupirHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $absensiSupirHeader->userbukacetak = auth('api')->user()->name;
                $absensiSupirHeader->jumlahcetak = $absensiSupirHeader->jumlahcetak + 1;
                if ($absensiSupirHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                        'postingdari' => 'PRINT ABSENSI SUPIR HEADER',
                        'idtrans' => $absensiSupirHeader->id,
                        'nobuktitrans' => $absensiSupirHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $absensiSupirHeader->toArray(),
                        'modifiedby' => $absensiSupirHeader->modifiedby
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    DB::commit();
                }
            }
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL FINAL ABSENSI
     */

    public function approvalfinalabsensi(ApprovalValidasiApprovalRequest $request)
    {

        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id
            ];
            $absensisupirheader = (new AbsensiSupirHeader())->processapprovalfinalabsensi($data);

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensisupirheader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
