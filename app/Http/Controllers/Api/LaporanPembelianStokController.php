<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Http\Requests\ValidasiLaporanPembelianStokRequest;
use App\Models\LaporanPembelianStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class LaporanPembelianStokController extends Controller
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
    public function report(ValidasiLaporanPembelianStokRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $stokdari = $request->stokdari_id;
        $stoksampai = $request->stoksampai_id;

        $laporanpembelianstok = new LaporanPembelianStok();
        $laporan_pembelianstok = $laporanpembelianstok->getReport($dari, $sampai, $stokdari, $stoksampai);

        if ($request->isCheck) {
            if (count($laporan_pembelianstok) === 0) {
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],

                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {
            foreach ($laporan_pembelianstok as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            }

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            return response([
                'data' => $laporan_pembelianstok,
                'namacabang' => 'CABANG ' . $getCabang->namacabang
                // 'data' => $report
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $stokdari = $request->stokdari_id;
        $stoksampai = $request->stoksampai_id;

        $laporanpembelianstok = new LaporanPembelianStok();
        $laporan_pembelianstok = $laporanpembelianstok->getExport($dari, $sampai, $stokdari, $stoksampai);


        if ($request->isCheck) {
            if (count($laporan_pembelianstok) === 0) {
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],

                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'data' => 'ok'
                ]);
            }
        } else {
            foreach ($laporan_pembelianstok as $item) {
            $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            // return response([
            //     'data' => $laporan_pembelianstok,
            //     'namacabang' => 'CABANG ' . $getCabang->namacabang
            //     // 'data' => $report
            // ]);

            $pengeluaran = json_decode($laporan_pembelianstok);
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
            $sheet->mergeCells('A1:L1');
            $sheet->setCellValue('A2', $namacabang);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:L2');

            $sheet->setCellValue('A3', strtoupper($pengeluaran[0]->judulLaporan));
            $sheet->getStyle("A3")->getFont()->setBold(true);
            $sheet->mergeCells('A3:L3');

            $sheet->setCellValue('A4', strtoupper('Periode: ' . date('d - M - Y', strtotime($request->dari)) . ' S/D ' . date('d - M - Y', strtotime($request->sampai))));
            $sheet->getStyle("A4")->getFont()->setBold(true);
            $sheet->mergeCells('A4:L4');

            $sheet->setCellValue('A5', strtoupper('Stok: ' . $request->stokdari . ' S/D ' . $request->stoksampai));
            $sheet->getStyle("A5")->getFont()->setBold(true);
            $sheet->mergeCells('A5:L5');

            $header_start_row = 7;
            $detail_start_row = 8;

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
                    'label' => 'No',
                ],
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tgl Bukti',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Nama Supplier',
                    'index' => 'namasupplier',
                ],
                [
                    'label' => 'Stok',
                    'index' => 'stok_id',
                ],
                [
                    'label' => 'Nama Stok',
                    'index' => 'namastok',
                ],
                [
                    'label' => 'Qty',
                    'index' => 'qty',
                ],
                [
                    'label' => 'HARGA',
                    'index' => 'harga',
                ],
                [
                    'label' => 'DISKON',
                    'index' => 'nominaldiscount',
                ],
                [
                    'label' => 'TOTAL',
                    'index' => 'total',
                ],
                [
                    'label' => 'Satuan',
                    'index' => 'satuan',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
            ];

            foreach ($header_columns as $data_columns_index => $data_column) {
                $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
            }

            $lastColumn = $alphabets[$data_columns_index];
            $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->getFont()->setBold(true);
            $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $totalDebet = 0;
            $totalKredit = 0;
            $totalSaldo = 0;
            $no = 1;
            if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
                foreach ($pengeluaran as $response_index => $response_detail) {

                    // foreach ($header_columns as $detail_columns_index => $detail_column) {
                    //     $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail[$detail_column['index']] : $response_index + 1);
                    // }

                    $sheet->setCellValue("A$detail_start_row", $no);
                    $sheet->setCellValue("B$detail_start_row", $response_detail->nobukti);
                    $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                    $sheet->setCellValue("C$detail_start_row", $dateValue);
                    $sheet->getStyle("C$detail_start_row")
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                    $sheet->setCellValue("D$detail_start_row", $response_detail->namasupplier);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->stok_id);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->namastok);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("H$detail_start_row", $response_detail->harga);
                    $sheet->setCellValue("I$detail_start_row", $response_detail->nominaldiscount);
                    $sheet->setCellValue("J$detail_start_row", $response_detail->total);
                    $sheet->setCellValue("K$detail_start_row", $response_detail->satuan);
                    $sheet->setCellValue("L$detail_start_row", $response_detail->keterangan);

                    $sheet->getStyle("A$detail_start_row:L$detail_start_row")->applyFromArray($styleArray);
                    $sheet->getStyle("D$detail_start_row:L$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    // $sheet->getStyle("B$detail_start_row:B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                    // $sheet->getStyle("D$detail_start_row:D$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');


                    //    $totalKredit += $response_detail['kredit'];
                    //     $totalDebet += $response_detail['debet'];
                    //     $totalSaldo += $response_detail['Saldo'];
                    $detail_start_row++;
                    $no++;
                }
            }


            //ukuran kolom
            $sheet->getColumnDimension('A')->setWidth(4);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(19);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setWidth(56);



            // menambahkan sel Total pada baris terakhir + 1
            // $sheet->setCellValue("A" . ($detail_start_row + 1), 'Total');
            // $sheet->setCellValue("D" . ($detail_start_row + 1), "=SUM(D5:D" . $detail_start_row . ")");
            // $sheet->setCellValue("E" . ($detail_start_row + 1), "=SUM(E5:E" . $detail_start_row . ")");


            //FORMAT
            // set format ribuan untuk kolom D dan E
            $sheet->getStyle("D" . ($detail_start_row + 1) . ":E" . ($detail_start_row + 1))->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->getStyle("A" . ($detail_start_row + 1) . ":$lastColumn" . ($detail_start_row + 1))->getFont()->setBold(true);


            $rowKosong = "";
            // menambahkan sel Total pada baris terakhir + 1

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':G' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':G' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

            $totalNominal = "=SUM(H8:H" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("H$total_start_row", $totalNominal)->getStyle("H$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->setCellValue("H$total_start_row", $totalNominal)->getStyle("H$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("H$total_start_row", $totalNominal)->getStyle("H$total_start_row")->applyFromArray($styleArray);

            $totalNominal = "=SUM(I8:I" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("I$total_start_row", $totalNominal)->getStyle("I$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->setCellValue("I$total_start_row", $totalNominal)->getStyle("I$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("I$total_start_row", $totalNominal)->getStyle("I$total_start_row")->applyFromArray($styleArray);

            $totalNominal = "=SUM(J8:J" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("J$total_start_row", $totalNominal)->getStyle("J$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->setCellValue("J$total_start_row", $totalNominal)->getStyle("J$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("J$total_start_row", $totalNominal)->getStyle("J$total_start_row")->applyFromArray($styleArray);

            $sheet->setCellValue("K$total_start_row", $rowKosong)->getStyle("K$total_start_row")->applyFromArray($styleArray);
            $sheet->setCellValue("L$total_start_row", $rowKosong)->getStyle("L$total_start_row")->applyFromArray($styleArray);

            $ttd_start_row = $detail_start_row + 3;
            $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
            $sheet->setCellValue("C$ttd_start_row", 'Diperiksa Oleh,');
            $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

            $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
            $sheet->setCellValue("C" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
            $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

            $writer = new Xlsx($spreadsheet);
            $filename = 'EXPORTPEMBELIANSTOK' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
