<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ExportLaporanKasHarian;
use App\Http\Requests\ValidasiExportKasHairanRequest;
use App\Http\Requests\ValidasiExportKasHarianRequest;
use App\Http\Requests\ValidasiReportKasHarianRequest;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExportLaporanKasHarianController extends Controller
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

    public function tgl_indo($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
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
            $jenis = $request->bank_id;

            $exportkasharian = new ExportLaporanKasHarian();
            $export_kasharian = $exportkasharian->getExport($sampai, $jenis);

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            foreach ($export_kasharian[0] as $data) {
                $data->tgl = date('d-m-Y', strtotime($data->tgl));
            }

            // return response([
            //     'data' => $export_kasharian[0],
            //     'dataDua' => $export_kasharian[1],
            //     'namacabang' => 'CABANG ' . $getCabang->namacabang,
            //     'namacabang2' =>  $getCabang->namacabang
            // ]);

            $data = json_decode($export_kasharian[0]);
            $dataLaporanRekap = json_decode($export_kasharian[0]);
            $dataLaporanRekap01 = json_decode($export_kasharian[0]);
            $dataDua = json_decode($export_kasharian[1]);
            $namacabang = 'CABANG ' . $getCabang->namacabang;
            $namacabang2 = $getCabang->namacabang;
            $disetujui = $pengeluaran[0]->disetujui ?? '';
            $diperiksa = $pengeluaran[0]->diperiksa ?? '';

            // dd($data, $dataDua);

            // dd($data, $dataDua);
            if ($jenis == 1) {
                $kasbank = 'KAS HARIAN';
                $norek = '';
            } else {
                $kasbank = 'BANK';
                $norek = '(' . $request->bank . ')';
            }

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $alphabets = array_merge(range('A', 'Z'), range('AA', 'AZ'), range('BA', 'BZ'), range('CA', 'CZ'));
            $sheetIndex = 0;
            $sheetDates = array_unique(array_column($data, 'tgl'));

            // Create cell styles
            $boldStyle = [
                'font' => ['bold' => true],
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

            // Laporan Harian
            foreach ($sheetDates as $date) {
                $sheet = $spreadsheet->createSheet($sheetIndex);
                $spreadsheet->setActiveSheetIndex($sheetIndex);
                $sheet->setTitle(ltrim(date('d', strtotime($date)), 0));
                $sheetIndex++;

                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                $sheet->setCellValue('A1', $data[0]->judul);
                $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A2', $namacabang);
                $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A2:H2');


                $tanggal = str_replace($englishMonths, $indonesianMonths, date('d - M - Y', strtotime($date)));

                $sheet->setCellValue('A3', 'LAPORAN ' . $kasbank . ' ' . $norek);
                $sheet->getStyle("A3")->getFont()->setBold(true);
                // $sheet->mergeCells('A2:H2');

                $sheet->setCellValue('A4', 'PER ' . $tanggal);
                $sheet->getStyle("A4")->getFont()->setBold(true);
                // $sheet->mergeCells('A3:H3');

                $headerRow = 6;
                $columnIndex = 0;
                $headerColumns = [
                    'no' => 'No',
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
                    if ($index == 'keterangan') {
                        $sheet->getColumnDimension($alphabets[$columnIndex])->setWidth(71);
                    } else if ($index == 'no') {
                        $sheet->getColumnDimension($alphabets[$columnIndex])->setWidth(4);
                    } else {
                        $sheet->getColumnDimension($alphabets[$columnIndex])->setAutoSize(true);
                    }
                    $sheet->getStyle($alphabets[$columnIndex] . $headerRow)->applyFromArray($boldStyle);
                    // $sheet->getStyle($alphabets[$columnIndex] . $headerRow)->applyFromArray($borderStyle);
                    $columnIndex++;
                }
                // dd('tst');

                $filteredData = array_filter($data, function ($row) use ($date) {
                    // dd($row->jenislaporan,$row->tgl, $date);
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
                    $sheet->setCellValue('A' . $dataRow, $rowNumber); // Set row number
                    // $sheet->getStyle('A' . $dataRow)->applyFromArray($borderStyle);
                    unset($row->jenislaporan);
                    unset($row->jenis);
                    $columnIndex = 1; // Reset column index for each row
                    foreach ($row as $index => $value) {
                        if ($columnIndex > $lastColumnIndex) {
                            break; // Exit the loop if the column index exceeds the index of the "saldo" column
                        }

                        if ($index == 'tgl') {
                            $dateValue = ($value != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($value))) : '';
                            $sheet->setCellValue($alphabets[$columnIndex] . $dataRow, $dateValue);
                        } else {
                            if($index == 'debet'){
                                if ($row->nilaikosongdebet == 1) { 
                                    $sheet->setCellValueExplicit($alphabets[$columnIndex] . $dataRow, null, DataType::TYPE_NULL);  
                                }else{ 
                                    $sheet->setCellValue($alphabets[$columnIndex] . $dataRow,  $value);
                                }
                            } else if($index == 'kredit'){
                                if ($row->nilaikosongkredit == 1) { 
                                    $sheet->setCellValueExplicit($alphabets[$columnIndex] . $dataRow, null, DataType::TYPE_NULL);  
                                }else{ 
                                    $sheet->setCellValue($alphabets[$columnIndex] . $dataRow,  $value);
                                }
                            } else {
                                $sheet->setCellValue($alphabets[$columnIndex] . $dataRow, $value);
                            }
                        }
                        // $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->applyFromArray($borderStyle);

                        // Apply number format to debet, kredit, and saldo columns
                        if ($index == 'debet' || $index == 'kredit' || $index == 'saldo') {
                            // if ($value == 0) {
                            //     $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->getNumberFormat()->setFormatCode(";-0;;@");
                            // } else {
                                $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                            // }


                            // $sheet->getStyle($alphabets[$columnIndex] . $dataRow)->getNumberFormat()->applyFromArray($boldStyle);
                        }

                        // Apply date format to tgl column
                        if ($index == 'tgl') {
                            $sheet->getStyle($alphabets[$columnIndex] . $dataRow)
                                ->getNumberFormat()
                                ->setFormatCode('dd-mm-yyyy');
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
                    if ($dataRow > $headerRow + 1) {
                        $sheet->setCellValue('H' . $dataRow, '=(H' . $previousRow . '+F' . $dataRow . ')-G' . $dataRow);
                    }
                    // $sheet->getStyle('H' . $dataRow)->applyFromArray($borderStyle);
                    $sheet->getStyle('H' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $previousRow = $dataRow; // Update the previous row number

                    $dataRow++;
                    $rowNumber++; // Increment row number
                }


                // Setelah perulangan selesai, tambahkan total ke sheet
                $sheet->setCellValue('F' . $dataRow, "=SUM(F6:F" . ($dataRow - 1) . ")");
                // $sheet->getStyle('F' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('F' . $dataRow)->applyFromArray($boldStyle);
                $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->setCellValue('G' . $dataRow, "=SUM(G6:G" . ($dataRow - 1) . ")");
                // $sheet->getStyle('G' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('G' . $dataRow)->applyFromArray($boldStyle);
                $sheet->getStyle('G' . $dataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                // Merge cells untuk menampilkan teks "TOTAL"
                $sheet->mergeCells('A' . $dataRow . ':E' . $dataRow);
                $sheet->setCellValue('A' . $dataRow, 'TOTAL:');
                $sheet->getStyle('A' . $dataRow . ':H' . $dataRow)->applyFromArray($boldStyle);
                // $sheet->getStyle('A' . $dataRow . ':H' . $dataRow)->applyFromArray($borderStyle);
                $sheet->getStyle('A' . $dataRow . ':E' . $dataRow)->getAlignment()->setHorizontal('right');
            }

            // Laporan Rekap
            $rekapSheet = $spreadsheet->createSheet($sheetIndex);
            $spreadsheet->setActiveSheetIndex($sheetIndex);
            $rekapSheet->setTitle('LAPORAN REKAP');
            $sheetIndex++;

            $bulan = $this->getBulan(substr($request->periode, 0, 2));
            $tahun = substr($request->periode, 3, 4);

            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $rekapSheet->setCellValue('A1', $data[0]->judul);
            $rekapSheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $rekapSheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $rekapSheet->mergeCells('A1:H1');
            $rekapSheet->setCellValue('A2', $namacabang);
            $rekapSheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $rekapSheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $rekapSheet->mergeCells('A2:H2');


            $rekapSheet->setCellValue('A3', 'LAPORAN REKAP ' . $kasbank . ' ' . $norek);
            $rekapSheet->getStyle("A3")->getFont()->setBold(true);
            // $rekapSheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            // $rekapSheet->mergeCells('A2:H2');

            $rekapSheet->setCellValue('A4', 'PERIODE ' . $bulan . ' - ' . $tahun);
            $rekapSheet->getStyle("A4")->getFont()->setBold(true);
            // $rekapSheet->getStyle('A3')->getAlignment()->setHorizontal('center');
            // $rekapSheet->mergeCells('A3:H3');

            $rekapHeaderRow = 6;
            $rekapColumnIndex = 0;
            $rekapHeaderColumns = [
                // 'no' => 'No',
                'tgl' => 'Tanggal',
                'nobukti' => 'No Bukti',
                'perkiraan' => 'Perkiraan',
                'keterangan' => 'Keterangan',
                'debet' => 'Debet',
                'kredit' => 'Kredit',
                'saldo' => 'Saldo',
            ];


            foreach ($rekapHeaderColumns as $index => $label) {
                $rekapSheet->setCellValue($alphabets[$rekapColumnIndex] . $rekapHeaderRow, $label);
                if ($index == 'keterangan') {
                    $rekapSheet->getColumnDimension($alphabets[$rekapColumnIndex])->setWidth(70);
                } else if ($index == 'no') {
                    $rekapSheet->getColumnDimension($alphabets[$rekapColumnIndex])->setWidth(4);
                } else if ($index == 'tgl') {
                    $rekapSheet->getColumnDimension($alphabets[$rekapColumnIndex])->setWidth(12);
                } else if ($index == 'perkiraan') {
                    $rekapSheet->getColumnDimension($alphabets[$rekapColumnIndex])->setWidth(25);
                } else {
                    $rekapSheet->getColumnDimension($alphabets[$rekapColumnIndex])->setAutoSize(true);
                }
                $rekapSheet->getStyle($alphabets[$rekapColumnIndex] . $rekapHeaderRow)->applyFromArray($boldStyle);
                $rekapColumnIndex++;
            }

            $filteredRekapData = array_filter($dataLaporanRekap, function ($row) {
                // $row->jenislaporan = 'LAPORAN REKAP';
                return $row->jenislaporan == 'LAPORAN REKAP';
            });

            $rekapDataRow = $rekapHeaderRow + 1;
            $rekapColumnIndex = 0;
            $lastRekapColumnIndex = array_search('saldo', array_keys($rekapHeaderColumns)); // Get the index of the "saldo" column
            $rekapRowNumber = 1; // Initial row number

            $totalDebet = 0;
            $totalKredit = 0;

            $previousRow = $rekapDataRow - 1; // Initialize the previous row number

            foreach ($filteredRekapData as $row) {
                unset($row->jenislaporan);
                unset($row->jenis);
                $rekapColumnIndex = 0; // Reset column index for each row
                foreach ($row as $index => $value) {
                    if ($rekapColumnIndex > $lastRekapColumnIndex) {
                        break; // Exit the loop if the column index exceeds the index of the "saldo" column
                    }
                    if ($index == 'tgl') {
                        $dateValue = ($value != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($value))) : '';
                        $rekapSheet->setCellValue($alphabets[$rekapColumnIndex] . $rekapDataRow, $dateValue);
                    } else {
                        if($index == 'debet'){
                            if ($row->nilaikosongdebet == 1) { 
                                $rekapSheet->setCellValueExplicit($alphabets[$rekapColumnIndex] . $rekapDataRow, null, DataType::TYPE_NULL);  
                            }else{ 
                                $rekapSheet->setCellValue($alphabets[$rekapColumnIndex] . $rekapDataRow,  $value);
                            }
                        } else if($index == 'kredit'){
                            if ($row->nilaikosongkredit == 1) { 
                                $rekapSheet->setCellValueExplicit($alphabets[$rekapColumnIndex] . $rekapDataRow, null, DataType::TYPE_NULL);  
                            }else{ 
                                $rekapSheet->setCellValue($alphabets[$rekapColumnIndex] . $rekapDataRow,  $value);
                            }
                        } else {
                            $rekapSheet->setCellValue($alphabets[$rekapColumnIndex] . $rekapDataRow, $value);
                        }
                    }
                    // Apply number format to debet, kredit, and saldo columns
                    if ($index == 'debet' || $index == 'kredit' || $index == 'saldo') {
                        // if ($value == 0) {
                        //     $rekapSheet->getStyle($alphabets[$rekapColumnIndex] . $rekapDataRow)->getNumberFormat()->setFormatCode(";-0;;@");
                        // } else {
                            $rekapSheet->getStyle($alphabets[$rekapColumnIndex] . $rekapDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        // }
                    }

                    // Apply date format to tgl column
                    if ($index == 'tgl') {
                        $rekapSheet->getStyle($alphabets[$rekapColumnIndex] . $rekapDataRow)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                    }
                    if ($index == 'debet') {
                        $totalDebet += $value;
                    }
                    if ($index == 'kredit') {
                        $totalKredit += $value;
                    }
                    $rekapColumnIndex++;
                }

                // Add the formula to the current row's J column
                if ($rekapDataRow > $rekapHeaderRow + 1) {
                    $rekapSheet->setCellValue('G' . $rekapDataRow, '=(G' . $previousRow . '+E' . $rekapDataRow . ')-F' . $rekapDataRow);
                }
                $rekapSheet->getStyle('G' . $rekapDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $previousRow = $rekapDataRow; // Update the previous row number
                $rekapDataRow++;
                $rekapRowNumber++; // Increment row number
            }


            // Setelah perulangan selesai, tambahkan total ke sheet
            $rekapSheet->setCellValue('E' . $rekapDataRow, "=SUM(E6:E" . ($rekapDataRow - 1) . ")");
            $rekapSheet->getStyle('E' . $rekapDataRow)->applyFromArray($boldStyle);
            $rekapSheet->getStyle('E' . $rekapDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $rekapSheet->setCellValue('F' . $rekapDataRow, "=SUM(F6:F" . ($rekapDataRow - 1) . ")");
            $rekapSheet->getStyle('F' . $rekapDataRow)->applyFromArray($boldStyle);
            $rekapSheet->getStyle('F' . $rekapDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Merge cells untuk menampilkan teks "TOTAL"
            $rekapSheet->mergeCells('A' . $rekapDataRow . ':D' . $rekapDataRow);
            $rekapSheet->setCellValue('A' . $rekapDataRow, 'TOTAL:');
            $rekapSheet->getStyle('A' . $rekapDataRow . ':G' . $rekapDataRow)->applyFromArray($boldStyle);
            $rekapSheet->getStyle('A' . $rekapDataRow . ':D' . $rekapDataRow)->getAlignment()->setHorizontal('right');

            // Laporan Rekap 01
            $rekap01Sheet = $spreadsheet->createSheet($sheetIndex);
            $spreadsheet->setActiveSheetIndex($sheetIndex);
            $rekap01Sheet->setTitle('LAPORAN REKAP 01');
            $sheetIndex++;

            $periode = str_replace($englishMonths, $indonesianMonths, date('M - Y', strtotime($request->periode)));
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $rekap01Sheet->setCellValue('A1', $data[0]->judul);
            $rekap01Sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $rekap01Sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $rekap01Sheet->mergeCells('A1:H1');
            $rekap01Sheet->setCellValue('A2', $namacabang);
            $rekap01Sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $rekap01Sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $rekap01Sheet->mergeCells('A2:H2');

            $rekap01Sheet->setCellValue('A3', 'LAPORAN REKAP 01 ' . $kasbank . ' ' . $norek);
            $rekap01Sheet->getStyle("A3")->getFont()->setBold(true);

            $rekap01Sheet->setCellValue('A4', 'PERIODE ' . $bulan . ' - ' . $tahun);
            $rekap01Sheet->getStyle("A4")->getFont()->setBold(true);

            $rekap01HeaderRow = 6;
            $rekap01ColumnIndex = 0;
            $rekap01HeaderColumns = [
                // 'no' => 'No',
                'tgl' => 'Tanggal',
                'nobukti' => 'No Bukti',
                'perkiraan' => 'Perkiraan',
                'keterangan' => 'Keterangan',
                'debet' => 'Debet',
                'kredit' => 'Kredit',
                'saldo' => 'Saldo',
            ];


            foreach ($rekap01HeaderColumns as $index => $label) {
                $rekap01Sheet->setCellValue($alphabets[$rekap01ColumnIndex] . $rekap01HeaderRow, $label);
                if ($index == 'keterangan') {
                    $rekap01Sheet->getColumnDimension($alphabets[$rekap01ColumnIndex])->setWidth(70);
                } else if ($index == 'tgl') {
                    $rekap01Sheet->getColumnDimension($alphabets[$rekap01ColumnIndex])->setWidth(12);
                } else if ($index == 'no') {
                    $rekap01Sheet->getColumnDimension($alphabets[$rekap01ColumnIndex])->setWidth(4);
                } else if ($index == 'perkiraan') {
                    $rekap01Sheet->getColumnDimension($alphabets[$rekap01ColumnIndex])->setWidth(25);
                } else {
                    $rekap01Sheet->getColumnDimension($alphabets[$rekap01ColumnIndex])->setAutoSize(true);
                }
                $rekap01Sheet->getStyle($alphabets[$rekap01ColumnIndex] . $rekap01HeaderRow)->applyFromArray($boldStyle);
                $rekap01ColumnIndex++;
            }

            $filteredRekap01Data = array_filter($dataLaporanRekap01, function ($row) {
                // $row->jenislaporan = 'LAPORAN REKAP 01';
                return $row->jenislaporan == 'LAPORAN REKAP 01';
            });

            $rekap01DataRow = $rekap01HeaderRow + 1;
            $rekap01ColumnIndex = 0;
            $lastRekap01ColumnIndex = array_search('saldo', array_keys($rekap01HeaderColumns)); // Get the index of the "saldo" column
            $rekap01RowNumber = 1; // Initial row number

            $previousRow = $rekapDataRow - 1; // Initialize the previous row number $previousRow = $rekapDataRow - 1; // Initialize the previous row number

            foreach ($filteredRekap01Data as $row) {
                unset($row->jenislaporan);
                unset($row->jenis);
                $rekap01ColumnIndex = 0; // Reset column index for each row
                foreach ($row as $index => $value) {
                    if ($rekap01ColumnIndex > $lastRekap01ColumnIndex) {
                        break; // Exit the loop if the column index exceeds the index of the "saldo" column
                    }
                    if ($index == 'tgl') {
                        $dateValue = ($value != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($value))) : '';
                        $rekap01Sheet->setCellValue($alphabets[$rekap01ColumnIndex] . $rekap01DataRow, $dateValue);
                    } else {
                        if($index == 'debet'){
                            if ($row->nilaikosongdebet == 1) { 
                                $rekap01Sheet->setCellValueExplicit($alphabets[$rekap01ColumnIndex] . $rekap01DataRow, null, DataType::TYPE_NULL);  
                            }else{ 
                                $rekap01Sheet->setCellValue($alphabets[$rekap01ColumnIndex] . $rekap01DataRow,  $value);
                            }
                        } else if($index == 'kredit'){
                            if ($row->nilaikosongkredit == 1) { 
                                $rekap01Sheet->setCellValueExplicit($alphabets[$rekap01ColumnIndex] . $rekap01DataRow, null, DataType::TYPE_NULL);  
                            }else{ 
                                $rekap01Sheet->setCellValue($alphabets[$rekap01ColumnIndex] . $rekap01DataRow,  $value);
                            }
                        } else {
                            $rekap01Sheet->setCellValue($alphabets[$rekap01ColumnIndex] . $rekap01DataRow, $value);
                        }                        
                        // $rekap01Sheet->getStyle($alphabets[$rekap01ColumnIndex] . $rekap01DataRow)->applyFromArray($borderStyle);
                    }
                    // Apply number format to debet, kredit, and saldo columns
                    if ($index == 'debet' || $index == 'kredit' || $index == 'saldo') {
                        // if ($value == 0) {
                        //     $rekap01Sheet->getStyle($alphabets[$rekap01ColumnIndex] . $rekap01DataRow)->getNumberFormat()->setFormatCode(";-0;;@");
                        // } else {
                            $rekap01Sheet->getStyle($alphabets[$rekap01ColumnIndex] . $rekap01DataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        // }
                    }
                    // Apply date format to tgl column
                    if ($index == 'tgl') {
                        $rekap01Sheet->getStyle($alphabets[$rekap01ColumnIndex] . $rekap01DataRow)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                    }
                    $rekap01ColumnIndex++;
                }

                // Add the formula to the current row's J column
                if ($rekap01DataRow > $rekap01HeaderRow + 1) {
                    $rekap01Sheet->setCellValue('G' . $rekap01DataRow, '=(G' . $previousRow . '+E' . $rekap01DataRow . ')-F' . $rekap01DataRow);
                }
                $rekap01Sheet->getStyle('G' . $rekap01DataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $previousRow = $rekap01DataRow; // Update the previous row number
                $rekap01DataRow++;
                $rekap01RowNumber++; // Increment row number
            }

            // Setelah perulangan selesai, tambahkan total ke sheet
            $rekap01Sheet->setCellValue('E' . $rekap01DataRow, "=SUM(E6:E" . ($rekap01DataRow - 1) . ")");
            $rekap01Sheet->getStyle('E' . $rekap01DataRow)->applyFromArray($boldStyle);
            $rekap01Sheet->getStyle('E' . $rekap01DataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");


            $rekap01Sheet->setCellValue('F' . $rekap01DataRow, "=SUM(F6:F" . ($rekap01DataRow - 1) . ")");
            // $rekap01Sheet->getStyle('G' . $rekap01DataRow)->applyFromArray($borderStyle);
            $rekap01Sheet->getStyle('F' . $rekap01DataRow)->applyFromArray($boldStyle);
            $rekap01Sheet->getStyle('F' . $rekap01DataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Merge cells untuk menampilkan teks "TOTAL"
            $rekap01Sheet->mergeCells('A' . $rekap01DataRow . ':D' . $rekap01DataRow);
            $rekap01Sheet->setCellValue('A' . $rekap01DataRow, 'TOTAL:');
            $rekap01Sheet->getStyle('A' . $rekap01DataRow . ':G' . $rekap01DataRow)->applyFromArray($boldStyle);
            // $rekap01Sheet->getStyle('A' . $rekap01DataRow . ':H' . $rekap01DataRow)->applyFromArray($borderStyle);
            $rekap01Sheet->getStyle('A' . $rekap01DataRow . ':D' . $rekap01DataRow)->getAlignment()->setHorizontal('right');

            // Laporan Rekap Perkiraan
            $rekapPerkiraanSheet = $spreadsheet->createSheet($sheetIndex);
            $spreadsheet->setActiveSheetIndex($sheetIndex);
            $rekapPerkiraanSheet->setTitle('REKAP PERKIRAAN');
            $sheetIndex++;
            $periode = str_replace($englishMonths, $indonesianMonths, date('M - Y', strtotime($request->periode)));

            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $rekapPerkiraanSheet->setCellValue('A1', $data[0]->judul);
            $rekapPerkiraanSheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $rekapPerkiraanSheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $rekapPerkiraanSheet->mergeCells('A1:E1');
            $rekapPerkiraanSheet->setCellValue('A2', $namacabang);
            $rekapPerkiraanSheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $rekapPerkiraanSheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $rekapPerkiraanSheet->mergeCells('A2:E2');

            $rekapPerkiraanSheet->setCellValue('A3', 'LAPORAN REKAP PERKIRAAN');
            $rekapPerkiraanSheet->getStyle("A3")->getFont()->setBold(true);

            $rekapPerkiraanSheet->setCellValue('A4', 'PERIODE ' . $bulan . ' - ' . $tahun);
            $rekapPerkiraanSheet->getStyle("A4")->getFont()->setBold(true);

            $rekapPerkiraanHeaderRow = 6;
            $rekapPerkiraanColumnIndex = 0;
            $rekapPerkiraanHeaderColumns = [
                'no' => '',
                'perkiraan' => '',
                'nominaldebet' => 'Penerimaan',
                'perkiraanpengeluaran' => '',
                'nominalkredit' => 'Pengeluaran',
            ];

            foreach ($rekapPerkiraanHeaderColumns as $index => $label) {
                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanHeaderRow, $label);
                if ($index == 'no') {
                    $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setWidth(20);
                } else {
                    $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setAutoSize(true);
                }
                $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanHeaderRow)->applyFromArray($boldStyle);
                // $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanHeaderRow)->applyFromArray($borderStyle);
                $rekapPerkiraanColumnIndex++;
            }

            $rekapPerkiraanDataRow = $rekapPerkiraanHeaderRow + 1;
            $rekapPerkiraanColumnIndex = 0;
            $rekapPerkiraanRowNumber = 1; // Initial row number

            $rekapPerkiraanSheet->setCellValue('A' . $rekapPerkiraanDataRow, $bulan . ' - ' . $tahun); // Set nomor baris


            foreach ($dataDua as $row) {
                $rekapPerkiraanColumnIndex = 1;
                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->perkiraan);
                $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setAutoSize(true);
                $rekapPerkiraanColumnIndex++;
                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->nominaldebet);
                $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setAutoSize(true);
                // $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
                // if ($row->nominaldebet == 0) {
                //     $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode(";-0;;@");
                // } else {
                    $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                // }

                $rekapPerkiraanColumnIndex++;

                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->perkiraanpengeluaran);
                $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setAutoSize(true);
                // $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
                $rekapPerkiraanColumnIndex++;


                $rekapPerkiraanSheet->setCellValue($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow, $row->nominalkredit);
                $rekapPerkiraanSheet->getColumnDimension($alphabets[$rekapPerkiraanColumnIndex])->setAutoSize(true);
                // $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
                if ($row->nominalkredit == 0) {
                    $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode(";-0;;@");
                } else {
                    $rekapPerkiraanSheet->getStyle($alphabets[$rekapPerkiraanColumnIndex] . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                }
                $rekapPerkiraanColumnIndex++;

                $rekapPerkiraanDataRow++;
                $rekapPerkiraanColumnIndex = 0;
                $rekapPerkiraanRowNumber++;
            }




            // Menghitung total kolom D (nominaldebet)
            $rekapPerkiraanSheet->setCellValue('C' . $rekapPerkiraanDataRow, "=SUM(C6:C" . ($rekapPerkiraanDataRow - 1) . ")");
            // $rekapPerkiraanSheet->getStyle('D' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('C' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('C' . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Menghitung total kolom E (nominalkredit)
            $rekapPerkiraanSheet->setCellValue('E' . $rekapPerkiraanDataRow, "=SUM(E6:E" . ($rekapPerkiraanDataRow - 1) . ")");
            // $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            // Merge cells A hingga C dan tampilkan tulisan "TOTAL:"
            $rekapPerkiraanSheet->mergeCells('B' . $rekapPerkiraanDataRow . ':B' . $rekapPerkiraanDataRow);
            $rekapPerkiraanSheet->setCellValue('B' . $rekapPerkiraanDataRow, 'Jumlah:');
            $rekapPerkiraanSheet->mergeCells('D' . $rekapPerkiraanDataRow . ':D' . $rekapPerkiraanDataRow);
            $rekapPerkiraanSheet->setCellValue('D' . $rekapPerkiraanDataRow, 'Jumlah:');
            $rekapPerkiraanSheet->getStyle('C' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow . ':E' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            // $rekapPerkiraanSheet->getStyle('A' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('C' . $rekapPerkiraanDataRow . ':C' . $rekapPerkiraanDataRow)->getAlignment()->setHorizontal('right');
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow . ':E' . $rekapPerkiraanDataRow)->getAlignment()->setHorizontal('right');

            $rekapPerkiraanDataRow++;
            $rekapPerkiraanDataRow++;
            $rekapPerkiraanSheet->mergeCells('D' . $rekapPerkiraanDataRow . ':D' . $rekapPerkiraanDataRow);
            $rekapPerkiraanSheet->setCellValue('D' . $rekapPerkiraanDataRow, 'Saldo Akhir:');
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow . ':E' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow . ':E' . $rekapPerkiraanDataRow)->getAlignment()->setHorizontal('right');
            $rekapPerkiraanSheet->setCellValue('E' . $rekapPerkiraanDataRow, "=(C" . ($rekapPerkiraanDataRow - 2) . "-E" . ($rekapPerkiraanDataRow - 2) . ")");
            // $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->applyFromArray($borderStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->applyFromArray($boldStyle);
            $rekapPerkiraanSheet->getStyle('E' . $rekapPerkiraanDataRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $rekapPerkiraanDataRow++;
            $rekapPerkiraanDataRow++;
            $rekapPerkiraanDataRow++;
            $rekapPerkiraanSheet->setCellValue('B' . $rekapPerkiraanDataRow,  $namacabang2 . ', ' . $this->tgl_indo(date('Y-m-d')));

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN ' . $kasbank . ' ' . $norek . ' ' . date('dmYHis');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }

    /**
     * @ClassName
     * @Keterangan REPORT REKAP
     */
    public function report(ValidasiReportKasHarianRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $sampai = $request->periode;
            $jenis = $request->bank_id;


            $export = ExportLaporanKasHarian::getExport($sampai, $jenis);


            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            $direktur = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JABATAN')
                ->where('subgrp', 'DIREKTUR')
                ->first();
            $gm = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JABATAN')
                ->where('subgrp', 'GENERAL MANAGER')
                ->first();
            $manTrucking = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JABATAN')
                ->where('subgrp', 'MANAGER TRUCKING')
                ->first();
            $kasir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JABATAN')
                ->where('subgrp', 'KASIR')
                ->first();

            foreach ($export[0] as $data) {
                $data->tgl = date('d-m-Y', strtotime($data->tgl));
            }
            return response([
                'data' => $export[0],
                'dataDua' => $export[1],
                'namacabang' => 'CABANG ' . $getCabang->namacabang,
                'namacabang2' =>  $getCabang->namacabang,
                'tandatangan' =>  [
                    "direktur" => $direktur->text,
                    "gm" => $gm->text,
                    "manTrucking" => $manTrucking->text,
                    "kasir" => $kasir->text,
                ]
            ]);
        }
    }
}
