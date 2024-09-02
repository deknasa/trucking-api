<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\KasGantungHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranDetail;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\Bank;
use App\Models\AlatBayar;
use App\Models\AkunPusat;
use App\Models\LogTrail;

use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ApprovalValidasiApprovalRequest;
use App\Http\Requests\EditingByRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePengeluaranDetailRequest;
use App\Models\Error;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\PengeluaranPenerima;
use DateTime;
use Exception;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PengeluaranHeaderController extends Controller
{

    /**
     * @ClassName 
     * pengeluaranheadercontainer
     * @Detail PengeluaranDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pengeluaran = new PengeluaranHeader();

        return response([
            'data' => $pengeluaran->get(),
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages
            ]
        ]);
    }


    public function default()
    {


        $pengeluaranheader = new PengeluaranHeader();
        return response([
            'status' => true,
            'data' => $pengeluaranheader->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranHeaderRequest $request)
    {

        DB::beginTransaction();

        try {
            $pengeluaranHeader = (new PengeluaranHeader())->processStore([
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "postingdari" => $request->postingdari,
                "statusapproval" => $request->statusapproval,
                "dibayarke" => $request->dibayarke,
                "penerima_id" => $request->penerima_id,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "penerimaan_nobukti" => $request->nobukti_penerimaan,
                "statusformat" => $request->statusformat,
                "nominal_detail" => $request->nominal_detail,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                // "coakredit"=>$request->coakredit,
                "keterangan_detail" => $request->keterangan_detail,
                "noinvoice" => $request->noinvoice,
                "bank_detail" => $request->bank_detail,
                "manual" => true,
            ]);
            if ($request->button == 'btnSubmit') {
                $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
                if ($request->limit == 0) {
                    $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
                } else {
                    $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
                }
                $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = PengeluaranHeader::findAll($id);
        $detail = PengeluaranDetail::findAll($id);
        $detailpenerima = PengeluaranPenerima::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail,
            'detailpenerima' => $detailpenerima
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranheader)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranheader, [
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "postingdari" => $request->postingdari,
                "statusapproval" => $request->statusapproval,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "penerima_id" => $request->penerima_id,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "penerimaan_nobukti" => $request->nobukti_penerimaan,
                "statusformat" => $request->statusformat,
                "nominal_detail" => $request->nominal_detail,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                // "coakredit"=>$request->coakredit,
                "keterangan_detail" => $request->keterangan_detail,
                "noinvoice" => $request->noinvoice,
                "bank_detail" => $request->bank_detail,
                "manual" => true,
            ]);
            /* Set position and page */
            $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
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
    public function destroy(DestroyPengeluaranHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranHeader = (new PengeluaranHeader())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable(), true);
            $pengeluaranHeader->position = $selected->position;
            $pengeluaranHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranHeader
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
    public function approval(ApprovalValidasiApprovalRequest $request)
    {
        // dd('a');

        DB::beginTransaction();

        try {
            if ($request->pengeluaranId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->pengeluaranId); $i++) {
                    $pengeluaranHeader = PengeluaranHeader::find($request->pengeluaranId[$i]);
                    if ($pengeluaranHeader->statusapproval == $statusApproval->id) {
                        $pengeluaranHeader->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $pengeluaranHeader->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $pengeluaranHeader->tglapproval = date('Y-m-d', time());
                    $pengeluaranHeader->userapproval = auth('api')->user()->name;

                    if ($pengeluaranHeader->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                            'postingdari' => 'APPROVAL PENGELUARAN KAS/BANK',
                            'idtrans' => $pengeluaranHeader->id,
                            'nobuktitrans' => $pengeluaranHeader->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $pengeluaranHeader->toArray(),
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
                        'penerimaan' => "PENGELUARAN $query->keterangan"
                    ],
                    'message' => "PENGELUARAN $query->keterangan",
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
            $pengeluaranHeader = PengeluaranHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranHeader->statuscetak = $statusSudahCetak->id;
                // $pengeluaranHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $pengeluaranHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranHeader->jumlahcetak = $pengeluaranHeader->jumlahcetak + 1;
                if ($pengeluaranHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN HEADER',
                        'idtrans' => $pengeluaranHeader->id,
                        'nobuktitrans' => $pengeluaranHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranHeader->toArray(),
                        'modifiedby' => $pengeluaranHeader->modifiedby
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
    public function editCoa(UpdatePengeluaranDetailRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pengeluaran = PengeluaranHeader::findOrFail($id);
            /* Store header */
            $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaran, [
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "postingdari" => $request->postingdari,
                "statusapproval" => $request->statusapproval,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "penerimaan_nobukti" => $request->nobukti_penerimaan,
                "statusformat" => $request->statusformat,
                "nominal_detail" => $request->nominal_detail,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                // "coakredit"=>$request->coakredit,
                "keterangan_detail" => $request->keterangan_detail,
                "noinvoice" => $request->noinvoice,
                "bank_detail" => $request->bank_detail,
            ]);
            /* Set position and page */
            $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }


    public function cekvalidasi($id)
    {
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $pengeluaran = PengeluaranHeader::find($id);

        if (!isset($pengeluaran)) {
            $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
            $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        }

        $nobukti = $pengeluaran->nobukti ?? '';
        // $cekdata = $pengeluaran->cekvalidasiaksi($pengeluaran->nobukti);
        $status = $pengeluaran->statusapproval ?? 0;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak ?? 0;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $aksi = request()->aksi ?? '';


        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pengeluaranheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
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
                    (new MyModel())->createLockEditing($id, 'pengeluaranheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'pengeluaranheader', $useredit);
            }

            $data = [
                'message' => '',
                'error' => false,
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function cekValidasiAksi($id)
    {
        $pengeluaranHeader = new PengeluaranHeader();
        $nobukti = PengeluaranHeader::from(DB::raw("pengeluaranheader"))->where('id', $id)->first();
        $cekdata = $pengeluaranHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'kodeerror' => $cekdata['kodeerror'],
                'editcoa' => $cekdata['editcoa']
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('pengeluaranheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'pengeluaranheader', $useredit);
            // (new MyModel())->updateEditingBy('pengeluaranheader', $id, 'EDIT');
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'editcoa' => false
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
        $pengeluaranHeader = new PengeluaranHeader();
        $pengeluaran_Header = $pengeluaranHeader->getExport($id);

        if ($request->export == true) {

            $pengeluaranDetail = new PengeluaranDetail();
            $pengeluaran_Detail = $pengeluaranDetail->get();

            $tglBukti = $pengeluaran_Header->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $pengeluaran_Header->tglbukti = $dateTglBukti;

            if ($pengeluaran_Header->tipe_bank === 'KAS') {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaran_Header->judul);
                $sheet->setCellValue('A2', 'Laporan Pengeluaran ' . $pengeluaran_Header->bank_id);
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');

                $header_start_row = 4;
                $detail_table_header_row = 8;
                $detail_start_row = $detail_table_header_row + 1;

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
                        'label' => 'Kas',
                        'index' => 'bank_id',
                    ],
                ];

                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA PERKIRAAN',
                        'index' => 'coadebet'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan'
                    ],
                    [
                        'label' => 'NOMINAL',
                        'index' => 'nominal',
                        'format' => 'currency'
                    ]
                ];

                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_Header->{$header_column['index']});
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

                // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaran_Detail as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->coadebet);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                    // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('C')->setWidth(50);

                    $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("D$total_start_row", "=SUM(D9:D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);

                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PENGELUARAN ' . $pengeluaran_Header->bank_id . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
            } else {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $pengeluaran_Header->judul);
                $sheet->setCellValue('A2', 'Laporan pengeluaran ' . $pengeluaran_Header->bank_id);
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');

                $header_start_row = 4;
                $header_start_row_right = 4;
                $detail_table_header_row = 8;
                $detail_start_row = $detail_table_header_row + 1;

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
                    ],
                ];

                $detail_columns = [
                    [
                        'label' => 'NO',
                    ],
                    [
                        'label' => 'NAMA PERKIRAAN',
                        'index' => 'coadebet'
                    ],
                    [
                        'label' => 'BANK',
                        'index' => 'bank'
                    ],
                    [
                        'label' => 'JATUH TEMPO',
                        'index' => 'tgljatuhtempo'
                    ],
                    [
                        'label' => 'NO INVOICE',
                        'index' => 'invoice_nobukti'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan'
                    ],
                    [
                        'label' => 'NOMINAL',
                        'index' => 'nominal',
                        'format' => 'currency'
                    ]
                ];

                //LOOPING HEADER        
                foreach ($header_columns as $header_column) {
                    $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_Header->{$header_column['index']});
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

                // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->applyFromArray($styleArray);

                // LOOPING DETAIL
                $nominal = 0;
                foreach ($pengeluaran_Detail as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }

                    $tgljatuhtempo = $response_detail->tgljatuhtempo;
                    $timeStamp = strtotime($tgljatuhtempo);
                    $datetgljatuhtempo = date('d-m-Y', $timeStamp);
                    $response_detail->tgljatuhtempo = $datetgljatuhtempo;

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->coadebet);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->bank);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->tgljatuhtempo);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->invoice_nobukti);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->nominal);

                    // $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('F')->setWidth(50);

                    $sheet->getStyle("A$detail_start_row:G$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("G$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $detail_start_row++;
                }
                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':F' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':F' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("G$total_start_row", "=SUM(G9:G" . ($detail_start_row - 1) . ")")->getStyle("G$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("G$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('G')->setAutoSize(true);

                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PENGELUARAN ' . $pengeluaran_Header->bank_id . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
            }
        } else {
            return response([
                'data' => $pengeluaran_Header
            ]);
        }
        return response([
            'data' => $pengeluaranHeader->getExport($id)
        ]);
    }

    public function editingat(EditingByRequest $request) {}
}
