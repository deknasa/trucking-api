<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanLabaRugiRequest;
use App\Models\Cabang;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanLabaRugi;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanLabaRugiController extends Controller
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
    public function report(ValidasiLaporanLabaRugiRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);
        $cabang_id = $request->cabang_id ?? 0;

        $laporanlabarugi = new LaporanLabaRugi();

        $laporan_labarugi = $laporanlabarugi->getReport($bulan, $tahun, $cabang_id);

        $cabang = Cabang::find($request->cabang_id);
        $dataHeader = [
            'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
        ];

        if (count($laporan_labarugi) == 0) {
            return response([
                'data' => $laporan_labarugi,
                'message' => 'tidak ada data'
            ], 500);
        } else {
            return response([
                'data' => $laporan_labarugi,
                'dataheader' => $dataHeader,
                'message' => 'berhasil'
            ]);
        }
    }

    public function getBulan($bln)
    {
        switch ($bln) {
            case 1:
                return "JANUARI";
                break;
            case 2:
                return "FEBRUARI";
                break;
            case 3:
                return "MARET";
                break;
            case 4:
                return "APRIL";
                break;
            case 5:
                return "MEI";
                break;
            case 6:
                return "JUNI";
                break;
            case 7:
                return "JULI";
                break;
            case 8:
                return "AGUSTUS";
                break;
            case 9:
                return "SEPTEMBER";
                break;
            case 10:
                return "OKTOBER";
                break;
            case 11:
                return "NOVEMBER";
                break;
            case 12:
                return "DESEMBER";
                break;
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);
        $cabang_id = $request->cabang_id ?? 0;

        $laporanlabarugi = new LaporanLabaRugi();
        $laporan_labarugi = $laporanlabarugi->getReport($bulan, $tahun, $cabang_id);

        if (count($laporan_labarugi) == 0) {
            return response([
                'data' => $laporan_labarugi,
                'message' => 'tidak ada data'
            ], 500);
        } else {

            $cabang = Cabang::find($request->cabang_id);
            // $dataHeader = [
            $cabang = ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang;
            // ];
            // return response([
            //     'data' => $laporan_labarugi,
            //     'dataheader' => $dataHeader,
            //     'message' => 'berhasil'
            // ]);

            $pengeluaran = json_decode($laporan_labarugi);
            $disetujui = $pengeluaran[0]->disetujui ?? '';
            $diperiksa = $pengeluaran[0]->diperiksa ?? '';

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet = $spreadsheet->getActiveSheet();
            $bulan = $this->getBulan(substr($request->sampai, 0, 2));
            $tahun = substr($request->sampai, 3, 4);

            $sheet->setCellValue('A1', $pengeluaran[0]->CmpyName ?? '');
            $sheet->setCellValue('A2', $cabang ?? '');
            $sheet->setCellValue('A3', 'LAPORAN LABA RUGI');
            $sheet->setCellValue('A4', 'PERIODE : ' . $bulan . ' - ' . $tahun);
            $sheet->setCellValue('A5',  $pengeluaran[0]->Cabang ?? '');

            $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle("A3")->getFont()->setBold(true);
            $sheet->getStyle("A4")->getFont()->setBold(true);
            $sheet->getStyle("A5")->getFont()->setBold(true);

            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:C1');
            $sheet->mergeCells('A2:C2');
            $sheet->mergeCells('A3:B3');
            $sheet->mergeCells('A4:B4');
            $sheet->mergeCells('A5:B5');

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
                    'label' => 'KETERANGAN',
                    'index' => 'keteranganmain',
                ],

                [
                    'label' => 'NILAI',
                    'index' => 'Nominal',
                ],
            ];

            foreach ($header_columns as $data_columns_index => $data_column) {
                $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
            }

            $lastColumn = $alphabets[$data_columns_index];
            $sheet->getStyle("A$header_start_row:C$header_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);

            $sheet->mergeCells("B$header_start_row:C$header_start_row");
            $totalDebet = 0;
            $totalKredit = 0;
            $totalSaldo = 0;
            // $no = 1;
            if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
                // $no = 1;
                foreach ($pengeluaran as $row) {
                    $keteranganmain = $row->keteranganmain;
                    $KeteranganParent = $row->KeteranganParent;
                    $groupedData[$keteranganmain][$KeteranganParent][] = $row;
                }
                // Menambahkan baris untuk Pendapatan
                // Tulis label "Pendapatan :" pada kolom "A"
                $previous_keterangan_main = '';
                $previous_keterangan_type = '';
                $total_per_keterangan_type = 0;
                $total_start_row = 0;
                $total_start_row_per_main = 0;
                $start_last_main = 0;
                $start_row_main = 0;
                $rowPendapatan = '';

                // Gabungkan sel pada kolom "A" untuk label "Pendapatan :"
                $sheet->mergeCells("A$detail_start_row:A$detail_start_row");
                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);

                $sumLaba = [];
                foreach ($groupedData as $keteranganmain => $group) {
                    $sheet->setCellValue("A$detail_start_row", $keteranganmain)->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
                    $startRowMain = $detail_start_row;
                    $detail_start_row++;
                    $prevKetParent = '';
                    $startRowParent = 0;
                    foreach ($group as $KeteranganParent => $row) {
                        $startRowParent = $detail_start_row;
                        $sheet->setCellValue("A$detail_start_row", "      " . $KeteranganParent)->getStyle("A" . ($detail_start_row) . ":C" . ($detail_start_row))->applyFromArray($styleArray)->getFont()->setBold(true);
                        $detail_start_row++;

                        foreach ($row as $response_detail) {
                            $sheet->setCellValue("A$detail_start_row", "            " . $response_detail->keterangancoa);
                            $sheet->setCellValue("B$detail_start_row", $response_detail->Nominal);

                            $sheet->getStyle("A" . ($detail_start_row) . ":C" . ($detail_start_row))->applyFromArray($styleArray);
                            $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                            $detail_start_row++;
                        }
                        $totalPerParent = "=SUM(B" . ($startRowParent + 1) . ":B" . ($detail_start_row - 1) . ")";
                        $sheet->setCellValue("C$startRowParent", $totalPerParent)->getStyle("C$startRowParent")->getFont()->setBold(true);
                        $sheet->getStyle("C$startRowParent")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    }
                    $sheet->getStyle("A" . ($detail_start_row) . ":C" . ($detail_start_row))->applyFromArray($styleArray)->getFont()->setBold(true);

                    $totalPerMain = "=SUM(B" . ($startRowMain) . ":B" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("A$detail_start_row", "TOTAL $keteranganmain");
                    $sheet->setCellValue("C$detail_start_row", $totalPerMain);
                    $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    array_push($sumLaba, 'C' . $detail_start_row);

                    $detail_start_row += 2;
                }
                $totalLaba = "=" . implode('+', $sumLaba);
                $detail_start_row--;
                $sheet->getStyle("A" . ($detail_start_row) . ":C" . ($detail_start_row))->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->setCellValue("A$detail_start_row", "LABA (RUGI) BERSIH : ");
                $sheet->setCellValue("C$detail_start_row", $totalLaba)->getStyle("C$detail_start_row");
                $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            }
            //ukuran kolom
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $detail_start_row++;
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN LABA RUGI ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
