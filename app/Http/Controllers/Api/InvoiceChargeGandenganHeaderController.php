<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyInvoiceChargeGandenganRequest;
use Illuminate\Http\Request;
use App\Models\InvoiceChargeGandenganHeader;
use App\Models\InvoiceChargeGandenganDetail;
use App\Models\Parameter;
use App\Models\Trado;
use App\Http\Requests\StoreInvoiceChargeGandenganHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateInvoiceChargeGandenganHeaderRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreInvoiceChargeGandenganDetailRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\Error;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceChargeGandenganHeaderController extends Controller
{
    /**
     * @ClassName 
     * InvoiceChargeGandenganHeader
     * @Detail InvoiceChargeGandenganDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $invoice = new InvoiceChargeGandenganHeader();

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
    public function store(StoreInvoiceChargeGandenganHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tglproses' => $request->tglproses,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'id_detail' => $request->id_detail,
                'jobtrucking_detail' => $request->jobtrucking_detail,
                'nopolisi_detail' => $request->nopolisi_detail,
                'gandengan_detail' => $request->gandengan_detail,
                'tgltrip_detail' => $request->tgltrip_detail,
                'tglkembali_detail' => $request->tglkembali_detail,
                'jumlahhari_detail' => $request->jumlahhari_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail,
                'jenisorder_detail' => $request->jenisorder_detail,
                'namagudang_detail' => $request->namagudang_detail,
            ];

            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processStore($data);
            $invoiceChargeGandengan->position = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable())->position;
            if ($request->limit == 0) {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / (10));
            } else {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));
            }
            $invoiceChargeGandengan->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceChargeGandengan->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceChargeGandengan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
        return response([
            'status' => true,
            'data' => $invoiceChargeGandenganHeader->find($id),
            'detail' => $invoiceChargeGandenganHeader->getInvoiceGandengan($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateInvoiceChargeGandenganHeaderRequest $request, InvoiceChargeGandenganHeader $invoicechargegandenganheader): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tglproses' => $request->tglproses,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'id_detail' => $request->id_detail,
                'jobtrucking_detail' => $request->jobtrucking_detail,
                'nopolisi_detail' => $request->nopolisi_detail,
                'gandengan_detail' => $request->gandengan_detail,
                'tgltrip_detail' => $request->tgltrip_detail,
                'tglkembali_detail' => $request->tglkembali_detail,
                'jumlahhari_detail' => $request->jumlahhari_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail,
                'jenisorder_detail' => $request->jenisorder_detail,
                'namagudang_detail' => $request->namagudang_detail,
            ];
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processUpdate($invoicechargegandenganheader, $data);
            $invoiceChargeGandengan->position = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable())->position;
            if ($request->limit == 0) {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / (10));
            } else {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));
            }
            $invoiceChargeGandengan->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceChargeGandengan->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceChargeGandengan
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
    public function destroy(DestroyInvoiceChargeGandenganRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processDestroy($id, 'DELETE INVOICE CHARGE GANDENGAN');
            $selected = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable(), true);
            $invoiceChargeGandengan->position = $selected->position;
            $invoiceChargeGandengan->id = $selected->id;
            if ($request->limit == 0) {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / (10));
            } else {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));
            }
            $invoiceChargeGandengan->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceChargeGandengan->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceChargeGandengan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $InvoiceChargeGandenganHeader = InvoiceChargeGandenganHeader::findOrFail($id);
        $status = $InvoiceChargeGandenganHeader->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $InvoiceChargeGandenganHeader->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
                ->first();

            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $InvoiceChargeGandenganHeader->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
                ->first();

            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $InvoiceChargeGandenganHeader->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];
            return response($data);
        }
    }

    public function cekvalidasiAksi($id)
    {
        $invoiceHeader = new InvoiceChargeGandenganHeader();
        $nobukti = InvoiceChargeGandenganHeader::from(DB::raw("invoicechargegandenganheader"))->where('id', $id)->first();
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
                'message' => $query->keterangan,
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
    public function getinvoicegandengan($id)
    {
        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
        return response([
            'status' => true,
            'data' => $invoiceChargeGandenganHeader->getInvoiceGandengan($id)
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoiceChargeGandengan = InvoiceChargeGandenganHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceChargeGandengan->statuscetak != $statusSudahCetak->id) {
                $invoiceChargeGandengan->statuscetak = $statusSudahCetak->id;
                $invoiceChargeGandengan->tglbukacetak = date('Y-m-d H:i:s');
                $invoiceChargeGandengan->userbukacetak = auth('api')->user()->name;
                $invoiceChargeGandengan->jumlahcetak = $invoiceChargeGandengan->jumlahcetak + 1;
                if ($invoiceChargeGandengan->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceChargeGandengan->getTable()),
                        'postingdari' => 'PRINT INVOICE CHARGE GANDENGAN HEADER',
                        'idtrans' => $invoiceChargeGandengan->id,
                        'nobuktitrans' => $invoiceChargeGandengan->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceChargeGandengan->toArray(),
                        'modifiedby' => $invoiceChargeGandengan->modifiedby
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
        $invoiceChargeGandengan = new InvoiceChargeGandenganHeader();
        $invoice_ChargeGandengan = $invoiceChargeGandengan->getExport($id);

        if ($request->export == true) {
            $invoiceChargeDetail = new InvoiceChargeGandenganDetail();
            $invoice_ChargeDetail = $invoiceChargeDetail->get();

            $tglBukti = $invoice_ChargeGandengan->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $invoice_ChargeGandengan->tglbukti = $dateTglBukti;

            $tglProses = $invoice_ChargeGandengan->tglproses;
            $timeStampProses = strtotime($tglProses);
            $dateTglProses = date('d-m-Y', $timeStampProses);
            $invoice_ChargeGandengan->tglproses = $dateTglProses;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $invoice_ChargeGandengan->judul);
            $sheet->setCellValue('A2', $invoice_ChargeGandengan->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:G1');
            $sheet->mergeCells('A2:G2');

            $header_start_row = 4;
            $detail_table_header_row = 9;
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
                    'label' => 'Tanggal Proses',
                    'index' => 'tglproses',
                ],
                [
                    'label' => 'Customer',
                    'index' => 'agen',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'GANDENGAN',
                    'index' => 'gandengan',
                ],
                [
                    'label' => 'JOB TRUCKING',
                    'index' => 'jobtrucking',
                ],
                [
                    'label' => 'DARI',
                    'index' => 'tgltrip',
                ],
                [
                    'label' => 'SAMPAI',
                    'index' => 'tglakhir',
                ],
                [
                    'label' => 'ORDERAN',
                    'index' => 'orderan',
                ],
                [
                    'label' => 'JUMLAH HARI',
                    'index' => 'jumlahhari',
                ],
                [
                    'label' => 'NAMA GUDANG',
                    'index' => 'namagudang',
                ],
                [
                    'label' => 'NOMINAL',
                    'index' => 'nominal',
                ],
            ];

            //LOOPING HEADER       
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $invoice_ChargeGandengan->{$header_column['index']});
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
            $sheet->getStyle("A$detail_table_header_row:I$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $nominal = 0;
            foreach ($invoice_ChargeDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:I$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:I$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                // dd('here');
                $tglTrip = ($response_detail->tgltrip != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tgltrip))) : '';
                $tglAkhir = ($response_detail->tglakhir != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglakhir))) : '';

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->gandengan);
                $sheet->setCellValue("C$detail_start_row", $response_detail->jobtrucking);
                $sheet->setCellValue("D$detail_start_row", $tglTrip);
                $sheet->setCellValue("E$detail_start_row", $tglAkhir);
                $sheet->setCellValue("F$detail_start_row", $response_detail->orderan);
                $sheet->setCellValue("G$detail_start_row", $response_detail->jumlahhari);
                $sheet->setCellValue("H$detail_start_row", $response_detail->namagudang);
                $sheet->setCellValue("I$detail_start_row", $response_detail->nominal);

                $sheet->getStyle("A$detail_start_row:I$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row:E$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $sheet->getStyle("I$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getStyle("I$detail_start_row")->applyFromArray($style_number);
                $nominal += $response_detail->nominal;
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':H' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':H' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

            $sheet->setCellValue("I$detail_start_row", "=SUM(I10:I" . ($detail_start_row - 1) . ")")->getStyle("I$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("I$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN INVOICE CHARGE GANDENGAN ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $invoice_ChargeGandengan
            ]);
        }
    }
}
