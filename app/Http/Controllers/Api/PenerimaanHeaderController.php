<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalPenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\PenerimaanHeader;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\BankPelanggan;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PelunasanPiutangHeader;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePenerimaanDetailRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\Error;
use Exception;
use Illuminate\Database\QueryException;
use PhpParser\Builder\Param;
use App\Models\MyModel;
use DateTime;
use App\Http\Requests\ApprovalValidasiApprovalRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PenerimaanHeaderController extends Controller
{
    /**
     * @ClassName 
     * PenerimaanHeaderController
     * @Detail PenerimaanDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $penerimaan = new PenerimaanHeader();
        return response([
            'data' => $penerimaan->get(),
            'attributes' => [
                'totalRows' => $penerimaan->totalRows,
                'totalPages' => $penerimaan->totalPages
            ]
        ]);
    }


    public function default()
    {


        $penerimaanheader = new PenerimaanHeader();
        return response([
            'status' => true,
            'data' => $penerimaanheader->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePenerimaanHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'coakredit' => $request->coakredit,
                'keterangan_detail' => $request->keterangan_detail,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'penerimaangiro_nobukti' => $request->penerimaangiro_nobukti,
            ];
            $penerimaanHeader = (new penerimaanHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $penerimaanHeader->position = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable())->position;
                if ($request->limit == 0) {
                    $penerimaanHeader->page = ceil($penerimaanHeader->position / (10));
                } else {
                    $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
                }
                $penerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $penerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = PenerimaanHeader::findAll($id);
        $detail = PenerimaanDetail::findAll($id);
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

    public function update(UpdatePenerimaanHeaderRequest $request, PenerimaanHeader $penerimaanheader)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'coakredit' => $request->coakredit,
                'keterangan_detail' => $request->keterangan_detail,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'penerimaangiro_nobukti' => $request->penerimaangiro_nobukti,
            ];
            /* Store header */
            $penerimaanheader = (new PenerimaanHeader())->processUpdate($penerimaanheader, $data);
            /* Set position and page */
            $penerimaanheader->position = $this->getPosition($penerimaanheader, $penerimaanheader->getTable())->position;
            if ($request->limit == 0) {
                $penerimaanheader->page = ceil($penerimaanheader->position / (10));
            } else {
                $penerimaanheader->page = ceil($penerimaanheader->position / ($request->limit ?? 10));
            }
            $penerimaanheader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanheader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanheader
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
    public function destroy(DestroyPenerimaanHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = (new PenerimaanHeader())->processDestroy($id);
            $selected = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable(), true);
            $penerimaanHeader->position = $selected->position;
            $penerimaanHeader->id = $selected->id;
            if ($request->limit == 0) {
                $penerimaanHeader->page = ceil($penerimaanHeader->position / (10));
            } else {
                $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
            }
            $penerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanHeader
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
        DB::beginTransaction();

        try {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->penerimaanId); $i++) {
                $penerimaanHeader = PenerimaanHeader::find($request->penerimaanId[$i]);

                if ($penerimaanHeader->statusapproval == $statusApproval->id) {
                    $penerimaanHeader->statusapproval = $statusNonApproval->id;
                    $aksi = $statusNonApproval->text;
                } else {
                    $penerimaanHeader->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                }

                $penerimaanHeader->tglapproval = date('Y-m-d', time());
                $penerimaanHeader->userapproval = auth('api')->user()->name;

                if ($penerimaanHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanHeader->getTable()),
                        'postingdari' => 'APPROVAL PENERIMAAN KAS/BANK',
                        'idtrans' => $penerimaanHeader->id,
                        'nobuktitrans' => $penerimaanHeader->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $penerimaanHeader->toArray(),
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
            throw $th;
        }
    }

    public function bukaCetak($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = PenerimaanHeader::find($id);
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanHeader->statuscetak == $statusCetak->id) {
                $penerimaanHeader->statuscetak = $statusBelumCetak->id;
            } else {
                $penerimaanHeader->statuscetak = $statusCetak->id;
            }

            $penerimaanHeader->tglbukacetak = date('Y-m-d', time());
            $penerimaanHeader->userbukacetak = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanHeader->getTable()),
                    'postingdari' => 'BUKA/BELUM CETAK PENERIMAANHEADER',
                    'idtrans' => $penerimaanHeader->id,
                    'nobuktitrans' => $penerimaanHeader->id,
                    'aksi' => 'BUKA/BELUM CETAK',
                    'datajson' => $penerimaanHeader->toArray(),
                    'modifiedby' => $penerimaanHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function editCoa(UpdatePenerimaanDetailRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'coakredit' => $request->coakredit,
                'keterangan_detail' => $request->keterangan_detail,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'penerimaangiro_nobukti' => $request->penerimaangiro_nobukti,
            ];
            $penerimaan = PenerimaanHeader::findOrFail($id);
            /* Store header */
            $penerimaanheader = (new PenerimaanHeader())->processUpdate($penerimaan, $data);
            /* Set position and page */
            $penerimaanheader->position = $this->getPosition($penerimaanheader, $penerimaanheader->getTable())->position;
            if ($request->limit == 0) {
                $penerimaanheader->page = ceil($penerimaanheader->position / (10));
            } else {
                $penerimaanheader->page = ceil($penerimaanheader->position / ($request->limit ?? 10));
            }
            $penerimaanheader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanheader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function tarikPelunasan($id)
    {
        $penerimaan = new PenerimaanHeader();
        // ($id!='') ? $tarik = $penerimaan->tarikPelunasan($id) : $tarik = $penerimaan->tarikPelunasan();
        return response([
            'data' => $penerimaan->tarikPelunasan($id),
        ]);
    }
    public function getPelunasan($id, $table)
    {
        $get = new PenerimaanHeader();
        return response([
            'data' => $get->getPelunasan($id, $table),
        ]);
    }


    public function cekvalidasi($id)
    {
        $pengeluaran = PenerimaanHeader::find($id);
        $nobukti = $pengeluaran->nobukti ?? '';
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('penerimaanheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('PENERIMAAN KAS/BANK BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    // (new MyModel())->updateEditingBy('penerimaanheader', $id, $aksi);
                    (new MyModel())->createLockEditing($id, 'penerimaanheader', $useredit);
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
                // (new MyModel())->updateEditingBy('penerimaanheader', $id, $aksi);
                (new MyModel())->createLockEditing($id, 'penerimaanheader', $useredit);
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
        $penerimaanHeader = new PenerimaanHeader();
        $nobukti = PenerimaanHeader::from(DB::raw("penerimaanheader"))->where('id', $id)->first();
        $cekdata = $penerimaanHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'editcoa' => $cekdata['editcoa']
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('penerimaanheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'penerimaanheader', $useredit);
            // (new MyModel())->updateEditingBy('penerimaanheader', $id, 'EDIT');

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'editcoa' => false
            ];

            return response($data);
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = PenerimaanHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanHeader->statuscetak = $statusSudahCetak->id;
                // $penerimaanHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $penerimaanHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanHeader->jumlahcetak = $penerimaanHeader->jumlahcetak + 1;
                if ($penerimaanHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanHeader->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN HEADER',
                        'idtrans' => $penerimaanHeader->id,
                        'nobuktitrans' => $penerimaanHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanHeader->toArray(),
                        'modifiedby' => $penerimaanHeader->modifiedby
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
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $penerimaanHeader = new PenerimaanHeader();
        $penerimaan_Header = $penerimaanHeader->getExport($id);

        if ($request->export == true) {
            $penerimaanDetail = new PenerimaanDetail();
            $penerimaan_Detail = $penerimaanDetail->get();

            $tglBukti = $penerimaan_Header->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $penerimaan_Header->tglbukti = $dateTglBukti;

            $tglLunas = $penerimaan_Header->tgllunas;
            $timeStamp = strtotime($tglLunas);
            $datetglLunas = date('d-m-Y', $timeStamp);
            $penerimaan_Header->tgllunas = $datetglLunas;

            if ($penerimaan_Header->tipe_bank === 'KAS') {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $penerimaan_Header->judul);
                $sheet->setCellValue('A2', 'Laporan Penerimaan' . $penerimaan_Header->bank_id);
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');

                $header_start_row = 4;
                $header_start_row_right = 4;
                $detail_table_header_row = 9;
                $detail_start_row = $detail_table_header_row + 1;

                $alphabets = range('A', 'Z');

                $header_columns = [
                    [
                        'label' => 'No Bukti',
                        'index' => 'nobukti',
                    ],
                    [
                        'label' => 'Kas',
                        'index' => 'bank_id',
                    ]
                ];
                $header_right_columns = [
                    [
                        'label' => 'Diterima Dari',
                        'index' => 'diterimadari',
                    ],
                    [
                        'label' => 'Tanggal',
                        'index' => 'tglbukti',
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
                        'index' => 'keterangan_detail'
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
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaan_Header->{$header_column['index']});
                }
                foreach ($header_right_columns as $header_right_column) {
                    $sheet->setCellValue('D' . $header_start_row_right, $header_right_column['label']);
                    $sheet->setCellValue('E' . $header_start_row_right++, ': ' . $penerimaan_Header->{$header_right_column['index']});
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
                foreach ($penerimaan_Detail as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->coadebet);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan_detail);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                    // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('C')->setWidth(50);

                    $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $detail_start_row++;
                }

                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("D$total_start_row", "=SUM(D10:D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);

                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PENERIMAAN ' . $penerimaan_Header->bank_id . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
            } else {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $penerimaan_Header->judul);
                $sheet->setCellValue('A2', 'Laporan Penerimaan ' . $penerimaan_Header->bank_id);
                $sheet->getStyle("A1")->getFont()->setSize(11);
                $sheet->getStyle("A2")->getFont()->setSize(11);
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A2")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');

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
                    ]
                ];
                $header_columns_right = [
                    [
                        'label' => 'Diterima Dari',
                        'index' => 'diterimadari',
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
                        'index' => 'bank_detail'
                    ],
                    [
                        'label' => 'INVOICE',
                        'index' => 'invoice_nobukti'
                    ],
                    [
                        'label' => 'KETERANGAN',
                        'index' => 'keterangan_detail'
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
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaan_Header->{$header_column['index']});
                }
                foreach ($header_columns_right as $header_column_right) {
                    $sheet->setCellValue('D' . $header_start_row_right, $header_column_right['label']);
                    $sheet->setCellValue('E' . $header_start_row_right++, ': ' . $penerimaan_Header->{$header_column_right['index']});
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
                $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray);

                // LOOPING DETAIL
                $nominal = 0;
                foreach ($penerimaan_Detail as $response_index => $response_detail) {

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : 0);
                        $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFont()->setBold(true);
                        $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getAlignment()->setHorizontal('center');
                    }

                    $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->coadebet);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->bank_detail);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->invoice_nobukti);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->keterangan_detail);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->nominal);

                    // $sheet->getStyle("E$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getColumnDimension('E')->setWidth(50);

                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("F$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $detail_start_row++;
                }
                $total_start_row = $detail_start_row;
                $sheet->mergeCells('A' . $total_start_row . ':E' . $total_start_row);
                $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':E' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("F$total_start_row", "=SUM(F9:F" . ($detail_start_row - 1) . ")")->getStyle("F$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("F$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);

                $writer = new Xlsx($spreadsheet);
                $filename = 'LAPORAN PENERIMAAN BANK ' . $penerimaan_Header->bank_id . date('dmYHis');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                header('Filename: ' . $filename);
                $writer->save('php://output');
            }
        } else {
            return response([
                'data' => $penerimaan_Header
            ]);
        }
        // return response([
        //     'data' => $penerimaanHeader->getExport($id)
        // ]);
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}
}
