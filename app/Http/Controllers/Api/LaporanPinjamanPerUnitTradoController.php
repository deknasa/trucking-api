<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanPinjamanUnitTradoRequest;
use App\Models\LaporanPinjamanPerUnitTrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LaporanPinjamanPerUnitTradoController extends Controller
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
    public function report(ValidasiLaporanPinjamanUnitTradoRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
            $trado_id = $request->trado_id;

            $laporanPinjaman = new LaporanPinjamanPerUnitTrado();
            $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
                ->select('cabang.namacabang')
                ->join("parameter", 'parameter.text', 'cabang.id')
                ->where('parameter.grp', 'ID CABANG')
                ->first();
            return response([
                'data' => $laporanPinjaman->getReport($trado_id),
                'namacabang' => 'CABANG ' . $getCabang->namacabang
            ]);
        }
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanPinjamanUnitTradoRequest $request)
    {
        $trado_id = $request->trado_id;
        $trado = $request->trado;

        $laporanPinjaman = new LaporanPinjamanPerUnitTrado();
        $laporan_Pinjaman = $laporanPinjaman->getReport($trado_id);

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        // return response([
        //     'data' => $laporanPinjaman->getReport($trado_id),
        //     'namacabang' => 'CABANG ' . $getCabang->namacabang
        // ]);

        $data = json_decode($laporan_Pinjaman);
        $namacabang = 'CABANG ' . $getCabang->namacabang;
        $disetujui = $pengeluaran[0]->disetujui ?? '';
        $diperiksa = $pengeluaran[0]->diperiksa ?? '';

        //PRINT TO EXCEL
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet->setCellValue('A1', strtoupper($data[0]->judul));
        $sheet->getStyle("A1")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A2', strtoupper($namacabang));
        $sheet->getStyle("A2")->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A2:D2');

        $sheet->setCellValue('A3', strtoupper($data[0]->judulLaporan));
        $sheet->getStyle("A3")->getFont()->setBold(true);
        $sheet->mergeCells('A3:D3');

        $sheet->setCellValue('A4', strtoupper('Trado : ' . $trado));
        $sheet->getStyle("A4")->getFont()->setBold(true);
        $sheet->mergeCells('A4:D4');

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

            // 'borders' => [
            //     'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            //     'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            //     'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            //     'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            // ]
        ];


        $alphabets = range('A', 'Z');


        $currentRow = 5;
        $currentSupir = '';
        $totalNominal = 0;
        $barisSupir = 0;

        foreach ($data as $item) {
            if ($item->namasupir !== $currentSupir) {
                if ($currentSupir !== '') {
                    $currentRow++;
                    $sheet->mergeCells('A' . ($currentRow - 1) . ':C' . ($currentRow - 1));
                    $sheet->setCellValue('A' . ($currentRow - 1), 'Total Pinjaman ' . $currentSupir);
                    $sheet->setCellValue('D' . ($currentRow - 1), $totalNominal);
                    $sheet->getStyle('A' . ($currentRow - 1) . ':D' . ($currentRow - 1))->applyFromArray($styleArray)->getFont()->setBold(true);
                    $sheet->getStyle('D' . ($currentRow - 1))->applyFromArray($style_number)->getFont()->setBold(true);
                    $sheet->getStyle('A' . ($currentRow - 1) . ':D' . ($currentRow - 1))->getNumberFormat()->setFormatCode("#,##0.00");
                }

                $currentSupir = $item->namasupir;
                $totalNominal = 0;
                $barisSupir = $currentRow;

                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, $currentSupir);
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, 'No Bukti');
                $sheet->setCellValue('B' . $currentRow, 'Tanggal');
                $sheet->setCellValue('C' . $currentRow, 'Keterangan');
                $sheet->setCellValue('D' . $currentRow, 'Saldo');
                $headerStyle = $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->applyFromArray($styleArray);
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $currentRow++;
                $barisSupir = $currentRow;
            }

            $sheet->setCellValue('A' . $currentRow, $item->pengeluarantrucking_nobukti);

            $dateValue = ($item->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($item->tglbukti))) : '';
            $sheet->setCellValue("B$currentRow", $dateValue);
            $sheet->getStyle("B$currentRow")
                ->getNumberFormat()
                ->setFormatCode('dd-mm-yyyy');

            $sheet->setCellValue('C' . $currentRow, $item->keterangan);
            $sheet->setCellValue('D' . $currentRow, $item->nominal);
            $sheet->getStyle("D$currentRow")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            $sheet->getStyle("C$currentRow")->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('C')->setWidth(160);
            $totalNominal += $item->nominal;
            $currentRow++;
        }
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Total Pinjaman ' . $currentSupir);
        $sheet->setCellValue('D' . $currentRow, "=SUM(D$barisSupir:D" . ($currentRow - 1) . ")");
        $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->applyFromArray($styleArray)->getFont()->setBold(true);
        $sheet->getStyle('D' . $currentRow)->applyFromArray($style_number)->getFont()->setBold(true);
        $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(75);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'LaporanPinjamanPerUnitTrado' . date('dmYHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
