<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyInvoiceEmklHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetJobInvoiceEmklRequest;
use App\Http\Requests\StoreInvoiceEmklHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateInvoiceEmklHeaderRequest;
use App\Models\Error;
use App\Models\InvoiceEmklDetail;
use App\Models\InvoiceEmklHeader;
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\Parameter;
use DateTime;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceEmklHeaderController extends Controller
{
    /**
     * @ClassName 
     * PiutangHeader
     * @Detail InvoiceEmklDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $piutang = new InvoiceEmklHeader();

        return response([
            'data' => $piutang->get(),
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreInvoiceEmklHeaderRequest $request): JsonResponse
    {
        // dd('test');
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'jenisorder_id' => $request->jenisorder_id,
                'jenisorder' => $request->jenisorder,
                'statusinvoice' => $request->statusinvoice,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'statuspajak' => $request->statuspajak,
                'kapal' => $request->kapal,
                'destination' => $request->destination,
                'nobuktiinvoicepajak' => $request->nobuktiinvoicepajak,
                'keterangan' => $request->keterangan,
                'job_id' => $requestData['job_id'],
                'nominal' => $requestData['nominal'],
                'nojobemkl' => $requestData['nojobemkl'],
                'keterangan_detail' => $requestData['keterangan_detail'],
                "keterangan_biaya" => $requestData['keterangan_biaya'],      
            ];
            $invoiceEmklHeader = (new InvoiceEmklHeader())->processStore($data);
            $invoiceEmklHeader->position = $this->getPosition($invoiceEmklHeader, $invoiceEmklHeader->getTable())->position;
            if ($request->limit == 0) {
                $invoiceEmklHeader->page = ceil($invoiceEmklHeader->position / (10));
            } else {
                $invoiceEmklHeader->page = ceil($invoiceEmklHeader->position / ($request->limit ?? 10));
            }
            $invoiceEmklHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceEmklHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceEmklHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $invoice = (new InvoiceEmklHeader())->findAll($id);
        return response([
            'status' => true,
            'data' => $invoice
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateInvoiceEmklHeaderRequest $request, InvoiceEmklHeader $invoiceemklheader)
    {
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'jenisorder_id' => $request->jenisorder_id,
                'jenisorder' => $request->jenisorder,
                'statusinvoice' => $request->statusinvoice,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'statuspajak' => $request->statuspajak,
                'kapal' => $request->kapal,
                'destination' => $request->destination,
                'nobuktiinvoicepajak' => $request->nobuktiinvoicepajak,
                'keterangan' => $request->keterangan,
                'job_id' => $requestData['job_id'],
                'nominal' => $requestData['nominal'],
                'nojobemkl' => $requestData['nojobemkl'],
                'keterangan_detail' => $requestData['keterangan_detail'],
                "keterangan_biaya" => $requestData['keterangan_biaya'],      
            ];
            $invoiceEmklHeader = (new InvoiceEmklHeader())->processUpdate($invoiceemklheader, $data);
            $invoiceEmklHeader->position = $this->getPosition($invoiceEmklHeader, $invoiceEmklHeader->getTable())->position;
            if ($request->limit == 0) {
                $invoiceEmklHeader->page = ceil($invoiceEmklHeader->position / (10));
            } else {
                $invoiceEmklHeader->page = ceil($invoiceEmklHeader->position / ($request->limit ?? 10));
            }
            $invoiceEmklHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceEmklHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceEmklHeader
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
    public function destroy(DestroyInvoiceEmklHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $invoiceEmklHeader = (new InvoiceEmklHeader())->processDestroy($id, 'DELETE INVOICE EMKL');
            $selected = $this->getPosition($invoiceEmklHeader, $invoiceEmklHeader->getTable(), true);
            $invoiceEmklHeader->position = $selected->position;
            $invoiceEmklHeader->id = $selected->id;
            if ($request->limit == 0) {
                $invoiceEmklHeader->page = ceil($invoiceEmklHeader->position / (10));
            } else {
                $invoiceEmklHeader->page = ceil($invoiceEmklHeader->position / ($request->limit ?? 10));
            }
            $invoiceEmklHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceEmklHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceEmklHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoiceEmklHeader = InvoiceEmklHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceEmklHeader->statuscetak != $statusSudahCetak->id) {
                $invoiceEmklHeader->statuscetak = $statusSudahCetak->id;
                $invoiceEmklHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $invoiceEmklHeader->userbukacetak = auth('api')->user()->name;
                $invoiceEmklHeader->jumlahcetak = $invoiceEmklHeader->jumlahcetak + 1;
                if ($invoiceEmklHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceEmklHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EMKL HEADER',
                        'idtrans' => $invoiceEmklHeader->id,
                        'nobuktitrans' => $invoiceEmklHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceEmklHeader->toArray(),
                        'modifiedby' => $invoiceEmklHeader->modifiedby
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


    public function cekValidasiAksi($id)
    {
        $piutangHeader = new InvoiceEmklHeader();
        $nobukti = InvoiceEmklHeader::from(DB::raw("invoiceemklheader"))->where('id', $id)->first();
        $cekdata = $piutangHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('invoiceemklheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'invoiceemklheader', $useredit);
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
    public function cekvalidasi($id)
    {

        $pengeluaran = InvoiceEmklHeader::find($id);
        $nobukti = $pengeluaran->nobukti ?? '';
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('invoiceemklheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));


        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = Error::from(DB::raw("error with (readuncommitted)"))
            //     ->select('keterangan')
            //     ->whereRaw("kodeerror = 'SAP'")
            //     ->first();

            $data = [
                'error' => true,
                'message' => $keterror,
                // 'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = Error::from(DB::raw("error with (readuncommitted)"))
            //     ->select('keterangan')
            //     ->whereRaw("kodeerror = 'SDC'")
            //     ->first();

            $data = [
                'error' => true,
                // 'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
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

            $waktu = (new Parameter())->cekBatasWaktuEdit('Invoice Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'invoiceemklheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'invoiceemklheader', $useredit);
            }

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
     * @Keterangan APPROVAL DATA
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->invoiceId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->invoiceId); $i++) {
                    $invoice = InvoiceEmklHeader::find($request->invoiceId[$i]);
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
                            'postingdari' => 'APPROVAL INVOICE EMKL',
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
                        'penerimaan' => "INVOICE EMKL $query->keterangan"
                    ],
                    'message' => "INVOICE EMKL $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $piutangHeader = new InvoiceEmklHeader();
        $piutang_Header = $piutangHeader->getExport($id);

        $piutangDetail = new InvoiceEmklDetail();
        $piutang_Detail = $piutangDetail->get();
        if ($request->export == true) {
            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $piutang_Header->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:E1');

            $header_start_row = 4;
            $header_right_start_row = 4;
            $detail_table_header_row = 9;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'No Invoice',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'To',
                    'index' => 'pelanggan',
                ],
                [
                    'label' => 'Vessel',
                    'index' => 'kapal',
                ],
                [
                    'label' => 'Dest',
                    'index' => 'destination',
                ],
                [
                    'label' => 'Qty',
                    'index' => 'qty',
                ],
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
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
                if ($header_column['label'] == 'nobukti') {
                    if ($piutang_Header->statusformatreimbursement == 'YA') {
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $piutang_Header->nobuktiinvoicereimbursement);
                    } else {
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $piutang_Header->nobuktiinvoicepajak);
                    }
                } else {
                    $sheet->setCellValue('C' . $header_start_row++, ': ' . $piutang_Header->{$header_column['index']});
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

            // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
            $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $total = 0;
            foreach ($piutang_Detail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("C$detail_start_row", $response_detail->nominal);

                // $sheet->getStyle("B$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('B')->setWidth(30);

                $sheet->getStyle("A$detail_start_row:B$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("C$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':B' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':B' . $total_start_row)->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->setCellValue("C$detail_start_row", "=SUM(C10:C" . ($detail_start_row - 1) . ")")->getStyle("C$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Invoice Emkl' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $piutang_Header
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('invoiceemklheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }


    public function getJob(GetJobInvoiceEmklRequest $request)
    {
        $invoice = new InvoiceEmklHeader();
        $datahasil = $invoice->getSPSearch($request, 0, false);

        if (isset($datahasil)) {
            return response([
                // "data" => $invoice->getSP($request)
                "data" => $datahasil
            ]);
        } else {
            return response([
                // "data" => $invoice->getSP($request)
                "data" => []
            ]);
        }
    }

    public function getEdit($id, Request $request)
    {
        $invoice = new InvoiceEmklHeader();

        return response([
            //  "data" => $invoice->getEdit($id, $request)
            "data" => $invoice->getSPSearch($request, $id, true)

        ]);
    }

    public function getAllEdit($id, Request $request)
    {
        $invoice = new InvoiceEmklHeader();

        return response([
            // "data" => $invoice->getAllEdit($id, $request)
            "data" => $invoice->getSPSearch($request, 0, false)
        ]);
    }
}
