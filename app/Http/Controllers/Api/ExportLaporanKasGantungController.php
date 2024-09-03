<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportLaporanKasGantung;
use App\Http\Requests\ValidasiExportKasHarianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportLaporanKasGantungController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
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
    public function export(ValidasiExportKasHarianRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->periode;
            $jenis = $request->bank_id; //kas bank

            $laporanKasGantung = new ExportLaporanKasGantung();
            $laporan_KasGantung = $laporanKasGantung->getExport($sampai, $jenis);

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            $namacabang = 'CABANG ' . $getCabang->namacabang;

            // dd($laporan_KasGantung);

            $data = json_decode($laporan_KasGantung[0]);
            $dataDua = json_decode($laporan_KasGantung[1]);

            $spreadsheet = new Spreadsheet();
            $alphabets = array_merge(range('A', 'Z'), range('AA', 'AZ'), range('BA', 'BZ'), range('CA', 'CZ'));
            $sheetIndex = 0;
            $sheetDates = array_unique(array_column($data, 'tgl'));

            // Create cell styles
            $boldStyle = [
                'font' => ['bold' => true],
            ];
            $borderOutsideStyle = [
                'borders' => [
                    'left' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $borderVertical = [
                'borders' => [
                    'left' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];


            $englishMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $indonesianMonths = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];

            // Laporan Gantung
            foreach ($sheetDates as $date) {
                $sheet = $spreadsheet->createSheet($sheetIndex);
                $spreadsheet->setActiveSheetIndex($sheetIndex);
                $sheet->setTitle(ltrim(date('d', strtotime($date)), 0));
                $sheetIndex++;

                $tanggal = str_replace($englishMonths, $indonesianMonths, date('d - M - Y', strtotime($date)));
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

                $sheet->setCellValue('A1', $data[0]->judul);
                $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A2', $namacabang);
                $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A2:G2');

                $sheet->setCellValue('A3', 'LAPORAN KAS GANTUNG');
                $sheet->getStyle("A3")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A4', 'PER ' . $tanggal);
                $sheet->getStyle("A4")->getFont()->setBold(true);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A4:G4');

                $headerRow = 6;
                $columnIndex = 0;
                $headerColumns = [
                    'tgl' => 'Tanggal',
                    'nobukti' => 'No Bukti',
                    'perkiraan' => 'Perkiraan',
                    'keterangan' => 'Keterangan',
                    'debet' => 'Debet',
                    'kredit' => 'Kredit',
                    'saldo' => 'Saldo',
                ];

                foreach ($headerColumns as $index => $label) {
                    $sheet->setCellValue($alphabets[$columnIndex] . $headerRow, $label);
                    if ($index != 'keterangan') {
                        $sheet->getColumnDimension($alphabets[$columnIndex])->setAutoSize(true);
                    }
                    $sheet->getStyle($alphabets[$columnIndex] . $headerRow)->applyFromArray($boldStyle);
                    // $sheet->getStyle($alphabets[$columnIndex] . $headerRow)->applyFromArray($borderStyle);
                    $columnIndex++;
                }

                $sheet->getColumnDimension('D')->setWidth(75);
                $filteredData = array_filter($data, function ($row) use ($date) {
                    // dd($row);
                    return $row->tgl == $date && $row->jenislaporan != 'LAPORAN REKAP' && $row->jenislaporan != 'LAPORAN REKAP 01';
                });

                $dataRow = $headerRow + 1;
                $columnIndex = 0;
                $lastColumnIndex = array_search('saldo', array_keys($headerColumns)); // Get the index of the "saldo" column
                $rowNumber = 1; // Initial row number

                $totalDebet = 0;
                $totalKredit = 0;

                $previousRow = $dataRow - 1; // Initialize the previous row number

                foreach ($filteredData as $row) {
                    // $sheet->setCellValue('A' . $dataRow, $rowNumber); // Set row number
                    // $sheet->getStyle('A' . $dataRow)->applyFromArray($borderStyle);

                    $columnIndex = 0; // Reset column index for each row
                    foreach ($row as $index => $value) {
                        if ($columnIndex > $lastColumnIndex) {
                            break; // Exit the loop if the column index exceeds the index of the "saldo" column
                        }
                        if ($index == 'tglbukti') {
                            $dateValue = ($value != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($value))) : '';
                            $sheet->setCellValue($alphabets[$columnIndex] . $dataRow, $dateValue);
                        } else {
                            $sheet->setCellValue($alphabets[$columnIndex] . $dataRow, $value);
                            // $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->applyFromArray($borderStyle);
                        }
                        // Apply number format to debet, kredit, and saldo columns
                        if ($index == 'debet' || $index == 'kredit' || $index == 'saldo') {
                            $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                            $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->getNumberFormat()->applyFromArray($boldStyle);
                        }

                        // Apply date format to tgl column
                        if ($index == 'tglbukti') {
                            $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                        }

                        if ($index == 'debet') {
                            $totalDebet += $value;
                        }
                        if ($index == 'kredit') {
                            $totalKredit += $value;
                        }

                        $columnIndex++;
                    }

                    // Add the formula to the current row's J column
                    if ($dataRow == $headerRow + 1) {
                        $sheet->setCellValue('G' . $dataRow, '=(E' . $dataRow . '-F' . $dataRow . ')');
                    }
                    if ($dataRow > $headerRow + 1) {
                        $sheet->setCellValue('G' . $dataRow, '=(G' . $previousRow . '+E' . $dataRow . ')-F' . $dataRow);
                    }
                    // $sheet->getStyle('G' . $dataRow)->applyFromArray($borderStyle);
                    $sheet->getStyle('G' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $previousRow = $dataRow; // Update the previous row number

                    $dataRow++;
                    $rowNumber++; // Increment row number
                }


                // Setelah perulangan selesai, tambahkan total ke sheet
                $sheet->setCellValue('E' . $dataRow, "=SUM(E7:E" . ($dataRow - 1) . ")");
                // $sheet->getStyle('E' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('E' . $dataRow)->applyFromArray($boldStyle);
                $sheet->getStyle('E' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->setCellValue('F' . $dataRow, "=SUM(F7:F" . ($dataRow - 1) . ")");
                // $sheet->getStyle('F' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('F' . $dataRow)->applyFromArray($boldStyle);
                $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->setCellValue('G' . $dataRow, "=(E" . ($dataRow) . "-F" . ($dataRow)  . ")");
                // $sheet->getStyle('G' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('G' . $dataRow)->applyFromArray($boldStyle);
                $sheet->getStyle('G' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                // Merge cells untuk menampilkan teks "TOTAL"
                $sheet->mergeCells('A' . $dataRow . ':D' . $dataRow);
                $sheet->setCellValue('A' . $dataRow, 'TOTAL:');
                $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->applyFromArray($boldStyle);
                // $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->getAlignment()->setHorizontal('right');
            }

            // rekapitulasi
            // Laporan Rekap Perkiraan
            $rekapPerkiraanSheet = $spreadsheet->createSheet($sheetIndex);
            $spreadsheet->setActiveSheetIndex($sheetIndex);
            $rekapPerkiraanSheet->setTitle('REKAPITULASI');
            $sheetIndex++;

            $bulan = $this->getBulan(substr($request->periode, 0, 2));
            $bulan1 = substr($request->periode, 0, 2);
            $tahun = substr($request->periode, 3, 4);
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $rekapPerkiraanSheet->setCellValue('A1', $data[0]->judul);
            $rekapPerkiraanSheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $rekapPerkiraanSheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $rekapPerkiraanSheet->mergeCells('A1:F1');
            $rekapPerkiraanSheet->setCellValue('A2', $namacabang);
            $rekapPerkiraanSheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $rekapPerkiraanSheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $rekapPerkiraanSheet->mergeCells('A2:F2');

            $rekapPerkiraanSheet->setCellValue('A3', 'LAPORAN REKAP KAS GANTUNG');
            $rekapPerkiraanSheet->getStyle("A3")->getFont()->setSize(11)->setBold(true);
            $rekapPerkiraanSheet->getStyle('A3')->getAlignment()->setHorizontal('center');
            $rekapPerkiraanSheet->setCellValue('A4', 'PERIODE : ' . $bulan1 . ' - ' . $tahun);
            $rekapPerkiraanSheet->getStyle("A4")->getFont()->setSize(11)->setBold(true);
            $rekapPerkiraanSheet->getStyle('A4')->getAlignment()->setHorizontal('center');
            $rekapPerkiraanSheet->mergeCells('A3:F3');
            $rekapPerkiraanSheet->mergeCells('A4:F4');

            $rekapPerkiraanHeaderRow = 6;
            $rekapPerkiraanColumnIndex = 0;
            $rekapPerkiraanHeaderColumns = [
                'tglbukti' => 'Tanggal',
                'nobukti' => 'No Bukti',
                'keterangan' => 'Keterangan',
                'nominal' => 'Nominal',
                'nominalbayar' => 'Nominal Bayar',
                'sisa' => 'Sisa',

            ];


            foreach ($rekapPerkiraanHeaderColumns as $index => $label) {
                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanHeaderRow, $label);
                if ($index != 'keterangan') {
                    $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setAutoSize(true);
                }
                $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanHeaderRow)->applyFromArray($boldStyle);
                $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanHeaderRow)->applyFromArray($borderStyle);
                $rekapPerkiraanColumnIndex++;
            }

            $rekapPerkiraanSheet->getColumnDimension('C')->setWidth(98);
            $rekapPerkiraanDataRow = $rekapPerkiraanHeaderRow + 1;
            $rekapPerkiraanColumnIndex = 0;
            $rekapPerkiraanRowNumber = 1; // Initial row number
            $prevNobukti = '';
            $start = 0;
            $kelang = 1;
            // dd($dataDua);
            foreach ($dataDua as $row) {
                $nobuktiAwal = $row->nobukti3;

                $rekapPerkiraanColumnIndex = 0;
                // $rekapPerkiraanSheet->setCellValue('A' . $rekapPerkiraanDataRow, $rekapPerkiraanRowNumber); // Set nomor baris
                // $rekapPerkiraanSheet->getStyle('A' . $rekapPerkiraanDataRow)->applyFromArray($borderVertical);
                // if($nobuktiAwal != $prevNobukti){

                // }
                if ($row->jenis != "3") {

                    $dateValue = ($row->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($row->tglbukti))) : '';
                    $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $dateValue);
                    $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                } else {
                    $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical);
                }
                $rekapPerkiraanColumnIndex++;

                if ($row->jenis != "3") {
                    $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->nobukti);
                } else {
                    $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical);
                }
                $rekapPerkiraanColumnIndex++;

                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->keterangan)->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical);
                if ($row->jenis == "1") {
                    $rekapPerkiraanSheet->getStyle("A$rekapPerkiraanDataRow:F$rekapPerkiraanDataRow")->applyFromArray($boldStyle);
                }
                $rekapPerkiraanColumnIndex++;


                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->nominal);
                $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $rekapPerkiraanColumnIndex++;

                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->nominalbayar);
                $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $rekapPerkiraanColumnIndex++;

                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->sisa);
                $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderVertical)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $rekapPerkiraanColumnIndex++;


                if ($nobuktiAwal != $prevNobukti) {
                    if ($prevNobukti != '') {
                        // $rekapPerkiraanSheet->getStyle("A" . ($rekapPerkiraanDataRow - $kelang) . ":F" . ($rekapPerkiraanDataRow - 1))->applyFromArray($borderOutsideStyle);
                        $rekapPerkiraanSheet->getStyle("A$rekapPerkiraanDataRow:F$rekapPerkiraanDataRow")->applyFromArray($borderOutsideStyle);
                    }
                } else {
                    $rekapPerkiraanSheet->getStyle("A$rekapPerkiraanDataRow:F$rekapPerkiraanDataRow")->applyFromArray($borderVertical);
                    $kelang++;
                }

                $rekapPerkiraanDataRow++;
                $rekapPerkiraanColumnIndex = 0;
                $rekapPerkiraanRowNumber++;
                $prevNobukti = $row->nobukti3;
            }

            if ($prevNobukti != '') {
                // $rekapPerkiraanSheet->getStyle("A" . ($rekapPerkiraanDataRow - $kelang) . ":F" . ($rekapPerkiraanDataRow - 1))->applyFromArray($borderOutsideStyle);
            }



            // Menghitung total kolom D (nominaldebet)
            $rekapPerkiraanSheet->setCellValue('D' . $rekapPerkiraanDataRow, "=SUM(D5:D" . ($rekapPerkiraanDataRow - 1) . ")");
            $rekapPerkiraanSheet->getStyle('D' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('D' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('D' . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Menghitung total kolom E (nominalkredit)
            $rekapPerkiraanSheet->setCellValue('E' . $rekapPerkiraanDataRow, "=SUM(E5:E" . ($rekapPerkiraanDataRow - 1) . ")");
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Menghitung total kolom F (nominalsisa)
            $rekapPerkiraanSheet->setCellValue('F' . $rekapPerkiraanDataRow, "=SUM(F5:F" . ($rekapPerkiraanDataRow - 1) . ")");
            $rekapPerkiraanSheet->getStyle('F' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('F' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('F' . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Merge cells A hingga C dan tampilkan tulisan "TOTAL:"
            $rekapPerkiraanSheet->mergeCells('A' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow);
            $rekapPerkiraanSheet->setCellValue('A' . $rekapPerkiraanDataRow, 'TOTAL:');
            $rekapPerkiraanSheet->getStyle('A' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('A' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('A' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow)->getAlignment()->setHorizontal('right');


            // end rekapitulasi

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN KAS GANTUNG ' . date('dmYHis');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
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
}
