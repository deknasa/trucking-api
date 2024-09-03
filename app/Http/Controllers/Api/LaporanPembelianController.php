<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ReportLaporanPembelianRequest;
use App\Models\LaporanPembelian;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class LaporanPembelianController extends Controller
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
    public function report(ReportLaporanPembelianRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));
        $supplierdari = $request->supplierdari;
        $suppliersampai = $request->suppliersampai;
        $supplierdari_id = $request->supplierdari_id;
        $suppliersampai_id = $request->suppliersampai_id;
        $status = $request->status;

        $laporanpembelian = new LaporanPembelian();
        $laporan_pembelian = $laporanpembelian->getReport($dari, $sampai, $supplierdari, $suppliersampai, $supplierdari_id, $suppliersampai_id, $status);

        if ($request->isCheck) {
            if (count($laporan_pembelian) === 0) {
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
            foreach ($laporan_pembelian as $item) {
                $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
            }
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
                
            return response([
                'data' => $laporan_pembelian,
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
        $supplierdari = $request->supplierdari ?? '';
        $suppliersampai = $request->suppliersampai ?? '';
        $supplierdari_id = $request->supplierdari_id ?? 0;
        $suppliersampai_id = $request->suppliersampai_id ?? 0;
        $status = $request->status;

        $laporanpembelian = new LaporanPembelian();

        $laporan_pembelian = $laporanpembelian->getReport($dari, $sampai, $supplierdari, $suppliersampai, $supplierdari_id, $suppliersampai_id, $status);

        if ($request->isCheck) {
            if (count($laporan_pembelian) === 0) {
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

            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            // return response([
            //     'data' => $laporan_pembelian,
            //     'namacabang' => 'CABANG ' . $getCabang->namacabang
            //     // 'data' => $Export
            // ]);

            $pengeluaran = json_decode($laporan_pembelian);
            $namacabang = 'CABANG ' . $getCabang->namacabang;
            $disetujui = $pengeluaran[0]->disetujui ?? '';
            $diperiksa = $pengeluaran[0]->diperiksa ?? '';

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pengeluaran[0]->judul);
            $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A2', $namacabang);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:G2');

            $sheet->setCellValue('A3', $pengeluaran[0]->judulLaporan);
            // $sheet->mergeCells('A3:B3');
            $sheet->setCellValue('A4', 'Tanggal : ' . date('d-M-Y', strtotime($dari)) . ' s/d ' . date('d-M-Y', strtotime($sampai)));
            // $sheet->mergeCells('A4:B4');
            $sheet->setCellValue('A5', 'Status : ' . $request->status);
            // $sheet->mergeCells('A5:B5');

            $sheet->getStyle("A3")->getFont()->setBold(true);
            $sheet->getStyle("A4:B4")->getFont()->setBold(true);
            $sheet->getStyle("A5:B5")->getFont()->setBold(true);

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
                    'label' => 'Nama Stok',
                    'index' => 'namastok',
                ],
                [
                    'label' => 'Qty',
                    'index' => 'qty',
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

            if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
                foreach ($pengeluaran as $response_index => $response_detail) {

                    // foreach ($header_columns as $detail_columns_index => $detail_column) {
                    //     // dd($detail_column);

                    //     $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail[$detail_column['index']] : $response_index + 1);
                    // }

                    $sheet->setCellValue("A$detail_start_row", $response_detail->nobukti);
                    $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                    $sheet->setCellValue("B$detail_start_row", $dateValue);
                    $sheet->getStyle("B$detail_start_row")
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                    $sheet->setCellValue("C$detail_start_row", $response_detail->namasupplier);
                    $sheet->setCellValue("D$detail_start_row", $response_detail->namastok);
                    $sheet->setCellValue("E$detail_start_row", $response_detail->qty);
                    $sheet->setCellValue("F$detail_start_row", $response_detail->satuan);
                    $sheet->setCellValue("G$detail_start_row", $response_detail->keterangan);

                    $sheet->getStyle("A$detail_start_row:G$detail_start_row")->applyFromArray($styleArray);
                    // $sheet->getStyle("C$detail_start_row:I$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
                    // $sheet->getStyle("B$detail_start_row:B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                    // $sheet->getStyle("D$detail_start_row:D$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');


                    //    $totalKredit += $response_detail['kredit'];
                    //     $totalDebet += $response_detail['debet'];
                    //     $totalSaldo += $response_detail['Saldo'];
                    $detail_start_row++;
                }
            }

            $ttd_start_row = $detail_start_row + 2;
            $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
            $sheet->setCellValue("C$ttd_start_row", 'Diperiksa Oleh,');
            $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

            $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
            $sheet->setCellValue("C" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
            $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

            //ukuran kolom
            $sheet->getColumnDimension('A')->setWidth(18);
            $sheet->getColumnDimension('B')->setWidth(14);
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->getColumnDimension('D')->setWidth(33);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setWidth(8);
            $sheet->getColumnDimension('G')->setWidth(72);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN PEMBELIAN PER SUPPLIER' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
