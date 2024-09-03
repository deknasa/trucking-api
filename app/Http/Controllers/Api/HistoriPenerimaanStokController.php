<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexHistoriPenerimaanRequest;
use Illuminate\Http\Request;
use App\Models\HistoriPenerimaanStok;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\Penerimaanstok;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HistoriPenerimaanStokController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexHistoriPenerimaanRequest $request)
    {
        $HistoriPenerimaanStok = new HistoriPenerimaanStok();
        return response([
            'data' => $HistoriPenerimaanStok->get(),
            'attributes' => [
                'totalRows' => $HistoriPenerimaanStok->totalRows,
                'totalPages' => $HistoriPenerimaanStok->totalPages
            ]
        ]);
    }

    public function default()
    {
        $histori = new HistoriPenerimaanStok();
        return response([
            'status' => true,
            'data' => $histori->default(),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $HistoriPenerimaanStok = new HistoriPenerimaanStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = PenerimaanStok::findOrFail($request->filter);

        $report = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->keterangan,
        ];
        $dataHeader = $report;
        $historipenerimaanstok = $HistoriPenerimaanStok->get();

        if ($request->export == true) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $historipenerimaanstok[0]->judul);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->setCellValue('A2', $historipenerimaanstok[0]->judulLaporan);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:H2');

            $header_start_row = 3;
            $header_right_start_row = 3;
            $detail_table_header_row = 8;
            $detail_start_row = $detail_table_header_row + 1;
            $mergecell_start_row = 7;

            $alphabets = range('A', 'Z');
            $header_columns = [
                [
                    'label' => 'Periode',
                    'index' => 'dari',
                ],
                [
                    'label' => 'stok',
                    'index' => 'stokdari',
                ],
                [
                    'label' => 'Penerimaan Stok',
                    'index' => 'filter',
                ],
            ];

            $header_right_columns = [

                [
                    'label' => 's/d',
                    'index' => 'sampai',
                ],
            ];
            $underheader_right_columns = [

                [
                    'label' => 's/d',
                    'index' => 'stoksampai',
                ],
            ];

            $detail_columns = [
                [
                    'label' => 'Kd Brg',
                    'index' => 'kodebarang',
                ],
                [
                    'label' => 'Nama Barang',
                    'index' => 'namabarang',
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Kategori',
                    'index' => 'kategori_id',
                ],
                [
                    'label' => 'QTY',
                    'index' => 'qtymasuk'
                ],
                [
                    'label' => 'Nominal',
                    'index' => 'nilaimasuk'
                ],
                [
                    'label' => 'Total',
                    'index' => 'total'
                ],
            ];
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $dataHeader[$header_column['index']]);
            }

            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                $sheet->setCellValue('E' . $header_right_start_row++, $dataHeader['sampai']);
            }
            foreach ($underheader_right_columns as $header_right_column) {
                $sheet->setCellValue('D4', $header_right_column['label']);
                $sheet->setCellValue('E4', $dataHeader['stoksampai']);
            }


            foreach ($detail_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            }
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

            $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            foreach ($historipenerimaanstok as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                }

                $sheet->setCellValue("A$detail_start_row", $response_detail->kodebarang);
                $sheet->setCellValue("B$detail_start_row", $response_detail->namabarang);
                // $sheet->setCellValue("C$detail_start_row", date('d-m-Y', strtotime($response_detail->tglbukti)));
                $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                $sheet->setCellValue("C$detail_start_row", $dateValue);
                $sheet->getStyle("C$detail_start_row")
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');

                $sheet->setCellValue("D$detail_start_row", $response_detail->nobukti);
                $sheet->setCellValue("E$detail_start_row", $response_detail->kategori_id);
                $sheet->setCellValue("F$detail_start_row", $response_detail->qtymasuk);
                $sheet->setCellValue("G$detail_start_row", $response_detail->nilaimasuk);
                $sheet->setCellValue("H$detail_start_row", $response_detail->total);

                $sheet->getStyle("A$detail_start_row:H$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("F$detail_start_row:H$detail_start_row")->applyFromArray($style_number);
                $sheet->getStyle("F$detail_start_row:H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Histori Penerimaan Stok  ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $historipenerimaanstok,
                'dataheader' => $dataHeader
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export() {}
}
