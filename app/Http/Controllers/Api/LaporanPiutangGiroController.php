<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanPiutangGiro;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPiutangGiroController extends Controller
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

        $laporanpiutanggiro = new LaporanPiutangGiro();

        $laporan_piutanggiro = $laporanpiutanggiro->getReport($periode);

        if ($request->isCheck) {
            if (count($laporan_piutanggiro) === 0) {
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

            foreach ($laporan_piutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
                $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            }

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_piutanggiro,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
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

        $laporanpiutanggiro = new LaporanPiutangGiro();
        $laporan_piutanggiro = $laporanpiutanggiro->getExport($periode);

        if ($request->isCheck) {
            if (count($laporan_piutanggiro) === 0) {
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

            foreach ($laporan_piutanggiro as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
                $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
            }

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            // return response([
            //     'data' => $laporan_piutanggiro,
            //     'namacabang' => 'CABANG ' . $getCabang->namacabang
            //     // 'data' => $report
            // ]);

            $pengeluaran = json_decode($laporan_piutanggiro);
            $namacabang = 'CABANG ' . $getCabang->namacabang;
            $disetujui = $pengeluaran[0]->disetujui ?? '';
            $diperiksa = $pengeluaran[0]->diperiksa ?? '';

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pengeluaran[0]->judul);
            $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A2', $namacabang);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:E2');

            $englishMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $indonesianMonths = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];

            $tanggal = str_replace($englishMonths, $indonesianMonths, date('d - M - Y', strtotime($request->periode)));

            $sheet->setCellValue('A3', strtoupper($pengeluaran[0]->judulLaporan));
            $sheet->setCellValue('A4', strtoupper('Periode : ' . $tanggal));
            $sheet->getStyle("A3")->getFont()->setBold(true);
            $sheet->getStyle("A4")->getFont()->setBold(true);

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

                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
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

            if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
                foreach ($pengeluaran as $response_index => $response_detail) {

                    foreach ($header_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    }

                    $sheet->setCellValue("A$detail_start_row", $response_detail->nobukti);
                    $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                    $sheet->setCellValue("B$detail_start_row", $dateValue);
                    $sheet->getStyle("B$detail_start_row")
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                    $sheet->setCellValue("C$detail_start_row", $response_detail->nowarkat);
                    $dateValue = ($response_detail->tgljatuhtempo != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tgljatuhtempo))) : '';
                    $sheet->setCellValue("D$detail_start_row", $dateValue);
                    $sheet->getStyle("D$detail_start_row")
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                    $sheet->setCellValue("E$detail_start_row", $response_detail->nominal);
                    $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $detail_start_row++;
                }
            }

            //total
            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

            $totalDebet = "=SUM(E7:E" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

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
            $sheet->getColumnDimension('C')->setWidth(64);
            $sheet->getColumnDimension('D')->setWidth(24);
            $sheet->getColumnDimension('E')->setWidth(24);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN PIUTANG GIRO ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
