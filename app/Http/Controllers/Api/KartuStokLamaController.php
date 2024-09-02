<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\KartuStokLama;
use App\Http\Requests\StoreKartuStokLamaRequest;
use App\Http\Requests\GetKartuStokLamaRequest;
use App\Http\Requests\UpdateKartuStokLamaRequest;
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

class KartuStokLamaController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetKartuStokLamaRequest $request)
    {
        $kartuStok = new KartuStokLama();

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
        $kartuStok = new KartuStokLama();
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
        $kartuStok = new KartuStokLama();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stokdari = ($stokdari_id != null) ? $stokdari_id->namastok : '';
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $stoksampai = ($stoksampai_id != null) ? $stoksampai_id->namastok : '';
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            }
        }
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
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
        $kartuStok = new KartuStokLama();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            }
        }

        $export = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text ?? "",
            'datafilter' => $datafilter ?? "",
        ];

        $kartustok = $kartuStok->get();
        $dataHeader = $export;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $kartustok[0]->judul);
        $sheet->setCellValue('A2', $kartustok[0]->judulLaporan);
        $sheet->getStyle("A1")->getFont()->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle("A2")->getFont()->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle("A1")->getFont()->setBold(true);
        $sheet->getStyle("A2")->getFont()->setBold(true);

        $header_start_row = 3;
        $header_right_start_row = 3;
        $detail_table_header_row = 8;
        $detail_start_row = $detail_table_header_row + 1;
        $mergecell_start_row = 7;

        $alphabets = range('A', 'Z');
        $header_columns = [
            [
                'label' => 'Periode',
                'index' => 'dari',
            ],
            [
                'label' => 'stok',
                'index' => 'stokdari',
            ],
            [
                'label' => 'Gudang',
                'index' => 'datafilter',
            ],
        ];

        $header_right_columns = [

            [
                'label' => 's/d',
                'index' => 'sampai',
            ],
        ];

        $underheader_right_columns = [
            [
                'label' => 's/d',
                'index' => 'stoksampai',
            ],
        ];

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
                'label' => 'QTY',
                'index' => 'qtymasuk'
            ],
            [
                'label' => 'Nominal',
                'index' => 'nilaimasuk'
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
        foreach ($header_columns as $header_column) {
            $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
            $sheet->setCellValue('C' . $header_start_row++, ': ' . $dataHeader[$header_column['index']]);
        }

        foreach ($header_right_columns as $header_right_column) {
            $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
            $sheet->setCellValue('E' . $header_right_start_row++, $dataHeader['sampai']);
        }
        foreach ($underheader_right_columns as $header_right_column) {
            $sheet->setCellValue('D4', $header_right_column['label']);
            $sheet->setCellValue('E4', $dataHeader['stoksampai']);
        }


        foreach ($detail_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
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

        $sheet->getStyle("A$detail_table_header_row:K$detail_table_header_row")->applyFromArray($styleArray);

        // LOOPING DETAIL
        foreach ($kartustok as $response_index => $response_detail) {

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
            $sheet->setCellValue("F$detail_start_row",  $response_detail->qtymasuk)->getStyle("F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("G$detail_start_row",  $response_detail->nilaimasuk)->getStyle("G$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("H$detail_start_row",  $response_detail->qtykeluar)->getStyle("H$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("I$detail_start_row",  $response_detail->nilaikeluar)->getStyle("I$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("J$detail_start_row",  $response_detail->qtysaldo)->getStyle("J$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->setCellValue("K$detail_start_row",  $response_detail->nilaisaldo)->getStyle("K$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getStyle("A$detail_start_row:J$detail_start_row")->applyFromArray($styleArray);
            $sheet->getStyle("F$detail_start_row:K$detail_start_row")->applyFromArray($style_number);
            $detail_start_row++;
        }

        $sheet->mergeCells('A' . $mergecell_start_row . ':E' . $mergecell_start_row);
        $sheet->mergeCells('F' . $mergecell_start_row . ':G' . $mergecell_start_row);
        $sheet->mergeCells('H' . $mergecell_start_row . ':I' . $mergecell_start_row);
        $sheet->mergeCells('J' . $mergecell_start_row . ':K' . $mergecell_start_row);
        $sheet->setCellValue("A$mergecell_start_row", '')->getStyle('A' . $mergecell_start_row . ':E' . $mergecell_start_row)->applyFromArray($styleArray);
        $sheet->setCellValue("F$mergecell_start_row", 'Masuk')->getStyle('F' . $mergecell_start_row . ':G' . $mergecell_start_row)->applyFromArray($styleArray)->getFont();
        $sheet->getStyle("F$mergecell_start_row")->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("H$mergecell_start_row", 'Keluar')->getStyle('H' . $mergecell_start_row . ':I' . $mergecell_start_row)->applyFromArray($styleArray)->getFont();
        $sheet->getStyle("H$mergecell_start_row")->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("J$mergecell_start_row", 'Saldo')->getStyle('J' . $mergecell_start_row . ':K' . $mergecell_start_row)->applyFromArray($styleArray)->getFont();
        $sheet->getStyle("J$mergecell_start_row")->getAlignment()->setHorizontal('center');

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
        $sheet->getColumnDimension('K')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Kartu Stok Lama ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');

        // return response([
        //     'data' => $kartuStok->get(),
        //     'dataheader' => $export
        // ]);
    }
}
