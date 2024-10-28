<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanBukuBesarRequest;
use App\Models\AkunPusat;
use App\Models\Cabang;
use App\Models\LaporanBukuBesar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanBukuBesarController extends Controller
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
    public function report(ValidasiLaporanBukuBesarRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $laporanbukubesar = new LaporanBukuBesar();


            $coadari_id = AkunPusat::find($request->coadari_id);
            $coasampai_id = AkunPusat::find($request->coasampai_id);
            // $cabang_id = auth('api')->user()->cabang_id;
            $cabang = Cabang::find($request->cabang_id);
            $dataHeader = [
                'coadari' => $coadari_id->coa,
                'coasampai' => $coasampai_id->coa,
                'ketcoadari' => $coadari_id->keterangancoa,
                'ketcoasampai' => $coasampai_id->keterangancoa,
                'dari' => $request->dari,
                'sampai' => $request->sampai,
                'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
            ];

            return response([
                'data' => $laporanbukubesar->getReport(),
                'dataheader' => $dataHeader
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanBukuBesarRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {

            $laporanbukubesar = new LaporanBukuBesar();
            $laporan_bukubesar = $laporanbukubesar->getReport();

            $coadari_id = ($request->coadari_id != '') ? AkunPusat::find($request->coadari_id) : '';
            $coasampai_id = ($request->coasampai_id != '') ? AkunPusat::find($request->coasampai_id) : '';
            $cabang = Cabang::find($request->cabang_id);

            $coadari = ($coadari_id != '') ? $coadari_id->coa : '';
            $coasampai = ($coasampai_id != '') ? $coasampai_id->coa : '';
            $ketcoadari = ($coadari_id != '') ? $coadari_id->keterangancoa : '';
            $ketcoasampai = ($coasampai_id != '') ? $coasampai_id->keterangancoa : '';
            $dari = $request->dari;
            $sampai = $request->sampai;
            $cabang = ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang;

            // return response([
            //     'data' => $laporanbukubesar->getReport(),
            //     'dataheader' => $dataHeader
            // ]);
            $bukubesar = json_decode($laporan_bukubesar);
            $disetujui = $bukubesar[0]->disetujui ?? '';
            $diperiksa = $bukubesar[0]->diperiksa ?? '';

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $bukubesar[0]->judul);
            $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:F1');

            $sheet->setCellValue('A2', $cabang);
            $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A2:F2');
            $sheet->setCellValue('A3', 'Buku Besar Divisi Trucking');
            $sheet->getStyle("A3")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A3:F3');

            $sheet->setCellValue('A4', 'Periode : ' . $dari . ' s/d ' . $sampai);
            $sheet->getStyle("A4")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A4:F4');

            $sheet->setCellValue('A5', 'No Perk. : ' .  $coadari . ' s/d ' . $coasampai);
            $sheet->getStyle("A5")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A5:F5');

            $sheet->setCellValue('A6', ' ' . $ketcoadari . ' s/d ' . $ketcoasampai);
            $sheet->getStyle("A6")->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A6:F6');

            $detail_table_header_row = 7;
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

            $detail_columns = [
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Debet',
                    'index' => 'debet'
                ],
                [
                    'label' => 'Kredit',
                    'index' => 'kredit'
                ],
                [
                    'label' => 'Saldo',
                    'index' => 'Saldo'
                ]
            ];


            // foreach ($detail_columns as $detail_columns_index => $detail_column) {
            //     $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            // }
            // $sheet->getStyle("A$detail_table_header_row:F$detail_table_header_row")->getFont()->setBold(true);

            // LOOPING DETAIL
            $totalKredit = 0;
            $totalDebet = 0;
            $totalSaldo = 0;
            $prevKeteranganCoa = null;
            // dd($bukubesar);
            $groupedData = [];
            if (is_array($bukubesar)) {
                foreach ($bukubesar as $row) {
                    $coa = $row->coa;
                    if (!isset($groupedData[$coa])) {
                        $groupedData[$coa] = [];
                    }
                    $groupedData[$coa][] = $row;
                }
            }

            if (is_array($bukubesar)) {
                foreach ($groupedData as $coa => $group) {
                    $sheet->mergeCells("A$detail_start_row:F$detail_start_row");
                    $sheet->setCellValue("A$detail_start_row", 'Kode Perkiraan : ' . $coa . ' (' . $group[0]->keterangancoa . ')')->getStyle('A' . $detail_start_row . ':F' . $detail_start_row);
                    $detail_start_row++;

                    // table header
                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    $sheet->getStyle("A$detail_start_row:F$detail_start_row")->getFont()->setBold(true);
                    $detail_start_row++;


                    $dataRow = $detail_table_header_row + 2;
                    $previousRow = $dataRow - 1;
                    foreach ($group as $response_index => $response_detail) {
                        // ... (your existing code for filling in details)
                        $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

                        $sheet->setCellValue("A$detail_start_row", $dateValue);
                        $sheet->setCellValue("B$detail_start_row", ($response_detail->nobukti == '') ? $response_detail->keterangan : $response_detail->nobukti);
                        $sheet->setCellValue("C$detail_start_row", ($response_detail->keterangan == 'SALDO AWAL') ? '' : $response_detail->keterangan);
                        // $sheet->setCellValue("D$detail_start_row", ($response_detail->keterangan == 'SALDO AWAL') ? 0 : $response_detail->debet);
                        // $sheet->setCellValue("E$detail_start_row", ($response_detail->keterangan == 'SALDO AWAL') ? 0 : $response_detail->kredit);
                        if ($response_detail->nilaikosongdebet == 1) { 
                            $sheet->setCellValueExplicit("D$detail_start_row", null, DataType::TYPE_NULL);  
                        }else{ 
                            $sheet->setCellValue("D$detail_start_row",  $response_detail->debet);
                        }
                        if ($response_detail->nilaikosongkredit == 1) { 
                            $sheet->setCellValueExplicit("E$detail_start_row", null, DataType::TYPE_NULL);  
                        }else{ 
                            $sheet->setCellValue("E$detail_start_row",  $response_detail->kredit);
                        }
                        if ($response_detail->nobukti == '') {
                            $sheet->setCellValue('F' . $detail_start_row, $response_detail->Saldo);
                            $previousRow = $detail_start_row;
                        } else {
                            if ($detail_start_row > $detail_table_header_row + 1) {
                                $sheet->setCellValue('F' . $detail_start_row, '=(F' . $previousRow . '+D' . $detail_start_row . ')-E' . $detail_start_row);
                            }
                        }

                        $sheet->getStyle("A$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                        $sheet->getStyle("D$detail_start_row:F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $totalKredit += $response_detail->kredit;
                        $totalDebet += $response_detail->debet;
                        $totalSaldo += $response_detail->Saldo;
                        $previousRow = $detail_start_row;
                        $detail_start_row++;
                        $prevKeteranganCoa = $response_detail->keterangancoa;
                    }
                    // Display the group totals at the end of the group
                    $sheet->setCellValue("C$detail_start_row", 'Total')->getStyle('C' . $detail_start_row)->getFont()->setBold(true);
                    $sheet->setCellValue("D$detail_start_row", "=SUM(D" . ($detail_start_row - count($group)) . ":D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->getFont()->setBold(true);
                    $sheet->setCellValue("E$detail_start_row", "=SUM(E" . ($detail_start_row - count($group)) . ":E" . ($detail_start_row - 1) . ")")->getStyle("E$detail_start_row")->getFont()->setBold(true);
                    $sheet->getStyle("D$detail_start_row:F$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $detail_start_row += 2; // Add an empty row between groups
                }
            }
            $ttd_start_row = $detail_start_row + 2;
            $sheet->setCellValue("A$ttd_start_row", 'Disetujui Oleh,');
            $sheet->setCellValue("C$ttd_start_row", 'Diperiksa Oleh,');
            $sheet->setCellValue("F$ttd_start_row", 'Disusun Oleh,');

            $sheet->setCellValue("A" . ($ttd_start_row + 3), '(                )');
            $sheet->setCellValue("C" . ($ttd_start_row + 3), '(                )');
            $sheet->setCellValue("F" . ($ttd_start_row + 3), '(                )');

            $sheet->getColumnDimension('C')->setWidth(87);
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN BUKU BESAR ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        }
    }
}
