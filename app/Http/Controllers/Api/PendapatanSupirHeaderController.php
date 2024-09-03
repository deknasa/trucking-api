<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPendapatanSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\getPinjamanPendapatanSupirRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePendapatanSupirDetailRequest;
use App\Models\PendapatanSupirHeader;
use App\Http\Requests\StorePendapatanSupirHeaderRequest;
use App\Http\Requests\UpdatePendapatanSupirHeaderRequest;
use App\Models\Error;
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\PendapatanSupirDetail;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PendapatanSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * PendapatanSupirHeader
     * @Detail PendapatanSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pendapatanSupir = new PendapatanSupirHeader();

        return response([
            'data' => $pendapatanSupir->get(),
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages
            ]
        ]);
    }
    public function default()
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'status' => true,
            'data' => $pendapatanSupir->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePendapatanSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $requestData = json_decode($request->detail, true);
            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "tglbukti" => $request->tglbukti,
                "bank_id" => $request->bank_id,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                'id_detail' => $requestData['id_detail'],
                'nobukti_trip' => $requestData['nobukti_trip'],
                'nobukti_ric' => $requestData['nobukti_ric'],
                'dari_id' => $requestData['dari_id'],
                'sampai_id' => $requestData['sampai_id'],
                'nominal_detail' => $requestData['nominal_detail'],
                'gajikenek' => $requestData['gajikenek'],
                'supirtrip' => $requestData['supirtrip'],
                "nominal_depo" => $request->nominal_depo,
                "keterangan_depo" => $request->keterangan_depo,
                "supir_depo" => $request->supir_depo,
                "pinj_supir" => $request->pinj_supir,
                "pinj_nominal" => $request->pinj_nominal,
                "pinj_keterangan" => $request->pinj_keterangan,
                "pinj_nobukti" => $request->pinj_nobukti,
                "pinj_id" => $request->pinj_id,
                // "periode" => $request->periode,
            ];

            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
                if ($request->limit == 0) {
                    $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / (10));
                } else {
                    $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));
                }
                $pendapatanSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $pendapatanSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $pendapatanSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function show($id)
    {
        // dd('test');

        $data = (new PendapatanSupirHeader())->findUpdate($id);

        $supir_id = ($data->supir_id == '') ? 0 : $data->supir_id;

        $formatTab = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'TAB KOMISI')
            ->first()->text;
        if ($formatTab == 'FORMAT 1') {
            $detail = (new PendapatanSupirHeader())->getTrip($data->tgldari, $data->tglsampai, $supir_id, $id, 'show');
        } else if ($formatTab == 'FORMAT 3') {
            $detail = (new PendapatanSupirHeader())->gettrip3($data->tgldari, $data->tglsampai, $supir_id, $id, 'show');
        } else {
            $detail = (new PendapatanSupirHeader())->getTrip2($data->tgldari, $data->tglsampai, $supir_id, $id, 'show');
        }
        return response([
            'data' => $data,
            'detail' => $detail,
            'pjp' => (new PendapatanSupirHeader())->getNobuktiPJP($data->nobukti),
            'dpo' => (new PendapatanSupirHeader())->getNobuktiDPO($data->nobukti),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePendapatanSupirHeaderRequest $request, PendapatanSupirHeader $pendapatanSupirHeader): JsonResponse
    {
        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "tglbukti" => $request->tglbukti,
                "bank_id" => $request->bank_id,
                "supir_id" => $request->supir_id,
                "supir" => $request->supir,
                'id_detail' => $requestData['id_detail'],
                'nobukti_trip' => $requestData['nobukti_trip'],
                'nobukti_ric' => $requestData['nobukti_ric'],
                'dari_id' => $requestData['dari_id'],
                'sampai_id' => $requestData['sampai_id'],
                'nominal_detail' => $requestData['nominal_detail'],
                'gajikenek' => $requestData['gajikenek'],
                'supirtrip' => $requestData['supirtrip'],
                "nominal_depo" => $request->nominal_depo,
                "keterangan_depo" => $request->keterangan_depo,
                "supir_depo" => $request->supir_depo,
                "pinj_supir" => $request->pinj_supir,
                "pinj_nominal" => $request->pinj_nominal,
                "pinj_keterangan" => $request->pinj_keterangan,
                "pinj_nobukti" => $request->pinj_nobukti,
                "pinj_id" => $request->pinj_id,
                // "periode" => $request->periode,

            ];



            $pendapatanSupirHeader = (new PendapatanSupirHeader())->processUpdate($pendapatanSupirHeader, $data);
            $pendapatanSupirHeader->position = $this->getPosition($pendapatanSupirHeader, $pendapatanSupirHeader->getTable())->position;
            if ($request->limit == 0) {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / (10));
            } else {
                $pendapatanSupirHeader->page = ceil($pendapatanSupirHeader->position / ($request->limit ?? 10));
            }
            $pendapatanSupirHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pendapatanSupirHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $pendapatanSupirHeader
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
    public function destroy(DestroyPendapatanSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pendapatanSupir = (new PendapatanSupirHeader())->processDestroy($id, 'DELETE PENDAPATAN SUPIR');
            $selected = $this->getPosition($pendapatanSupir, $pendapatanSupir->getTable(), true);
            $pendapatanSupir->position = $selected->position;
            $pendapatanSupir->id = $selected->id;
            if ($request->limit == 0) {
                $pendapatanSupir->page = ceil($pendapatanSupir->position / (10));
            } else {
                $pendapatanSupir->page = ceil($pendapatanSupir->position / ($request->limit ?? 10));
            }
            $pendapatanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pendapatanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pendapatanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }



    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->pendapatanId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->pendapatanId); $i++) {
                    $pendapatanSupir = PendapatanSupirHeader::find($request->pendapatanId[$i]);
                    if ($pendapatanSupir->statusapproval == $statusApproval->id) {
                        $pendapatanSupir->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $pendapatanSupir->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $pendapatanSupir->tglapproval = date('Y-m-d', time());
                    $pendapatanSupir->userapproval = auth('api')->user()->name;

                    if ($pendapatanSupir->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($pendapatanSupir->getTable()),
                            'postingdari' => 'APPROVAL PENDAPATAN SUPIR',
                            'idtrans' => $pendapatanSupir->id,
                            'nobuktitrans' => $pendapatanSupir->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $pendapatanSupir->toArray(),
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
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'penerimaan' => "PENDAPATAN SUPIR $query->keterangan"
                    ],
                    'message' => "PENDAPATAN SUPIR $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pendapatanSupirHeader = PendapatanSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pendapatanSupirHeader->statuscetak != $statusSudahCetak->id) {
                $pendapatanSupirHeader->statuscetak = $statusSudahCetak->id;
                // $pendapatanSupirHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $pendapatanSupirHeader->userbukacetak = auth('api')->user()->name;
                $pendapatanSupirHeader->jumlahcetak = $pendapatanSupirHeader->jumlahcetak + 1;
                if ($pendapatanSupirHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
                        'postingdari' => 'PRINT PENDAPATAN SUPIR HEADER',
                        'idtrans' => $pendapatanSupirHeader->id,
                        'nobuktitrans' => $pendapatanSupirHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pendapatanSupirHeader->toArray(),
                        'modifiedby' => $pendapatanSupirHeader->modifiedby
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

    public function cekvalidasi($id)
    {
        $pendapatan = PendapatanSupirHeader::find($id);
        $nobukti = $pendapatan->nobukti ?? '';
        $status = $pendapatan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pendapatan->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';


        $pengeluaran = $pendapatan->pengeluaran_nobukti ?? '';
        // dd($pengeluaran);
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
        }

        lanjut:

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pendapatansupirheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' =>  $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' =>  $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pendapatan->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' )';
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('PENGELUARAN KAS/BANK BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    // (new MyModel())->updateEditingBy('pengeluaranheader', $id, $aksi);
                    (new MyModel())->createLockEditing($id, 'pendapatansupirheader', $useredit);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {
                // $cekEnableForceEdit = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                // ->where('grp','FORCE EDIT')->first()->text ?? 'TIDAK';
                // $force = ($cekEnableForceEdit == 'YA') ? true : false;

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                    // 'force' => $force
                ];

                return response($data);
            }
        } else {

            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                // (new MyModel())->updateEditingBy('pengeluaranheader', $id, $aksi);
                (new MyModel())->createLockEditing($id, 'pendapatansupirheader', $useredit);
            }
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function cekValidasiAksi($id)
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        $nobukti = PendapatanSupirHeader::from(DB::raw("pendapatansupirheader"))->where('id', $id)->first();
        $cekdata = $pendapatanSupir->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('pendapatansupirheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'pendapatansupirheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $pendapatanSupirHeader = new PendapatanSupirHeader();
        $pendapatan_SupirHeader = $pendapatanSupirHeader->getExport($id);

        if ($request->export == true) {
            $grp = $request->grp;
            $subgrp = $request->subgrp;
            $newRequest = new Request([
                'grp' => $grp,
                'subgrp' => $subgrp,
            ]);
            $parameter = new ParameterController();
            $tampilan = $parameter->getparamfirst($newRequest);
            $format = $tampilan['text'];

            if ($format == 'FORMAT 1') {
                $pendapatanSupirDetail = new PendapatanSupirDetail();
                $pendapatan_SupirDetail = $pendapatanSupirDetail->get();
            } else {
                $pendapatanSupirDetail = new PendapatanSupirDetail();
                $pendapatan_SupirDetail = $pendapatanSupirDetail->getsupir();
            }

            $tglBukti = $pendapatan_SupirHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $pendapatan_SupirHeader->tglbukti = $dateTglBukti;

            $tglDari = $pendapatan_SupirHeader->tgldari;
            $timeStampDari = strtotime($tglDari);
            $datetglDari = date('d-m-Y', $timeStampDari);
            $pendapatan_SupirHeader->tgldari = $datetglDari;

            $tglSampai = $pendapatan_SupirHeader->tglsampai;
            $timeStampSampai = strtotime($tglSampai);
            $datetglSampai = date('d-m-Y', $timeStampSampai);
            $pendapatan_SupirHeader->tglsampai = $datetglSampai;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pendapatan_SupirHeader->judul);
            $sheet->setCellValue('A2', $pendapatan_SupirHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');

            $header_start_row = 4;
            $header_right_start_row = 4;
            $detail_table_header_row = 8;
            $detail_start_row = $detail_table_header_row + 1;
            $dataRow = $detail_table_header_row + 2;
            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Bank',
                    'index' => 'bank_id',
                ]
            ];
            $header_right_columns = [
                [
                    'label' => 'Tanggal Dari',
                    'index' => 'tgldari',
                ],
                [
                    'label' => 'Tanggal Sampai',
                    'index' => 'tglsampai',
                ],
                [
                    'label' => 'Supir',
                    'index' => 'supir_id',
                ],
            ];


            if ($format == 'FORMAT 1') {
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'TRADO',
                        'index' => 'kodetrado',
                    ],
                    [
                        'label' => 'NOMINAL',
                        'index' => 'total',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TANDA TANGAN',
                    ],
                ];
            } else {
                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NO BUKTI RIC',
                        'index' => 'nobuktirincian',
                    ],
                    [
                        'label' => 'NAMA SUPIR',
                        'index' => 'namasupir',
                    ],
                    [
                        'label' => 'KOMISI',
                        'index' => 'komisi',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'DEPOSITO',
                        'index' => 'deposito',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'PENGEMBALIAN PINJAMAN',
                        'index' => 'pengembalianpinjaman',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TOTAL TERIMA',
                        'format' => 'currency'
                    ],
                    [
                        'label' => 'TANDA TANGAN',
                    ],
                ];
            }
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $pendapatan_SupirHeader->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                if ($header_right_column['index'] == 'supir_id') {
                    if ($pendapatan_SupirHeader->{$header_right_column['index']} != '') {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pendapatan_SupirHeader->{$header_right_column['index']});
                    }
                } else {
                    $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                    $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pendapatan_SupirHeader->{$header_right_column['index']});
                }
            }
            foreach ($detail_columns as $detail_columns_index => $detail_column) {
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

            if ($format == 'FORMAT 1') {

                $sheet->getStyle("A$detail_table_header_row:D" . "$detail_table_header_row")->applyFromArray($styleArray);

                foreach ($pendapatan_SupirDetail as $response_index => $response_detail) {

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->kode_trado);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("D$detail_start_row", $response_index + 1);

                    if (($response_index + 1) % 2 == 0) {
                        $sheet->getStyle("D$detail_start_row")->getAlignment()->setHorizontal('center');
                        $sheet->getStyle("D$detail_start_row")->getAlignment()->setVertical('center');
                    } else {
                        $sheet->getStyle("D$detail_start_row")->getAlignment()->setHorizontal('left');
                        $sheet->getStyle("D$detail_start_row")->getAlignment()->setVertical('center');
                    }

                    $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("C$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $spreadsheet->getActiveSheet()->getRowDimension($detail_start_row)->setRowHeight(28);
                    $dataRow++;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;

                $sheet->mergeCells('A' . $total_start_row . ':B' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("C$detail_start_row",  "=SUM(C8:C" . ($dataRow - 1) . ")")->getStyle("C$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setWidth(30);
            } else {

                $sheet->getStyle("A$detail_table_header_row:H" . "$detail_table_header_row")->applyFromArray($styleArray);

                foreach ($pendapatan_SupirDetail as $response_index => $response_detail) {

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->nobuktirincian);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->namasupir);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->komisi);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->deposito);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->pengembalianpinjaman);
                    $sheet->setCellValue('G' . $detail_start_row, '=D' . $detail_start_row . '-E' . $detail_start_row . '-F' . $detail_start_row);

                    $sheet->setCellValue("H$detail_start_row", $response_index + 1);

                    if (($response_index + 1) % 2 == 0) {
                        $sheet->getStyle("H$detail_start_row")->getAlignment()->setHorizontal('center');
                        $sheet->getStyle("H$detail_start_row")->getAlignment()->setVertical('center');
                    } else {
                        $sheet->getStyle("H$detail_start_row")->getAlignment()->setHorizontal('left');
                        $sheet->getStyle("H$detail_start_row")->getAlignment()->setVertical('center');
                    }
                    $sheet->getStyle("A$detail_start_row:H$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:G$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $spreadsheet->getActiveSheet()->getRowDimension($detail_start_row)->setRowHeight(28);

                    // $total += $response_detail['nominal'];
                    $dataRow++;
                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;

                $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':H' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("D$detail_start_row",  "=SUM(D8:D" . ($dataRow - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->setCellValue("E$detail_start_row",  "=SUM(E8:E" . ($dataRow - 1) . ")")->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->setCellValue("F$detail_start_row",  "=SUM(F8:F" . ($dataRow - 1) . ")")->getStyle("F$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->setCellValue("G$detail_start_row",  "=SUM(G8:G" . ($dataRow - 1) . ")")->getStyle("G$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $sheet->getStyle("D$detail_start_row:G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);
                $sheet->getColumnDimension('G')->setAutoSize(true);
                $sheet->getColumnDimension('H')->setWidth(22);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Pendapatan Supir' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $pendapatan_SupirHeader
            ]);
        }
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function exportsupir($id)
    {
        $pendapatanSupirHeader = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupirHeader->getExportsupir($id)
        ]);
    }


    public function gettrip(Request $request)
    {
        $tgldari  = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai  = date('Y-m-d', strtotime($request->tglsampai));
        $supir_id  = $request->supir_id;
        $id  = $request->idPendapatan;
        $aksi = $request->aksi;
        // dd('test');
        $pendapatanSupir = new PendapatanSupirHeader();
        $formatTab = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'TAB KOMISI')
            ->first()->text;
        if ($formatTab == 'FORMAT 1') {
            $data = $pendapatanSupir->getTrip($tgldari, $tglsampai, $supir_id, $id, $aksi);
        } else if ($formatTab == 'FORMAT 3') {
            $data = $pendapatanSupir->gettrip3($tgldari, $tglsampai, $supir_id, $id, $aksi);
        } else {
            $data = $pendapatanSupir->getTrip2($tgldari, $tglsampai, $supir_id, $id, $aksi);
        }
        return response([
            'data' => $data,
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages,
                'totalNominal' => $pendapatanSupir->totalNominal,
                'totalGajiKenek' => $pendapatanSupir->totalGajiKenek,
            ]
        ]);
    }

    public function getDataDeposito(Request $request)
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupir->getDataDeposito(),
        ]);
    }
    public function getPinjaman($supir_id)
    {
        $pendapatanSupir = new PendapatanSupirHeader();
        return response([
            'data' => $pendapatanSupir->getPinjaman($supir_id),
        ]);
    }
}
