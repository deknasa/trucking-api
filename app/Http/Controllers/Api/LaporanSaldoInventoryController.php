<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LaporanSaldoInventory;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLaporanSaldoInventoryRequest;
use App\Http\Requests\UpdateLaporanSaldoInventoryRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanSaldoInventoryController extends Controller
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

        $kelompok_id = $request->kelompok_id ?? 0;
        $statusreuse = $request->statusreuse ?? 0;
        $statusban = $request->statusban ?? 0;
        $jenislaporan = $request->jenislaporan ?? 0;
        $filter = $request->filter;
        $jenistgltampil = $request->jenistgltampil ?? '';
        $priode = $request->priode;
        $stokdari_id = $request->stokdari_id ?? 0;
        $stoksampai_id = $request->stoksampai_id ?? 0;
        $dataFilter = $request->dataFilter;
        $prosesneraca = 0;
        // dump($kelompok_id);
        // dump($statusreuse);
        // dump($statusban);
        // dump($filter);
        // dump($jenistgltampil);
        // dump($priode);
        // dump($stokdari_id);
        // dump($stoksampai_id);
        // dd($dataFilter);
        // dd($request->all());
        $laporanSaldoInventory = new LaporanSaldoInventory();
        // dd( $request->jenislaporan);
        $report = LaporanSaldoInventory::getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca,$jenislaporan);
        // $report = [
        //     [
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],
        //     [
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],[
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],[
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],[
        //         'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',
        //     ],
        // ];      

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $getOpname = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'OPNAME STOK')
            ->where('subgrp', 'OPNAME STOK')
            ->where('text', '3')
            ->first();

        if (isset($getOpname)) {
            $opname = '1';
        } else {
            $opname = '0';
        }

        $queryuser = db::table("user")->from(db::raw("[user] a with (readuncommitted)"))
            ->select(
                'a.cabang_id'
            )
            ->whereraw("a.name='" . auth('api')->user()->name . "'")
            ->where('a.cabang_id', 1)
            ->first();
        if (isset($queryuser)) {
            $opname = '0';
        }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        return response([
            'data' => $report,
            'opname' => $opname,
            'judul' => $getJudul->text,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $kelompok_id = $request->kelompok_id ?? 0;
        $statusreuse = $request->statusreuse ?? 0;
        $statusban = $request->statusban ?? 0;
        $filter = $request->filter;
        $jenistgltampil = $request->jenistgltampil ?? '';
        $priode = $request->priode;
        $stokdari_id = $request->stokdari_id ?? 0;
        $stoksampai_id = $request->stoksampai_id ?? 0;
        $dataFilter = $request->dataFilter;
        $prosesneraca = 0;

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        $laporanSaldoInventory = new LaporanSaldoInventory();
        $data = $laporanSaldoInventory->getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca);
        $disetujui = $data[0]->disetujui ?? '';
        $diperiksa = $data[0]->diperiksa ?? '';
        $namacabang = 'CABANG ' . $getCabang->namacabang;

        // return response([
        //     'data' => $report,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);

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

        $sheet->setCellValue('A3', 'LAPORAN SALDO INVENTORY');
        $sheet->getStyle("A3")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A3:G3');

        $sheet->setCellValue('A5', 'PERIODE');
        $sheet->getStyle("A5")->getFont()->setBold(true);
        $sheet->getStyle("B5")->getFont()->setBold(true);

        $sheet->setCellValue('B5', ': ' . $request->priode);

        $sheet->setCellValue('A6', 'STOK');
        $sheet->getStyle("A6")->getFont()->setBold(true);
        $sheet->getStyle("B6")->getFont()->setBold(true);

        $stokdari = $data[0]->stokdari ?? " ";
        $stoksampai = $data[0]->stoksampai ?? " ";
        $kategori = $data[0]->kategori ?? "ALL";
        $lokasi = $data[0]->lokasi ?? " ";
        $namalokasi = $data[0]->namalokasi ?? " ";
        $sheet->setCellValue('B5', ': ' .  $stokdari . " S/D" . " " . $stoksampai);
        $kelompok = ($request->kelompok != '') ? $request->kelompok : 'ALL';

        $sheet->setCellValue('A7', 'KATEGORI');
        $sheet->getStyle("A7")->getFont()->setBold(true);
        $sheet->getStyle("B7")->getFont()->setBold(true);
        $sheet->setCellValue('B7', ': ' . $kelompok);

        $sheet->setCellValue('A8', $lokasi);
        $sheet->getStyle("A8")->getFont()->setBold(true);
        $sheet->getStyle("B8")->getFont()->setBold(true);
        $sheet->setCellValue('B8', ': ' . $namalokasi);

        $sheet->getStyle("C5")->getFont()->setBold(true);

        $detail_table_header_row = 9;
        $detail_start_row = $detail_table_header_row + 1;

        $alphabets = range('A', 'Z');

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [

            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
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

        $header_columns = [
            [
                "index" => "vulkanisir ke/no",
                "label" => "Vulkanisir Ke/No",
            ],
            [
                "index" => "Nm. Brg",
                "label" => "Nama Barang",
            ],
            [
                "index" => "Tanggal",
                "label" => "Tanggal",
            ],
            [
                "index" => "QTY",
                "label" => "Qty",
            ],
            [
                "index" => "Satuan",
                "label" => "Satuan",
            ],
            [
                "index" => "Saldo",
                "label" => "Nominal",
            ]
        ];




        // LOOPING DETAIL
        $previous_kategori = '';
        $start_row_main = 0;
        $no = 1;
        if (is_array($data) || is_iterable($data)) {
            $cellQty = [];
            $cellTotal = [];
            foreach ($data as $response_index => $response_detail) {

                $kategori = $response_detail->kategori;
                if ($kategori != $previous_kategori) {
                    if ($previous_kategori != '') {

                        $cellQty[] = "D$detail_start_row";
                        $cellTotal[] = "F$detail_start_row";
                        $sheet->setCellValue("B$detail_start_row", "TOTAL $previous_kategori");
                        $sheet->setCellValue("D$detail_start_row", "=SUM(D$start_row_main:D" . ($detail_start_row - 1) . ")");
                        $sheet->setCellValue("F$detail_start_row", "=SUM(F$start_row_main:F" . ($detail_start_row - 1) . ")");

                        $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                        $sheet->getStyle("F$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                        $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
                        $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
                        $detail_start_row += 2;
                    }
                    if ($previous_kategori == '') {
                        foreach ($header_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, $detail_column['label'] ?? $detail_columns_index + 1);
                        }
                        $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleHeader)->getFont()->setBold(true);
                        $detail_start_row++;
                    }

                    $start_row_main = $detail_start_row;
                    if ($kategori == 'BAN') {
                        $sheet->setCellValue('A' . ($detail_start_row), 'Vulkan Ke');
                    } else {
                        $sheet->setCellValue('A' . ($detail_start_row), 'No');
                    }
                    $sheet->setCellValue('B' . ($detail_start_row), $response_detail->kategori);
                    $sheet->getStyle("A$detail_start_row")->applyFromArray($styleHeader)->getFont()->setBold(true);
                    $sheet->getStyle("B$detail_start_row:F$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
                    $detail_start_row++;
                    $detail_start_row = $detail_start_row;
                }

                if ($kategori == 'BAN') {
                    $sheet->setCellValue("A$detail_start_row", $response_detail->vulkanisirke);
                } else {
                    $sheet->setCellValue("A$detail_start_row", $no++);
                }
                $sheet->setCellValue("B$detail_start_row", $response_detail->namabarang);
                $sheet->setCellValue("C$detail_start_row", date('d-m-Y', strtotime($response_detail->tanggal)));
                $sheet->setCellValue("D$detail_start_row", $response_detail->qty);
                $sheet->setCellValue("E$detail_start_row", $response_detail->satuan);
                $sheet->setCellValue("F$detail_start_row", $response_detail->nominal);

                $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
                $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");

                $detail_start_row++;
                $previous_kategori = $kategori;
            }

            if ($previous_kategori != '') {
                $cellQty[] = "D$detail_start_row";
                $cellTotal[] = "F$detail_start_row";
                $sheet->setCellValue("B$detail_start_row", "TOTAL $previous_kategori");
                $sheet->setCellValue("D$detail_start_row", "=SUM(D$start_row_main:D" . ($detail_start_row - 1) . ")");
                $sheet->setCellValue("F$detail_start_row", "=SUM(F$start_row_main:F" . ($detail_start_row - 1) . ")");

                $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
                $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("F$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
                $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
                $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
            }
            $detail_start_row++;


            $qty = implode("+", $cellQty);
            $total = implode("+", $cellTotal);
            $sheet->setCellValue("B$detail_start_row", "GRAND TOTAL");
            $sheet->setCellValue("D$detail_start_row", "=$qty");
            $sheet->setCellValue("F$detail_start_row", "=$total");

            $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("F$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
        }
        // set diketahui dibuat
        $ttd_start_row = $detail_start_row + 2;
        $sheet->setCellValue("B$ttd_start_row", 'Disetujui Oleh,');
        $sheet->setCellValue("D$ttd_start_row", 'Diperiksa Oleh,');
        $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("B" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("D" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');


        //style header
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

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN SALDO INVENTORY' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
