<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanPinjamanSupirRequest;
use App\Models\LaporanPinjamanSupirKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPinjamanSupirKaryawanController extends Controller
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
    public function report(ValidasiLaporanPinjamanSupirRequest $request)
    {
        $sampai = $request->sampai;
        $jenis = $request->jenis ?? 0;
        $laporanPinjSupir = new LaporanPinjamanSupirKaryawan();
        $prosesneraca = 0;

        $dataPinjSupir = $laporanPinjSupir->getReport($sampai, $prosesneraca, $jenis);

        if (count($dataPinjSupir) == 0) {
            return response([
                'data' => $dataPinjSupir,
                'message' => 'tidak ada data'
            ], 500);
        } else {

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $dataPinjSupir,
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
        $jenis = $request->jenis ?? 83;
        $prosesneraca = 0;

        $laporanPinjSupir = new LaporanPinjamanSupirKaryawan();
        $laporan_PinjSupir = $laporanPinjSupir->getReport($sampai, $prosesneraca, $jenis);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $export,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);

        $data = json_decode($laporan_PinjSupir);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $data[0]->disetujui ?? '';
        $diperiksa = $data[0]->diperiksa ?? '';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $data[0]->judul ?? '');
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A2', $namacabang ?? '');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A3',  $data[0]->judulLaporan ?? '');
        $sheet->mergeCells('A3:B3');
        $sheet->setCellValue('A4', 'Periode: ' . date('d-M-Y', strtotime($request->sampai)));
        $sheet->mergeCells('A4:B4');
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->getStyle("A4:B4")->getFont()->setBold(true);
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
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
                'label' => 'Tgl Bukti',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'No Bukti',
                'index' => 'nobukti',
            ],
            [
                'label' => 'Nama Karyawan',
                'index' => 'namakaryawan',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Debet',
                'index' => 'debet',
            ],
            [
                'label' => 'Kredit',
                'index' => 'kredit',
            ],
            [
                'label' => 'Saldo',
                'index' => 'Saldo',
            ],

        ];

        foreach ($header_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

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
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
            $sheet->setCellValue("A$detail_start_row", $dateValue);
            $sheet->getStyle("A$detail_start_row")
                ->getNumberFormat()
                ->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("B$detail_start_row", $response_detail->nobukti);
            $sheet->setCellValue("C$detail_start_row", $response_detail->namakaryawan);
            $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
            $sheet->setCellValue("E$detail_start_row", $response_detail->debet);
            $sheet->setCellValue("F$detail_start_row", $response_detail->kredit);

            if ($detail_start_row == 7) {
                $sheet->setCellValue('G' . $detail_start_row, $response_detail->Saldo);
            } else {
                if ($dataRow > $detail_table_header_row + 1) {
                    $sheet->setCellValue('G' . $dataRow, '=(G' . $previousRow . '+E' . $dataRow . ')-F' . $dataRow);
                }
            }

            $sheet->getStyle("A$detail_start_row:G$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("E$detail_start_row:G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getStyle("A$detail_start_row:A$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('D')->setWidth(100);
            $previousRow = $dataRow; // Update the previous row number
            $dataRow++;
            $totalKredit += $response_detail->kredit;
            $totalDebet += $response_detail->debet;
            $totalSaldo += $response_detail->Saldo;
            $detail_start_row++;
        }
        //total
        $total_start_row = $detail_start_row;
        $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
        $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

        $totalDebet = "=SUM(E7:E" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00");

        $totalKredit = "=SUM(F7:F" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("F$total_start_row", $totalKredit)->getStyle("F$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("F$total_start_row", $totalKredit)->getStyle("F$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00");

        $totalSaldo = "=E" . $total_start_row . "-F" . $total_start_row;
        $sheet->setCellValue("G$total_start_row", $totalSaldo)->getStyle("G$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("G$total_start_row", $totalSaldo)->getStyle("G$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00");

        $ttd_start_row = $detail_start_row + 2;
        $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
        $sheet->setCellValue("D$ttd_start_row", 'Diperiksa Oleh,');
        $sheet->setCellValue("G$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("D" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("G" . ($ttd_start_row + 3), '(                )');

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(72);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN PINJAMAN KARYAWAN' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
