<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceLunasKePusat;
use App\Http\Requests\StoreinvoicelunaskepusatRequest;
use App\Http\Requests\UpdateinvoicelunaskepusatRequest;
use App\Http\Requests\InvoiceLunasKePusatRequest;

use stdClass;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceLunasKePusatController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {

        $invoicelunaskepusat = new InvoiceLunasKePusat();
        return response([
            'data' => $invoicelunaskepusat->get(),
            'attributes' => [
                'total' => $invoicelunaskepusat->totalPages,
                'records' => $invoicelunaskepusat->totalRows,
            ]
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreinvoicelunaskepusatRequest $request)
    {
        DB::beginTransaction();
        try {


            $data = [
                "invoiceheader_id" => $request->invoiceheader_id,
                "nobukti" => $request->nobukti,
                "tglbukti" => $request->tglbukti,
                "agen_id" => $request->agen_id,
                "nominal" => $request->nominal,
                "tglbayar" => $request->tglbayar,
                "bayar" => $request->bayar,
                "sisa" => $request->sisa,
                "potongan" => $request->potongan,
            ];
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processStore($data);
            $InvoiceLunasKePusat->position = $request->indexRow;
            // $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas($InvoiceLunasKePusat->id)->position;
            // $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $InvoiceLunasKePusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
        $dataInvoice = $invoicelunaskepusat->getinvoicelunas($id);
        return response([
            'status' => true,
            'data' => $dataInvoice
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoicelunaskepusat  $invoicelunaskepusat
     * @return \Illuminate\Http\Response
     */
    public function edit(invoicelunaskepusat $invoicelunaskepusat)
    {
        //
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateinvoicelunaskepusatRequest $request, InvoiceLunasKePusat $invoicelunaskepusat)
    {
        DB::beginTransaction();
        try {
            $data = [
                "invoiceheader_id" => $request->invoiceheader_id,
                "nobukti" => $request->nobukti,
                "tglbukti" => $request->tglbukti,
                "agen_id" => $request->agen_id,
                "nominal" => $request->nominal,
                "tglbayar" => $request->tglbayar,
                "bayar" => $request->bayar,
                "sisa" => $request->sisa,
                "potongan" => $request->potongan,
            ];
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processUpdate($invoicelunaskepusat, $data);
            $InvoiceLunasKePusat->position = $request->indexRow;
            // $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas($InvoiceLunasKePusat->trado_id)->position;

            // $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $InvoiceLunasKePusat
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(InvoiceLunasKepusatRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processDestroy($id);
            $InvoiceLunasKePusat->position = $request->indexRow;
            // $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas(0,true)->position;

            // $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil dihapus',
                'data' => $InvoiceLunasKePusat
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekValidasi(Request $request, $invoiceheader_id)
    {
        $cekInvoicePusat = DB::table("invoicelunaskepusat")->from(DB::raw("invoicelunaskepusat with (readuncommitted)"))
            ->where('invoiceheader_id', $invoiceheader_id)->where('nobukti', $request->nobukti)->first();
        if ($cekInvoicePusat != '') {
            return response([
                'errors' => false
            ]);
        } else {
            $getError = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'BPI')
                ->first();
            return response([
                'errors' => true,
                'message' => 'INVOICE LUNAS KE PUSAT ' . $getError->keterangan
            ]);
        }
    }

    public function cekValidasiAdd(Request $request, $invoiceheader_id)
    {

        // $now = date("Y-m-d");
        $getinvoice = db::table("invoicelunaskepusat")->from(DB::raw("invoicelunaskepusat with (readuncommitted)"))->where('invoiceheader_id', $invoiceheader_id)->where('nobukti', $request->nobukti)->first();

        if ($getinvoice != null) {
            $getError = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SPI')
                ->first();

            return response([
                'errors' => true,
                'message' => 'INVOICE LUNAS KE PUSAT ' . $getError->keterangan
            ]);
        } else {
            return response([
                'errors' => false,
            ]);
        }
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $requestData = json_decode($request->data, true);
        $data = [
            'id' => $requestData['id'],
            'nobukti' => $requestData['nobukti']
        ];

        $invoicelunaskepusat = new InvoiceLunasKePusat();
        $invoice_lunaskepusat = $invoicelunaskepusat->getExport($data);

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $judul = $getJudul->text;
        $namacabang = (new Parameter())->cekText('CABANG','CABANG');
        // dd($invoice_lunaskepusat);

        $bulan = $this->getBulan(substr($request->periode, 0, 2));
        $tahun = substr($request->periode, 3, 4);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $judul);
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:G1');

        $sheet->setCellValue('A2', 'LAPORAN INVOICE LUNAS KE PUSAT');
        $sheet->setCellValue('A3', 'PERIODE : ' . $bulan . ' - ' . $tahun);
        $sheet->setCellValue('A4', 'CABANG : ' . $namacabang);

        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->getStyle("A3:B3")->getFont()->setBold(true);
        $sheet->getStyle("A4:B4")->getFont()->setBold(true);

        $detail_table_header_row = 6;
        $detail_start_row = $detail_table_header_row + 1;

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

        $alphabets = range('A', 'Z');

        $detail_columns = [
            [
                'label' => 'TGL BUKTI',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'NO BUKTI',
                'index' => 'nobukti',
            ],
            [
                'label' => 'CUSTOMER',
                'index' => 'agen_id',
            ],
            [
                'label' => 'NOMINAL',
                'index' => 'nominal'
            ],
            [
                'label' => 'TGL BAYAR',
                'index' => 'tglbayar',
            ],
            [
                'label' => 'BAYAR',
                'index' => 'bayar'
            ],
            [
                'label' => 'NK',
                'index' => 'nk'
            ],
            [
                'label' => 'SISA',
                'index' => 'sisa'
            ]
        ];


        foreach ($detail_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        foreach ($invoice_lunaskepusat as $response_index => $response_detail) {


            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
            $tglBayar = ($response_detail->tglbayar != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbayar))) : '';

            $sheet->setCellValue("A$detail_start_row", $dateValue);
            $sheet->setCellValue("B$detail_start_row", $response_detail->nobukti);
            $sheet->setCellValue("C$detail_start_row", $response_detail->agen_id);
            $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);
            $sheet->setCellValue("E$detail_start_row", $tglBayar);
            $sheet->setCellValue("F$detail_start_row", $response_detail->bayar);
            $sheet->setCellValue("G$detail_start_row", $response_detail->potongan);

            $rumus = "=D$detail_start_row-(F$detail_start_row+G$detail_start_row)";
            $sheet->setCellValue("H$detail_start_row", $rumus);


            $sheet->getStyle("A$detail_start_row:H$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("F$detail_start_row:H$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("A$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');

            $detail_start_row++;
        }

        $sheet->getStyle("A$detail_start_row:G$detail_start_row")->applyFromArray($styleArray);
        $sheet->mergeCells('A' . $detail_start_row . ':C' . $detail_start_row);
        $sheet->setCellValue("A$detail_start_row", 'Total')->getStyle('A' . $detail_start_row . ':C' . $detail_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

        $sheet->setCellValue("D$detail_start_row", "=SUM(D7:D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("F$detail_start_row", "=SUM(F7:F" . ($detail_start_row - 1) . ")")->getStyle("F$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

        $sheet->setCellValue("H$detail_start_row",  "=SUM(H7:H" . ($detail_start_row - 1) . ")")->getStyle("H$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("G$detail_start_row",  "=SUM(G7:G" . ($detail_start_row - 1) . ")")->getStyle("G$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);


        $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->getStyle("G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->getStyle("H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN INVOICE LUNAS KE PUSAT' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function getBulan($bln)
    {
        switch ($bln) {
            case 1:
                return "JANUARI";
                break;
            case 2:
                return "FEBRUARI";
                break;
            case 3:
                return "MARET";
                break;
            case 4:
                return "APRIL";
                break;
            case 5:
                return "MEI";
                break;
            case 6:
                return "JUNI";
                break;
            case 7:
                return "JULI";
                break;
            case 8:
                return "AGUSTUS";
                break;
            case 9:
                return "SEPTEMBER";
                break;
            case 10:
                return "OKTOBER";
                break;
            case 11:
                return "NOVEMBER";
                break;
            case 12:
                return "DESEMBER";
                break;
        }
    }


    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
        return response([
            'data' => $invoicelunaskepusat->report(),
        ]);
    }
}
