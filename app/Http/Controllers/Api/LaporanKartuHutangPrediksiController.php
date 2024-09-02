<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKartuHutangPrediksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanKartuHutangPrediksiController extends Controller
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
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $sampai = $request->sampai;
        $LaporanKartuHutangPrediksi = new LaporanKartuHutangPrediksi();

        $dataHutangPrediksi = $LaporanKartuHutangPrediksi->getReport($sampai, $dari);

        if (count($dataHutangPrediksi) == 0) {
            return response([
                'data' => $dataHutangPrediksi,
                'message' => 'tidak ada data'
            ], 500);
        } else {

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $dataHutangPrediksi,
                'message' => 'berhasil',
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }





        // $report = LaporanKartuHutangPrediksi::getReport($sampai, $dari);
        // $report = [
        //     [
        //         "noebs" => 'BKT-M BCA 0003/II/2023',
        //         'tanggal' => '23/2/2023',
        //         'nobukti' => '',
        //         'keterangan' => 'TES KETERANGAN I',
        //         'nominal' => '123412',
        //         'bayar' => '0',
        //         'saldo' => '214124124'

        //     ]
        // ];
        // return response([
        //     'data' => $report
        // ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;

        $LaporanKartuHutangPrediksi = new LaporanKartuHutangPrediksi();
        $laporan_HutangPrediksi = $LaporanKartuHutangPrediksi->getReport($sampai, $dari);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $dataHutangPrediksi,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);
        $data = json_decode($laporan_HutangPrediksi);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $data[0]->judul);
        $sheet->setCellValue('A2', $namacabang);
        $sheet->setCellValue('A3', $data[0]->judulLaporan);
        $sheet->setCellValue('A4', 'PERIODE : ' . date('d-M-Y', strtotime($sampai)));
        // $sheet->setCellValue('A4', 'PERIODE : ' .date('d-M-Y', strtotime($detailParams['dari'])) . ' s/d ' . date('d-M-Y', strtotime($detailParams['sampai'])));
        // $sheet = $spreadsheet->getActiveSheet();
        // $sheet->setCellValue('b1', 'LAPORAN PINJAMAN SUPIR');
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->getStyle("A4")->getFont()->setBold(true);

        // $sheet->setCellValue('A4', 'PERIODE');
        // $sheet->getStyle("A4")->getFont()->setSize(12)->setBold(true);

        // $sheet->setCellValue('B4', $request->periode);
        // $sheet->setCellValue('B4', ':'." ".$request->periode);
        // $sheet->getStyle("B4")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');

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

        $header_columns = [
            [
                'label' => 'No Bukti',
                'index' => 'noebs',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tanggal',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Nominal',
                'index' => 'nominal',
            ],
            [
                'label' => 'Bayar',
                'index' => 'bayar',
            ],
            [
                'label' => 'Saldo',
                'index' => 'saldo',
            ],

        ];

        foreach ($header_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        // LOOPING DETAIL
        $totalDebet = 0;
        $totalKredit = 0;
        $totalSaldo = 0;
        $dataRow = $detail_table_header_row + 1;
        $previousRow = $dataRow - 1; // Initialize the previous row number
        foreach ($data as $response_index => $response_detail) {

            foreach ($header_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
            }
            $dateValue = ($response_detail->tanggal != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tanggal))) : '';

            $sheet->setCellValue("A$detail_start_row", $response_detail->noebs);
            $sheet->setCellValue("B$detail_start_row", $dateValue);
            $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
            $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);
            $sheet->setCellValue("E$detail_start_row", $response_detail->bayar);
            // $sheet->setCellValue("F$detail_start_row", $response_detail->Saldo);

            if ($detail_start_row == 7) {
                $sheet->setCellValue('F' . $detail_start_row, $response_detail->saldo);
            } else {
                if ($dataRow > $detail_table_header_row + 1) {
                    $sheet->setCellValue('F' . $dataRow, '=(F' . $previousRow . '+D' . $dataRow . ')-E' . $dataRow);
                }
            }

            $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("D$detail_start_row:F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->getStyle("B$detail_start_row:B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');

            $previousRow = $dataRow; // Update the previous row number

            $dataRow++;
            $detail_start_row++;
        }

        //total
        $total_start_row = $detail_start_row;
        $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
        $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

        $totalDebet = "=SUM(D6:D" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("D$total_start_row", $totalDebet)->getStyle("D$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("D$total_start_row", $totalDebet)->getStyle("D$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

        $totalKredit = "=SUM(E6:E" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("E$total_start_row", $totalKredit)->getStyle("E$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("E$total_start_row", $totalKredit)->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

        $totalSaldo = "=D" . $total_start_row . "-E" . $total_start_row;
        $sheet->setCellValue("F$total_start_row", $totalSaldo)->getStyle("F$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("F$total_start_row", $totalSaldo)->getStyle("F$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

        $ttd_start_row = $detail_start_row + 2;
        $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
        $sheet->setCellValue("C$ttd_start_row", 'Diperiksa Oleh,');
        $sheet->setCellValue("E$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("C" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("E" . ($ttd_start_row + 3), '(                )');

        $sheet->getColumnDimension('A')->setWidth(21);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(74);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN KARTU HUTANG PREDIKSI' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
