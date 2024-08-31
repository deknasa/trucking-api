<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderSpkDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReminderSpkDetailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $reminderSpk = new ReminderSpkDetail();
        $getdetail = 0;
        $stok_id = request()->stok_id ?? 0;
        $trado_id = request()->trado_id ?? 0;
        $gandengan_id = request()->gandengan_id ?? 0;
        $gudang = request()->gudang ?? '';
        $stok = request()->stok ?? '';

        return response([
            'data' => $reminderSpk->get($getdetail, $stok_id, $trado_id, $gandengan_id, $gudang, $stok, 0, 0, '', '', '', 0, '', 0, 0),
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
        $reminderSpk = new ReminderSpkDetail();
        $reminder_Spk = $reminderSpk->getdetail();

        // return response([
        //     'data' => $reminderSpk->getdetail(),
        //     'attributes' => [
        //         'totalRows' => $reminderSpk->totalRows,
        //         'totalPages' => $reminderSpk->totalPages
        //     ]
        // ]);

        $data = $reminder_Spk;
        // dd($data[0]);
        $disetujui = $data[0]->disetujui ?? '';
        $diperiksa = $data[0]->diperiksa ?? '';

        foreach ($data as $row) {
            $gudang_header = $row['gudang_header'];
            $stok_header = $row['stok_header'];
            $groupedData[$gudang_header][$stok_header][] = $row;
        }

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // dd($data[0]['judul']);

        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $data[0]['judul']);
        $sheet->setCellValue('A2', 'REMINDER SPK');
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:G1');

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

        $header_columns = [
            ['label' => ''],
            [
                'label' => 'No Bukti',
                'index' => 'nobukti',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'Gudang',
                'index' => 'gudang',
            ],
            [
                'label' => 'Nama Barang',
                'index' => 'namabarang',
            ],
            [
                'label' => 'Satuan',
                'index' => 'satuan',
            ],
            [
                'label' => 'Qty',
                'index' => 'qty',
            ],
            [
                'label' => 'Hrg Sat',
                'index' => 'hargasatuan',
            ],
            [
                'label' => 'Total',
                'index' => 'total',
            ],
            [
                'label' => 'Potongan Disc',
                'index' => 'persentasediscount',
            ],
            [
                'label' => 'Nominal Disc',
                'index' => 'nominaldiscount',
            ],
            [
                'label' => 'Nominal',
                'index' => 'nominal',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
        ];
        $detail_columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'No Bukti',
                'index' => 'nobukti',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'Gudang',
                'index' => 'gudang',
            ],
            [
                'label' => 'Nama Barang',
                'index' => 'namabarang',
            ],
            [
                'label' => 'Satuan',
                'index' => 'satuan',
            ],
            [
                'label' => 'Qty',
                'index' => 'qty',
            ],
            [
                'label' => 'Harga Satuan',
                'index' => 'hargasatuan',
            ],
            [
                'label' => 'Total',
                'index' => 'total',
            ],
            [
                'label' => 'Potongan Disc',
                'index' => 'persentasediscount',
            ],
            [
                'label' => 'Nominal Disc',
                'index' => 'nominaldiscount',
            ],
            [
                'label' => 'Nominal',
                'index' => 'nominal',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],

        ];

        $alphabets = range('A', 'Z');
        $groupHeaderRow = 4;
        $detail_start_row = $groupHeaderRow + 1;
        foreach ($groupedData as $gudang => $group) {
            $noHeader = 1;
            foreach ($group as $stok => $row) {

                foreach ($header_columns as $data_columns_index => $data_column) {

                    $sheet->setCellValue($alphabets[$data_columns_index] . $groupHeaderRow, $data_column['label'] ?? $data_columns_index + 1);
                    $lastColumn = $alphabets[$data_columns_index];
                    $sheet->getStyle("A$groupHeaderRow:$lastColumn$groupHeaderRow")->applyFromArray($styleArray)->getFont()->setBold(true);
                }
                $groupHeaderRow++;
                $tglbukti = ($row[0]['tglbukti_header'] != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($row[0]['tglbukti_header']))) : '';
                $sheet->setCellValue("B$groupHeaderRow", $row[0]['nobukti_header']);
                $sheet->setCellValue("C$groupHeaderRow", $tglbukti);
                $sheet->setCellValue("D$groupHeaderRow", $row[0]['gudang_header']);
                $sheet->setCellValue("E$groupHeaderRow", $row[0]['stok_header']);
                $sheet->setCellValue("F$groupHeaderRow", $row[0]['satuan_header']);
                $sheet->setCellValue("G$groupHeaderRow", $row[0]['qty_header'])->getStyle("G$groupHeaderRow")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->setCellValue("H$groupHeaderRow", $row[0]['hargasatuan_header'])->getStyle("H$groupHeaderRow")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->setCellValue("I$groupHeaderRow", $row[0]['total_header'])->getStyle("I$groupHeaderRow")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->setCellValue("J$groupHeaderRow", $row[0]['persentasediscount_header'])->getStyle("J$groupHeaderRow")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->setCellValue("K$groupHeaderRow", $row[0]['nominaldiscount_header'])->getStyle("K$groupHeaderRow")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->setCellValue("L$groupHeaderRow", "=I$groupHeaderRow-K$groupHeaderRow")->getStyle("L$groupHeaderRow")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->setCellValue("M$groupHeaderRow", $row[0]['keterangan_header']);

                $sheet->getStyle("C$groupHeaderRow")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $sheet->getStyle("A$groupHeaderRow:M$groupHeaderRow")->applyFromArray($styleArray)->getFont()->setBold(true);
                $groupHeaderRow++;

                $detail_start_row = $groupHeaderRow + 1;
                // SET TABLE HEADER
                foreach ($detail_columns as $data_columns_index => $data_column) {

                    $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $data_column['label'] ?? $data_columns_index + 1);
                    $lastColumn = $alphabets[$data_columns_index];
                    $sheet->getStyle("A$detail_start_row:$lastColumn$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
                }
                $detail_start_row++;
                // SET TABLE DETAIL
                $no = 1;
                foreach ($row as $response_detail) {

                    $tglbukti = ($response_detail['tglbukti'] != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail['tglbukti']))) : '';
                    $sheet->setCellValue("A$detail_start_row", $no++);
                    $sheet->setCellValue("B$detail_start_row", $response_detail['nobukti']);
                    $sheet->setCellValue("C$detail_start_row", $tglbukti);
                    $sheet->setCellValue("D$detail_start_row", $response_detail['gudang']);
                    $sheet->setCellValue("E$detail_start_row", $response_detail['namastok']);
                    $sheet->setCellValue("F$detail_start_row", $response_detail['satuan']);
                    $sheet->setCellValue("G$detail_start_row", $response_detail['qty']);
                    $sheet->setCellValue("H$detail_start_row", $response_detail['hargasatuan']);
                    $sheet->setCellValue("I$detail_start_row", $response_detail['total']);
                    $sheet->setCellValue("J$detail_start_row", $response_detail['persentasediscount']);
                    $sheet->setCellValue("K$detail_start_row", $response_detail['nominaldiscount']);
                    $sheet->setCellValue("L$detail_start_row", "=I$detail_start_row-K$detail_start_row");
                    $sheet->setCellValue("M$detail_start_row", $response_detail['keterangan']);


                    $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                    $sheet->getStyle("G$detail_start_row:L$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                    $sheet->getStyle("A$detail_start_row:$lastColumn$detail_start_row")->applyFromArray($styleArray);
                    $detail_start_row++;
                }

                $groupHeaderRow = $detail_start_row + 2;
            }
        }

        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $writer = new Xlsx($spreadsheet);
        $filename = 'REMINDER SPK' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
