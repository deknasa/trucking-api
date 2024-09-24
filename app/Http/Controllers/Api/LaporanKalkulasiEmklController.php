<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKalkulasiEmkl;
use App\Http\Requests\StoreLaporanKalkulasiEmklRequest;
use App\Http\Requests\UpdateLaporanKalkulasiEmklRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKalkulasiEmklController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $periode = $request->periode;
        $jenis = $request->jenis;
        $export = LaporanKalkulasiEmkl::getReport($periode, $jenis);

        $data = json_decode($export);

        if ($jenis == 1) {
            $this->export1($data, $request->periode, $request->jenis);
        }
        if ($jenis == 2) {
            $this->export2($data, $request->periode, $request->jenis);
        }
    }

    public function export1($data, $periode, $jenis)
    {

        $judul = $data[0]->judulLaporan;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Comic Sans MS');

        $detail_table_header_row = 2;
        $detail_start_row = $detail_table_header_row + 1;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],

        ];

        $styleHeader3 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],

        ];

        $styleHeader4 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        ];

        $styleHeader5 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        ];

        $styleHeader2 = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),

        ];

        $styleBorderTop = [
            'borders' => [
                'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]

        ];

        $styleBorderLeft = [
            'borders' => [
                // 'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]

        ];
        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        ];

        $styleArray2 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];


        $sheet->setCellValue("A" . ($detail_table_header_row - 1), $judul)->getStyle("A1")->applyFromArray($styleHeader3)->getFont()->setBold(true);
        $sheet->setCellValue("M" . ($detail_table_header_row - 1), 'Prediksi')->getStyle("M1")->applyFromArray($styleHeader3)->getFont()->setBold(true);
        $sheet->setCellValue("O" . ($detail_table_header_row - 1), 'Prediksi')->getStyle("O1")->applyFromArray($styleHeader3)->getFont()->setBold(true);
        $sheet->setCellValue("Q" . ($detail_table_header_row - 1), 'Prediksi')->getStyle("Q1")->applyFromArray($styleHeader3)->getFont()->setBold(true);
        $sheet->setCellValue("U" . ($detail_table_header_row - 1), 'Prediksi')->getStyle("U1")->applyFromArray($styleHeader3)->getFont()->setBold(true);
        $sheet->setCellValue("AC" . ($detail_table_header_row - 1), 'Prediksi')->getStyle("AC1")->applyFromArray($styleHeader3)->getFont()->setBold(true);
        $sheet->setCellValue("AH" . ($detail_table_header_row + 2), 'Prediksi')->getStyle("AH4")->applyFromArray($styleHeader3)->getFont()->setBold(true);


        $sheet->setCellValue("A$detail_table_header_row", 'Nomor Job')->getStyle("A2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("A$detail_table_header_row:A" . ($detail_table_header_row + 3));
        $sheet->setCellValue("B$detail_table_header_row", 'Shipper (Pengirim)')->getStyle("B2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("B$detail_table_header_row:B" . ($detail_table_header_row + 3));
        $sheet->setCellValue("C$detail_table_header_row", 'CONSIGNEE ( PENERIMA )')->getStyle("C2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("C$detail_table_header_row:C" . ($detail_table_header_row + 3));
        $sheet->setCellValue("D$detail_table_header_row", 'SIZE ( FEET )')->getStyle("D2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("D$detail_table_header_row:D" . ($detail_table_header_row + 1));
        $sheet->setCellValue("D" . ($detail_table_header_row + 2), '20" 21" 40"')->getStyle("D4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("D" . ($detail_table_header_row + 2) . ":D" . ($detail_table_header_row + 3));
        $sheet->setCellValue("E$detail_table_header_row", 'FEEDER ( NAMA KAPAL )')->getStyle("E2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 3));
        $sheet->setCellValue("F$detail_table_header_row", 'VOY')->getStyle("F2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 3));
        $sheet->setCellValue("G$detail_table_header_row", 'NO. CONTAINER/SEAL')->getStyle("G2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 3));
        $sheet->setCellValue("H$detail_table_header_row", 'LOKASI MUAT')->getStyle("H2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:H" . ($detail_table_header_row + 3));
        $sheet->setCellValue("I$detail_table_header_row", 'MARKETING')->getStyle("I2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("I$detail_table_header_row:I" . ($detail_table_header_row + 3));
        $sheet->setCellValue("J$detail_table_header_row", 'TRUCK')->getStyle("J2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("J$detail_table_header_row:J" . ($detail_table_header_row + 3));
        $sheet->setCellValue("K$detail_table_header_row", 'NOMINAL')->getStyle("K2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("K$detail_table_header_row:K" . ($detail_table_header_row + 3));
        $sheet->setCellValue("L$detail_table_header_row", 'NO INV')->getStyle("L2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:L" . ($detail_table_header_row + 3));
        $sheet->setCellValue("M$detail_table_header_row", 'THC BITUNG')->getStyle("M2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("M$detail_table_header_row:M" . ($detail_table_header_row + 3));
        $sheet->setCellValue("N$detail_table_header_row", 'NOMOR BUKTI')->getStyle("N2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("N$detail_table_header_row:N" . ($detail_table_header_row + 3));
        $sheet->setCellValue("O$detail_table_header_row", 'FREIGHT')->getStyle("O2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("O$detail_table_header_row:O" . ($detail_table_header_row + 3));
        $sheet->setCellValue("P$detail_table_header_row", 'NO BUKTI')->getStyle("P2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("P$detail_table_header_row:P" . ($detail_table_header_row + 3));
        $sheet->setCellValue("Q$detail_table_header_row", 'LSS')->getStyle("Q2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("Q$detail_table_header_row:Q" . ($detail_table_header_row + 3));
        $sheet->setCellValue("R$detail_table_header_row", 'NO BUKTI')->getStyle("R2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("R$detail_table_header_row:R" . ($detail_table_header_row + 3));
        $sheet->setCellValue("S$detail_table_header_row", 'APBS')->getStyle("S2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("S$detail_table_header_row:S" . ($detail_table_header_row + 3));
        $sheet->setCellValue("T$detail_table_header_row", 'NO BUKTI')->getStyle("T2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("T$detail_table_header_row:T" . ($detail_table_header_row + 3));
        $sheet->setCellValue("U$detail_table_header_row", 'DO')->getStyle("U2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("U$detail_table_header_row:U" . ($detail_table_header_row + 3));
        $sheet->setCellValue("V$detail_table_header_row", 'NO BUKTI')->getStyle("V2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("V$detail_table_header_row:V" . ($detail_table_header_row + 3));
        $sheet->setCellValue("W$detail_table_header_row", 'LAP. MUAT')->getStyle("W2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("W$detail_table_header_row:W" . ($detail_table_header_row + 3));
        $sheet->setCellValue("X$detail_table_header_row", 'NO BUKTI')->getStyle("X2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("X$detail_table_header_row:X" . ($detail_table_header_row + 3));
        $sheet->setCellValue("Y$detail_table_header_row", 'KET')->getStyle("Y2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("Y$detail_table_header_row:Y" . ($detail_table_header_row + 3));
        $sheet->setCellValue("Z$detail_table_header_row", 'PREDIKSI LAP. MUAT')->getStyle("Z2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("Z$detail_table_header_row:Z" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AA$detail_table_header_row", 'SEAL ( SEGEL )')->getStyle("AA2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AA$detail_table_header_row:AA" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AB$detail_table_header_row", 'NO BUKTI')->getStyle("AB2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AB$detail_table_header_row:AB" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AC$detail_table_header_row", 'LAL')->getStyle("AC2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AC$detail_table_header_row:AC" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AD$detail_table_header_row", 'NO BUKTI')->getStyle("AD2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AD$detail_table_header_row:AD" . ($detail_table_header_row + 3));

        $sheet->setCellValue("AF" . ($detail_table_header_row + 2), 'TRUCKING')->getStyle("AF4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AF" . ($detail_table_header_row + 2) . ":AF" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AG" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AG4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AG" . ($detail_table_header_row + 2) . ":AG" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AH" . ($detail_table_header_row + 2), 'THC')->getStyle("AH4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AH" . ($detail_table_header_row + 2) . ":AH" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AI" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AI4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AI" . ($detail_table_header_row + 2) . ":AI" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AJ" . ($detail_table_header_row + 2), 'CLEANING')->getStyle("AJ4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AJ" . ($detail_table_header_row + 2) . ":AJ" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AK" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AK4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AK" . ($detail_table_header_row + 2) . ":AK" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AL" . ($detail_table_header_row + 2), 'ASURANSI')->getStyle("AL4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AL" . ($detail_table_header_row + 2) . ":AL" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AM" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AM4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AM" . ($detail_table_header_row + 2) . ":AM" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AN" . ($detail_table_header_row + 2), 'DOCUMENT')->getStyle("AN4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AN" . ($detail_table_header_row + 2) . ":AN" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AO" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AO4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AO" . ($detail_table_header_row + 2) . ":AO" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AP" . ($detail_table_header_row + 2), 'STORAGE')->getStyle("AP4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AP" . ($detail_table_header_row + 2) . ":AP" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AQ" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AQ4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AQ" . ($detail_table_header_row + 2) . ":AQ" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AR" . ($detail_table_header_row + 2), 'DEMURAGE')->getStyle("AR4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AR" . ($detail_table_header_row + 2) . ":AR" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AS" . ($detail_table_header_row + 2), 'B. LAIN')->getStyle("AS4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AS" . ($detail_table_header_row + 2) . ":AS" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AT" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("AT4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AT" . ($detail_table_header_row + 2) . ":AT" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AU" . ($detail_table_header_row + 2), 'TOTAL BIAYA DOORING')->getStyle("AU4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AU" . ($detail_table_header_row + 2) . ":AU" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AV" . ($detail_table_header_row + 3), 'DPP')->getStyle("AV5")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AV" . ($detail_table_header_row + 3) . ":AV" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AW" . ($detail_table_header_row + 3), 'PPN 1.1 %')->getStyle("AW5")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AW" . ($detail_table_header_row + 3) . ":AW" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AX" . ($detail_table_header_row + 3), 'TOTAL INVOICE')->getStyle("AX5")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AX" . ($detail_table_header_row + 3) . ":AX" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AY" . ($detail_table_header_row + 2), 'NO. INV')->getStyle("AU4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AY" . ($detail_table_header_row + 2) . ":AY" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AZ$detail_table_header_row", 'PENDAPATAN')->getStyle("AZ2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AZ$detail_table_header_row:AZ" . ($detail_table_header_row + 3));



        $b = 2;

        while ($b <= 5) {
            $sheet->getStyle("A" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("B" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("C" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("D" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("E" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("F" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("G" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("H" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("I" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("J" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("K" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("L" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("M" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("N" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("O" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("P" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("Q" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("R" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("S" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("T" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("U" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("V" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("W" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("X" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("Y" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("Z" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AA" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AB" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AC" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AD" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AE" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AZ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);

            $sheet->getStyle("AV" . $b)->applyFromArray($styleBorderLeft)->getFont()->setBold(true);
            $sheet->getStyle("AY" . $b)->applyFromArray($styleBorderLeft)->getFont()->setBold(true);

            if ($b == 2) {
                $sheet->getStyle("AF" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AG" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AH" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AI" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AJ" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AK" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AL" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AM" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AN" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AO" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AP" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AQ" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AR" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AS" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AT" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AU" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AV" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AW" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AX" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
                $sheet->getStyle("AY" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            }
            if ($b >= 4) {
                $sheet->getStyle("AF" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AG" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AH" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AI" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AJ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AK" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AL" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AM" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AN" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AO" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AP" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AQ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AR" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AS" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AT" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AU" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            }
            if ($b >= 5) {
                $sheet->getStyle("AV" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AW" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AX" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            }
            if ($b >= 3) {
                $sheet->getStyle("AY" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            }



            // WRAP

            $sheet->getStyle("A" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("B" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("C" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("D" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("E" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("F" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("G" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("H" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("I" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("J" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("K" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("L" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("M" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("N" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("O" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("P" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("Q" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("R" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("S" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("T" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("U" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("V" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("W" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("X" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("Y" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("Z" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AA" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AB" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AC" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AD" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AE" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AZ" . $b)->getAlignment()->setWrapText(true);
            if ($b >= 4) {
                $sheet->getStyle("AF" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AG" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AH" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AI" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AJ" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AK" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AL" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AM" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AN" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AO" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AP" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AQ" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AR" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AS" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AT" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AU" . $b)->getAlignment()->setWrapText(true);
            }
            if ($b >= 5) {
                $sheet->getStyle("AV" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AW" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AX" . $b)->getAlignment()->setWrapText(true);
            }
            if ($b >= 3) {
                $sheet->getStyle("AY" . $b)->getAlignment()->setWrapText(true);
            }

            $b = $b + 1;
        }


        $rowIndex = 6;
        $awalBaris = 6;
        // $group = [];
        $groupRowCount = 0;
        foreach ($data as $response_index => $response_detail) {
            $sheet->setCellValue("A$rowIndex", $response_detail->nobukti);
            $sheet->setCellValue("B$rowIndex", $response_detail->shipper);
            $sheet->setCellValue("C$rowIndex", $response_detail->penerima);
            $sheet->setCellValue("D$rowIndex", $response_detail->container);
            $sheet->setCellValue("E$rowIndex", $response_detail->kapal);
            $sheet->setCellValue("F$rowIndex", $response_detail->voy);
            $sheet->setCellValue("G$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("H$rowIndex", $response_detail->lokasibongkarmuat);
            $sheet->setCellValue("I$rowIndex", $response_detail->marketing);

            $sheet->setCellValue("AU$rowIndex", "=K$rowIndex+M$rowIndex+O$rowIndex+Q$rowIndex+S$rowIndex+U$rowIndex+W$rowIndex+Z$rowIndex+AA$rowIndex+AC$rowIndex+AF$rowIndex+AH$rowIndex+AJ$rowIndex+AL$rowIndex+AN$rowIndex+AP$rowIndex+AR$rowIndex+AS$rowIndex")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AW$rowIndex", "=AV$rowIndex*1.1/100")->getStyle("AC$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AX$rowIndex", "=AV$rowIndex+AW$rowIndex")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AZ$rowIndex", "=AX$rowIndex-AU$rowIndex-AW$rowIndex")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

            $sheet->getStyle("A" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("B" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("C" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("D" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("E" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("F" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("G" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("H" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("I" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("J" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("K" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("L" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("M" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("N" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("O" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("P" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("Q" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("R" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("S" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("T" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("U" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("V" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("W" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("X" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("Y" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("Z" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AA" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AB" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AC" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AD" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AE" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AZ" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

            $sheet->getStyle("AV" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AY" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AF" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AG" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AH" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AI" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AJ" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AK" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AL" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AM" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AN" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AO" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AP" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AQ" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AR" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AS" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AT" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AU" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AV" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AW" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AX" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AY" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AF" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AG" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AH" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AI" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AJ" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AK" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AL" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AM" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AN" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AO" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AP" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AQ" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AR" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AS" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AT" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AU" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AV" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AW" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AX" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AY" . $rowIndex)->applyFromArray($styleHeader4);


            $rowIndex++;
        }


        $sheet->setCellValue("K$rowIndex", "=SUM(K" . ($awalBaris) . ":K" . ($rowIndex - 1) . ")")->getStyle("K$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("M$rowIndex", "=SUM(M" . ($awalBaris) . ":M" . ($rowIndex - 1) . ")")->getStyle("M$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("O$rowIndex", "=SUM(O" . ($awalBaris) . ":O" . ($rowIndex - 1) . ")")->getStyle("O$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("Q$rowIndex", "=SUM(Q" . ($awalBaris) . ":Q" . ($rowIndex - 1) . ")")->getStyle("Q$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("S$rowIndex", "=SUM(S" . ($awalBaris) . ":S" . ($rowIndex - 1) . ")")->getStyle("S$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("U$rowIndex", "=SUM(U" . ($awalBaris) . ":U" . ($rowIndex - 1) . ")")->getStyle("U$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("W$rowIndex", "=SUM(W" . ($awalBaris) . ":W" . ($rowIndex - 1) . ")")->getStyle("W$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AA$rowIndex", "=SUM(AA" . ($awalBaris) . ":AA" . ($rowIndex - 1) . ")")->getStyle("AA$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AC$rowIndex", "=SUM(AC" . ($awalBaris) . ":AC" . ($rowIndex - 1) . ")")->getStyle("AC$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AF$rowIndex", "=SUM(AF" . ($awalBaris) . ":AF" . ($rowIndex - 1) . ")")->getStyle("AF$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AH$rowIndex", "=SUM(AH" . ($awalBaris) . ":AH" . ($rowIndex - 1) . ")")->getStyle("AH$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AJ$rowIndex", "=SUM(AJ" . ($awalBaris) . ":AJ" . ($rowIndex - 1) . ")")->getStyle("AJ$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AL$rowIndex", "=SUM(AL" . ($awalBaris) . ":AL" . ($rowIndex - 1) . ")")->getStyle("AL$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AN$rowIndex", "=SUM(AN" . ($awalBaris) . ":AN" . ($rowIndex - 1) . ")")->getStyle("AN$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AP$rowIndex", "=SUM(AP" . ($awalBaris) . ":AP" . ($rowIndex - 1) . ")")->getStyle("AP$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AR$rowIndex", "=SUM(AR" . ($awalBaris) . ":AR" . ($rowIndex - 1) . ")")->getStyle("AR$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AS$rowIndex", "=SUM(AS" . ($awalBaris) . ":AS" . ($rowIndex - 1) . ")")->getStyle("AS$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AU$rowIndex", "=SUM(AU" . ($awalBaris) . ":AU" . ($rowIndex - 1) . ")")->getStyle("AU$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AX$rowIndex", "=SUM(AX" . ($awalBaris) . ":AX" . ($rowIndex - 1) . ")")->getStyle("AX$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AZ$rowIndex", "=SUM(AZ" . ($awalBaris) . ":AZ" . ($rowIndex - 1) . ")")->getStyle("AZ$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");




        $sheet->getStyle("K" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("M" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("O" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("Q" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("S" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("U" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("W" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AA" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AC" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AF" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AH" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AJ" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AL" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AN" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AP" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AR" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AS" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AU" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AX" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AZ" . $rowIndex)->getFont()->setBold(true);



        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(42);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(22);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(18);
        $sheet->getColumnDimension('P')->setWidth(18);
        $sheet->getColumnDimension('Q')->setWidth(18);
        $sheet->getColumnDimension('R')->setWidth(18);
        $sheet->getColumnDimension('S')->setWidth(18);
        $sheet->getColumnDimension('T')->setWidth(18);
        $sheet->getColumnDimension('U')->setWidth(18);
        $sheet->getColumnDimension('V')->setWidth(18);
        $sheet->getColumnDimension('W')->setWidth(18);
        $sheet->getColumnDimension('X')->setWidth(18);
        $sheet->getColumnDimension('Y')->setWidth(18);
        $sheet->getColumnDimension('Z')->setWidth(18);
        $sheet->getColumnDimension('AA')->setWidth(18);
        $sheet->getColumnDimension('AB')->setWidth(18);
        $sheet->getColumnDimension('AC')->setWidth(18);
        $sheet->getColumnDimension('AD')->setWidth(18);
        $sheet->getColumnDimension('AE')->setWidth(18);
        $sheet->getColumnDimension('AF')->setWidth(18);
        $sheet->getColumnDimension('AG')->setWidth(18);
        $sheet->getColumnDimension('AH')->setWidth(18);
        $sheet->getColumnDimension('AI')->setWidth(18);
        $sheet->getColumnDimension('AJ')->setWidth(18);
        $sheet->getColumnDimension('AK')->setWidth(18);
        $sheet->getColumnDimension('AL')->setWidth(18);
        $sheet->getColumnDimension('AM')->setWidth(18);
        $sheet->getColumnDimension('AN')->setWidth(18);
        $sheet->getColumnDimension('AO')->setWidth(18);
        $sheet->getColumnDimension('AP')->setWidth(18);
        $sheet->getColumnDimension('AQ')->setWidth(18);
        $sheet->getColumnDimension('AR')->setWidth(18);
        $sheet->getColumnDimension('AS')->setWidth(18);
        $sheet->getColumnDimension('AT')->setWidth(18);
        $sheet->getColumnDimension('AU')->setWidth(18);
        $sheet->getColumnDimension('AV')->setWidth(18);
        $sheet->getColumnDimension('AW')->setWidth(18);
        $sheet->getColumnDimension('AX')->setWidth(18);
        $sheet->getColumnDimension('AY')->setWidth(18);
        $sheet->getColumnDimension('AZ')->setWidth(18);



        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN KALKULASI EMKL ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
    public function export2($data, $periode, $jenis)
    {

        $judul = $data[0]->judulLaporan;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Comic Sans MS');

        $detail_table_header_row = 2;
        $detail_start_row = $detail_table_header_row + 1;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],

        ];

        $styleHeader3 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],

        ];

        $styleHeader4 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        ];

        $styleHeader5 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        ];

        $styleHeader2 = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),

        ];

        $styleBorderTop = [
            'borders' => [
                'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]

        ];

        $styleBorderLeft = [
            'borders' => [
                // 'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                // 'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]

        ];
        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        ];

        $styleArray2 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];


        $sheet->setCellValue("A" . ($detail_table_header_row - 1), $judul)->getStyle("A1")->applyFromArray($styleHeader3)->getFont()->setBold(true);

        $sheet->setCellValue("I$detail_table_header_row", 'Reimburse')->getStyle("I2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("I$detail_table_header_row:K" . ($detail_table_header_row + 1));
        $sheet->setCellValue("L$detail_table_header_row", 'Reimburse')->getStyle("L2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:N" . ($detail_table_header_row + 1));
        $sheet->setCellValue("O$detail_table_header_row", 'Reimburse')->getStyle("O2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("O$detail_table_header_row:Q" . ($detail_table_header_row + 1));
        $sheet->setCellValue("AL$detail_table_header_row", 'Invoice ( Tagihan ) Dooring Diisi Oleh Controller')->getStyle("AL2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AL$detail_table_header_row:AT" . ($detail_table_header_row + 1));


        $sheet->setCellValue("A$detail_table_header_row", 'Nomor Job')->getStyle("A2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("A$detail_table_header_row:A" . ($detail_table_header_row + 3));
        $sheet->setCellValue("B$detail_table_header_row", 'Shipper (Pengirim)')->getStyle("B2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("B$detail_table_header_row:B" . ($detail_table_header_row + 3));
        $sheet->setCellValue("C$detail_table_header_row", 'CONSIGNEE ( PENERIMA )')->getStyle("C2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("C$detail_table_header_row:C" . ($detail_table_header_row + 3));
        $sheet->setCellValue("D$detail_table_header_row", 'SIZE ( FEET )')->getStyle("D2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("D$detail_table_header_row:D" . ($detail_table_header_row + 1));
        $sheet->setCellValue("D" . ($detail_table_header_row + 2), '20" 21" 40"')->getStyle("D4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("D" . ($detail_table_header_row + 2) . ":D" . ($detail_table_header_row + 3));
        $sheet->setCellValue("E$detail_table_header_row", 'FEEDER ( NAMA KAPAL )')->getStyle("E2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 3));
        $sheet->setCellValue("F$detail_table_header_row", 'VOY')->getStyle("F2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 3));
        $sheet->setCellValue("G$detail_table_header_row", 'NO. CONTAINER/SEAL')->getStyle("G2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 3));
        $sheet->setCellValue("H$detail_table_header_row", 'LOKASI BONGKAR')->getStyle("H2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:H" . ($detail_table_header_row + 3));
        $sheet->setCellValue("I" . ($detail_table_header_row + 2), 'THC BITUNG')->getStyle("I4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("I" . ($detail_table_header_row + 2) . ":I" . ($detail_table_header_row + 3));
        $sheet->setCellValue("J" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("J4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("J" . ($detail_table_header_row + 2) . ":J" . ($detail_table_header_row + 3));
        $sheet->setCellValue("K" . ($detail_table_header_row + 2), 'NO INVOICE')->getStyle("K4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("K" . ($detail_table_header_row + 2) . ":K" . ($detail_table_header_row + 3));
        $sheet->setCellValue("L" . ($detail_table_header_row + 2), 'STORAGE')->getStyle("L4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("L" . ($detail_table_header_row + 2) . ":L" . ($detail_table_header_row + 3));
        $sheet->setCellValue("M" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("M4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("M" . ($detail_table_header_row + 2) . ":M" . ($detail_table_header_row + 3));
        $sheet->setCellValue("N" . ($detail_table_header_row + 2), 'NO INVOICE')->getStyle("N4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("N" . ($detail_table_header_row + 2) . ":N" . ($detail_table_header_row + 3));
        $sheet->setCellValue("O" . ($detail_table_header_row + 2), 'DEMURAGE')->getStyle("O4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("O" . ($detail_table_header_row + 2) . ":O" . ($detail_table_header_row + 3));
        $sheet->setCellValue("P" . ($detail_table_header_row + 2), 'NO BUKTI')->getStyle("P4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("P" . ($detail_table_header_row + 2) . ":P" . ($detail_table_header_row + 3));
        $sheet->setCellValue("Q" . ($detail_table_header_row + 2), 'NO INVOICE')->getStyle("Q4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("Q" . ($detail_table_header_row + 2) . ":Q" . ($detail_table_header_row + 3));

        $sheet->setCellValue("R$detail_table_header_row", 'TRUCK')->getStyle("R2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("R$detail_table_header_row:R" . ($detail_table_header_row + 3));
        $sheet->setCellValue("S$detail_table_header_row", 'NOMINAL')->getStyle("S2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("S$detail_table_header_row:S" . ($detail_table_header_row + 3));
        $sheet->setCellValue("T$detail_table_header_row", 'NO INVOICE')->getStyle("T2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("T$detail_table_header_row:T" . ($detail_table_header_row + 3));
        $sheet->setCellValue("U$detail_table_header_row", 'B. LAIN')->getStyle("U2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("U$detail_table_header_row:U" . ($detail_table_header_row + 3));
        $sheet->setCellValue("V$detail_table_header_row", 'NO BUKTI')->getStyle("V2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("V$detail_table_header_row:V" . ($detail_table_header_row + 3));
        $sheet->setCellValue("W$detail_table_header_row", 'KETERANGAN')->getStyle("W2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("W$detail_table_header_row:W" . ($detail_table_header_row + 3));
        $sheet->setCellValue("X$detail_table_header_row", 'KLAIM')->getStyle("X2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("X$detail_table_header_row:X" . ($detail_table_header_row + 3));
        $sheet->setCellValue("Y$detail_table_header_row", 'NO BUKTI')->getStyle("Y2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("Y$detail_table_header_row:Y" . ($detail_table_header_row + 3));
        $sheet->setCellValue("Z$detail_table_header_row", 'LOLO (LIFT ON LIFT OFF')->getStyle("Z2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("Z$detail_table_header_row:Z" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AA$detail_table_header_row", 'NO BUKTI')->getStyle("AA2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AA$detail_table_header_row:AA" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AB$detail_table_header_row", 'LAL')->getStyle("AB2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AB$detail_table_header_row:AB" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AC$detail_table_header_row", 'NO BUKTI')->getStyle("AC2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AC$detail_table_header_row:AC" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AD$detail_table_header_row", 'CLEANING')->getStyle("AD2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AD$detail_table_header_row:AD" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AE$detail_table_header_row", 'NO BUKTI')->getStyle("AE2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AE$detail_table_header_row:AE" . ($detail_table_header_row + 3));

        $sheet->setCellValue("AF$detail_table_header_row", 'DO')->getStyle("AF2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AF$detail_table_header_row:AF" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AG$detail_table_header_row", 'NO BUKTI')->getStyle("AG2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AG$detail_table_header_row:AG" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AH$detail_table_header_row", 'BURUH BONGKAR')->getStyle("AH2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AH$detail_table_header_row:AH" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AI$detail_table_header_row", 'NO BUKTI')->getStyle("AI2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AI$detail_table_header_row:AI" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AJ$detail_table_header_row", 'TOTAL BIAYA BONGKAR')->getStyle("AJ2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AJ$detail_table_header_row:AJ" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AK" . ($detail_table_header_row + 2), '')->getStyle("AK2")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AK" . ($detail_table_header_row + 2) . ":AK" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AL" . ($detail_table_header_row + 2), 'TABEL DOORING')->getStyle("AL4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AL" . ($detail_table_header_row + 2) . ":AL" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AM" . ($detail_table_header_row + 2), 'DO')->getStyle("AM4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AM" . ($detail_table_header_row + 2) . ":AM" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AN" . ($detail_table_header_row + 2), 'UANG KAWAL')->getStyle("AN4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AN" . ($detail_table_header_row + 2) . ":AN" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AO" . ($detail_table_header_row + 2), 'UANG BURUH')->getStyle("AO4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AO" . ($detail_table_header_row + 2) . ":AO" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AP" . ($detail_table_header_row + 2), 'BIAYA CLEANING')->getStyle("AP4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AP" . ($detail_table_header_row + 2) . ":AP" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AQ" . ($detail_table_header_row + 2), 'BIAYA LAIN')->getStyle("AQ4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AQ" . ($detail_table_header_row + 2) . ":AQ" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AR" . ($detail_table_header_row + 2), 'NILAI INVOICE')->getStyle("AR4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AR" . ($detail_table_header_row + 2) . ":AR" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AS" . ($detail_table_header_row + 2), 'NOMOR INVOICE ( DIISI OLEH EMKL')->getStyle("AS4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AS" . ($detail_table_header_row + 2) . ":AS" . ($detail_table_header_row + 3));
        $sheet->setCellValue("AT" . ($detail_table_header_row + 2), 'PENDAPATAN (PROFIT)')->getStyle("AT4")->applyFromArray($styleHeader2)->getFont()->setBold(true);
        $sheet->mergeCells("AT" . ($detail_table_header_row + 2) . ":AT" . ($detail_table_header_row + 3));




        $b = 2;

        while ($b <= 5) {
            $sheet->getStyle("A" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("B" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("C" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("D" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("E" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("F" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("G" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("H" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("I" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("J" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("K" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("L" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("M" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("N" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("O" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("P" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("Q" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("R" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("S" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("T" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("U" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("V" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("W" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("X" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("Y" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("Z" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AA" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AB" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AC" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AD" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AE" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AF" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AG" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AH" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AI" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AJ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AK" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AL" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AN" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AO" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AP" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AQ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AR" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AS" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            $sheet->getStyle("AT" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);


            // if ($b == 2) {
            //     $sheet->getStyle("AE" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AF" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AG" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AH" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AI" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AJ" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AK" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AL" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AM" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AN" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AO" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AP" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AQ" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AR" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AS" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            //     $sheet->getStyle("AT" . $b)->applyFromArray($styleBorderTop)->getFont()->setBold(true);
            // }
            if ($b >= 4) {
                $sheet->getStyle("AF" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AG" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AH" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AI" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AJ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AK" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AL" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AM" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AN" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AO" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AP" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AQ" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AR" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
                $sheet->getStyle("AS" . $b)->applyFromArray($styleHeader2)->getFont()->setBold(true);
            }
            // if ($b>=5) {
            //     $sheet->getStyle("AV".$b)->applyFromArray($styleHeader2)->getFont()->setBold(true);   
            //     $sheet->getStyle("AW".$b)->applyFromArray($styleHeader2)->getFont()->setBold(true);   
            //     $sheet->getStyle("AX".$b)->applyFromArray($styleHeader2)->getFont()->setBold(true);   

            // }
            // if ($b>=3) {
            //     $sheet->getStyle("AY".$b)->applyFromArray($styleHeader2)->getFont()->setBold(true);   

            // }



            // WRAP

            $sheet->getStyle("A" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("B" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("C" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("D" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("E" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("F" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("G" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("H" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("I" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("J" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("K" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("L" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("M" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("N" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("O" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("P" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("Q" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("R" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("S" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("T" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("U" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("V" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("W" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("X" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("Y" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("Z" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AA" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AB" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AC" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AD" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AE" . $b)->getAlignment()->setWrapText(true);
            $sheet->getStyle("AZ" . $b)->getAlignment()->setWrapText(true);
            if ($b >= 4) {
                $sheet->getStyle("AF" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AG" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AH" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AI" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AJ" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AK" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AL" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AM" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AN" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AO" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AP" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AQ" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AR" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AS" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AT" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AU" . $b)->getAlignment()->setWrapText(true);
            }
            if ($b >= 5) {
                $sheet->getStyle("AV" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AW" . $b)->getAlignment()->setWrapText(true);
                $sheet->getStyle("AX" . $b)->getAlignment()->setWrapText(true);
            }
            if ($b >= 3) {
                $sheet->getStyle("AY" . $b)->getAlignment()->setWrapText(true);
            }

            $b = $b + 1;
        }


        $rowIndex = 6;
        $awalBaris = 6;
        // $group = [];
        $groupRowCount = 0;
        foreach ($data as $response_index => $response_detail) {
            $sheet->setCellValue("A$rowIndex", $response_detail->nobukti);
            $sheet->setCellValue("B$rowIndex", $response_detail->shipper);
            $sheet->setCellValue("C$rowIndex", $response_detail->penerima);
            $sheet->setCellValue("D$rowIndex", $response_detail->container);
            $sheet->setCellValue("E$rowIndex", $response_detail->kapal);
            $sheet->setCellValue("F$rowIndex", $response_detail->voy);
            $sheet->setCellValue("G$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("H$rowIndex", $response_detail->lokasibongkarmuat);

            $sheet->setCellValue("AJ$rowIndex", "=S$rowIndex+U$rowIndex+X$rowIndex+Z$rowIndex+AB$rowIndex+AD$rowIndex+AF$rowIndex+AH$rowIndex")->getStyle("AJ$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AR$rowIndex", "=AL$rowIndex+AM$rowIndex+AN$rowIndex+AO$rowIndex+AP$rowIndex+AQ$rowIndex")->getStyle("AR$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AT$rowIndex", "=AR$rowIndex-AJ$rowIndex")->getStyle("AT$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

            $sheet->getStyle("A" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("B" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("C" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("D" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("E" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("F" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("G" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("H" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("I" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("J" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("K" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("L" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("M" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("N" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("O" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("P" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("Q" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("R" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("S" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("T" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("U" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("V" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("W" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("X" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("Y" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("Z" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AA" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AB" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AC" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AD" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AE" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AF" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AG" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AH" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AI" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AJ" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

            $sheet->getStyle("AL" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AM" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AN" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AO" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AP" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AQ" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AR" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->getStyle("AS" . $rowIndex)->applyFromArray($styleHeader4);
            $sheet->getStyle("AT" . $rowIndex)->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");


            $rowIndex++;
        }


        $sheet->setCellValue("I$rowIndex", "=SUM(I" . ($awalBaris) . ":I" . ($rowIndex - 1) . ")")->getStyle("I$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("L$rowIndex", "=SUM(L" . ($awalBaris) . ":L" . ($rowIndex - 1) . ")")->getStyle("L$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("O$rowIndex", "=SUM(O" . ($awalBaris) . ":O" . ($rowIndex - 1) . ")")->getStyle("O$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("S$rowIndex", "=SUM(S" . ($awalBaris) . ":S" . ($rowIndex - 1) . ")")->getStyle("S$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("U$rowIndex", "=SUM(U" . ($awalBaris) . ":U" . ($rowIndex - 1) . ")")->getStyle("U$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("X$rowIndex", "=SUM(X" . ($awalBaris) . ":X" . ($rowIndex - 1) . ")")->getStyle("X$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("Z$rowIndex", "=SUM(Z" . ($awalBaris) . ":Z" . ($rowIndex - 1) . ")")->getStyle("Z$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AB$rowIndex", "=SUM(AB" . ($awalBaris) . ":AB" . ($rowIndex - 1) . ")")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AD$rowIndex", "=SUM(AD" . ($awalBaris) . ":AD" . ($rowIndex - 1) . ")")->getStyle("AD$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AF$rowIndex", "=SUM(AF" . ($awalBaris) . ":AF" . ($rowIndex - 1) . ")")->getStyle("AF$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AH$rowIndex", "=SUM(AH" . ($awalBaris) . ":AH" . ($rowIndex - 1) . ")")->getStyle("AH$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AJ$rowIndex", "=SUM(AJ" . ($awalBaris) . ":AJ" . ($rowIndex - 1) . ")")->getStyle("AJ$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AL$rowIndex", "=SUM(AL" . ($awalBaris) . ":AL" . ($rowIndex - 1) . ")")->getStyle("AL$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AN$rowIndex", "=SUM(AN" . ($awalBaris) . ":AN" . ($rowIndex - 1) . ")")->getStyle("AN$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AO$rowIndex", "=SUM(AO" . ($awalBaris) . ":AO" . ($rowIndex - 1) . ")")->getStyle("AO$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AP$rowIndex", "=SUM(AP" . ($awalBaris) . ":AP" . ($rowIndex - 1) . ")")->getStyle("AP$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AQ$rowIndex", "=SUM(AQ" . ($awalBaris) . ":AQ" . ($rowIndex - 1) . ")")->getStyle("AQ$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AR$rowIndex", "=SUM(AR" . ($awalBaris) . ":AR" . ($rowIndex - 1) . ")")->getStyle("AR$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        $sheet->setCellValue("AT$rowIndex", "=SUM(AT" . ($awalBaris) . ":AT" . ($rowIndex - 1) . ")")->getStyle("AT$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");




        $sheet->getStyle("K" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("M" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("O" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("Q" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("S" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("U" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("W" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AA" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AC" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AF" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AH" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AJ" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AL" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AN" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AP" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AR" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AS" . $rowIndex)->getFont()->setBold(true);
        $sheet->getStyle("AT" . $rowIndex)->getFont()->setBold(true);



        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(42);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(22);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(18);
        $sheet->getColumnDimension('P')->setWidth(18);
        $sheet->getColumnDimension('Q')->setWidth(18);
        $sheet->getColumnDimension('R')->setWidth(18);
        $sheet->getColumnDimension('S')->setWidth(18);
        $sheet->getColumnDimension('T')->setWidth(18);
        $sheet->getColumnDimension('U')->setWidth(18);
        $sheet->getColumnDimension('V')->setWidth(18);
        $sheet->getColumnDimension('W')->setWidth(18);
        $sheet->getColumnDimension('X')->setWidth(18);
        $sheet->getColumnDimension('Y')->setWidth(18);
        $sheet->getColumnDimension('Z')->setWidth(18);
        $sheet->getColumnDimension('AA')->setWidth(18);
        $sheet->getColumnDimension('AB')->setWidth(18);
        $sheet->getColumnDimension('AC')->setWidth(18);
        $sheet->getColumnDimension('AD')->setWidth(18);
        $sheet->getColumnDimension('AE')->setWidth(18);
        $sheet->getColumnDimension('AF')->setWidth(18);
        $sheet->getColumnDimension('AG')->setWidth(18);
        $sheet->getColumnDimension('AH')->setWidth(18);
        $sheet->getColumnDimension('AI')->setWidth(18);
        $sheet->getColumnDimension('AJ')->setWidth(18);
        $sheet->getColumnDimension('AK')->setWidth(18);
        $sheet->getColumnDimension('AL')->setWidth(18);
        $sheet->getColumnDimension('AM')->setWidth(18);
        $sheet->getColumnDimension('AN')->setWidth(18);
        $sheet->getColumnDimension('AO')->setWidth(18);
        $sheet->getColumnDimension('AP')->setWidth(18);
        $sheet->getColumnDimension('AQ')->setWidth(18);
        $sheet->getColumnDimension('AR')->setWidth(18);
        $sheet->getColumnDimension('AS')->setWidth(18);
        $sheet->getColumnDimension('AT')->setWidth(18);


        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN KALKULASI EMKL ' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function export1old($data, $periode, $jenis)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Comic Sans MS');

        $detail_table_header_row = 1;
        $detail_start_row = $detail_table_header_row + 1;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];

        $styleArray2 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];



        $sheet->setCellValue("A$detail_table_header_row", 'Nomor Job')->getStyle("A2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("A$detail_table_header_row:A" . ($detail_table_header_row + 2));
        $sheet->setCellValue("B$detail_table_header_row", 'Shipper (Pengirim)')->getStyle("B2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("B$detail_table_header_row:B" . ($detail_table_header_row + 2));
        $sheet->setCellValue("C$detail_table_header_row", 'CONSIGNEE ( PENERIMA )')->getStyle("C1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("C$detail_table_header_row:D" . ($detail_table_header_row + 2));
        $sheet->setCellValue("E$detail_table_header_row", 'Rute')->getStyle("E1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 2));
        $sheet->setCellValue("F$detail_table_header_row", 'Qty')->getStyle("F1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 2));
        $sheet->setCellValue("G$detail_table_header_row", 'Lokasi Muat')->getStyle("G1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 2));
        $sheet->setCellValue("H$detail_table_header_row", 'No Container/Seal')->getStyle("H1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:H" . ($detail_table_header_row + 2));
        $sheet->setCellValue("I$detail_table_header_row", 'EMKL')->getStyle("I1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("I$detail_table_header_row:I" . ($detail_table_header_row + 2));
        $sheet->setCellValue("J$detail_table_header_row", 'No SP')->getStyle("J1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("J$detail_table_header_row:K$detail_table_header_row");
        $sheet->setCellValue("J" . ($detail_table_header_row + 1), 'Full')->getStyle("J2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("J" . ($detail_table_header_row + 1) . ":J" . ($detail_table_header_row + 2));
        $sheet->setCellValue("K" . ($detail_table_header_row + 1), 'Empty')->getStyle("K2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("K" . ($detail_table_header_row + 1) . ":K" . ($detail_table_header_row + 2));
        $sheet->setCellValue("L$detail_table_header_row", 'No Job')->getStyle("L1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:L" . ($detail_table_header_row + 2));
        $sheet->setCellValue("M$detail_table_header_row", 'Omset')->getStyle("M1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("M$detail_table_header_row:M" . ($detail_table_header_row + 2));
        $sheet->setCellValue("N$detail_table_header_row", 'Inv')->getStyle("N1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("N$detail_table_header_row:N" . ($detail_table_header_row + 2));
        $sheet->setCellValue("O$detail_table_header_row", 'Gaji')->getStyle("O1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("O" . ($detail_table_header_row + 1), 'Borongan')->getStyle("O2")->applyFromArray($styleHeader)->getFont()->setBold(true);

        $sheet->setCellValue("R2", 'Komisi')->getStyle("R2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("T2", 'Lain')->getStyle("T2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("T3", 'Extra')->getStyle("T3")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->setCellValue("V3", 'Ritasi')->getStyle("V3")->applyFromArray($styleHeader)->getFont()->setBold(true);

        $sheet->setCellValue("W" . ($detail_table_header_row + 1), 'Uang Makan')->getStyle("W2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("W" . ($detail_table_header_row + 1) . ":W" . ($detail_table_header_row + 2));
        $sheet->setCellValue("X3", 'Ket')->getStyle("X3")->applyFromArray($styleHeader)->getFont()->setBold(true);

        $sheet->setCellValue("Y1", 'Biaya')->getStyle("Y1")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("Y1" . ":Z1");

        $sheet->setCellValue("Y" . ($detail_table_header_row + 1), 'Uang Jalan')->getStyle("Y2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("Y" . ($detail_table_header_row + 1) . ":Y" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Z" . ($detail_table_header_row + 1), 'BBM')->getStyle("Z2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("Z" . ($detail_table_header_row + 1) . ":Z" . ($detail_table_header_row + 2));

        $sheet->setCellValue("AB" . ($detail_table_header_row + 1), 'Total Biaya')->getStyle("AB2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("AB" . ($detail_table_header_row + 1) . ":AB" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AC" . ($detail_table_header_row + 1), 'Sisa')->getStyle("AC2")->applyFromArray($styleHeader)->getFont()->setBold(true);
        $sheet->mergeCells("AC" . ($detail_table_header_row + 1) . ":AC" . ($detail_table_header_row + 1));


        $rowIndex = 4;
        $previous_nopol = null;
        // $group = [];
        $groupRowCount = 0;
        $sheet->setCellValue("G4", "periode : " . $dari . " s/d " . $sampai);
        foreach ($data as $response_index => $response_detail) {
            $nopol = $response_detail->nopol;

            if ($nopol != $previous_nopol) {
                if ($previous_nopol !== null) {
                    // $rowIndex++; // Move to the next row
                    // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

                    // Calculate the total for the previous group and set it in the next column
                    $startTotalIndex = $rowIndex - $groupRowCount;
                    $endTotalIndex = $rowIndex - 1;

                    $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("O$rowIndex", "=SUM(O$startTotalIndex:O$endTotalIndex)")->getStyle("O$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("R$rowIndex", "=SUM(R$startTotalIndex:R$endTotalIndex)")->getStyle("R$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("W$rowIndex", "=SUM(W$startTotalIndex:W$endTotalIndex)")->getStyle("W$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("AC$rowIndex", "=SUM(AC$startTotalIndex:AC$endTotalIndex)")->getStyle("AC$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("AB$rowIndex", "=SUM(AB$startTotalIndex:AB$endTotalIndex)")->getStyle("AB$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $groupRowCount = 0;
                    $rowIndex++;
                    $rowIndex++; // Move to the next row
                }
                $sheet->setCellValue("D$rowIndex", $nopol);
                $rowIndex++;

                // Store the starting row index of the current group
                $groupStartIndex = $rowIndex;
            }
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

            $sheet->setCellValue("A$rowIndex", $dateValue);
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("B$rowIndex", $response_detail->gandengan);
            $sheet->setCellValue("C$rowIndex", $response_detail->nopol);
            $sheet->setCellValue("D$rowIndex", $response_detail->namasupir);
            $sheet->setCellValue("E$rowIndex", $response_detail->rute);
            $sheet->setCellValue("F$rowIndex", $response_detail->qty);
            $sheet->setCellValue("G$rowIndex", $response_detail->lokasimuat);
            $sheet->setCellValue("H$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("I$rowIndex", $response_detail->emkl);
            $sheet->setCellValue("J$rowIndex", $response_detail->spfull);
            $sheet->setCellValue("K$rowIndex", $response_detail->spempty);
            $sheet->setCellValue("L$rowIndex", $response_detail->jobtrucking);
            $sheet->setCellValue("M$rowIndex", $response_detail->omsetmedan)->getStyle("M$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("N$rowIndex", $response_detail->invoice);
            $sheet->setCellValue("O$rowIndex", $response_detail->gajisupir)->getStyle("O$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("P$rowIndex", $response_detail->nobuktiebs);
            $sheet->setCellValue("Q$rowIndex", $response_detail->pengeluarannobuktiebs);
            $sheet->setCellValue("R$rowIndex", $response_detail->komisi)->getStyle("R$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("T$rowIndex", $response_detail->uangextra)->getStyle("T$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("V$rowIndex", $response_detail->ritasi)->getStyle("V$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("W$rowIndex", $response_detail->uangmakan)->getStyle("W$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("X$rowIndex", $response_detail->ketritasi);
            $sheet->setCellValue("Y$rowIndex", $response_detail->uangjalan)->getStyle("Y$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Z$rowIndex", $response_detail->uangbbm)->getStyle("Z$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");


            $sheet->setCellValue("AB$rowIndex", "=O$rowIndex+R$rowIndex+T$rowIndex+V$rowIndex+W$rowIndex")->getStyle("AB$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AC$rowIndex", "=M$rowIndex-AB$rowIndex")->getStyle("AC$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AD$rowIndex", $response_detail->nobukti);

            $rowIndex++;

            // Store the current group details in an array
            $group[] = $response_detail;
            $sheet->getColumnDimension('A')->setWidth(7);
            $previous_nopol = $nopol;
            $groupRowCount++;
        }

        // Add total and calculate the total for the last group
        if ($previous_nopol !== null) {
            // $rowIndex++;
            // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

            $startTotalIndex = $groupStartIndex;
            $endTotalIndex = $rowIndex - 1;

            $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("O$rowIndex", "=SUM(O$startTotalIndex:O$endTotalIndex)")->getStyle("O$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("R$rowIndex", "=SUM(R$startTotalIndex:R$endTotalIndex)")->getStyle("R$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("W$rowIndex", "=SUM(W$startTotalIndex:W$endTotalIndex)")->getStyle("W$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AC$rowIndex", "=SUM(AC$startTotalIndex:AC$endTotalIndex)")->getStyle("AC$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AB$rowIndex", "=SUM(AB$startTotalIndex:AB$endTotalIndex)")->getStyle("AB$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        }
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);



        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN MINGGUAN SUPIR' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function export2old($data, $periode, $jenis)
    {
        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $detail_table_header_row = 1;
        $detail_start_row = $detail_table_header_row + 1;

        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        $styleHeader = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $style_number = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];

        $styleArray2 = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        $sheet->setCellValue("A$detail_table_header_row", 'Tanggal')->getStyle("A1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("A$detail_table_header_row:A" . ($detail_table_header_row + 2));
        $sheet->setCellValue("B$detail_table_header_row", 'No')->getStyle("B1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("B$detail_table_header_row:B" . ($detail_table_header_row + 2));
        $sheet->setCellValue("C$detail_table_header_row", 'Rute')->getStyle("C1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("C$detail_table_header_row:C" . ($detail_table_header_row + 2));
        $sheet->setCellValue("D$detail_table_header_row", 'Qty')->getStyle("D1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("D$detail_table_header_row:D" . ($detail_table_header_row + 2));
        $sheet->setCellValue("E$detail_table_header_row", 'Lokasi Muat')->getStyle("E1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("E$detail_table_header_row:E" . ($detail_table_header_row + 2));
        $sheet->setCellValue("F$detail_table_header_row", 'No Container/Seal')->getStyle("F1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("F$detail_table_header_row:F" . ($detail_table_header_row + 2));
        $sheet->setCellValue("G$detail_table_header_row", 'EMKL')->getStyle("G1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("G$detail_table_header_row:G" . ($detail_table_header_row + 2));
        $sheet->setCellValue("H$detail_table_header_row", 'No SP')->getStyle("H1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("H$detail_table_header_row:J$detail_table_header_row");
        $sheet->setCellValue("H" . ($detail_table_header_row + 1), 'Full')->getStyle("H2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("H" . ($detail_table_header_row + 1) . ":H" . ($detail_table_header_row + 2));
        $sheet->setCellValue("I" . ($detail_table_header_row + 1), 'Empty')->getStyle("I2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("I" . ($detail_table_header_row + 1) . ":I" . ($detail_table_header_row + 2));
        $sheet->setCellValue("J" . ($detail_table_header_row + 1), 'Full/Empty')->getStyle("J2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("J" . ($detail_table_header_row + 1) . ":J" . ($detail_table_header_row + 2));
        $sheet->setCellValue("K$detail_table_header_row", 'No Job')->getStyle("K1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("K$detail_table_header_row:K" . ($detail_table_header_row + 2));
        $sheet->setCellValue("L$detail_table_header_row", 'Omset')->getStyle("L1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("L$detail_table_header_row:L" . ($detail_table_header_row + 2));
        $sheet->setCellValue("M$detail_table_header_row", 'Omset Tambahan')->getStyle("M1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("M$detail_table_header_row:M" . ($detail_table_header_row + 2));
        $sheet->setCellValue("N$detail_table_header_row", 'Total Omset')->getStyle("N1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("N$detail_table_header_row:N" . ($detail_table_header_row + 2));
        $sheet->setCellValue("O$detail_table_header_row", 'Inv')->getStyle("O1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("O$detail_table_header_row:O" . ($detail_table_header_row + 2));
        $sheet->setCellValue("P$detail_table_header_row", 'Biaya Operasional')->getStyle("P1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("P$detail_table_header_row:W$detail_table_header_row");
        $sheet->setCellValue("P" . ($detail_table_header_row + 1), 'Borongan')->getStyle("P2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("P" . ($detail_table_header_row + 1) . ":P" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Q" . ($detail_table_header_row + 1), 'EBS')->getStyle("Q2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Q" . ($detail_table_header_row + 1) . ":Q" . ($detail_table_header_row + 2));
        $sheet->setCellValue("R" . ($detail_table_header_row + 1), 'No Pengeluaran EBS')->getStyle("R2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("R" . ($detail_table_header_row + 1) . ":R" . ($detail_table_header_row + 2));
        $sheet->setCellValue("S" . ($detail_table_header_row + 1), 'Komisi Supir')->getStyle("S2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("S" . ($detail_table_header_row + 1) . ":S" . ($detail_table_header_row + 2));
        $sheet->setCellValue("T" . ($detail_table_header_row + 1), 'Komisi Kenek ')->getStyle("T2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("T" . ($detail_table_header_row + 1) . ":T" . ($detail_table_header_row + 2));

        $sheet->setCellValue("U" . ($detail_table_header_row + 1), 'No Bukti Komisi')->getStyle("U2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("U" . ($detail_table_header_row + 1) . ":U" . ($detail_table_header_row + 2));
        $sheet->setCellValue("V" . ($detail_table_header_row + 1), 'G. LAIN')->getStyle("V2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("V" . ($detail_table_header_row + 1) . ":V" . ($detail_table_header_row + 2));
        $sheet->setCellValue("W" . ($detail_table_header_row + 1), '')->getStyle("W")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("W" . ($detail_table_header_row + 1) . ":W" . ($detail_table_header_row + 2));
        $sheet->setCellValue("X" . ($detail_table_header_row + 1), 'Ket')->getStyle("X2")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("X" . ($detail_table_header_row + 1) . ":X" . ($detail_table_header_row + 2));

        $sheet->setCellValue("Y$detail_table_header_row", 'Total Biaya')->getStyle("Y1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Y$detail_table_header_row:Y" . ($detail_table_header_row + 2));
        $sheet->setCellValue("Z$detail_table_header_row", 'Laba')->getStyle("Z1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("Z$detail_table_header_row:Z" . ($detail_table_header_row + 2));
        $sheet->setCellValue("AA$detail_table_header_row", 'No Trip')->getStyle("AA1")->applyFromArray($styleHeader)->getFont()->setSize(14)->setBold(true);
        $sheet->mergeCells("AA$detail_table_header_row:AA" . ($detail_table_header_row + 2));

        $rowIndex = 4;
        $previous_nopol = null;
        // $group = [];
        $groupRowCount = 0;
        $sheet->setCellValue("A4", "periode : " . $dari . " s/d " . $sampai);
        foreach ($data as $response_index => $response_detail) {
            $nopol = $response_detail->nopol;

            if ($nopol != $previous_nopol) {
                if ($previous_nopol !== null) {
                    // $rowIndex++; // Move to the next row
                    // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

                    // Calculate the total for the previous group and set it in the next column
                    $startTotalIndex = $rowIndex - $groupRowCount;
                    $endTotalIndex = $rowIndex - 1;

                    $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("N$rowIndex", "=SUM(N$startTotalIndex:N$endTotalIndex)")->getStyle("N$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("L$rowIndex", "=SUM(L$startTotalIndex:L$endTotalIndex)")->getStyle("L$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("P$rowIndex", "=SUM(P$startTotalIndex:P$endTotalIndex)")->getStyle("P$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("S$rowIndex", "=SUM(S$startTotalIndex:S$endTotalIndex)")->getStyle("S$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("T$rowIndex", "=SUM(T$startTotalIndex:T$endTotalIndex)")->getStyle("T$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
                    $groupRowCount = 0;
                    $rowIndex++;
                    $rowIndex++; // Move to the next row
                }
                $sheet->setCellValue("B$rowIndex", $nopol);
                $rowIndex++;

                // Store the starting row index of the current group
                $groupStartIndex = $rowIndex;
            }
            $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';

            $sheet->setCellValue("A$rowIndex", $dateValue);
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("B$rowIndex", $response_detail->namasupir);
            $sheet->setCellValue("C$rowIndex", $response_detail->rute);
            $sheet->setCellValue("D$rowIndex", $response_detail->qty);
            $sheet->setCellValue("E$rowIndex", $response_detail->lokasimuat);
            $sheet->setCellValue("F$rowIndex", $response_detail->nocontseal);
            $sheet->setCellValue("G$rowIndex", $response_detail->emkl);
            $sheet->setCellValue("H$rowIndex", $response_detail->spfull);
            $sheet->setCellValue("I$rowIndex", $response_detail->spempty);
            $sheet->setCellValue("J$rowIndex", $response_detail->spfullempty);
            $sheet->setCellValue("K$rowIndex", $response_detail->jobtrucking);
            $sheet->setCellValue("L$rowIndex", $response_detail->omset)->getStyle("L$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("M$rowIndex", $response_detail->omsettambahan)->getStyle("M$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("N$rowIndex", "=(L$rowIndex+M$rowIndex)")->getStyle("N$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("O$rowIndex", $response_detail->invoice);
            $sheet->setCellValue("P$rowIndex", $response_detail->borongan)->getStyle("P$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Q$rowIndex", $response_detail->nobuktiebs);
            $sheet->setCellValue("R$rowIndex", $response_detail->pengeluarannobuktiebs);
            $sheet->setCellValue("S$rowIndex", $response_detail->komisi)->getStyle("S$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("T$rowIndex", $response_detail->gajikenek)->getStyle("T$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

            $sheet->setCellValue("U$rowIndex", $response_detail->nobuktikbtkomisi);
            $sheet->setCellValue("V$rowIndex", $response_detail->uanglain)->getStyle("V$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");

            $sheet->setCellValue("W$rowIndex", $response_detail->nobuktikbtebs2);
            $sheet->setCellValue("X$rowIndex", $response_detail->ketuanglain);
            $sheet->setCellValue("Y$rowIndex", "=P$rowIndex+S$rowIndex+T$rowIndex+V$rowIndex")->getStyle("Y$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Z$rowIndex", "=N$rowIndex-Y$rowIndex")->getStyle("Z$rowIndex")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("AA$rowIndex", $response_detail->nobukti);
            $rowIndex++;

            // Store the current group details in an array
            $group[] = $response_detail;
            $sheet->getColumnDimension('A')->setWidth(12);
            $previous_nopol = $nopol;
            $groupRowCount++;
        }

        // Add total and calculate the total for the last group
        if ($previous_nopol !== null) {
            // $rowIndex++;
            // $sheet->setCellValue("A$rowIndex", 'Total')->getStyle("A$rowIndex")->applyFromArray($styleHeader)->getFont()->setBold(true);

            $startTotalIndex = $groupStartIndex;
            $endTotalIndex = $rowIndex - 1;

            $sheet->setCellValue("M$rowIndex", "=SUM(M$startTotalIndex:M$endTotalIndex)")->getStyle("M$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("N$rowIndex", "=SUM(N$startTotalIndex:N$endTotalIndex)")->getStyle("N$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("L$rowIndex", "=SUM(L$startTotalIndex:L$endTotalIndex)")->getStyle("L$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("P$rowIndex", "=SUM(P$startTotalIndex:P$endTotalIndex)")->getStyle("P$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("S$rowIndex", "=SUM(S$startTotalIndex:S$endTotalIndex)")->getStyle("S$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("T$rowIndex", "=SUM(T$startTotalIndex:T$endTotalIndex)")->getStyle("T$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("V$rowIndex", "=SUM(V$startTotalIndex:V$endTotalIndex)")->getStyle("V$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Y$rowIndex", "=SUM(Y$startTotalIndex:Y$endTotalIndex)")->getStyle("Y$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
            $sheet->setCellValue("Z$rowIndex", "=SUM(Z$startTotalIndex:Z$endTotalIndex)")->getStyle("Z$rowIndex")->applyFromArray($styleArray2)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)-0;;@");
        }
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
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LAPORAN MINGGUAN SUPIR' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
