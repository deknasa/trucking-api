<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanUangJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\RangeExportReportRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class LaporanUangJalanController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {

        $laporanuangjalan = new LaporanUangJalan();
        return response([
            'data' => $laporanuangjalan->get(),
            'attributes' => [
                'totalRows' => $laporanuangjalan->totalRows,
                'totalPages' => $laporanuangjalan->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        // $report = LaporanUangJalan::getReport($sampai, $jenis);
        // $laporan_uang_jalan = [
        //     [
        //         'namasupir' => "CHANDRA ARIANTO",
        //         "tglabsensi" => "2023/01/30",
        //         "nominalambil" => "1000000",
        //         "tglric" => "2023/02/03",
        //         "nobuktiric" => "RIC 0019/II/2023",
        //         "nominalkembali" => "200000",
        //         "judulLaporan" => 'LAPORAN UANG JALAN',
        //         "judul" => 'PT TRANSPORINDO AGUNG SEJAHTERA',
        //         "usercetak" => 'User : '. auth('api')->user()->name,
        //         'tglcetak' => 'Tgl Cetak: '. date('d-m-Y H:i:s')
        //     ],
        //     [
        //         'namasupir' => "CHANDRA ARIANTO",
        //         "tglabsensi" => "2023/01/30",
        //         "nominalambil" => "1000000",
        //         "tglric" => "2023/02/03",
        //         "nobuktiric" => "RIC 0019/II/2023",
        //         "nominalkembali" => "200000",
        //         "judulLaporan" => 'LAPORAN UANG JALAN',
        //         "judul" => 'PT TRANSPORINDO AGUNG SEJAHTERA',
        //         "usercetak" => 'User : '. auth('api')->user()->name,
        //         'tglcetak' => 'Tgl Cetak: '. date('d-m-Y H:i:s')
        //     ],
        // ];

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $tgldari = date('Y-m-d', strtotime($request->ricdari));
        $tglsampai = date('Y-m-d', strtotime($request->ricsampai));
        $tglambil_jalandari = date('Y-m-d', strtotime($request->ambildari));
        $tglambil_jalansampai = date('Y-m-d', strtotime($request->ambilsampai));
        $supirdari = $request->supirdari;
        $supirsampai = $request->supirsampai;
        $status = $request->status;

        $laporanuangjalan = new LaporanUangJalan();

        $laporan_uang_jalan = $laporanuangjalan->getReport($tgldari, $tglsampai, $tglambil_jalandari, $tglambil_jalansampai, $supirdari, $supirsampai, $status);

        foreach ($laporan_uang_jalan as $item) {
            $item->tglabsensi = date('d-m-Y', strtotime($item->tglabsensi));
            $item->tglkembali = date('d-m-Y', strtotime($item->tglkembali));
        }

        return response([
            'data' => $laporan_uang_jalan,
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $tgldari = date('Y-m-d', strtotime($request->ricdari1));
        $tglsampai = date('Y-m-d', strtotime($request->ricsampai));
        $tglambil_jalandari = date('Y-m-d', strtotime($request->ambildari));
        $tglambil_jalansampai = date('Y-m-d', strtotime($request->ambilsampai));
        $ricdari = $request->ricdari;
        $ricsampai = $request->ricsampai;
        $supirdari = $request->supirdari;
        $supirsampai = $request->supirsampai;
        $status = $request->status;

        $laporanuangjalan = new LaporanUangJalan();
        $laporan_uang_jalan = $laporanuangjalan->getExport($tgldari, $tglsampai, $tglambil_jalandari, $tglambil_jalansampai, $supirdari, $supirsampai, $status);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporan_uang_jalan,
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        //     //   'data' => $export
        // ]);

        $pengeluaran = json_decode($laporan_uang_jalan);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $pengeluaran[0]->judul ?? '');
        $sheet->setCellValue('A2', $namacabang ?? '');
        $sheet->setCellValue('A3',  $pengeluaran[0]->judulLaporan ?? '');
        $sheet->setCellValue('A4', 'TGL RIC : ' . date('d-M-Y', strtotime($ricdari)) . ' s/d ' . date('d-M-Y', strtotime($ricsampai)));
        $sheet->setCellValue('A5', 'TGL AMBIL UANG JALAN : ' . date('d-M-Y', strtotime($tglambil_jalandari)) . ' s/d ' . date('d-M-Y', strtotime($tglambil_jalansampai)));

        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->getStyle("A5")->getFont()->setBold(true);

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
                'label' => 'Nama Supir',
                'index' => 'namasupir',
            ],
            [
                'label' => 'Tgl Absensi',
                'index' => 'tglabsensi',
            ],
            [
                'label' => 'Nominal Ambil',
                'index' => 'nominalambil',
            ],
            [
                'label' => 'Tgl Ric',
                'index' => 'tglkembali',
            ],
            [
                'label' => 'No Bukti Ric',
                'index' => 'nobuktiric',
            ],
            [
                'label' => 'Nominal Kembali',
                'index' => 'nominalkembali',
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
                $tglabsensi = ($response_detail->tglabsensi != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglabsensi))) : '';
                $tglkembali = ($response_detail->tglkembali != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglkembali))) : '';

                $sheet->setCellValue("A$detail_start_row", $response_detail->namasupir);
                $sheet->setCellValue("B$detail_start_row", $tglabsensi);
                $sheet->setCellValue("C$detail_start_row", $response_detail->nominalambil);
                $sheet->setCellValue("D$detail_start_row", $tglkembali);
                $sheet->setCellValue("E$detail_start_row", $response_detail->nobuktiric);
                $sheet->setCellValue("F$detail_start_row", $response_detail->nominalkembali);

                $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("B$detail_start_row:B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $sheet->getStyle("D$detail_start_row:D$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $detail_start_row++;
            }
        }

        //ukuran kolom
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(21);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);

        //FORMAT
        // set format ribuan untuk kolom D dan E
        $sheet->getStyle("D" . ($detail_start_row + 1) . ":E" . ($detail_start_row + 1))->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        $sheet->getStyle("A" . ($detail_start_row + 1) . ":$lastColumn" . ($detail_start_row + 1))->getFont()->setBold(true);

        //persetujuan
        $sheet->mergeCells('A' . ($detail_start_row + 3) . ':B' . ($detail_start_row + 3));
        $sheet->setCellValue('A' . ($detail_start_row + 3), 'Disetujui Oleh,');
        $sheet->mergeCells('C' . ($detail_start_row + 3) . ($detail_start_row + 3));
        $sheet->setCellValue('C' . ($detail_start_row + 3), 'Diperiksa Oleh');
        $sheet->mergeCells('D' . ($detail_start_row + 3) . ':E' . ($detail_start_row + 3));
        $sheet->setCellValue('D' . ($detail_start_row + 3), 'Disusun Oleh,');

        $sheet->mergeCells('A' . ($detail_start_row + 6) . ':B' . ($detail_start_row + 6));
        $sheet->setCellValue('A' . ($detail_start_row + 6), '( ' . $disetujui . ' )');
        $sheet->mergeCells('C' . ($detail_start_row + 6) . ($detail_start_row + 6));
        $sheet->setCellValue('C' . ($detail_start_row + 6), '( ' . $diperiksa . ' )');
        $sheet->mergeCells('D' . ($detail_start_row + 6) . ':E' . ($detail_start_row + 6));
        $sheet->setCellValue('D' . ($detail_start_row + 6), '(                                          )');

        // style persetujuan
        $sheet->getStyle('A' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($detail_start_row + 3))->getFont()->setSize(12);
        $sheet->getStyle('C' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . ($detail_start_row + 3))->getFont()->setSize(12);
        $sheet->getStyle('D' . ($detail_start_row + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . ($detail_start_row + 3))->getFont()->setSize(12);

        $sheet->getStyle('A' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($detail_start_row + 6))->getFont()->setSize(12);
        $sheet->getStyle('C' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . ($detail_start_row + 6))->getFont()->setSize(12);
        $sheet->getStyle('D' . ($detail_start_row + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . ($detail_start_row + 6))->getFont()->setSize(12);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN UANG JALAN ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
