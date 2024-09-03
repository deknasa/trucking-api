<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPemotonganPinjamanPerEBS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPemotonganPinjamanPerEBSController extends Controller
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
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $laporanpemotonganpinjamanperebs = new LaporanPemotonganPinjamanPerEBS();
        // $report = [
        //     [
        //         'nobukti' => 'EBS 0001/II/2023',
        //         'tanggal' => '21/2/2023',
        //         'nobk' => 'BK 2134 NMA',
        //         'supir' => 'HERMAN',
        //         'tgldari' => '22/2/2023',
        //         'tglsampai' => '22/2/2023',
        //         'pinjamansendiri' => '124124',
        //         'ketpinjamansendiri' => 'Charge supir Syaiful atas Ban Masak yg Rusak Jebol Samping pada B 9949 JH dgn no ban 1100 - 06316109 ketebalan 4mm sebesar Rp.420.666 + Rp.500.000 (Biaya Vul 2), total keseluruhan Rp. 920.666 Dibulatk (PJT 0002/XII/2022) Pinjaman Supir Syaiful B 9949 JH untuk Biaya Perdamaian, Kepolisian dan Ibu Sidabutar atas Laka di  Tebing Tinggi(PJT 0062/XI/2022)',
        //         'pinjamanbersama' => '124124',
        //         'ketpinjamanbersama' => 'Charge bersama semua supir atas Ban yang meledak dgn no ban 1100 - 04924112 pada Gandengan T- 07 Panjang sebesar Rp.740.000,- dibagi 17 supir(PJT 0017/III/2018)'
        //     ]
        // ];
        $laporan_pemotongan_pinjamanperebs = $laporanpemotonganpinjamanperebs->getReport($dari, $sampai,);

        foreach ($laporan_pemotongan_pinjamanperebs as $item) {
            $item->tgldari = date('d-m-Y', strtotime($item->tgldari));
            $item->tglsampai = date('d-m-Y', strtotime($item->tglsampai));

            $item->tglbukti = date('d-m-Y', strtotime(substr($item->tglbukti, 0, 10)));
            $item->tanggaldari = date('d-m-Y', strtotime($item->tanggaldari));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        return response([
            'data' => $laporan_pemotongan_pinjamanperebs,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
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

        $laporanpemotonganpinjamanperebs = new LaporanPemotonganPinjamanPerEBS();
        $laporan_pemotonganpinjamanperebs = $laporanpemotonganpinjamanperebs->getReport($dari, $sampai,);

        foreach ($laporan_pemotonganpinjamanperebs as $item) {
            $item->tgldari = date('d-m-Y', strtotime($item->tgldari));
            $item->tglsampai = date('d-m-Y', strtotime($item->tglsampai));

            $item->tglbukti = date('d-m-Y', strtotime(substr($item->tglbukti, 0, 10)));
            $item->tanggaldari = date('d-m-Y', strtotime($item->tanggaldari));
        }
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_pemotonganpinjamanperebs,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);

        $pengeluaran = json_decode($laporan_pemotonganpinjamanperebs);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $pengeluaran[0]->judul ?? '');
        $sheet->setCellValue('A2', $namacabang ?? '');
        $sheet->setCellValue('A3', $pengeluaran[0]->judulLaporan ?? '');
        $sheet->setCellValue('A4', 'PERIODE : ' . date('d-M-Y', strtotime($dari)) . ' s/d ' . date('d-M-Y', strtotime($sampai)));

        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:T1');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);

        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:T2');
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->getStyle("A4")->getFont()->setBold(true);

        $header_start_row = 5;
        $detail_start_row = 6;

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
                'label' => 'Gaji Supir No Bukti',
                'index' => 'gajisupir_nobukti',
            ],
            [
                'label' => 'Nama Supir',
                'index' => 'namasupir',
            ],
            [
                'label' => 'Total',
                'index' => 'total',
            ],
            [
                'label' => 'Uang Jalan',
                'index' => 'uangjalan',
            ],
            [
                'label' => 'Bbm',
                'index' => 'bbm',
            ],
            [
                'label' => 'Potongan Pinjaman',
                'index' => 'potonganpinjaman',
            ],
            [
                'label' => 'Deposito',
                'index' => 'deposito',
            ],
            [
                'label' => 'Potongan Pinjaman Semua',
                'index' => 'potonganpinjamansemua',
            ],
            [
                'label' => 'No Polisi',
                'index' => 'nopolisi',
            ],
            [
                'label' => 'Tgl Dari',
                'index' => 'tgldari',
            ],
            [
                'label' => 'Tgl Sampai',
                'index' => 'tglsampai',
            ],
            [
                'label' => 'Komisi Supir',
                'index' => 'komisisupir',
            ],
            [
                'label' => 'Tol Supir',
                'index' => 'tolsupir',
            ],
            [
                'label' => 'Voucher',
                'index' => 'voucher',
            ],
            [
                'label' => 'Tgl Dari',
                'index' => 'tanggaldari',
            ],
            [
                'label' => 'Tgl Sampai',
                'index' => 'tanggalsampai',
            ],
            [
                'label' => 'Keterangan Pinjaman Supir',
                'index' => 'keteranganpinjamansupir',
            ],
            [
                'label' => 'Keterangan Pinjaman Supir Semua',
                'index' => 'keteranganpinjamansupirsemua',
            ],
        ];

        foreach ($header_columns as $data_columns_index => $data_column) {
            $sheet->setCellValue($alphabets[$data_columns_index] . $header_start_row, $data_column['label'] ?? $data_columns_index + 1);
        }

        $lastColumn = $alphabets[$data_columns_index];
        $sheet->getStyle("A$header_start_row:$lastColumn$header_start_row")->applyFromArray($styleArray)->getFont()->setBold(true);
        $totalDebet = 0;
        $totalKredit = 0;
        $totalSaldo = 0;

        if (is_array($pengeluaran) || is_iterable($pengeluaran)) {
            foreach ($pengeluaran as $response_index => $response_detail) {

                foreach ($header_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                }
                $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
                $tgldari = ($response_detail->tgldari != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tgldari))) : '';
                $tglsampai = ($response_detail->tglsampai != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglsampai))) : '';
                $tanggaldari = ($response_detail->tanggaldari != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tanggaldari))) : '';
                $tanggalsampai = ($response_detail->tanggalsampai != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tanggalsampai))) : '';

                $sheet->setCellValue("A$detail_start_row", $response_detail->nobukti);
                $sheet->setCellValue("B$detail_start_row", $dateValue);
                $sheet->setCellValue("D$detail_start_row", $response_detail->gajisupir_nobukti);
                $sheet->setCellValue("D$detail_start_row", $response_detail->namasupir);
                $sheet->setCellValue("E$detail_start_row", $response_detail->total);
                $sheet->setCellValue("F$detail_start_row", $response_detail->uangjalan);
                $sheet->setCellValue("G$detail_start_row", $response_detail->bbm);
                $sheet->setCellValue("H$detail_start_row", $response_detail->potonganpinjaman);
                $sheet->setCellValue("I$detail_start_row", $response_detail->deposito);
                $sheet->setCellValue("J$detail_start_row", $response_detail->potonganpinjamansemua);
                $sheet->setCellValue("K$detail_start_row", $response_detail->nopolisi);
                $sheet->setCellValue("L$detail_start_row", $tgldari);
                $sheet->setCellValue("M$detail_start_row", $tglsampai);
                $sheet->setCellValue("N$detail_start_row", $response_detail->komisisupir);
                $sheet->setCellValue("O$detail_start_row", $response_detail->tolsupir);
                $sheet->setCellValue("P$detail_start_row", $response_detail->voucher);
                $sheet->setCellValue("Q$detail_start_row", $tanggaldari);
                $sheet->setCellValue("R$detail_start_row", $tanggalsampai);
                $sheet->setCellValue("S$detail_start_row", $response_detail->keteranganpinjamansupir);
                $sheet->setCellValue("T$detail_start_row", $response_detail->keteranganpinjamansupirsemua);

                $sheet->getStyle("A$detail_start_row:T$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("C$detail_start_row:T$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $sheet->getStyle("L$detail_start_row:M$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $sheet->getStyle("Q$detail_start_row:R$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');

                $detail_start_row++;
            }
        }

        //ukuran kolom
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(30);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(20);
        $sheet->getColumnDimension('Q')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(20);
        $sheet->getColumnDimension('S')->setWidth(63);
        $sheet->getColumnDimension('T')->setWidth(63);

        //FORMAT
        // set format ribuan untuk kolom D dan E
        $sheet->getStyle("D" . ($detail_start_row + 1) . ":E" . ($detail_start_row + 1))->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        $sheet->getStyle("A" . ($detail_start_row + 1) . ":$lastColumn" . ($detail_start_row + 1))->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN PEMOTONGAN PINJAMAN PER-EBS ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
