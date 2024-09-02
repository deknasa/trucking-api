<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LogAbsensiController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));
        $logAbsensi = new LogAbsensi();
        return response([
            'data' => $logAbsensi->get($tgldari, $tglsampai),
            'attributes' => [
                'totalRows' => $logAbsensi->totalRows,
                'totalPages' => $logAbsensi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));

        $logAbsensi = new LogAbsensi();
        $log_Absensi = $logAbsensi->get($tgldari, $tglsampai);

        // dd($log_Absensi);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $log_Absensi[0]->judul);
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:E1');

        $sheet->setCellValue('A2', $log_Absensi[0]->judulLaporan);

        $sheet->setCellValue('A4', 'Periode');
        $sheet->setCellValue('B4', ': ' . $request->tgldari . ' s/d ' . $request->tglsampai);

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
        $detail_columns = [
            [
                'label' => 'Karyawan/Supir/Kenek',
                'index' => 'karyawan',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tanggal',
            ],
            [
                'label' => 'Jadwal Kerja',
                'index' => 'jadwalkerja',
            ],
            [
                'label' => 'Status',
                'index' => 'statusabsen'
            ],
            [
                'label' => 'Log Waktu',
                'index' => 'logwaktu'
            ],
        ];

        foreach ($detail_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        // LOOPING DETAIL
        $dataRow = $detail_table_header_row + 2;
        $previousRow = $dataRow - 1; // Initialize the previous row number
        foreach ($log_Absensi as $response_index => $response_detail) {

            foreach ($detail_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
            }

            $sheet->setCellValue("A$detail_start_row", $response_detail->karyawan);
            $sheet->setCellValue("B$detail_start_row", date('d-m-Y', strtotime($response_detail->tanggal)));
            $sheet->setCellValue("C$detail_start_row", $response_detail->jadwalkerja);
            $sheet->setCellValue("D$detail_start_row", $response_detail->statusabsen);
            $sheet->setCellValue("E$detail_start_row", $response_detail->logwaktu);

            $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);

            $dataRow++;
            $detail_start_row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan Log Absensi' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report() {}
}
