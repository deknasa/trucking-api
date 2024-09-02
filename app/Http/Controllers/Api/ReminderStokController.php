<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderStok;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReminderStokController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $reminderStok = new ReminderStok();
        return response([
            'data' => $reminderStok->get(),
            'attributes' => [
                'totalRows' => $reminderStok->totalRows,
                'totalPages' => $reminderStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $forExport = $request->forExport;
        $reminderStok = new ReminderStok();
        $reminder_Stok = $reminderStok->get($forExport);

        $data = json_decode($reminder_Stok);
        $disetujui = $data[0]->disetujui ?? '';
        $diperiksa = $data[0]->diperiksa ?? '';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', $data[0]->judul);
        $sheet->getStyle("A1")->getFont()->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:E1');

        $sheet->setCellValue('A2', $data[0]->judulLaporan);
        $sheet->getStyle("A2")->getFont()->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:E2');

        $detail_table_header_row = 5;
        $detail_start_row = 6;

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
                'label' => 'No',
                'index' => '',
            ],
            [
                'label' => 'Kode',
                'index' => 'namastok',
            ],
            [
                'label' => 'Nama',
                'index' => 'keterangan',
            ],
            [
                'label' => 'QTY Min',
                'index' => 'qtymin'
            ],
            [
                'label' => 'Saldo Stok',
                'index' => 'qty'
            ]
        ];
        foreach ($detail_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        // LOOPING DETAIL
        $dataRow = $detail_table_header_row + 2;
        $previousRow = $dataRow - 1; // Initialize the previous row number
        $a = 1;
        foreach ($data as $response_index => $response_detail) {

            $sheet->setCellValue("A$detail_start_row", $a);
            $sheet->setCellValue("B$detail_start_row", $response_detail->namastok);
            $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
            $sheet->setCellValue("D$detail_start_row", $response_detail->qtymin);
            $sheet->setCellValue("E$detail_start_row", $response_detail->qty);

            $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("D$detail_start_row:E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00");

            $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('C')->setWidth(100);
            $a++;
            $dataRow++;
            $detail_start_row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan Reminder Stok' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
