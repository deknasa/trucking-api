<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHistoryPinjaman;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanHistoryPinjamanController extends Controller
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
        $supirdari_id = $request->supirdari_id ?? 0;
        $supirsampai_id = $request->supirsampai_id ?? 0;
        $supirdari = ($supirdari_id != '') ? Supir::find($supirdari_id) : '';
        $supirsampai = ($supirsampai_id != '') ? Supir::find($supirsampai_id) : '';

        $laporanhistorypinjaman = new LaporanHistoryPinjaman();

        $laporan_historypinjaman = $laporanhistorypinjaman->getReport($supirdari_id, $supirsampai_id);
        foreach ($laporan_historypinjaman as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            if ($supirdari_id != '') {
                $item->supirdari = $supirdari->namasupir;
                $item->supirsampai = $supirsampai->namasupir;
            }
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();


        return response([
            'data' => $laporan_historypinjaman,
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
        $supirdari_id = $request->supirdari_id ?? '';
        $supirsampai_id = $request->supirsampai_id ?? '';
        $supirdari = ($supirdari_id != '') ? Supir::find($supirdari_id) : '';
        $supirsampai = ($supirsampai_id != '') ? Supir::find($supirsampai_id) : '';

        $laporanhistorypinjaman = new LaporanHistoryPinjaman();


        $laporan_historypinjaman = $laporanhistorypinjaman->getReport($supirdari_id, $supirsampai_id);
        foreach ($laporan_historypinjaman as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_historypinjaman,
        //     'supirdari' => ($supirdari_id != '') ? $supirdari->namasupir : 'SEMUA',
        //     'supirsampai' => ($supirsampai_id != '') ? $supirsampai->namasupir : 'SEMUA',
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     // 'data' => $report
        // ]);

        $pengeluaran = json_decode($laporan_historypinjaman);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $supirdari = ($supirdari_id != '') ? $supirdari->namasupir : 'SEMUA';
        $supirsampai = ($supirsampai_id != '') ? $supirsampai->namasupir : 'SEMUA';
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $pengeluaran[0]->judul ?? '');
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A2', $namacabang ?? '');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:E2');

        $sheet->setCellValue('A3', strtoupper('Laporan History Pinjaman'));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A4', 'SUPIR : ' .  $supirdari . ' S/D ' . $supirsampai);
        $sheet->getStyle("A4")->getFont()->setBold(true);

        $sheet->mergeCells('A4:B4');

        $header_start_row = 6;
        $detail_start_row = 7;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $borderVertical = [
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $borderOutsideStyle = [
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $alphabets = range('A', 'Z');

        $header_columns = [
            [
                'label' => 'Tanggal',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'No Bukti',
                'index' => 'nobukti',
            ],
            [
                'label' => 'Nama Supir',
                'index' => 'namasupir',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Nominal',
                'index' => 'nominal',
            ],
            [
                'label' => 'Saldo',
                'index' => 'Saldo',
            ],
        ];

        foreach ($header_columns as $data_columns_index => $data_column) {
            $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
        }
        $sheet->getStyle("A$header_start_row:F$header_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
        $totalnominal = 0;
        $totalSaldo = 0;
        $prevNobukti = '';
        $kelang = 1;
        foreach ($pengeluaran as $response_index => $response_detail) {
            $nobuktiAwal = $response_detail->nobuktipinjaman;
            $totalnominal += $response_detail->nominal;
            $totalSaldo += $response_detail->Saldo;
            foreach ($header_columns as $data_columns_index => $data_column) {
                if (($data_column['index'] == 'nominal') || ($data_column['index'] == 'Saldo')) {
                    // $response_detail[$data_column['index']] = (number_format((float) $response_detail[$data_column['index']], '2', '.', ','))->applyFromArray($style_number);
                    $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $response_detail->{$data_column['index']})
                        ->getStyle($alphabets[$data_columns_index] . $detail_start_row)
                        ->applyFromArray($style_number)
                        ->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                } else if ($data_column['index'] == 'tglbukti') {
                    $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                    $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $dateValue)->getStyle($alphabets[$data_columns_index] . $detail_start_row)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                } else {
                    $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $response_detail->{$data_column['index']})->getStyle($alphabets[$data_columns_index] . $detail_start_row)
                        ->applyFromArray($borderVertical);
                }
            }

            if ($nobuktiAwal != $prevNobukti) {
                if ($prevNobukti != '') {
                    // $sheet->getStyle("A" . ($detail_start_row - $kelang) . ":F" . ($detail_start_row - 1))->applyFromArray($borderOutsideStyle);
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($borderOutsideStyle);
                }
            } else {

                $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($borderVertical);
                $kelang++;
            }
            // $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
            $detail_start_row++;

            $prevNobukti = $response_detail->nobuktipinjaman;
        }

        if ($prevNobukti != '') {
            $sheet->getStyle("A" . ($detail_start_row - $kelang) . ":F" . ($detail_start_row - 1))->applyFromArray($borderOutsideStyle);
        }


        $lastColumn = $alphabets[$data_columns_index];
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->getFont()->setBold(true);

        $total_start_row = $detail_start_row;
        $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
        $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':F' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

        $totalDebet = "=SUM(E7:E" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00");

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(72);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        // $sheet->getColumnDimension('F')->setAutoSize(true);
        $total_start_row = $detail_start_row;
        // $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A'.$total_start_row.':C'.$total_start_row)->getFont()->setBold(true);

        // $sheet->setCellValue("D$total_start_row", number_format((float) $totalnominal, '2', '.', ','))->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        // $sheet->setCellValue("E$total_start_row", number_format((float) $totalSaldo, '2', '.', ','))->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);



        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN HISTORY PINJAMAN ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
