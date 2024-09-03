<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetKartuStokRequest;
use App\Models\Gandengan;
use App\Models\Gudang;
use App\Models\KartuStok;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\Trado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KartuStokController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetKartuStokRequest $request)
    {
        $kartuStok = new KartuStok();

        return response([
            'data' => $kartuStok->get(),
            'attributes' => [
                'totalRows' => $kartuStok->totalRows,
                'totalPages' => $kartuStok->totalPages
            ]
        ]);
    }


    public function default()
    {
        $kartuStok = new KartuStok();
        return response([
            'status' => true,
            'data' => $kartuStok->default(),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $kartuStok = new KartuStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stokdari = ($stokdari_id != null) ? $stokdari_id->namastok : '';
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $stoksampai = ($stoksampai_id != null) ? $stoksampai_id->namastok : '';
        $filter = Parameter::find($request->filter);

        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang ?? 0;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan ?? 0;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan ?? 0;
            }
        }
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $user = Auth::user();
        $userCetak = $user->name;

        $report = [
            'stokdari' => $stokdari,
            'stoksampai' => $stoksampai,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text ?? "",
            'datafilter' => $datafilter ?? "",
            'judul' => $getJudul->text,
            'judulLaporan' => 'Laporan Kartu Stok',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        return response([
            'data' => $kartuStok->get(),
            'dataheader' => $report
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $kartuStok = new KartuStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stokdari = ($stokdari_id != null) ? $stokdari_id->namastok : '';
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $stoksampai = ($stoksampai_id != null) ? $stoksampai_id->namastok : '';
        $filter = Parameter::find($request->filter);

        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang ?? 0;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan ?? 0;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan ?? 0;
            }
        }

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $user = Auth::user();
        $userCetak = $user->name;

        $export = [
            'stokdari' => $stokdari,
            'stoksampai' => $stoksampai,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text ?? "",
            'judul' => $getJudul->text,
            'datafilter' => $datafilter ?? "",
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        // return response([
        //     'data' => $kartuStok->get(),
        //     'dataheader' => $export
        // ]);

        $kartu_Stok = $kartuStok->get();
        $dataHeader = $export;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $dataHeader['judul']);
        $sheet->getStyle("A1")->getFont()->setSize(11);
        $sheet->getStyle("A1")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A2', $dataHeader['namacabang']);
        $sheet->getStyle("A2")->getFont()->setSize(11);
        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A3', 'Laporan Kartu Stok');
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->setCellValue('A4', 'Periode : ' . $dataHeader['dari'] . ' s/d ' . $dataHeader['sampai']);
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->setCellValue('A5', 'Stok : ' . $dataHeader['stokdari'] . ' s/d ' . $dataHeader['stoksampai']);
        $sheet->getStyle("A5")->getFont()->setBold(true);
        $sheet->setCellValue('A6', $dataHeader['filter'] . ' : ' . $dataHeader['datafilter']);
        $sheet->getStyle("A6")->getFont()->setBold(true);

        $header_start_row = 4;
        $header_right_start_row = 4;
        $detail_table_header_row = 9;
        $detail_start_row = $detail_table_header_row + 1;
        $mergecell_start_row = 8;

        $alphabets = range('A', 'Z');

        $detail_columns = [
            [
                'label' => 'Kd Brg',
                'index' => 'kodebarang',
            ],
            [
                'label' => 'Nama Barang',
                'index' => 'namabarang',
            ],
            [
                'label' => 'Tanggal',
                'index' => 'tglbukti',
            ],
            [
                'label' => 'No Bukti',
                'index' => 'nobukti',
            ],
            [
                'label' => 'Kategori',
                'index' => 'kategori_id',
            ],
            [
                'label' => '@',
                'index' => 'satuan_masuk'
            ],
            [
                'label' => 'QTY',
                'index' => 'qtymasuk'
            ],
            [
                'label' => 'Nominal',
                'index' => 'nilaimasuk'
            ],
            [
                'label' => '@',
                'index' => 'satuan_keluar'
            ],
            [
                'label' => 'QTY',
                'index' => 'qtykeluar'
            ],
            [
                'label' => 'Nominal',
                'index' => 'nilaikeluar'
            ],
            [
                'label' => 'QTY',
                'index' => 'qtysaldo'
            ],
            [
                'label' => 'Nominal',
                'index' => 'nilaisaldo'
            ],
        ];

        foreach ($detail_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            if ($detail_column['label']=='@') {
                $sheet->getStyle($alphabets[$detail_columns_index] . $detail_table_header_row)->getAlignment()->setHorizontal('center');
            }
        }
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

        $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->applyFromArray($styleArray);

        // LOOPING DETAIL
        foreach ($kartu_Stok as $response_index => $response_detail) {

            foreach ($detail_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
            }

            $sheet->setCellValue("A$detail_start_row", $response_detail->kodebarang);
            $sheet->setCellValue("B$detail_start_row", $response_detail->namabarang);

            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';
            $sheet->setCellValue("C$detail_start_row", $dateValue);
            $sheet->getStyle("C$detail_start_row")
                ->getNumberFormat()
                ->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("D$detail_start_row", $response_detail->nobukti);
            $sheet->setCellValue("E$detail_start_row", $response_detail->kategori_id);
            $sheet->setCellValue("F$detail_start_row",  $response_detail['satuan_masuk'])->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("G$detail_start_row",  $response_detail['qtymasuk'])->getStyle("G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("H$detail_start_row",  $response_detail['nilaimasuk'])->getStyle("H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("I$detail_start_row",  $response_detail['satuan_keluar'])->getStyle("I$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("J$detail_start_row",  $response_detail['qtykeluar'])->getStyle("J$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("K$detail_start_row",  $response_detail['nilaikeluar'])->getStyle("K$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("L$detail_start_row",  $response_detail['qtysaldo'])->getStyle("L$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("M$detail_start_row",  $response_detail['nilaisaldo'])->getStyle("M$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getStyle("A$detail_start_row:L$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("F$detail_start_row:M$detail_start_row")->applyFromArray($style_number);
            $detail_start_row++;
        }

        $sheet->mergeCells('A' . $mergecell_start_row . ':E' . $mergecell_start_row);
        $sheet->mergeCells('F' . $mergecell_start_row . ':H' . $mergecell_start_row);
        $sheet->mergeCells('I' . $mergecell_start_row . ':K' . $mergecell_start_row);
        $sheet->mergeCells('L' . $mergecell_start_row . ':M' . $mergecell_start_row);
        $sheet->setCellValue("A$mergecell_start_row", '')->getStyle('A' . $mergecell_start_row . ':E' . $mergecell_start_row)->applyFromArray($styleArray);
        $sheet->setCellValue("F$mergecell_start_row", 'Masuk')->getStyle('F' . $mergecell_start_row . ':H' . $mergecell_start_row)->applyFromArray($styleArray)->getFont();
        $sheet->getStyle("F$mergecell_start_row")->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("I$mergecell_start_row", 'Keluar')->getStyle('I' . $mergecell_start_row . ':K' . $mergecell_start_row)->applyFromArray($styleArray)->getFont();
        $sheet->getStyle("I$mergecell_start_row")->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("L$mergecell_start_row", 'Saldo')->getStyle('L' . $mergecell_start_row . ':M' . $mergecell_start_row)->applyFromArray($styleArray)->getFont();
        $sheet->getStyle("L$mergecell_start_row")->getAlignment()->setHorizontal('center');

        $sheet->getColumnDimension('A')->setWidth(39);
        $sheet->getColumnDimension('B')->setWidth(39);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Kartu Stok  ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
