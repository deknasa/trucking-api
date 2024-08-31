<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidationForLaporanKlaimPjtSupirRequest;
use App\Models\LaporanKlaimPJTSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanKlaimPJTSupirController extends Controller
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
    public function report(ValidationForLaporanKlaimPjtSupirRequest $request)
    {
        $sampai = $request->sampai;
        $dari = $request->dari;
        $kelompok_id = $request->kelompok_id;
        $laporanKlaim = new LaporanKlaimPJTSupir();

        $laporan_klaim = $laporanKlaim->getReport($sampai, $dari, $kelompok_id);

        if (count($laporan_klaim) == 0) {
            return response([
                'data' => $laporan_klaim,
                'message' => 'tidak ada data'
            ], 500);
        } else {

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporan_klaim,
                'message' => 'berhasil',
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
        $sampai = $request->sampai;
        $dari = $request->dari;
        $kelompok_id = $request->kelompok_id;
        $kelompok = $request->kelompok;

        $laporanKlaim = new LaporanKlaimPJTSupir();
        $laporan_klaim = $laporanKlaim->getReport($sampai, $dari, $kelompok_id);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_klaim,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);

        $data = json_decode($laporan_klaim);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $data[0]->judul ?? '');
        $sheet->setCellValue('A2', $namacabang ?? '');
        $sheet->setCellValue('A3', $data[0]->judulLaporan ?? '');
        $sheet->setCellValue('A4', 'PERIODE : ' . date('d-M-Y', strtotime($dari)) . ' s/d ' . date('d-M-Y', strtotime($sampai)));
        $sheet->setCellValue('A5', 'KELOMPOK : ' . $kelompok);
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->getStyle("A5")->getFont()->setBold(true);

        $detail_table_header_row = 7;
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
                'index' => 'nobukti',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Supir',
                'index' => 'namasupir',
            ],
            [
                'label' => 'Nama Stok',
                'index' => 'namastok',
            ],
            [
                'label' => 'Nominal',
                'index' => 'nominal',
            ],

        ];

        foreach ($header_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        // LOOPING DETAIL
        foreach ($data as $response_index => $response_detail) {

            foreach ($header_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
            }
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

            $sheet->setCellValue("A$detail_start_row", $response_detail->nobukti);
            $sheet->setCellValue("B$detail_start_row", $dateValue);
            $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
            $sheet->setCellValue("D$detail_start_row", $response_detail->namasupir);
            $sheet->setCellValue("E$detail_start_row", $response_detail->namastok);
            $sheet->setCellValue("F$detail_start_row", $response_detail->nominal);
            $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $detail_start_row++;
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
        $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("C" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(68);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(32);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN KLAIM PJT SUPIR ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
