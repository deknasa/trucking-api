<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetStokPersediaanRequest;
use App\Models\StokPersediaan;
use App\Http\Requests\StoreStokPersediaanRequest;
use App\Http\Requests\UpdateStokPersediaanRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\Gandengan;
use App\Models\Gudang;
use App\Models\Trado;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StokPersediaanController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetStokPersediaanRequest $request)
    {
        $stokPersediaan = new StokPersediaan();

        // dd($request->all());
        $filter = $request->filter ?? 0;
        $gudang = $request->gudang ?? '';
        $gudang_id = $request->gudang_id ?? 0;
        $trado = $request->trado ?? '';
        $trado_id = $request->trado_id ?? 0;
        $gandengan = $request->gandengan ?? '';
        $gandengan_id = $request->gandengan_id ?? 0;
        $keterangan = $request->keterangan ?? -1;
        $data = $request->data ?? 0;


        return response([
            'data' => $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data),
            'attributes' => [
                'totalRows' => $stokPersediaan->totalRows,
                'totalPages' => $stokPersediaan->totalPages
            ]
        ]);
    }

    public function default()
    {
        $persediaan = new StokPersediaan();
        return response([
            'status' => true,
            'data' => $persediaan->default(),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $stokPersediaan = new StokPersediaan();


        $gudang = "";
        $trado = "";
        $gandengan = "";
        $data = $request->data ?? 0;
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
                $gudang = $datafilter;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $trado = $datafilter;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $gandengan = $datafilter;
            }
        }

        $user = Auth::user();
        $userCetak = $user->name;
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $report = [
            'filter' => $filter->text ?? "",
            'datafilter' => $datafilter ?? "",
            'judul' => $getJudul->text,
            'judulLaporan' => 'Laporan Stok Persediaan',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        $filter = $request->filter ?? 0;
        $gudang = $request->gudang ?? $gudang;
        $gudang_id = $request->gudang_id ?? $getdatafilter->id;
        $trado = $request->trado ?? $trado;
        $trado_id = $request->trado_id ?? $getdatafilter->id;
        $gandengan = $request->gandengan ?? $gandengan;
        $gandengan_id = $request->gandengan_id ?? $getdatafilter->id;
        $keterangan = $request->keterangan ?? -1;
        $data = $request->data ?? $request->datafilter;

        // return response([
        //     'data' => $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data, $request->forReport,true),
        //     'dataheader' => $report
        // ]);

        $stok_Persediaan = $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data, $request->forReport, true);
        $dataHeader = $report;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', $dataHeader['judul']);
        $sheet->getStyle("A1")->getFont()->setSize(11);
        $sheet->getStyle("A1")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A2', $dataHeader['namacabang']);
        $sheet->getStyle("A2")->getFont()->setSize(11);
        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:B2');

        $sheet->setCellValue('A3', $dataHeader['judulLaporan']);
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:B3');

        $sheet->setCellValue('A4', $dataHeader['filter']);
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->setCellValue('B4', ': ' . $dataHeader['datafilter']);
        $sheet->getStyle('B4')->getAlignment()->setHorizontal('left');
        $sheet->getStyle("B4")->getFont()->setBold(true);

        $header_start_row = 2;
        $header_right_start_row = 2;
        $detail_table_header_row = 6;
        $detail_start_row = $detail_table_header_row + 1;

        $alphabets = range('A', 'Z');
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

        $header_columns = [
            [
                'label' => 'Stok',
                'index' => 'stok_id',
            ],
            [
                'label' => 'QTY',
                'index' => 'qty',
            ],


        ];
        
        foreach ($header_columns as $detail_columns_index => $detail_column) {
            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            $sheet->getStyle($alphabets[$detail_columns_index] . $detail_table_header_row)->getFont()->setBold(true);
        }

        foreach ($stok_Persediaan as $response_index => $response_detail) {
            foreach ($header_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, $response_detail->{$detail_column['index']});
            }
            $detail_start_row++;
        }

        $endRow = $detail_start_row - 1;
        $sheet->getStyle("A$detail_table_header_row:B$endRow")->applyFromArray($styleArray);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $writer = new Xlsx($spreadsheet);
        $filename = $dataHeader['judulLaporan'] . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }


    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $stokPersediaan = new StokPersediaan();

        $gudang = "";
        $trado = "";
        $gandengan = "";
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
                $gudang = $datafilter;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $trado = $datafilter;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $gandengan = $datafilter;
            }
        }
        $filterText = $filter->text ?? '';

        $user = Auth::user();
        $userCetak = $user->name;
        // dd($request->all());
        $filter = $request->filter ?? 0;
        $gudang = $request->gudang ?? $gudang;
        $gudang_id = $request->gudang_id ?? $getdatafilter->id;
        $trado = $request->trado ?? $trado;
        $trado_id = $request->trado_id ?? $getdatafilter->id;
        $gandengan = $request->gandengan ?? $gandengan;
        $gandengan_id = $request->gandengan_id ?? $getdatafilter->id;
        $keterangan = $request->keterangan ?? -1;
        $data = $request->data ?? $request->datafilter;
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $report = [
            'filter' => $filterText,
            'datafilter' => $datafilter ?? "",
            'judul' => $getJudul->text,
            'judulLaporan' => 'Laporan Stok Persediaan',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        return response([
            'data' => $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data, true),
            'dataheader' => $report
        ]);
    }
}
