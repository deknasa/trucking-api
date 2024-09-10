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
        $export = LaporanKalkulasiEmkl::getReport($periode);

        foreach ($export as $data) {
            $data->tglbukti = date('d-m-Y', strtotime($data->tglbukti));
        }
        // dd('test');

        // return response([
        //     'data' => $export_laporanmingguan,
        // ]);

        $data = json_decode($export);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);
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
        $sheet->mergeCells("B$detail_table_header_row:C" . ($detail_table_header_row + 2));
        $sheet->setCellValue("D$detail_table_header_row", 'Rute')->getStyle("D1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("D$detail_table_header_row:D" . ($detail_table_header_row + 2));
        $sheet->setCellValue("E$detail_table_header_row", 'Qty')->getStyle("E1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 2));
        $sheet->setCellValue("F$detail_table_header_row", 'Lokasi Muat')->getStyle("F1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 2));
        $sheet->setCellValue("G$detail_table_header_row", 'No Container/Seal')->getStyle("G1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 2));
        $sheet->setCellValue("H$detail_table_header_row", 'EMKL')->getStyle("H1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:H" . ($detail_table_header_row + 2));
        $sheet->setCellValue("I$detail_table_header_row", 'No SP')->getStyle("I1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("I$detail_table_header_row:K$detail_table_header_row");
        $sheet->setCellValue("I" . ($detail_table_header_row + 1), 'Full')->getStyle("I2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("I" . ($detail_table_header_row + 1) . ":I" . ($detail_table_header_row + 2));
        $sheet->setCellValue("J" . ($detail_table_header_row + 1), 'Empty')->getStyle("J2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("J" . ($detail_table_header_row + 1) . ":J" . ($detail_table_header_row + 2));
        $sheet->setCellValue("K" . ($detail_table_header_row + 1), 'Full/Empty')->getStyle("K2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("K" . ($detail_table_header_row + 1) . ":K" . ($detail_table_header_row + 2));
        $sheet->setCellValue("L$detail_table_header_row", 'No Job')->getStyle("L1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:L" . ($detail_table_header_row + 2));
        $sheet->setCellValue("M$detail_table_header_row", 'Omset')->getStyle("M1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("M$detail_table_header_row:M" . ($detail_table_header_row + 2));
        $sheet->setCellValue("N$detail_table_header_row", 'Omset Tambahan')->getStyle("N1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("N$detail_table_header_row:N" . ($detail_table_header_row + 2));
        $sheet->setCellValue("O$detail_table_header_row", 'Total Omset')->getStyle("O1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("O$detail_table_header_row:O" . ($detail_table_header_row + 2));
        $sheet->setCellValue("P$detail_table_header_row", 'Ket. Tagih Lain')->getStyle("P1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("P$detail_table_header_row:P" . ($detail_table_header_row + 2));
        // $sheet->setCellValue("Q$detail_table_header_row", 'Omset Extra BBM')->getStyle("Q1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        // $sheet->mergeCells("Q$detail_table_header_row:Q" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Q$detail_table_header_row", 'Inv')->getStyle("Q1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Q$detail_table_header_row:Q" . ($detail_table_header_row + 2));

        $sheet->setCellValue("R$detail_table_header_row", 'Biaya Operasional')->getStyle("R1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("R$detail_table_header_row:T$detail_table_header_row");
        $sheet->setCellValue("R" . ($detail_table_header_row + 1), 'Borongan')->getStyle("R2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("R" . ($detail_table_header_row + 1) . ":R" . ($detail_table_header_row + 2));
        $sheet->setCellValue("S" . ($detail_table_header_row + 1), 'EBS')->getStyle("S2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("S" . ($detail_table_header_row + 1) . ":S" . ($detail_table_header_row + 2));
        $sheet->setCellValue("T" . ($detail_table_header_row + 1), 'No Pengeluaran EBS')->getStyle("T2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("T" . ($detail_table_header_row + 1) . ":T" . ($detail_table_header_row + 2));

        $sheet->setCellValue("U$detail_table_header_row", 'Biaya Extra')->getStyle("U1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("U$detail_table_header_row:Z$detail_table_header_row");
        $sheet->setCellValue("U" . ($detail_table_header_row + 1), 'B.Ext Trip')->getStyle("U2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("U" . ($detail_table_header_row + 1) . ":U" . ($detail_table_header_row + 2));
        $sheet->setCellValue("V" . ($detail_table_header_row + 1), 'Ket B.Ex Trip')->getStyle("V2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("V" . ($detail_table_header_row + 1) . ":V" . ($detail_table_header_row + 2));
        $sheet->setCellValue("W" . ($detail_table_header_row + 1), 'U. Makan + B. Ext RIC')->getStyle("W2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("W" . ($detail_table_header_row + 1) . ":W" . ($detail_table_header_row + 2));


        $sheet->setCellValue("X" . ($detail_table_header_row + 1), 'B. Extra Tagih')->getStyle("X2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("X" . ($detail_table_header_row + 1) . ":X" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Y" . ($detail_table_header_row + 1), 'No Biaya Extra')->getStyle("Y2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Y" . ($detail_table_header_row + 1) . ":Y" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Z" . ($detail_table_header_row + 1), 'Ket B. Extra')->getStyle("Z2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Z" . ($detail_table_header_row + 1) . ":Z" . ($detail_table_header_row + 2));

        $sheet->setCellValue("AA" . ($detail_table_header_row), 'Kas Gantung Supir')->getStyle("AA1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("AA" . ($detail_table_header_row) . ":AA" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AB$detail_table_header_row", 'Total Biaya')->getStyle("AB1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("AB$detail_table_header_row:AB" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AC$detail_table_header_row", 'Laba')->getStyle("AC1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("AC$detail_table_header_row:AC" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AD$detail_table_header_row", 'No Trip')->getStyle("AD1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("AD$detail_table_header_row:AD" . ($detail_table_header_row + 2));


        $rowIndex = 4;
        $previous_nopol = null;
        // $group = [];
        $groupRowCount = 0;
        $sheet->setCellValue("A4", "periode : " . $periode );
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
                    $sheet->setCellValue("O$rowIndex", "=SUM(O$startTotalIndex:O$endTotalIndex)")->getStyle("O$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("R$rowIndex", "=SUM(R$startTotalIndex:S$endTotalIndex)")->getStyle("R$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("U$rowIndex", "=SUM(U$startTotalIndex:V$endTotalIndex)")->getStyle("U$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("W$rowIndex", "=SUM(W$startTotalIndex:W$endTotalIndex)")->getStyle("W$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("X$rowIndex", "=SUM(X$startTotalIndex:X$endTotalIndex)")->getStyle("X$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("AA$rowIndex", "=SUM(AA$startTotalIndex:AA$endTotalIndex)")->getStyle("AA$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("AB$rowIndex", "=SUM(AB$startTotalIndex:AB$endTotalIndex)")->getStyle("AB$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->setCellValue("AC$rowIndex", "=SUM(AC$startTotalIndex:AC$endTotalIndex)")->getStyle("AC$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $groupRowCount = 0;
                    $rowIndex++;
                    $rowIndex++; // Move to the next row
                }
                $sheet->setCellValue("C$rowIndex", $nopol);
                $rowIndex++;

                // Store the starting row index of the current group
                $groupStartIndex = $rowIndex;
            }
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

            $sheet->setCellValue("A$rowIndex", $dateValue);
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("C$rowIndex", $response_detail->namasupir);
            $sheet->setCellValue("D$rowIndex", $response_detail->rute);
            $sheet->setCellValue("E$rowIndex", $response_detail->qty);
            $sheet->setCellValue("F$rowIndex", $response_detail->lokasimuat);
            $sheet->setCellValue("G$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("H$rowIndex", $response_detail->emkl);
            $sheet->setCellValue("I$rowIndex", $response_detail->spfull);
            $sheet->setCellValue("J$rowIndex", $response_detail->spempty);
            $sheet->setCellValue("K$rowIndex", $response_detail->spfullempty);
            $sheet->setCellValue("L$rowIndex", $response_detail->jobtrucking);
            $sheet->setCellValue("M$rowIndex", $response_detail->omset)->getStyle("M$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("N$rowIndex", $response_detail->omsettambahan)->getStyle("N$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("O$rowIndex", "=(M$rowIndex+N$rowIndex)")->getStyle("O$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("P$rowIndex", $response_detail->kettagihomset);
            // $sheet->setCellValue("Q$rowIndex", $response_detail->omsetextrabbm)->getStyle("Q$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Q$rowIndex", $response_detail->invoice);
            $sheet->setCellValue("R$rowIndex", $response_detail->borongan)->getStyle("R$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("S$rowIndex", $response_detail->nobuktiebs);
            $sheet->setCellValue("T$rowIndex", $response_detail->pengeluarannobuktiebs);
            
            $sheet->setCellValue("U$rowIndex", $response_detail->uanglain)->getStyle("U$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("V$rowIndex", $response_detail->ketuanglain);
            $sheet->setCellValue("W$rowIndex", $response_detail->uangmakan)->getStyle("W$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("X$rowIndex", $response_detail->biayaextrasupir_nominal)->getStyle("X$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("Y$rowIndex", $response_detail->biayaextrasupir_nobukti);
            $sheet->setCellValue("Z$rowIndex", $response_detail->biayaextrasupir_keterangan);
            $sheet->setCellValue("AA$rowIndex", $response_detail->uangjalan)->getStyle("AA$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AB$rowIndex", "=R$rowIndex+U$rowIndex+W$rowIndex+X$rowIndex")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AC$rowIndex", "=O$rowIndex-AB$rowIndex")->getStyle("AC$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AD$rowIndex", $response_detail->nobukti);
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
            $sheet->setCellValue("O$rowIndex", "=SUM(O$startTotalIndex:O$endTotalIndex)")->getStyle("O$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("R$rowIndex", "=SUM(R$startTotalIndex:R$endTotalIndex)")->getStyle("R$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("U$rowIndex", "=SUM(U$startTotalIndex:U$endTotalIndex)")->getStyle("U$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("W$rowIndex", "=SUM(W$startTotalIndex:W$endTotalIndex)")->getStyle("W$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("X$rowIndex", "=SUM(X$startTotalIndex:X$endTotalIndex)")->getStyle("X$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AA$rowIndex", "=SUM(AA$startTotalIndex:AA$endTotalIndex)")->getStyle("AA$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AB$rowIndex", "=SUM(AB$startTotalIndex:AB$endTotalIndex)")->getStyle("AB$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("AC$rowIndex", "=SUM(AC$startTotalIndex:AC$endTotalIndex)")->getStyle("AC$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(51);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(23);
        $sheet->getColumnDimension('G')->setWidth(24);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setWidth(16);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setWidth(17);
        $sheet->getColumnDimension('T')->setWidth(24);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setWidth(58);
        $sheet->getColumnDimension('W')->setWidth(26);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setWidth(19);
        $sheet->getColumnDimension('Z')->setWidth(19);
        $sheet->getColumnDimension('AA')->setWidth(22);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setWidth(20);
        $sheet->getColumnDimension('AE')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN MINGGUAN SUPIR' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
