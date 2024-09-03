<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Exception;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Events\NewNotification;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanGiroDetail;
use App\Models\PenerimaanGiroHeader;
use Illuminate\Support\Facades\Hash;
use App\Events\forceEditNotification;
use App\Http\Requests\EditingAtRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKacabEditingRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\StorePenerimaanGiroDetailRequest;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdatePenerimaanGiroHeaderRequest;
use App\Http\Requests\DestroyPenerimaanGiroHeaderRequest;
use App\Http\Requests\ValidasiApprovalPenerimaanGiroRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PenerimaanGiroHeaderController extends Controller
{
    /**
     * @ClassName 
     * PenerimaanGiroHeader
     * @Detail PenerimaanGiroDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $penerimaanGiro = new PenerimaanGiroHeader();

        return response([
            'data' => $penerimaanGiro->get(),
            'attributes' => [
                'totalRows' => $penerimaanGiro->totalRows,
                'totalPages' => $penerimaanGiro->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function get(GetIndexRangeRequest $request): JsonResponse
    {
        // untuk lookup penerimaangiro
        $penerimaanGiro = new PenerimaanGiroHeader();

        return response()->json([
            'data' => $penerimaanGiro->getPenerimaan(),
            'attributes' => [
                'totalRows' => $penerimaanGiro->totalRows,
                'totalPages' => $penerimaanGiro->totalPages,
                'totalNominal' => $penerimaanGiro->totalNominal
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePenerimaanGiroHeaderRequest $request): JsonResponse
    {
        DB::BeginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan_detail' => $request->keterangan_detail,
                'bank_id' => $request->bank_id,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'jenisbiaya' => $request->jenisbiaya,
                'bulanbeban' => $request->bulanbeban,
            ];
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $penerimaanGiroHeader->position = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable())->position;
                if ($request->limit == 0) {
                    $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / (10));
                } else {
                    $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));
                }
                $penerimaanGiroHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $penerimaanGiroHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $data = PenerimaanGiroHeader::findAll($id);
        $detail = PenerimaanGiroDetail::findAll($id);
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
    public function update(UpdatePenerimaanGiroHeaderRequest $request, PenerimaanGiroHeader $penerimaangiroheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan_detail' => $request->keterangan_detail,
                'bank_id' => $request->bank_id,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'jenisbiaya' => $request->jenisbiaya,
                'bulanbeban' => $request->bulanbeban,
            ];
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processUpdate($penerimaangiroheader, $data);
            /* Set position and page */
            $penerimaanGiroHeader->position = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable())->position;
            if ($request->limit == 0) {
                $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / (10));
            } else {
                $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));
            }
            $penerimaanGiroHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanGiroHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
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
    public function destroy(DestroyPenerimaanGiroHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processDestroy($id, 'DELETE PENERIMAAN GIRO');
            $selected = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable(), true);
            $penerimaanGiroHeader->position = $selected->position;
            $penerimaanGiroHeader->id = $selected->id;
            if ($request->limit == 0) {
                $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / (10));
            } else {
                $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));
            }
            $penerimaanGiroHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanGiroHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanGiroHeader
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
    public function approval(ValidasiApprovalPenerimaanGiroRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processApproval($request->all());

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function tarikPelunasan($id)
    {
        $penerimaan = new PenerimaanGiroHeader();
        // ($id!='') ? $tarik = $penerimaan->tarikPelunasan($id) : $tarik = $penerimaan->tarikPelunasan();
        return response([
            'data' => $penerimaan->tarikPelunasan($id),
        ]);
    }

    public function getPelunasan($id)
    {
        $get = new PenerimaanGiroHeader();
        return response([
            'data' => $get->getPelunasan($id),
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanGiro = PenerimaanGiroHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanGiro->statuscetak != $statusSudahCetak->id) {
                $penerimaanGiro->statuscetak = $statusSudahCetak->id;
                // $penerimaanGiro->tglbukacetak = date('Y-m-d H:i:s');
                // $penerimaanGiro->userbukacetak = auth('api')->user()->name;
                $penerimaanGiro->jumlahcetak = $penerimaanGiro->jumlahcetak + 1;
                if ($penerimaanGiro->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanGiro->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN GIRO HEADER',
                        'idtrans' => $penerimaanGiro->id,
                        'nobuktitrans' => $penerimaanGiro->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanGiro->toArray(),
                        'modifiedby' => $penerimaanGiro->modifiedby
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
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $pengeluaran = PenerimaanGiroHeader::find($id);

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

        $tgltutup = (new Parameter())->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('penerimaangiroheader', $id);
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('Penerimaan Giro Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'penerimaangiroheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'penerimaangiroheader', $useredit);
            }

            $data = [
                'error' => false,
                'message' => '',
                'kodeerror' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function cekValidasiAksi($id)
    {
        $penerimaanGiro = new PenerimaanGiroHeader();
        $nobukti = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader"))->where('id', $id)->first();
        $cekdata = $penerimaanGiro->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'kodeerror' => $cekdata['kodeerror'],
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('penerimaangiroheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'penerimaangiroheader', $useredit);

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
     * @Keterangan APPROVAL EDITING BY
     */
    public function approvaleditingby() {}

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
        $penerimaanGiroHeader = new PenerimaanGiroHeader();
        $penerimaan_GiroHeader = $penerimaanGiroHeader->getExport($id);

        $penerimaanGiroDetail = new PenerimaanGiroDetail();
        $penerimaan_GiroDetail = $penerimaanGiroDetail->get();

        if ($request->export == true) {
            $tglBukti = $penerimaan_GiroHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $penerimaan_GiroHeader->tglbukti = $dateTglBukti;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $penerimaan_GiroHeader->judul);
            $sheet->setCellValue('A2', 'Bukti Penerimaan Giro ' . $penerimaan_GiroHeader->bank);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:G1');
            $sheet->mergeCells('A2:G2');

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
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
            ];
            $header_right_columns = [
                [
                    'label' => 'No Warkat',
                    'index' => 'nowarkat',
                ],
                [
                    'label' => 'Diterima Dari',
                    'index' => 'diterimadari',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NAMA PERKIRAAN',
                    'index' => 'namacoakredit',
                ],
                [
                    'label' => 'BANK',
                    'index' => 'bank_id'
                ],
                [
                    'label' => 'TANGGAL JATUH TEMPO',
                    'index' => 'tgljatuhtempo',
                ],
                [
                    'label' => 'NO INVOICE',
                    'index' => 'invoice_nobukti',
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
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaan_GiroHeader->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $penerimaan_GiroHeader->{$header_right_column['index']});
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

            foreach ($penerimaan_GiroDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $tgljatuhtempo = $response_detail->tgljatuhtempo;
                $timeStamp = strtotime($tgljatuhtempo);
                $datetgljatuhtempo = date('d-m-Y', $timeStamp);
                $response_detail->tgljatuhtempo = $datetgljatuhtempo;

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->namacoakredit);
                $sheet->setCellValue("C$detail_start_row", $response_detail->bank_id);
                $sheet->setCellValue("D$detail_start_row", $response_detail->tgljatuhtempo);
                $dateValue = ($response_detail->tgljatuhtempo != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tgljatuhtempo))) : '';
                $sheet->setCellValue("D$detail_start_row", $dateValue);
                $sheet->getStyle("D$detail_start_row")
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
                $sheet->setCellValue("E$detail_start_row", $response_detail->invoice_nobukti);
                $sheet->setCellValue("F$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("G$detail_start_row", $response_detail->nominal);

                $sheet->getStyle("F$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('F')->setWidth(50);

                $sheet->getStyle("A$detail_start_row:G$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("G$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':F' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':F' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("G$detail_start_row", "=SUM(G9:G" . ($detail_start_row - 1) . ")")->getStyle("G$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN PENERIMAAN GIRO ' . $penerimaan_GiroHeader->bank . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Filename: ' . $filename);
            $writer->save('php://output');
        } else {
            return response([
                'data' => $penerimaan_GiroHeader
            ]);
        }
    }

    public function editingat(EditingAtRequest $request)
    {
        $penerimaanGiro = PenerimaanGiroHeader::find($request->id);
        $btn = $request->btn;

        if ($btn == 'EDIT') {
            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENERIMAAN GIRO BUKTI')->first();
            $memo = json_decode($param->memo, true);
            $waktu = $memo['BATASWAKTUEDIT'];

            $user = auth('api')->user()->name;
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($penerimaanGiro->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            // cek user
            // if ($penerimaanGiro->editing_by != '' && $penerimaanGiro->editing_by != $user) {
            //     // check apakah waktu sebelumnya sudah melewati batas edit
            //     if ($diffNow->i < $waktu) {
            //         return response([
            //             'isEdited' => true
            //         ]);
            //     }
            // }
        }

        return response([
            'data' => (new PenerimaanGiroHeader())->editingAt($request->id, $request->btn),
            // 'isEdited' => false
        ]);
    }

    public function approvalKacab(ApprovalKacabEditingRequest $request)
    {
        $query = DB::table("user")->from(DB::raw("[user] with (readuncommitted)"))
            ->select('userrole.role_id', DB::raw("[user].id"))
            ->join(DB::raw("userrole with (readuncommitted)"), DB::raw("[user].id"), 'userrole.user_id')
            ->where('user.user', request()->username)->first();

        $cekAcl = DB::table("acos")->from(DB::raw("acos with (readuncommitted)"))
            ->select(DB::raw("acos.id,acos.class,acos.method"))
            ->join(DB::raw("acl with (readuncommitted)"), 'acos.id', 'acl.aco_id')
            ->where("acos.class", 'penerimaangiroheader')
            ->where("acos.method", 'approvaleditingby')
            ->where('acl.role_id', $query->role_id)
            ->first();
        $edit = '';
        if ($cekAcl != '') {
            $status = true;
            $edit = (new PenerimaanGiroHeader())->editingAt($request->id, 'EDIT');
        } else {
            $cekUserAcl = DB::table("acos")->from(DB::raw("acos with (readuncommitted)"))
                ->select(DB::raw("acos.id,acos.class,acos.method"))
                ->join(DB::raw("useracl with (readuncommitted)"), 'acos.id', 'useracl.aco_id')
                ->where("acos.class", 'penerimaangiroheader')
                ->where("acos.method", 'approvaleditingby')
                ->where('useracl.user_id', $query->id)
                ->first();
            if ($cekUserAcl != '') {
                $status = true;

                $edit = (new PenerimaanGiroHeader())->editingAt($request->id, 'EDIT');
            } else {
                $status = false;
            }
        }
        if ($status) {


            event(new NewNotification(json_encode([
                'message' => "FORM INI SUDAH TIDAK BISA DIEDIT. SEDANG DIEDIT OLEH " . $edit->editing_by,
                'olduser' => $edit->oldeditingby,
                'user' => $edit->editing_by,
                'id' => $request->id

            ])));
        }

        return response([
            'status' => $status,
            'data' => $edit
        ]);
    }
}
