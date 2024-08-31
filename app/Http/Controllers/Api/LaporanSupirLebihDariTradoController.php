<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanSupirLebihDariTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanSupirLebihDariTradoController extends Controller
{
    /**
     * @ClassName
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $laporansupirlebihdaritrado = new LaporanSupirLebihDariTrado();
        return response([
            'data' => $laporansupirlebihdaritrado->get(),
            'attributes' => [
                'totalRows' => $laporansupirlebihdaritrado->totalRows,
                'totalPages' => $laporansupirlebihdaritrado->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $dari = date('Y-m-d', strtotime($request->dari));

        $laporansupirlebihdaritrado = new LaporanSupirLebihDariTrado();
        // $report = LaporanSupirLebihDariTrado::getReport($sampai, $dari);
        // $report = [
        //     [
        //         'supir' => 'HERMAN',
        //         'trado' => 'BK 1252 AJS',
        //         'tanggal' => '23/2/2023',
        //         'ritasi' => '1'
        //     ],[
        //         'supir' => 'HERMAN',
        //         'trado' => 'BK 2415 BNM',
        //         'tanggal' => '23/2/2023',
        //         'ritasi' => '2'
        //     ],
        // ];
        $laporansupirlebih_daritrado = $laporansupirlebihdaritrado->getReport($dari, $sampai);
        foreach ($laporansupirlebih_daritrado as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporansupirlebih_daritrado,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
            // 'data' => $report
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $dari = date('Y-m-d', strtotime($request->dari));

        $laporansupirlebihdaritrado = new LaporanSupirLebihDariTrado();
        $laporansupirlebih_daritrado = $laporansupirlebihdaritrado->getReport($dari, $sampai);

        foreach ($laporansupirlebih_daritrado as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporansupirlebih_daritrado,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     // 'data' => $report
        // ]);

        $pengeluaran = json_decode($laporansupirlebih_daritrado);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $pengeluaran[0]->judul ?? '');
        $sheet->setCellValue('A2', $namacabang ?? '');
        $sheet->setCellValue('A3', $pengeluaran[0]->judulLaporan ?? '');
        $sheet->setCellValue('A4', 'PERIODE : ' . date('d-M-Y', strtotime($request->dari)) . ' s/d ' . date('d-M-Y', strtotime($request->sampai)));
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:C2');
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

        ];
        $alphabets = range('A', 'Z');
        $header_columns = [
            [
                'label' => 'Nama Supir',
                'index' => 'namasupir',
            ],
            [
                'label' => 'Tanggal Bukti',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'Jumlah',
                'index' => 'jumlah',
            ],
        ];

        foreach ($header_columns as $data_columns_index => $data_column) {
            $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
        }

        $lastColumn = $alphabets[$data_columns_index];
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->getFont()->setBold(true);
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->applyFromArray($styleArray);

        $totalDebet = 0;
        $totalKredit = 0;
        $totalSaldo = 0;
        if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
            foreach ($pengeluaran as $response_index => $response_detail) {

                foreach ($header_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                }
                $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

                $sheet->setCellValue("A$detail_start_row", $response_detail->namasupir);
                $sheet->setCellValue("B$detail_start_row", $dateValue);
                $sheet->setCellValue("C$detail_start_row", $response_detail->jumlah);

                $sheet->getStyle("C$detail_start_row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("C$detail_start_row:C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $detail_start_row++;
            }
        }


        //ukuran kolom
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);

        //FORMAT
        // set format ribuan untuk kolom D dan E
        $sheet->getStyle("D" . ($detail_start_row + 1) . ":E" . ($detail_start_row + 1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle("A" . ($detail_start_row + 1) . ":$lastColumn" . ($detail_start_row + 1))->getFont()->setBold(true);

        //persetujuan
        $sheet->mergeCells('A' . ($detail_start_row + 3) . ':A' . ($detail_start_row + 3));
        $sheet->setCellValue('A' . ($detail_start_row + 3), 'Disetujui Oleh,');
        $sheet->mergeCells('B' . ($detail_start_row + 3) . ':B' . ($detail_start_row + 3));
        $sheet->setCellValue('B' . ($detail_start_row + 3), 'Diperiksa Oleh');
        $sheet->mergeCells('C' . ($detail_start_row + 3) . ':C' . ($detail_start_row + 3));
        $sheet->setCellValue('C' . ($detail_start_row + 3), 'Disusun Oleh,');

        $sheet->mergeCells('A' . ($detail_start_row + 6) . ':A' . ($detail_start_row + 6));
        $sheet->setCellValue('A' . ($detail_start_row + 6), '( ' . $disetujui . ' )');
        $sheet->mergeCells('B' . ($detail_start_row + 6)  . ':B' . ($detail_start_row + 6));
        $sheet->setCellValue('B' . ($detail_start_row + 6), '( ' . $diperiksa . ' )');
        $sheet->mergeCells('C' . ($detail_start_row + 6) . ':C' . ($detail_start_row + 6));
        $sheet->setCellValue('C' . ($detail_start_row + 6), '(                      )');

        // style persetujuan
        $sheet->getStyle('A' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($detail_start_row + 3))->getFont()->setSize(12);
        $sheet->getStyle('B' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . ($detail_start_row + 3))->getFont()->setSize(12);
        $sheet->getStyle('C' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . ($detail_start_row + 3))->getFont()->setSize(12);

        $sheet->getStyle('A' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($detail_start_row + 6))->getFont()->setSize(12);
        $sheet->getStyle('B' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . ($detail_start_row + 6))->getFont()->setSize(12);
        $sheet->getStyle('C' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . ($detail_start_row + 6))->getFont()->setSize(12);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN 1 SUPIR LEBIH DARI 1 TRADO ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
