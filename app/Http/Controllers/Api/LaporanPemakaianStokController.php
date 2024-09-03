<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LaporanPemakaianStok;
use App\Http\Requests\ValidasiLaporanPemakaianStokRequest;
use App\Http\Requests\StoreLaporanPemakaianStokRequest;
use App\Http\Requests\UpdateLaporanPemakaianStokRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPemakaianStokController extends Controller
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
    public function report(ValidasiLaporanPemakaianStokRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);

        $laporanpemakaianstok = new LaporanPemakaianStok();

        $laporan_pemakaianstok = $laporanpemakaianstok->getReport($bulan, $tahun);

        if (count($laporan_pemakaianstok) == 0) {
            return response([
                'data' => $laporan_pemakaianstok,
                'message' => 'tidak ada data'
            ], 500);
        } else {
            return response([
                'data' => $laporan_pemakaianstok,
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

        $laporanpemakaianstok = new LaporanPemakaianStok();
        $laporan_pemakaianstok = $laporanpemakaianstok->getReport($bulan, $tahun);
        // foreach($laporan_pemakaianstok as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_pemakaianstok,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     // 'data' => $report
        // ]);

        $pengeluaran = json_decode($laporan_pemakaianstok);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', $pengeluaran[0]->judul);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A2', $namacabang);
        $sheet->getStyle("A2")->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:J2');
        $englishMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $indonesianMonths = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $sheet->setCellValue('A3', strtoupper('Laporan Pemakaian Stok'));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:J3');

        $sheet->setCellValue('A4', strtoupper('Bulan ' . date('M-Y', strtotime($pengeluaran[0]->tglbukti))));
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->mergeCells('A4:J4');

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
                "label" => "No Pol/Gandengan",
                "index" => 'kodetrado',
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
                "label" => "Saldo",
                "index" => 'saldo',
            ],
            [
                "label" => "Keterangan",
                "index" => 'keterangan',
            ],
        ];

        foreach ($header_columns as $data_columns_index => $data_column) {
            $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
        }

        $lastColumn = $alphabets[$data_columns_index];
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->getFont()->setBold(true);

        $rowAwal = 7;
        $no = 1;
        if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
            // Menulis data dan melakukan grup berdasarkan kolom "KeteranganMain"

            $previous_kodetrado = '';
            // dd($pengeluaran);
            foreach ($pengeluaran as $response_detail) {
                $kodetrado = $response_detail->kodetrado;

                if ($previous_kodetrado !== $kodetrado) {
                    $detail_start_row++;
                }
                foreach ($header_columns as $data_columns_index => $data_column) {
                    if ($data_column['index'] == 'no') {
                        $value = $no;
                    } else if ($data_column['index'] == 'saldo') {
                        if ($previous_kodetrado != '') {
                            if ($previous_kodetrado !== $kodetrado) {
                                $value = "=SUM(I$rowAwal:I" . ($detail_start_row - 2) . ")";
                                $rowAwal = $detail_start_row;
                            } else {
                                $value = '';
                            }
                        }
                    } else {
                        $value = $response_detail->{$data_column['index']};
                    }

                    if ($data_column['index'] == 'tglbukti') {
                        $value = date('d-m-Y', strtotime($value));
                    }

                    if ($data_column['index'] == 'saldo') {
                        if ($previous_kodetrado != '') {
                            $sheet->setCellValue($alphabets[$data_columns_index] . ($detail_start_row - 2), $value);
                        }
                    } else if ($data_column['index'] == 'tglbukti') {
                        $dateValue = ($value != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($value))) : '';
                        $sheet->setCellValue($alphabets[$data_columns_index] . ($detail_start_row), $dateValue);
                        $sheet->getStyle($alphabets[$data_columns_index] . ($detail_start_row))
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy');
                        // $sheet->setCellValue($alphabets[$data_columns_index] . ($detail_start_row - 2), $dateValue);

                    } else {
                        $sheet->setCellValue($alphabets[$data_columns_index] . $detail_start_row, $value);
                    }
                }

                // Tingkatkan nomor baris
                $detail_start_row++;
                $no++;
                $previous_kodetrado = $kodetrado;
            }

            $value = "=SUM(I$rowAwal:I" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue($alphabets[9] . ($detail_start_row - 1), $value);
        }
        //ukuran kolom
        foreach ($header_columns as $data_columns_index => $data_column) {

            if ($data_column['index'] == 'kodetrado') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(12);
            } else if ($data_column['index'] == 'namastok') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(33);
            } else if ($data_column['index'] == 'qty') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(7);
            } else if ($data_column['index'] == 'keterangan') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(63);
            } else {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setAutoSize(true);
            }
        }
        $detail_start_row++;
        // menambahkan sel Total pada baris terakhir + 1
        $sheet->setCellValue("B" . ($detail_start_row), 'TOTAL');
        $sheet->setCellValue("F" . ($detail_start_row), "=SUM(F" . ($header_start_row + 1) . ":F" . ($detail_start_row - 2) . ")");
        $sheet->setCellValue("I" . ($detail_start_row), "=SUM(I" . ($header_start_row + 1) . ":I" . ($detail_start_row - 2) . ")");
        $sheet->setCellValue("J" . ($detail_start_row), "=SUM(J" . ($header_start_row + 1) . ":J" . ($detail_start_row - 2) . ")");

        //FORMAT
        $numberColumn = [
            "qty",
            "satuan",
            "harga",
            "nominal",
            "saldo"
        ];
        foreach ($header_columns as $data_columns_index => $data_column) {
            if (in_array($data_column['index'], $numberColumn)) {
                $sheet->getStyle($alphabets[$data_columns_index] . ($header_start_row + 2) . ":" . $alphabets[$data_columns_index] . ($detail_start_row + 2))->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            }
        }

        $ttd_start_row = $detail_start_row + 2;
        $sheet->setCellValue("B$ttd_start_row", 'Disetujui Oleh,');
        $sheet->setCellValue("D$ttd_start_row", 'Diperiksa Oleh,');
        $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("B" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("D" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

        $writer = new Xlsx($spreadsheet);
        $filename = 'EXPORT LAPORAN PEMAKAIAN BARANG' . date('dmYHis');
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
     * @param  \App\Http\Requests\StoreLaporanPemakaianStokRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanPemakaianStokRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanPemakaianStokRequest  $request
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanPemakaianStokRequest $request, LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanPemakaianStok  $laporanPemakaianStok
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanPemakaianStok $laporanPemakaianStok)
    {
        //
    }
}
