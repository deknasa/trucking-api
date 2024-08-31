<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanHutangGiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class LaporanHutangGiroController extends Controller
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
        $periode = date('Y-m-d', strtotime($request->periode));
        $laporanhutanggiro = new LaporanHutangGiro();

        $laporan_hutanggiro = $laporanhutanggiro->getReport($periode);

        if ($request->isCheck) {
            if (count($laporan_hutanggiro) === 0) {

                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],

                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {
            foreach ($laporan_hutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
                $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_hutanggiro,
                'namacabang' => 'CABANG ' . $getCabang->namacabang

            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = date('Y-m-d', strtotime($request->periode));

        $laporanhutanggiro = new LaporanHutangGiro();
        $laporan_hutanggiro = $laporanhutanggiro->getReport($periode);

        if ($request->isCheck) {
            if (count($laporan_hutanggiro) === 0) {
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {
            foreach ($laporan_hutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            // return response([
            //     'data' => $laporan_hutanggiro,
            //     'namacabang' => 'CABANG ' . $getCabang->namacabang
            //     // 'data' => $report
            // ]);

            $pengeluaran = json_decode($laporan_hutanggiro);
            $namacabang = 'CABANG ' . $getCabang->namacabang;
            $disetujui = $pengeluaran[0]->disetujui ?? '';
            $diperiksa = $pengeluaran[0]->diperiksa ?? '';

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pengeluaran[0]->judul);
            $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:F1');
            $sheet->setCellValue('A2', $namacabang);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:F2');


            $englishMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $indonesianMonths = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];

            $tanggal = str_replace($englishMonths, $indonesianMonths, date('d - M - Y', strtotime($request->periode)));

            $sheet->setCellValue('A3', $pengeluaran[0]->judulLaporan);
            $sheet->setCellValue('A4', 'Periode : ' . $tanggal);

            // $sheet->getStyle("A1")->getFont()->setSize(20)->setBold(true);
            $sheet->getStyle("A3")->getFont()->setBold(true);
            $sheet->getStyle("A4:B4")->getFont()->setBold(true);

            $header_start_row = 6;
            $detail_start_row = 7;

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
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tgl Bukti',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'No Warkat',
                    'index' => 'nowarkat',
                ],
                [
                    'label' => 'Tgl Jatuh Tempo',
                    'index' => 'tgljatuhtempo',
                ],
                [
                    'label' => 'Nominal',
                    'index' => 'nominal',
                ],

            ];


            foreach ($header_columns as $data_columns_index => $data_column) {
                $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, ucfirst($data_column['label']) ?? $data_columns_index + 1);
            }

            $lastColumn = $alphabets[$data_columns_index];
            $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
            $totalDebet = 0;
            if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
                foreach ($pengeluaran as $response_index => $response_detail) {
                    foreach ($header_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    }

                    $sheet->setCellValue("A$detail_start_row", $response_detail->nobukti);
                    $sheet->setCellValue("B$detail_start_row", date('d-m-Y', strtotime($response_detail->tglbukti)));
                    $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                    $sheet->setCellValue("B$detail_start_row", $dateValue);
                    $sheet->getStyle("B$detail_start_row")
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                    $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->nowarkat);
                    $dateValue = ($response_detail->tgljatuhtempo != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tgljatuhtempo))) : '';
                    $sheet->setCellValue("E$detail_start_row", $dateValue);
                    $sheet->getStyle("E$detail_start_row")
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                    $sheet->setCellValue("F$detail_start_row", $response_detail->nominal);
                    $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $detail_start_row++;
                }
            }
            //total
            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':E' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':E' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

            $totalDebet = "=SUM(F7:F" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("F$total_start_row", $totalDebet)->getStyle("F$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->setCellValue("F$total_start_row", $totalDebet)->getStyle("F$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $ttd_start_row = $detail_start_row + 2;
            $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
            $sheet->setCellValue("C$ttd_start_row", 'Diperiksa Oleh,');
            $sheet->setCellValue("E$ttd_start_row", 'Disusun Oleh,');

            $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
            $sheet->setCellValue("C" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
            $sheet->setCellValue("E" . ($ttd_start_row + 3), '(                )');
            //ukuran kolom
            $sheet->getColumnDimension('A')->setWidth(24);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(24);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(24);
            $sheet->getColumnDimension('C')->setWidth(64);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN HUTANG GIRO ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
