<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalProsesUangJalanSupirRequest;
use App\Http\Requests\DestroyPenerimaanTruckingHeaderRequest;
use App\Http\Requests\DestroyPengeluaranTruckingHeaderRequest;
use App\Http\Requests\DestroyPengembalianKasGantungHeaderRequest;
use App\Http\Requests\DestroyProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Models\ProsesUangJalanSupirHeader;
use App\Http\Requests\StoreProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\Error;
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PengembalianKasGantungHeader;
use App\Models\ProsesUangJalanSupirDetail;
use App\Models\Supir;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProsesUangJalanSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * ProsesUangJalanSupirHeader
     * @Detail ProsesUangJalanSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->get(),
            'attributes' => [
                'totalRows' => $prosesUangJalanSupir->totalRows,
                'totalPages' => $prosesUangJalanSupir->totalPages
            ]
        ]);
    }

    public function default()
    {

        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalanSupir->default(),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreProsesUangJalanSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'absensisupir' => $request->absensisupir,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
                'tgltransfer' => $request->tgltransfer,
                'keterangantransfer' => $request->keterangantransfer,
                'nilaitransfer' => $request->nilaitransfer,
                'bank_idtransfer' => $request->bank_idtransfer,
                'banktransfer' => $request->banktransfer,
                'nobukti_kasbank' => $request->nobukti_kasbank,
                'tgladjust' => $request->tgladjust,
                'nilaiadjust' => $request->nilaiadjust,
                'keteranganadjust' => $request->keteranganadjust,
                'bank_idadjust' => $request->bank_idadjust,
                'bankadjust' => $request->bankadjust,
                'penerimaan_nobukti' => $request->penerimaan_nobukti,
                'nobuktideposit' => $request->nobuktideposit,
                'tgldeposit' => $request->tgldeposit,
                'nilaideposit' => $request->nilaideposit,
                'keterangandeposit' => $request->keterangandeposit,
                'bank_iddeposit' => $request->bank_iddeposit,
                'bankdeposit' => $request->bankdeposit,
                'penerimaandeposit_nobukti' => $request->penerimaandeposit_nobukti,
                'bank_idpengembalian' => $request->bank_idpengembalian,
                'nobuktipengeluaran' => $request->nobuktipengeluaran,
                'pjt_id' => $request->pjt_id,
                'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti,
                'keteranganpinjaman' => $request->keteranganpinjaman,
                'sisa' => $request->sisa,
                'nombayar' => $request->nombayar
            ];

            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $prosesUangJalanSupir->position = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable())->position;
                if ($request->limit == 0) {
                    $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
                } else {
                    $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
                }
                $prosesUangJalanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $prosesUangJalanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $prosesUangJalanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $data = ProsesUangJalanSupirHeader::findAll($id);
        $detail = new ProsesUangJalanSupirDetail();

        return response([
            'status' => true,
            'data' => $data,
            'detail' => [
                'transfer' => $detail->findTransfer($id),
                'adjust' => $detail->adjustTransfer($id),
                'deposito' => $detail->deposito($id),
                'pengembalian' => $detail->pengembalian($id),
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateProsesUangJalanSupirHeaderRequest $request, ProsesUangJalanSupirHeader $prosesuangjalansupirheader): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'absensisupir' => $request->absensisupir,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'trado_id' => $request->trado_id,
                'tgltransfer' => $request->tgltransfer,
                'keterangantransfer' => $request->keterangantransfer,
                'nilaitransfer' => $request->nilaitransfer,
                'bank_idtransfer' => $request->bank_idtransfer,
                'banktransfer' => $request->banktransfer,
                'nobukti_kasbank' => $request->nobukti_kasbank,
                'tgladjust' => $request->tgladjust,
                'nilaiadjust' => $request->nilaiadjust,
                'keteranganadjust' => $request->keteranganadjust,
                'bank_idadjust' => $request->bank_idadjust,
                'bankadjust' => $request->bankadjust,
                'penerimaan_nobukti' => $request->penerimaan_nobukti,
                'nobuktideposit' => $request->nobuktideposit,
                'tgldeposit' => $request->tgldeposit,
                'nilaideposit' => $request->nilaideposit,
                'keterangandeposit' => $request->keterangandeposit,
                'bank_iddeposit' => $request->bank_iddeposit,
                'bankdeposit' => $request->bankdeposit,
                'penerimaandeposit_nobukti' => $request->penerimaandeposit_nobukti,
                'bank_idpengembalian' => $request->bank_idpengembalian,
                'nobuktipengeluaran' => $request->nobuktipengeluaran,
                'pjt_id' => $request->pjt_id,
                'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti,
                'keteranganpinjaman' => $request->keteranganpinjaman,
                'sisa' => $request->sisa,
                'nombayar' => $request->nombayar
            ];

            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processUpdate($prosesuangjalansupirheader, $data);
            $prosesUangJalanSupir->position = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable())->position;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            $prosesUangJalanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $prosesUangJalanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $prosesUangJalanSupir
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
    public function destroy(DestroyProsesUangJalanSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processDestroy($id, 'DELETE PROSES UANG JALAN SUPIR');
            $selected = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable(), true);
            $prosesUangJalanSupir->position = $selected->position;
            $prosesUangJalanSupir->id = $selected->id;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            $prosesUangJalanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $prosesUangJalanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $prosesUangJalanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPinjaman($supirId)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPinjaman($supirId)
        ]);
    }
    public function getPengembalian($id)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPengembalian($id)
        ]);
    }

    public function cekvalidasi($id)
    {
        $prosesUangJalan = ProsesUangJalanSupirHeader::find($id);
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($prosesUangJalan == '') {
            $keterangan = $error->cekKeteranganError('DTA') ?? '';

            $keterror = $keterangan . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'DTA',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $getDetail = DB::table("prosesuangjalansupirdetail")->from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->where('prosesuangjalansupir_id', $id)
            ->get();

        foreach ($getDetail as $row => $val) {
            if ($val->penerimaantrucking_nobukti != '') {
                $cekPenerimaan = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $val->penerimaantrucking_nobukti)
                    ->first();

                if ($cekPenerimaan != '') {
                    $penerimaan = $cekPenerimaan->penerimaan_nobukti ?? '';
                    // dd($penerimaan);
                    $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
                        ->select(
                            'a.id'
                        )
                        ->where('a.nobukti', $penerimaan)
                        ->first()->id ?? 0;
                    if ($idpenerimaan != 0) {
                        $validasipenerimaan = app(PenerimaanHeaderController::class)->cekvalidasi($idpenerimaan);
                        $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
                        if ($msg == true) {
                            return $validasipenerimaan;
                        }
                    }
                }
            }


            if ($val->pengeluarantrucking_nobukti != '') {
                $cekPengeluaran = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $val->pengeluarantrucking_nobukti)
                    ->first();

                if ($cekPengeluaran != '') {
                    $pengeluaran = $cekPengeluaran->pengeluaran_nobukti ?? '';
                    // dd($pengeluaran);
                    $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
                        ->select(
                            'a.id'
                        )
                        ->where('a.nobukti', $pengeluaran)
                        ->first()->id ?? 0;
                    if ($idpengeluaran != 0) {
                        $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
                        $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
                        if ($msg == true) {
                            return $validasipengeluaran;
                        }
                    }
                }
            }
        }

        $nobukti = $prosesUangJalan->nobukti ?? '';
        $status = $prosesUangJalan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $prosesUangJalan->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $parameter = new Parameter();

        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('prosesuangjalansupirheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $aksi = request()->aksi;
        if ($status == $statusApproval->id && ($aksi == 'EDIT' || $aksi == 'DELETE')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $prosesUangJalan->tglbukti) {
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

            $waktu = (new Parameter())->cekBatasWaktuEdit('Nota Kredit Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'prosesuangjalansupirheader', $useredit);
                }

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

            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->createLockEditing($id, 'prosesuangjalansupirheader', $useredit);
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
        $prosesUangJalan = DB::table("prosesuangjalansupirheader")->from(DB::raw("prosesuangjalansupirheader"))->where('id', $id)->first();

        $cekdata = (new ProsesUangJalanSupirHeader())->cekvalidasiaksi($prosesUangJalan->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('prosesuangjalansupirheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'prosesuangjalansupirheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $prosesUangJalanSupir = ProsesUangJalanSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($prosesUangJalanSupir->statuscetak != $statusSudahCetak->id) {
                $prosesUangJalanSupir->statuscetak = $statusSudahCetak->id;
                // $prosesUangJalanSupir->tglbukacetak = date('Y-m-d H:i:s');
                // $prosesUangJalanSupir->userbukacetak = auth('api')->user()->name;
                $prosesUangJalanSupir->jumlahcetak = $prosesUangJalanSupir->jumlahcetak + 1;
                if ($prosesUangJalanSupir->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
                        'postingdari' => 'PRINT PROSES UANG JALAN SUPIR HEADER',
                        'idtrans' => $prosesUangJalanSupir->id,
                        'nobuktitrans' => $prosesUangJalanSupir->id,
                        'aksi' => 'PRINT',
                        'datajson' => $prosesUangJalanSupir->toArray(),
                        'modifiedby' => $prosesUangJalanSupir->modifiedby
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
     * @Keterangan CETAK DATA
     */
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $prosesUangJalanSupirHeader = new ProsesUangJalanSupirHeader();
        $proses_UangJalanSupirHeader = $prosesUangJalanSupirHeader->getExport($id);

        if ($request->export == true) {

            $prosesUangJalanSupirDetail = new ProsesUangJalanSupirDetail();
            $proses_UangJalanSupirDetail = $prosesUangJalanSupirDetail->get();

            $tglBukti = $proses_UangJalanSupirHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $proses_UangJalanSupirHeader->tglbukti = $dateTglBukti;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $proses_UangJalanSupirHeader->judul);
            $sheet->setCellValue('A2', $proses_UangJalanSupirHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:F1');
            $sheet->mergeCells('A2:F2');

            $header_start_row = 4;
            $header_right_start_row = 4;
            $detail_table_header_row = 8;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tanggal Bukti',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'No Bukti Absensi Supir',
                    'index' => 'absensisupir_nobukti',
                ],
            ];
            $header_right_columns = [
                [
                    'label' => 'Supir',
                    'index' => 'supir_id',
                ],
                [
                    'label' => 'Trado',
                    'index' => 'trado_id',
                ],
                [
                    'label' => 'Uang Jalan',
                    'index' => 'nominaluangjalan',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NO BUKTI PENERIMAAN/PENGELUARAN KAS/BANK',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'BANK',
                    'index' => 'bank',
                ],
                [
                    'label' => 'STATUS PROSES',
                    'index' => 'statusproses',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'NOMINAL',
                    'index' => 'nominal',
                ]
            ];

            //LOOPING HEADER
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $proses_UangJalanSupirHeader->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                if ($header_right_column['index'] == 'nominaluangjalan') {
                    $sheet->setCellValue('E' . $header_right_start_row++, ': ' . number_format($proses_UangJalanSupirHeader->{$header_right_column['index']}, 2, ".", ","));
                } else {
                    $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $proses_UangJalanSupirHeader->{$header_right_column['index']});
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

            $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $nominal = 0;
            foreach ($proses_UangJalanSupirDetail as $response_index => $response_detail) {


                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->nobukti_proses);
                $sheet->setCellValue("C$detail_start_row", $response_detail->bank);
                $sheet->setCellValue("D$detail_start_row", $response_detail->statusproses);
                $sheet->setCellValue("E$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("F$detail_start_row", $response_detail->nominal);
                $sheet->getColumnDimension('E')->setWidth(30);

                $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("F$detail_start_row")->applyFromArray($style_number);
                $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }


            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Proses Uang Jalan Supir' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $proses_UangJalanSupirHeader
            ]);
        }


        return response([
            'data' => $prosesUangJalanSupirHeader->getExport($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalProsesUangJalanSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'prosesId' => $request->prosesId
            ];
            $prosesUangJalan = (new ProsesUangJalanSupirHeader())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
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
}
