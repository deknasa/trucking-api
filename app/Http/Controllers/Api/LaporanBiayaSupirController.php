<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanBiayaSupir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class LaporanBiayaSupirController extends Controller
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $laporanBiayaSupir = new LaporanBiayaSupir();
        $laporan_BiayaSupir = $laporanBiayaSupir->getExport($dari, $sampai);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporanBiayaSupir->getExport($dari, $sampai),
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);

        $data = json_decode($laporan_BiayaSupir);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';
        if (count($data) > 0) {

            $groupedData = [];
            if (is_array($data)) {
                foreach ($data as $row) {
                    $supir_id = $row->supir_id;
                    $groupedData[$supir_id][] = $row;
                }
            }

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $data[0]->judul);
            $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A2', $namacabang);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:G2');

            $sheet->setCellValue('A3', strtoupper($data[0]->judulLaporan));
            $sheet->getStyle("A3")->getFont()->setBold(true);
            $sheet->mergeCells('A3:G3');

            $sheet->setCellValue('A4', strtoupper('Periode : ' . $request->dari . ' s/d ' . $request->sampai));
            $sheet->getStyle("A4")->getFont()->setBold(true);
            $sheet->mergeCells('A4:G4');
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
                    'label' => 'No KTP',
                    'index' => 'noktp',
                ],
                [
                    'label' => 'No KTP',
                    'index' => 'noktp',
                ],
                [
                    'label' => 'No Transaksi',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Bukti Kas',
                    'index' => 'pengeluaran_nobukti',
                ],
                [
                    'label' => 'Tgl Post Kas',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Akun',
                    'index' => 'coa',
                ],
                [
                    'label' => 'Keterangan Akun',
                    'index' => 'keterangancoa',
                ],
                [
                    'label' => 'Nominal',
                    'index' => 'nominal',
                ],

            ];

            $styleBackground = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFDCDCDC',
                    ],
                ],
            ];

            $detail_start_row = 6;

            foreach ($header_columns as $data_columns_index => $data_column) {
                if ($data_column['label'] == 'No KTP') {

                    $sheet->mergeCells("A$detail_start_row:B$detail_start_row");
                }
                $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $data_column['label'] ?? $data_columns_index + 1);
                $lastColumn = $alphabets[$data_columns_index];
                $sheet->getStyle("A$detail_start_row:H$detail_start_row")->applyFromArray($styleBackground);
            }
            $detail_start_row++;
            foreach ($groupedData as $supir_id => $row) {

                $sheet->setCellValue("A$detail_start_row", 'Supir : ' . $row[0]->namasupir)->getStyle("A$detail_start_row:H$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->mergeCells("A$detail_start_row:H$detail_start_row");
                $detail_start_row++;
                // // DATA
                $startSum = $detail_start_row;
                foreach ($row as $response_detail) {

                    $tglbukti = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';


                    $sheet->setCellValueExplicit("B$detail_start_row", $response_detail->noktp, DataType::TYPE_STRING2);
                    $sheet->setCellValue("C$detail_start_row", $response_detail->nobukti);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->pengeluaran_nobukti);
                    $sheet->setCellValue("E$detail_start_row", $tglbukti);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->coa);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->keterangancoa);
                    $sheet->setCellValue("H$detail_start_row", $response_detail->nominal);

                    $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                    $sheet->getStyle("H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("B$detail_start_row:$lastColumn$detail_start_row")->applyFromArray($styleArray);
                    $detail_start_row++;
                }
                $sum = "=SUM(H$startSum:H" . ($detail_start_row - 1) . ")";
                $sheet->mergeCells("A$startSum:A" . ($detail_start_row));
                $sheet->getStyle("A$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("B$detail_start_row:$lastColumn$detail_start_row")->applyFromArray($styleBackground);
                $sheet->mergeCells("B$detail_start_row:G$detail_start_row");
                // $sheet->setCellValue("B$detail_start_row", "TOTAL")->getStyle("B$detail_start_row")->getFont()->setBold(true);
                $sheet->setCellValue("H$detail_start_row", $sum)->getStyle("H$detail_start_row")->getFont()->setBold(true);
                $sheet->getStyle("H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }
            $sheet->getColumnDimension('A')->setWidth(3);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN BIAYA SUPIR' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
