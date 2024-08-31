<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LaporanStok;
use App\Http\Requests\StoreLaporanStokRequest;
use App\Http\Requests\UpdateLaporanStokRequest;
use App\Http\Requests\ValidasiLaporanStokRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanStokController extends Controller
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
    public function report(ValidasiLaporanStokRequest $request)
    {
        $bulan = substr($request->sampai, 0, 2);
        $tahun = substr($request->sampai, 3, 4);
        $jenislaporan = $request->jenislaporan ?? 0;
        $laporanstok = new LaporanStok();

        $laporan_stok = $laporanstok->getReport($bulan, $tahun,$jenislaporan);

        if (count($laporan_stok) == 0) {
            return response([
                'data' => $laporan_stok,
                'message' => 'tidak ada data'
            ], 500);
        } else {
            return response([
                'data' => $laporan_stok,
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
        $bulan = substr($request->sampai,0,2);
        $tahun = substr($request->sampai,-4);
        $jenislaporan = $request->jenislaporan ?? 0;

        $laporanstok = new Laporanstok();


        $laporan_stok = $laporanstok->getReport($bulan, $tahun,$jenislaporan);
        // foreach($laporan_stok as $item){
        //     $item->tglbukti = date('d-m-Y', strtotime($item->tglbukti));
        //     $item->tgljatuhtempo = date('d-m-Y', strtotime($item->tgljatuhtempo));
        // }

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_stok,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     // 'data' => $report
        // ]);

        $pengeluaran = json_decode($laporan_stok);
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
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A2', $namacabang);
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:K2');

        $sheet->setCellValue('A3', strtoupper('Laporan Pemakaian Stok'));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:K3');

        $sheet->setCellValue('A4', strtoupper('Bulan ' . date('M-Y', strtotime($pengeluaran[0]->tglbukti))));
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->mergeCells('A4:K4');
        $sheet->getColumnDimension('I')->setWidth(60);

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
                "index" => 'namabarang',
            ],
            [
                "label" => "Qty Masuk",
                "index" => 'qtymasuk',
            ],
            [
                "label" => "Nominal Masuk",
                "index" => 'nominalmasuk',
            ],
            [
                "label" => "Qty Keluar",
                "index" => 'qtykeluar',
            ],
            [
                "label" => "Nominal Keluar",
                "index" => 'nominalkeluar',
            ],
            [
                "label" => "Keterangan",
                "index" => 'keterangan',
            ],
            [
                "label" => "Qty Saldo",
                "index" => 'qtysaldo',
            ],
            [
                "label" => "Nominal Saldo",
                "index" => 'nominalsaldo',
            ],

        ];

        foreach ($header_columns as $data_columns_index => $data_column) {
            $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
        }

        $lastColumn = $alphabets[$data_columns_index];
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->getFont()->setBold(true);

        $no = 1;
        if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
            foreach ($pengeluaran as $response_detail) {
                if ($no != 1) {
                    if ($response_detail->baris == 1) {
                        $detail_start_row++;
                    }
                }
                foreach ($header_columns as $data_columns_index => $data_column) {
                    if ($data_column['index'] == 'no') {
                        $value = $no;
                    } else {
                        $value = $response_detail->{$data_column['index']};
                    }

                    if ($data_column['index'] == 'tglbukti') {
                        $value = date('d-m-Y', strtotime($value));
                    }
                    if ($data_column['index'] == 'nominalsaldo') {
                        if ($response_detail->baris != 1) {
                            $value = '=(F' . ($detail_start_row) . '-H' . $detail_start_row . ')';
                            // $value = $response_detail[$data_column['index']];
                        }
                    }
                    if ($data_column['index'] == 'qtysaldo') {
                        if ($response_detail->baris != 1) {
                            $value = '=(J' . ($detail_start_row - 1) . '+E' . $detail_start_row . ')-G' . $detail_start_row;
                        }
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
                // Tingkatkan nomor baris
                $detail_start_row++;
                $no++;
            }
        }
        //ukuran kolom
        foreach ($header_columns as $data_columns_index => $data_column) {
            if ($data_column['index'] == 'namabarang') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(50);
            } else if ($data_column['index'] == 'keterangan') {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setWidth(72);
            } else {
                $sheet->getColumnDimension($alphabets[$data_columns_index])->setAutoSize(true);
            }
        }

        $detail_start_row++;
        // menambahkan sel Total pada baris terakhir + 1
        $sheet->setCellValue("B" . ($detail_start_row), 'TOTAL');
        $sheet->setCellValue("F" . ($detail_start_row), "=SUM(F" . ($header_start_row + 1) . ":F" . $detail_start_row . ")");
        $sheet->setCellValue("H" . ($detail_start_row), "=SUM(H" . ($header_start_row + 1) . ":H" . $detail_start_row . ")");
        $sheet->setCellValue("K" . ($detail_start_row), "=SUM(K" . ($header_start_row + 1) . ":K" . $detail_start_row . ")");

        //FORMAT
        $numberColumn = [
            "qtymasuk",
            "nominalmasuk",
            "qtykeluar",
            "nominalkeluar",
            "qtysaldo",
            "nominalsaldo"
        ];
        foreach ($header_columns as $data_columns_index => $data_column) {
            if (in_array($data_column['index'], $numberColumn)) {
                $sheet->getStyle($alphabets[$data_columns_index] . ($header_start_row + 1) . ":" . $alphabets[$data_columns_index] . ($detail_start_row + 1))->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            }
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(38);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setWidth(60);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN PEMAKAIAN BARANG ' . date('dmYHis');
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
     * @param  \App\Http\Requests\StoreLaporanStokRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLaporanStokRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function show(LaporanStok $laporanStok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function edit(LaporanStok $laporanStok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLaporanStokRequest  $request
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLaporanStokRequest $request, LaporanStok $laporanStok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LaporanStok  $laporanStok
     * @return \Illuminate\Http\Response
     */
    public function destroy(LaporanStok $laporanStok)
    {
        //
    }
}
