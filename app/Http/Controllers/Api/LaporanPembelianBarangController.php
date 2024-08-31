<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LaporanPembelianBarang;
use App\Http\Requests\ValidasiLaporanPembelianBarangRequest;
use App\Http\Requests\StoreLaporanPembelianBarangRequest;
use App\Http\Requests\UpdateLaporanPembelianBarangRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPembelianBarangController extends Controller
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
    public function report(ValidasiLaporanPembelianBarangRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);

        $laporanpembelianbarang = new LaporanPembelianBarang();

        $laporan_pembelianbarang = $laporanpembelianbarang->getReport($bulan, $tahun);

        if (count($laporan_pembelianbarang) == 0) {
            return response([
                'data' => $laporan_pembelianbarang,
                'message' => 'tidak ada data'
            ], 500);
        } else {
            return response([
                'data' => $laporan_pembelianbarang,
                'message' => 'berhasil'
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, -4);

        $laporanpembelianbarang = new LaporanPembelianBarang();
        $laporan_pembelianbarang = $laporanpembelianbarang->getReport($bulan, $tahun);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_pembelianbarang,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     // 'data' => $report
        // ]);

        $pengeluaran = json_decode($laporan_pembelianbarang);
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
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A2', $namacabang);
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:I2');

        $sheet->setCellValue('A3', strtoupper('Laporan Pembelian Stok'));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:I3');

        $sheet->setCellValue('A4', strtoupper('Bulan ' . date('M-Y', strtotime($pengeluaran[0]->tglbukti))));
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->mergeCells('A4:I4');

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
                "label" => "No",
                "index" => 'no',
            ],
            [
                "label" => "No Bukti",
                "index" => 'nobukti',
            ],
            [
                "label" => "Tanggal",
                "index" => 'tglbukti',
            ],
            [
                "label" => "Nama Stock",
                "index" => 'namastok',
            ],
            [
                "label" => "Qty",
                "index" => 'qty',
            ],
            [
                "label" => "Satuan",
                "index" => 'satuan',
            ],
            [
                "label" => "Harga",
                "index" => 'harga',
            ],
            [
                "label" => "Nominal",
                "index" => 'nominal',
            ],
            [
                "label" => "Keterangan",
                "index" => 'keterangan',
            ],
        ];
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

        foreach ($header_columns as $data_columns_index => $data_column) {
            $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
        }

        $lastColumn = $alphabets[$data_columns_index];
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        $no = 1;
        if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
            foreach ($pengeluaran as $response_detail) {
                foreach ($header_columns as $data_columns_index => $data_column) {
                    if ($data_column['index'] == 'no') {
                        $value = $no;
                    } else {
                        $value = $response_detail->{$data_column['index']};
                    }

                    if ($data_column['index'] == 'tglbukti') {
                        $value = date('d-m-Y', strtotime($value));
                    }
                    if ($data_column['index'] == 'tglbukti') {
                        $dateValue = ($value != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($value))) : '';
                        $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $dateValue);
                        $sheet->getStyle($alphabets[$data_columns_index] . $detail_start_row)
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy');
                    } else {
                        $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $value);
                    }
                }
                $sheet->getStyle("A$detail_start_row:I$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                // Tingkatkan nomor baris
                $detail_start_row++;
                $no++;
            }
        }
        //ukuran kolom
        foreach ($header_columns as $data_columns_index => $data_column) {
            if ($data_column['index'] == 'namastok') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(33);
            } else if ($data_column['index'] == 'satuan') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(8);
            } else if ($data_column['index'] == 'keterangan') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(66);
            } else {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setAutoSize(true);
            }
        }

        // menambahkan sel Total pada baris terakhir + 1

        $total_start_row = $detail_start_row;
        $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
        $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':I' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

        $totalDebet = "=SUM(E7:E" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("E$total_start_row", $totalDebet)->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

        $totalDebet = "=SUM(H7:H" . ($detail_start_row - 1) . ")";
        $sheet->setCellValue("H$total_start_row", $totalDebet)->getStyle("H$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->setCellValue("H$total_start_row", $totalDebet)->getStyle("H$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

        $ttd_start_row = $detail_start_row + 2;
        $sheet->setCellValue("B$ttd_start_row", 'Disetujui Oleh,');
        $sheet->setCellValue("D$ttd_start_row", 'Diperiksa Oleh,');
        $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("B" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("D" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

        $writer = new Xlsx($spreadsheet);
        $filename = 'EXPORT LAPORAN PEMBELIAN BARANG' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLaporanPembelianBarangRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanPembelianBarangRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanPembelianBarangRequest  $request
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanPembelianBarangRequest $request, LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanPembelianBarang  $laporanPembelianBarang
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanPembelianBarang $laporanPembelianBarang)
    {
        //
    }
}
