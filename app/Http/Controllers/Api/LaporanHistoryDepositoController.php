<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanHistoryDeposito;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanHistoryDepositoController extends Controller
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

        $supirdari_id = $request->supirdari_id;
        $supirdari = $request->supirdari;
        $laporanhistorydeposito = new LaporanHistoryDeposito();


        $laporan_historydeposito = $laporanhistorydeposito->getReport($supirdari_id);
        foreach ($laporan_historydeposito as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();



        return response([
            'data' => $laporan_historydeposito,
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
        $supirdari_id = $request->supirdari_id;
        $supirdari = $request->supirdari;

        $laporanhistorydeposito = new LaporanHistoryDeposito();
        $laporan_historydeposito = $laporanhistorydeposito->getReport($supirdari_id);

        foreach ($laporan_historydeposito as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_historydeposito,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     // 'data' => $report
        // ]);

        $pengeluaran = json_decode($laporan_historydeposito);
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
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A2', $namacabang);
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:E2');

        $sheet->setCellValue('A3', strtoupper('Laporan History Deposito'));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:E3');

        $sheet->setCellValue('A4', strtoupper('SUPIR: ' . $request->supirdari));
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->mergeCells('A4:E4');


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
                'label' => 'NO BUKTI',
                'index' => 'nobukti',
            ],
            [
                'label' => 'TANGGAL BUKTI',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'KETERANGAN',
                'index' => 'keterangan',
            ],
            [
                'label' => 'NOMINAL',
                'index' => 'nominal',
            ],
            [
                'label' => 'SALDO',
                'index' => 'Saldo',
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

                $sheet->setCellValue("A$detail_start_row", $response_detail->nobukti);
                $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                $sheet->setCellValue("B$detail_start_row", $dateValue);
                $sheet->getStyle("B$detail_start_row")
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');

                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);
                $sheet->setCellValue("E$detail_start_row", $response_detail->Saldo);


                $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("C$detail_start_row:E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }
        }
        //ukuran kolom
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(50);

        //FORMAT
        // set format ribuan untuk kolom D dan E
        $sheet->getStyle("D" . ($detail_start_row + 1) . ":E" . ($detail_start_row + 1))->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        $sheet->getStyle("A" . ($detail_start_row + 1) . ":$lastColumn" . ($detail_start_row + 1))->getFont()->setBold(true);

        // persetujuan
        $sheet->mergeCells('A' . ($detail_start_row + 3) . ':B' . ($detail_start_row + 3));
        $sheet->setCellValue('A' . ($detail_start_row + 3), 'Disetujui Oleh,');
        $sheet->mergeCells('C' . ($detail_start_row + 3) . ($detail_start_row + 3));
        $sheet->setCellValue('C' . ($detail_start_row + 3), 'Diperiksa Oleh');
        $sheet->mergeCells('D' . ($detail_start_row + 3) . ':E' . ($detail_start_row + 3));
        $sheet->setCellValue('D' . ($detail_start_row + 3), 'Disusun Oleh,');

        $sheet->mergeCells('A' . ($detail_start_row + 6) . ':B' . ($detail_start_row + 6));
        $sheet->setCellValue('A' . ($detail_start_row + 6), '( ' . $disetujui . ' )');
        $sheet->mergeCells('C' . ($detail_start_row + 6) . ($detail_start_row + 6));
        $sheet->setCellValue('C' . ($detail_start_row + 6), '( ' . $diperiksa . ' )');
        $sheet->mergeCells('D' . ($detail_start_row + 6) . ':E' . ($detail_start_row + 6));
        $sheet->setCellValue('D' . ($detail_start_row + 6), '(                                          )');

        // style persetujuan
        $sheet->getStyle('A' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($detail_start_row + 3))->getFont()->setSize(12);
        $sheet->getStyle('C' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . ($detail_start_row + 3))->getFont()->setSize(12);
        $sheet->getStyle('D' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . ($detail_start_row + 3))->getFont()->setSize(12);

        $sheet->getStyle('A' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($detail_start_row + 6))->getFont()->setSize(12);
        $sheet->getStyle('C' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . ($detail_start_row + 6))->getFont()->setSize(12);
        $sheet->getStyle('D' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . ($detail_start_row + 6))->getFont()->setSize(12);

        $writer = new Xlsx($spreadsheet);
        $filename = 'EXPORTHISTORYDEPOSITO' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
