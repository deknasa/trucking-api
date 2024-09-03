<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemakaianBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPemakaianBanController extends Controller
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
        $dari = $request->dari;
        $sampai = $request->sampai;

        // $jenisLaporan = $request->jenislaporan;
        $jenisLaporan = 'ANALISA BAN';
        $posisiAkhir = '';

        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $laporanpemakaianban = new LaporanPemakaianBan();
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();

            return response([
                'data' => $laporanpemakaianban->getReport($dari, $sampai, $posisiAkhir, $jenisLaporan),
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;
        $jenisLaporan = 'ANALISA BAN';
        $posisiAkhir = '';

        $laporanpemakaianban = new LaporanPemakaianBan();
        $laporan_pemakaian = $laporanpemakaianban->getReport($dari, $sampai, $posisiAkhir, $jenisLaporan);

        // dd($laporan_pemakaian);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_pemakaian,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);
        $posisiakhirtrado = $request->posisiakhirtrado;
        $posisiakhirgandengan = $request->posisiakhirgandengan;

        // dd($posisiakhirtrado, $posisiakhirgandengan);
        if ($posisiakhirtrado != null) {
            $parameter = $posisiakhirtrado;
        } else {
            $parameter = $posisiakhirgandengan;
        }

        $data = json_decode($laporan_pemakaian);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $posisiakhir = $parameter;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $data[0]->judul);
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A2', $namacabang);
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:E2');

        $englishMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $indonesianMonths = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $tgldari = str_replace($englishMonths, $indonesianMonths, date('d - M - Y', strtotime($request->tgldari)));
        $tglsampai = str_replace($englishMonths, $indonesianMonths, date('d - M - Y', strtotime($request->tglsampai)));

        $sheet->setCellValue('A3', strtoupper($data[0]->judulLaporan));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:E3');

        $sheet->setCellValue('A4', strtoupper('Periode: ' . date('d - M - Y', strtotime($request->dari)) . ' s/d ' . date('d - M - Y', strtotime($request->sampai))));
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->mergeCells('A4:E4');

        $sheet->setCellValue('A5', strtoupper('Posisi akhir ban : ' . $request->parameter));
        $sheet->getStyle("A5")->getFont()->setBold(true);
        $sheet->mergeCells('A5:E5');

        $sheet->setCellValue('A6', strtoupper('Jenis Laporan: ' . $request->jenislaporan));
        $sheet->getStyle("A6")->getFont()->setBold(true);
        $sheet->mergeCells('A6:E6');


        $detail_table_header_row = 8;
        $detail_start_row = $detail_table_header_row + 1;

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
        $alphabets = range('A', 'Z');
        $header_columns = [
            [
                'label' => 'No Ban',
                'index' => 'nobanA',
            ],
            [
                'label' => 'No Bukti',
                'index' => 'nobukti',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tanggal',
            ],
            [
                'label' => 'Gudang',
                'index' => 'gudang',
            ],
            [
                'label' => 'Kondisi Akhir',
                'index' => 'kondisiakhir',
            ],
            [
                'label' => 'No Pg',
                'index' => 'nopg',
            ],
            [
                'label' => 'No Ban',
                'index' => 'nobanB',
            ],
            [
                'label' => 'Alasan Pengembalian',
                'index' => 'alasanpenggantian',
            ],
            [
                'label' => 'Vul Ke',
                'index' => 'vulke',
            ],
            [
                'label' => 'No Klaim',
                'index' => 'noklaim',
            ],
            [
                'label' => 'No Pjt',
                'index' => 'nopjt',
            ],
            [
                'label' => 'Ket. Afkir',
                'index' => 'ketafkir',
            ],

        ];

        foreach ($header_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
        }
        $sheet->getStyle("A$detail_table_header_row:L$detail_table_header_row")->applyFromArray($styleArray)->getFont()->setBold(true);

        // LOOPING DETAIL
        $totalDebet = 0;
        $totalKredit = 0;
        $totalSaldo = 0;
        $dataRow = $detail_table_header_row + 1;
        foreach ($data as $response_index => $response_detail) {

            foreach ($header_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
            }
            $tanggal = ($response_detail->tanggal != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tanggal))) : '';


            $sheet->setCellValue("A$detail_start_row", $response_detail->nobanA);
            $sheet->setCellValue("B$detail_start_row", $response_detail->nobukti);
            $sheet->setCellValue("C$detail_start_row", date('d-m-Y', strtotime($response_detail->tanggal)));
            $dateValue = ($response_detail->tanggal != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tanggal))) : '';
            $sheet->setCellValue("C$detail_start_row", $dateValue);
            $sheet->getStyle("C$detail_start_row")
                ->getNumberFormat()
                ->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("D$detail_start_row", $response_detail->gudang);
            $sheet->setCellValue("E$detail_start_row", $response_detail->kondisiakhir);
            $sheet->setCellValue("F$detail_start_row", $response_detail->nopg);
            $sheet->setCellValue("G$detail_start_row", $response_detail->nobanB);
            $sheet->setCellValue("H$detail_start_row", $response_detail->alasanpenggantian);
            $sheet->setCellValue("I$detail_start_row", $response_detail->vulke);
            $sheet->setCellValue("J$detail_start_row", $response_detail->noklaim);
            $sheet->setCellValue("K$detail_start_row", $response_detail->nopjt);
            $sheet->setCellValue("L$detail_start_row", $response_detail->ketafkir);
            $sheet->getStyle("A$detail_start_row:L$detail_start_row")->applyFromArray($styleArray);
            $detail_start_row++;
        }
        $ttd_start_row = $detail_start_row + 2;
        $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
        $sheet->setCellValue("C$ttd_start_row", 'Diperiksa Oleh,');
        $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

        $sheet->setCellValue("A" . ($ttd_start_row + 3), '( ' . $disetujui . ' )');
        $sheet->setCellValue("C" . ($ttd_start_row + 3), '( ' . $diperiksa . ' )');
        $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setWidth(26);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN PEMAKAIAN BAN ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
