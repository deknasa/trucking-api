<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKalkulasiEmkl;
use App\Http\Requests\StoreLaporanKalkulasiEmklRequest;
use App\Http\Requests\UpdateLaporanKalkulasiEmklRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKalkulasiEmklController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        $jenis = $request->jenis;
        $export = LaporanKalkulasiEmkl::getReport($periode,$jenis);

        foreach ($export as $data) {
            $data->tglbukti = date('d-m-Y', strtotime($data->tglbukti));
        }
        // dd('test');

        // return response([
        //     'data' => $export_laporanmingguan,
        // ]);

        $data = json_decode($export);

        if ($jenis == 1) {
            $this->export1($data, $request->periode, $request->jenis );
        }
        if ($jenis == 2) {
            $this->export2($data, $request->periode, $request->jenis);
        }
    }

    public function export1($data, $periode, $jenis)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Comic Sans MS');

        $detail_table_header_row = 1;
        $detail_start_row = $detail_table_header_row + 1;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];

        $styleArray2 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        $sheet->setCellValue("A$detail_table_header_row", 'Tanggal')->getStyle("A1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("A$detail_table_header_row:A" . ($detail_table_header_row + 2));
        $sheet->setCellValue("B$detail_table_header_row", 'Gandengan')->getStyle("B1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("B$detail_table_header_row:B" . ($detail_table_header_row + 2));
        $sheet->setCellValue("C$detail_table_header_row", 'No')->getStyle("C1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("C$detail_table_header_row:D" . ($detail_table_header_row + 2));
        $sheet->setCellValue("E$detail_table_header_row", 'Rute')->getStyle("E1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 2));
        $sheet->setCellValue("F$detail_table_header_row", 'Qty')->getStyle("F1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 2));
        $sheet->setCellValue("G$detail_table_header_row", 'Lokasi Muat')->getStyle("G1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 2));
        $sheet->setCellValue("H$detail_table_header_row", 'No Container/Seal')->getStyle("H1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:H" . ($detail_table_header_row + 2));
        $sheet->setCellValue("I$detail_table_header_row", 'EMKL')->getStyle("I1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("I$detail_table_header_row:I" . ($detail_table_header_row + 2));
        $sheet->setCellValue("J$detail_table_header_row", 'No SP')->getStyle("J1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("J$detail_table_header_row:K$detail_table_header_row");
        $sheet->setCellValue("J" . ($detail_table_header_row + 1), 'Full')->getStyle("J2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("J" . ($detail_table_header_row + 1) . ":J" . ($detail_table_header_row + 2));
        $sheet->setCellValue("K" . ($detail_table_header_row + 1), 'Empty')->getStyle("K2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("K" . ($detail_table_header_row + 1) . ":K" . ($detail_table_header_row + 2));
        $sheet->setCellValue("L$detail_table_header_row", 'No Job')->getStyle("L1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:L" . ($detail_table_header_row + 2));
        $sheet->setCellValue("M$detail_table_header_row", 'Omset')->getStyle("M1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("M$detail_table_header_row:M" . ($detail_table_header_row + 2));
        $sheet->setCellValue("N$detail_table_header_row", 'Inv')->getStyle("N1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("N$detail_table_header_row:N" . ($detail_table_header_row + 2));
        $sheet->setCellValue("O$detail_table_header_row", 'Gaji')->getStyle("O1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("O" . ($detail_table_header_row + 1), 'Borongan')->getStyle("O2")->applyFromArray($styleHeader)->getFont()->setBold(true);

        $sheet->setCellValue("R2", 'Komisi')->getStyle("R2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("T2", 'Lain')->getStyle("T2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("T3", 'Extra')->getStyle("T3")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("V3", 'Ritasi')->getStyle("V3")->applyFromArray($styleHeader)->getFont()->setBold(true);
        
        $sheet->setCellValue("W" . ($detail_table_header_row + 1), 'Uang Makan')->getStyle("W2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("W" . ($detail_table_header_row + 1) . ":W" . ($detail_table_header_row + 2));
        $sheet->setCellValue("X3", 'Ket')->getStyle("X3")->applyFromArray($styleHeader)->getFont()->setBold(true);

        $sheet->setCellValue("Y1", 'Biaya')->getStyle("Y1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("Y1" . ":Z1");

        $sheet->setCellValue("Y" . ($detail_table_header_row + 1), 'Uang Jalan')->getStyle("Y2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("Y" . ($detail_table_header_row + 1) . ":Y" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Z" . ($detail_table_header_row + 1), 'BBM')->getStyle("Z2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("Z" . ($detail_table_header_row + 1) . ":Z" . ($detail_table_header_row + 2));

        $sheet->setCellValue("AB" . ($detail_table_header_row + 1), 'Total Biaya')->getStyle("AB2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("AB" . ($detail_table_header_row + 1) . ":AB" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AC" . ($detail_table_header_row + 1), 'Sisa')->getStyle("AC2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("AC" . ($detail_table_header_row + 1) . ":AC" . ($detail_table_header_row + 1));


        $rowIndex = 4;
        $previous_nopol = null;
        // $group = [];
        $groupRowCount = 0;
        $sheet->setCellValue("G4", "periode : " . $dari . " s/d " . $sampai);
        foreach ($data as $response_index => $response_detail) {
            $nopol = $response_detail->nopol;

            if ($nopol != $previous_nopol) {
                if ($previous_nopol !== null) {
                    // $rowIndex++; // Move to the next row
                    // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

                    // Calculate the total for the previous group and set it in the next column
                    $startTotalIndex = $rowIndex - $groupRowCount;
                    $endTotalIndex = $rowIndex - 1;

                    $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("O$rowIndex", "=SUM(O$startTotalIndex:O$endTotalIndex)")->getStyle("O$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("R$rowIndex", "=SUM(R$startTotalIndex:R$endTotalIndex)")->getStyle("R$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("W$rowIndex", "=SUM(W$startTotalIndex:W$endTotalIndex)")->getStyle("W$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("AC$rowIndex", "=SUM(AC$startTotalIndex:AC$endTotalIndex)")->getStyle("AC$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("AB$rowIndex", "=SUM(AB$startTotalIndex:AB$endTotalIndex)")->getStyle("AB$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $groupRowCount = 0;
                    $rowIndex++;
                    $rowIndex++; // Move to the next row
                }
                $sheet->setCellValue("D$rowIndex", $nopol);
                $rowIndex++;

                // Store the starting row index of the current group
                $groupStartIndex = $rowIndex;
            }
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

            $sheet->setCellValue("A$rowIndex", $dateValue);
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("B$rowIndex", $response_detail->gandengan);
            $sheet->setCellValue("C$rowIndex", $response_detail->nopol);
            $sheet->setCellValue("D$rowIndex", $response_detail->namasupir);
            $sheet->setCellValue("E$rowIndex", $response_detail->rute);
            $sheet->setCellValue("F$rowIndex", $response_detail->qty);
            $sheet->setCellValue("G$rowIndex", $response_detail->lokasimuat);
            $sheet->setCellValue("H$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("I$rowIndex", $response_detail->emkl);
            $sheet->setCellValue("J$rowIndex", $response_detail->spfull);
            $sheet->setCellValue("K$rowIndex", $response_detail->spempty);
            $sheet->setCellValue("L$rowIndex", $response_detail->jobtrucking);
            $sheet->setCellValue("M$rowIndex", $response_detail->omsetmedan)->getStyle("M$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("N$rowIndex", $response_detail->invoice);
            $sheet->setCellValue("O$rowIndex", $response_detail->gajisupir)->getStyle("O$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");            
            $sheet->setCellValue("P$rowIndex", $response_detail->nobuktiebs);
            $sheet->setCellValue("Q$rowIndex", $response_detail->pengeluarannobuktiebs);
            $sheet->setCellValue("R$rowIndex", $response_detail->komisi)->getStyle("R$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("T$rowIndex", $response_detail->uangextra)->getStyle("T$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("V$rowIndex", $response_detail->ritasi)->getStyle("V$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("W$rowIndex", $response_detail->uangmakan)->getStyle("W$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("X$rowIndex", $response_detail->ketritasi);
            $sheet->setCellValue("Y$rowIndex", $response_detail->uangjalan)->getStyle("Y$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Z$rowIndex", $response_detail->uangbbm)->getStyle("Z$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            
            
            $sheet->setCellValue("AB$rowIndex", "=O$rowIndex+R$rowIndex+T$rowIndex+V$rowIndex+W$rowIndex")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AC$rowIndex", "=M$rowIndex-AB$rowIndex")->getStyle("AC$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AD$rowIndex", $response_detail->nobukti);

            $rowIndex++;

            // Store the current group details in an array
            $group[] = $response_detail;
            $sheet->getColumnDimension('A')->setWidth(7);
            $previous_nopol = $nopol;
            $groupRowCount++;
        }

        // Add total and calculate the total for the last group
        if ($previous_nopol !== null) {
            // $rowIndex++;
            // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

            $startTotalIndex = $groupStartIndex;
            $endTotalIndex = $rowIndex - 1;

            $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("O$rowIndex", "=SUM(O$startTotalIndex:O$endTotalIndex)")->getStyle("O$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("R$rowIndex", "=SUM(R$startTotalIndex:R$endTotalIndex)")->getStyle("R$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("W$rowIndex", "=SUM(W$startTotalIndex:W$endTotalIndex)")->getStyle("W$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AC$rowIndex", "=SUM(AC$startTotalIndex:AC$endTotalIndex)")->getStyle("AC$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AB$rowIndex", "=SUM(AB$startTotalIndex:AB$endTotalIndex)")->getStyle("AB$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        }
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);



        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN MINGGUAN SUPIR' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function export2($data, $periode, $jenis)
    {
        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $detail_table_header_row = 1;
        $detail_start_row = $detail_table_header_row + 1;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];

        $styleArray2 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        $sheet->setCellValue("A$detail_table_header_row", 'Tanggal')->getStyle("A1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("A$detail_table_header_row:A" . ($detail_table_header_row + 2));
        $sheet->setCellValue("B$detail_table_header_row", 'No')->getStyle("B1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("B$detail_table_header_row:B" . ($detail_table_header_row + 2));
        $sheet->setCellValue("C$detail_table_header_row", 'Rute')->getStyle("C1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("C$detail_table_header_row:C" . ($detail_table_header_row + 2));
        $sheet->setCellValue("D$detail_table_header_row", 'Qty')->getStyle("D1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("D$detail_table_header_row:D" . ($detail_table_header_row + 2));
        $sheet->setCellValue("E$detail_table_header_row", 'Lokasi Muat')->getStyle("E1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 2));
        $sheet->setCellValue("F$detail_table_header_row", 'No Container/Seal')->getStyle("F1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 2));
        $sheet->setCellValue("G$detail_table_header_row", 'EMKL')->getStyle("G1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 2));
        $sheet->setCellValue("H$detail_table_header_row", 'No SP')->getStyle("H1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:J$detail_table_header_row");
        $sheet->setCellValue("H" . ($detail_table_header_row + 1), 'Full')->getStyle("H2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("H" . ($detail_table_header_row + 1) . ":H" . ($detail_table_header_row + 2));
        $sheet->setCellValue("I" . ($detail_table_header_row + 1), 'Empty')->getStyle("I2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("I" . ($detail_table_header_row + 1) . ":I" . ($detail_table_header_row + 2));
        $sheet->setCellValue("J" . ($detail_table_header_row + 1), 'Full/Empty')->getStyle("J2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("J" . ($detail_table_header_row + 1) . ":J" . ($detail_table_header_row + 2));
        $sheet->setCellValue("K$detail_table_header_row", 'No Job')->getStyle("K1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("K$detail_table_header_row:K" . ($detail_table_header_row + 2));
        $sheet->setCellValue("L$detail_table_header_row", 'Omset')->getStyle("L1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:L" . ($detail_table_header_row + 2));
        $sheet->setCellValue("M$detail_table_header_row", 'Omset Tambahan')->getStyle("M1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("M$detail_table_header_row:M" . ($detail_table_header_row + 2));
        $sheet->setCellValue("N$detail_table_header_row", 'Total Omset')->getStyle("N1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("N$detail_table_header_row:N" . ($detail_table_header_row + 2));
        $sheet->setCellValue("O$detail_table_header_row", 'Inv')->getStyle("O1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("O$detail_table_header_row:O" . ($detail_table_header_row + 2));
        $sheet->setCellValue("P$detail_table_header_row", 'Biaya Operasional')->getStyle("P1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("P$detail_table_header_row:W$detail_table_header_row");
        $sheet->setCellValue("P" . ($detail_table_header_row + 1), 'Borongan')->getStyle("P2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("P" . ($detail_table_header_row + 1) . ":P" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Q" . ($detail_table_header_row + 1), 'EBS')->getStyle("Q2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Q" . ($detail_table_header_row + 1) . ":Q" . ($detail_table_header_row + 2));
        $sheet->setCellValue("R" . ($detail_table_header_row + 1), 'No Pengeluaran EBS')->getStyle("R2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("R" . ($detail_table_header_row + 1) . ":R" . ($detail_table_header_row + 2));
        $sheet->setCellValue("S" . ($detail_table_header_row + 1), 'Komisi Supir')->getStyle("S2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("S" . ($detail_table_header_row + 1) . ":S" . ($detail_table_header_row + 2));
        $sheet->setCellValue("T" . ($detail_table_header_row + 1), 'Komisi Kenek ')->getStyle("T2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("T" . ($detail_table_header_row + 1) . ":T" . ($detail_table_header_row + 2));

        $sheet->setCellValue("U" . ($detail_table_header_row + 1), 'No Bukti Komisi')->getStyle("U2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("U" . ($detail_table_header_row + 1) . ":U" . ($detail_table_header_row + 2));
        $sheet->setCellValue("V" . ($detail_table_header_row + 1), 'G. LAIN')->getStyle("V2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("V" . ($detail_table_header_row + 1) . ":V" . ($detail_table_header_row + 2));
        $sheet->setCellValue("W" . ($detail_table_header_row + 1), '')->getStyle("W")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("W" . ($detail_table_header_row + 1) . ":W" . ($detail_table_header_row + 2));
        $sheet->setCellValue("X" . ($detail_table_header_row + 1), 'Ket')->getStyle("X2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("X" . ($detail_table_header_row + 1) . ":X" . ($detail_table_header_row + 2));

        $sheet->setCellValue("Y$detail_table_header_row", 'Total Biaya')->getStyle("Y1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Y$detail_table_header_row:Y" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Z$detail_table_header_row", 'Laba')->getStyle("Z1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Z$detail_table_header_row:Z" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AA$detail_table_header_row", 'No Trip')->getStyle("AA1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("AA$detail_table_header_row:AA" . ($detail_table_header_row + 2));

        $rowIndex = 4;
        $previous_nopol = null;
        // $group = [];
        $groupRowCount = 0;
        $sheet->setCellValue("A4", "periode : " . $dari . " s/d " . $sampai);
        foreach ($data as $response_index => $response_detail) {
            $nopol = $response_detail->nopol;

            if ($nopol != $previous_nopol) {
                if ($previous_nopol !== null) {
                    // $rowIndex++; // Move to the next row
                    // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

                    // Calculate the total for the previous group and set it in the next column
                    $startTotalIndex = $rowIndex - $groupRowCount;
                    $endTotalIndex = $rowIndex - 1;

                    $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("N$rowIndex", "=SUM(N$startTotalIndex:N$endTotalIndex)")->getStyle("N$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("L$rowIndex", "=SUM(L$startTotalIndex:L$endTotalIndex)")->getStyle("L$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("P$rowIndex", "=SUM(P$startTotalIndex:P$endTotalIndex)")->getStyle("P$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("S$rowIndex", "=SUM(S$startTotalIndex:S$endTotalIndex)")->getStyle("S$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("T$rowIndex", "=SUM(T$startTotalIndex:T$endTotalIndex)")->getStyle("T$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $groupRowCount = 0;
                    $rowIndex++;
                    $rowIndex++; // Move to the next row
                }
                $sheet->setCellValue("B$rowIndex", $nopol);
                $rowIndex++;

                // Store the starting row index of the current group
                $groupStartIndex = $rowIndex;
            }
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

            $sheet->setCellValue("A$rowIndex", $dateValue);
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("B$rowIndex", $response_detail->namasupir);
            $sheet->setCellValue("C$rowIndex", $response_detail->rute);
            $sheet->setCellValue("D$rowIndex", $response_detail->qty);
            $sheet->setCellValue("E$rowIndex", $response_detail->lokasimuat);
            $sheet->setCellValue("F$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("G$rowIndex", $response_detail->emkl);
            $sheet->setCellValue("H$rowIndex", $response_detail->spfull);
            $sheet->setCellValue("I$rowIndex", $response_detail->spempty);
            $sheet->setCellValue("J$rowIndex", $response_detail->spfullempty);
            $sheet->setCellValue("K$rowIndex", $response_detail->jobtrucking);
            $sheet->setCellValue("L$rowIndex", $response_detail->omset)->getStyle("L$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("M$rowIndex", $response_detail->omsettambahan)->getStyle("M$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("N$rowIndex", "=(L$rowIndex+M$rowIndex)")->getStyle("N$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("O$rowIndex", $response_detail->invoice);
            $sheet->setCellValue("P$rowIndex", $response_detail->borongan)->getStyle("P$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Q$rowIndex", $response_detail->nobuktiebs);
            $sheet->setCellValue("R$rowIndex", $response_detail->pengeluarannobuktiebs);
            $sheet->setCellValue("S$rowIndex", $response_detail->komisi)->getStyle("S$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("T$rowIndex", $response_detail->gajikenek)->getStyle("T$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->setCellValue("U$rowIndex", $response_detail->nobuktikbtkomisi);
            $sheet->setCellValue("V$rowIndex", $response_detail->uanglain)->getStyle("V$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->setCellValue("W$rowIndex", $response_detail->nobuktikbtebs2);
            $sheet->setCellValue("X$rowIndex", $response_detail->ketuanglain);
            $sheet->setCellValue("Y$rowIndex", "=P$rowIndex+S$rowIndex+T$rowIndex+V$rowIndex")->getStyle("Y$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Z$rowIndex", "=N$rowIndex-Y$rowIndex")->getStyle("Z$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AA$rowIndex", $response_detail->nobukti);
            $rowIndex++;

            // Store the current group details in an array
            $group[] = $response_detail;
            $sheet->getColumnDimension('A')->setWidth(12);
            $previous_nopol = $nopol;
            $groupRowCount++;
        }

        // Add total and calculate the total for the last group
        if ($previous_nopol !== null) {
            // $rowIndex++;
            // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

            $startTotalIndex = $groupStartIndex;
            $endTotalIndex = $rowIndex - 1;

            $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("N$rowIndex", "=SUM(N$startTotalIndex:N$endTotalIndex)")->getStyle("N$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("L$rowIndex", "=SUM(L$startTotalIndex:L$endTotalIndex)")->getStyle("L$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("P$rowIndex", "=SUM(P$startTotalIndex:P$endTotalIndex)")->getStyle("P$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("S$rowIndex", "=SUM(S$startTotalIndex:S$endTotalIndex)")->getStyle("S$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("T$rowIndex", "=SUM(T$startTotalIndex:T$endTotalIndex)")->getStyle("T$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN MINGGUAN SUPIR' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

}
