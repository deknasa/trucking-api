<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\PiutangDetail;
use App\Models\PiutangHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Illuminate\Http\JsonResponse;
use App\Models\InvoiceExtraDetail;
use App\Models\InvoiceExtraHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;
use App\Http\Requests\StoreInvoiceExtraDetailRequest;
use App\Http\Requests\StoreInvoiceExtraHeaderRequest;
use App\Http\Requests\UpdateInvoiceExtraHeaderRequest;
use App\Http\Requests\DestroyInvoiceExtraHeaderRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceExtraHeaderController extends Controller
{
    /**
     * @ClassName 
     * InvoiceExtraHeader
     * @Detail InvoiceExtraDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $invoice = new InvoiceExtraHeader();

        return response([
            "data" => $invoice->get(),
            "attributes" => [
                'totalRows' => $invoice->totalRows,
                'totalPages' => $invoice->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreInvoiceExtraHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'nominal' => $request->nominal,
                'agen_id' => $request->agen_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $invoiceExtra = (new InvoiceExtraHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $invoiceExtra->position = $this->getPosition($invoiceExtra, $invoiceExtra->getTable())->position;
                if ($request->limit == 0) {
                    $invoiceExtra->page = ceil($invoiceExtra->position / (10));
                } else {
                    $invoiceExtra->page = ceil($invoiceExtra->position / ($request->limit ?? 10));
                }
                $invoiceExtra->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $invoiceExtra->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceExtra
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $data = (new InvoiceExtraHeader)->findAll($id);
        $detail = (new InvoiceExtraDetail)->getAll($id);
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
    public function update(UpdateInvoiceExtraHeaderRequest $request, InvoiceExtraHeader $invoiceextraheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'nominal' => $request->nominal,
                'agen_id' => $request->agen_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $invoiceExtraHeader = (new InvoiceExtraHeader())->processUpdate($invoiceextraheader, $data);
            $invoiceExtraHeader->position = $this->getPosition($invoiceExtraHeader, $invoiceExtraHeader->getTable())->position;
            if ($request->limit == 0) {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / (10));
            } else {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / ($request->limit ?? 10));
            }
            $invoiceExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceExtraHeader
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
    public function destroy(DestroyInvoiceExtraHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $invoiceExtraHeader = (new InvoiceExtraHeader())->processDestroy($id, 'DELETE INVOICE EXTRA');
            $selected = $this->getPosition($invoiceExtraHeader, $invoiceExtraHeader->getTable(), true);
            $invoiceExtraHeader->position = $selected->position;
            $invoiceExtraHeader->id = $selected->id;
            if ($request->limit == 0) {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / (10));
            } else {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / ($request->limit ?? 10));
            }
            $invoiceExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceExtraHeader
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
            if ($request->extraId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->extraId); $i++) {
                    $invoice = InvoiceExtraHeader::find($request->extraId[$i]);
                    if ($invoice->statusapproval == $statusApproval->id) {
                        $invoice->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $invoice->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $invoice->tglapproval = date('Y-m-d', time());
                    $invoice->userapproval = auth('api')->user()->name;

                    if ($invoice->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($invoice->getTable()),
                            'postingdari' => 'APPROVAL INVOICE EXTRA',
                            'idtrans' => $invoice->id,
                            'nobuktitrans' => $invoice->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $invoice->toArray(),
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
                        'penerimaan' => "INVOICE $query->keterangan"
                    ],
                    'message' => "INVOICE $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function cekvalidasi($id)
    {
        $pengeluaran = InvoiceExtraHeader::findOrFail($id);
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
        $getEditing = (new Locking())->getEditing('invoiceextraheader', $id);
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
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' )';
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('Invoice Extra Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'invoiceextraheader', $useredit);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $pengeluaran->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
                (new MyModel())->createLockEditing($id, 'invoiceextraheader', $useredit);
            }
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
            $invoiceExtraHeader = InvoiceExtraHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceExtraHeader->statuscetak != $statusSudahCetak->id) {
                $invoiceExtraHeader->statuscetak = $statusSudahCetak->id;
                // $invoiceExtraHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $invoiceExtraHeader->userbukacetak = auth('api')->user()->name;
                $invoiceExtraHeader->jumlahcetak = $invoiceExtraHeader->jumlahcetak + 1;
                if ($invoiceExtraHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceExtraHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA HEADER',
                        'idtrans' => $invoiceExtraHeader->id,
                        'nobuktitrans' => $invoiceExtraHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceExtraHeader->toArray(),
                        'modifiedby' => $invoiceExtraHeader->modifiedby
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

    public function storePiutang($piutangHeader, $piutangDetail)
    {
        try {


            $piutang = new StorePiutangHeaderRequest($piutangHeader);
            $header = app(PiutangHeaderController::class)->store($piutang);

            $nobukti = $piutangHeader['nobukti'];
            $fetchId = PiutangHeader::select('id')
                ->whereRaw("nobukti = '$nobukti'")
                ->first();
            $id = $fetchId->id;

            foreach ($piutangDetail as $value) {

                $value['piutang_id'] = $id;
                $piutangDetails = new StorePiutangDetailRequest($value);
                $tes = app(PiutangDetailController::class)->store($piutangDetails);
            }


            return [
                'status' => true
            ];
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
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}
    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $invoiceExtraHeader = new InvoiceExtraHeader();
        $invoice_ExtraHeader = $invoiceExtraHeader->getExport($id);

        $invoiceExtraDetail = new InvoiceExtraDetail();
        $invoice_ExtraDetail = $invoiceExtraDetail->get();

        if ($request->export == true) {
            $tglBukti = $invoice_ExtraHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $invoice_ExtraHeader->tglbukti = $dateTglBukti;

            $tglBukti = $invoice_ExtraHeader->tgljatuhtempo;
            $invoice_ExtraHeader->tgljatuhtempo = date('d-m-Y', strtotime($tglBukti));

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $invoice_ExtraHeader->judul);
            $sheet->setCellValue('A2', $invoice_ExtraHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');

            $header_start_row = 4;
            $detail_table_header_row = 10;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');
            $header_columns = [
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti'
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti'
                ],
                [
                    'label' => 'No Bukti Piutang',
                    'index' => 'piutang_nobukti'
                ],
                [
                    'label' => 'Customer',
                    'index' => 'agen'
                ],
                [
                    'label' => 'Tanggal Jatuh Tempo',
                    'index' => 'tgljatuhtempo'
                ],
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
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
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $invoice_ExtraHeader->{$header_column['index']});
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
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ]
            ];

            // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
            $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $nominal = 0;
            foreach ($invoice_ExtraDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }
                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("C$detail_start_row", $response_detail->nominal);

                $sheet->getColumnDimension('B')->setWidth(50);

                $sheet->getStyle("A$detail_start_row:B$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("C$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':B' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':B' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("C$detail_start_row", "=SUM(C11:C" . ($detail_start_row - 1) . ")")->getStyle("C$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Invoice Extra ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $invoice_ExtraHeader
            ]);
        }
    }

    public function cekvalidasiAksi($id)
    {
        $invoiceHeader = new InvoiceExtraHeader();
        $nobukti = InvoiceExtraHeader::from(DB::raw("invoiceextraheader"))->where('id', $id)->first();
        $cekdata = $invoiceHeader->cekvalidasiaksi($nobukti->nobukti);
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
            $getEditing = (new Locking())->getEditing('invoiceextraheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'invoiceextraheader', $useredit);
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
