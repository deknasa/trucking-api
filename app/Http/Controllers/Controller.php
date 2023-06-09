<?php

namespace App\Http\Controllers;

use App\Events\UpdateExportProgress;
use App\Helpers\App as AppHelper;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use stdClass;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Instance of app helper
     * 
     * @var \App\Helpers\App
     */
    public $appHelper;

    public function __construct()
    {
        $this->appHelper = new AppHelper();
    }

    public function getRunningNumber(Request $request)
    {
        $request->validate([
            'group' => 'required',
            'subgroup' => 'required',
            'table' => 'required',
            'tgl' => 'required',
        ]);



        $parameter = DB::table('parameter')
            ->select(
                DB::raw(
                    "parameter.id,
                    parameter.text,
                    isnull(type.text,'') as type"
                )

            )
            ->leftJoin('parameter as type', 'parameter.type', 'type.id')
            ->where('parameter.grp', $request->group)
            ->where('parameter.subgrp', $request->subgroup)
            ->first();


        if (!isset($parameter->text)) {
            return response([
                'status' => false,
                'message' => 'Parameter tidak ditemukan'
            ]);
        }
        $bulan = date('n', strtotime($request->tgl));
        $tahun = date('Y', strtotime($request->tgl));

        $statusformat = $parameter->id;
        $text = $parameter->text;
        $type = $parameter->type;

        if ($type == 'RESET BULAN') {
            $lastRow = DB::table($request->table)
                ->where(DB::raw('month(tglbukti)'), '=', $bulan)
                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->lockForUpdate()->count();
        }

        if ($type == 'RESET TAHUN') {
            $lastRow = DB::table($request->table)
                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->lockForUpdate()->count();
        }
        if ($type == '') {
            $lastRow = DB::table($request->table)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->lockForUpdate()->count();
        }


        $runningNumber = $this->appHelper->runningNumber($text, $lastRow, $bulan);
        // dd($runningNumber);
        $nilai = 0;
        $nomor = $lastRow;
        while ($nilai < 1) {
            $cekbukti = DB::table($request->table)
                ->where(DB::raw('nobukti'), '=', $runningNumber)
                ->first();
            if (!isset($cekbukti)) {
                $nilai++;
                break;
            }
            $nomor++;
            $runningNumber = $this->appHelper->runningNumber($text, $nomor, $bulan);
        }





        return response([
            'status' => true,
            'data' => $runningNumber
        ]);
    }

    /* Compatible for single table */
    public function toExcel(string $Laporan, array $data, array $columns)
    {
        header('Access-Control-Allow-Origin: *');



        $tableHeaderRow = 4;
        $startRow = $tableHeaderRow + 1;
        $alphabets = range('A', 'Z');

        for ($i = 'A'; $i <= 'B'; $i++) {
            for ($j = 'A'; $j <= 'Z'; $j++) {
                $alphabets[] = $i . $j;
            }
        }
        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );



        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', $data[0]['judul']);
        $sheet->getStyle("A1")->getFont()->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:' . $alphabets[count($columns) - 1] . '1');

        $sheet->setCellValue('A2', $Laporan);
        $sheet->getStyle("A2")->getFont()->setSize(12);
        $sheet->mergeCells('A2:' . $alphabets[count($columns) - 1] . '2');


        /* Set the table header */
        foreach ($columns as $columnsIndex => $column) {
            $sheet->setCellValue($alphabets[$columnsIndex] . $tableHeaderRow, $column['label'] ?? $columnsIndex + 1);

            $sheet->getColumnDimension($alphabets[$columnsIndex])->setAutoSize(true);
        }

        /* Set the table header style */
        $sheet
            ->getStyle("A$tableHeaderRow:" . $alphabets[count($columns) - 1] . "$tableHeaderRow")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->applyFromArray($styleArray);

        // ->setARGB('FF02c4f5');



        $totalRows = count($data);

        /* Write each cell */
        foreach ($data as $dataIndex => $row) {
            $progress = ($dataIndex + 1) * 100 / $totalRows;
            event(new UpdateExportProgress($progress));

            foreach ($columns as $columnsIndex => $column) {

                $sheet->setCellValue($alphabets[$columnsIndex] . $tableHeaderRow, $column['label'] ?? $columnsIndex + 1);

                if (
                    isset($column['index']) && $column['index'] == 'tgl' ||
                    isset($column['index']) && $column['index'] == 'tglasuransimati' ||
                    isset($column['index']) && $column['index'] == 'tglserviceopname' || isset($column['index']) && $column['index'] == 'tglpajakstnk' ||
                    isset($column['index']) && $column['index'] == 'tglgantiakiterakhir'
                ) {
                    if (isset($row[$column['index']])) {
                        if (!$row[$column['index']]) {
                            $value = '';
                        } else {
                            $value = date('d-m-Y', strtotime($row[$column['index']]));
                        }
                    } else {
                        $value = $dataIndex + 1;
                    }

                    $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, $value);

                    $sheet->getStyle($alphabets[$columnsIndex] . $startRow)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                } else {
                    // $sheet->setCellValue($alphabets[$columnsIndex] . $tableHeaderRow, $column['label'] ?? $columnsIndex + 1);
                    $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, isset($column['index']) ? $row[$column['index']] : $dataIndex + 1);
                }
            }

            $startRow++;
        }

        /* Set border for all cells */
        $sheet
            ->getStyle("A$tableHeaderRow:" . $alphabets[count($columns) - 1] . ($startRow - 1))
            ->applyFromArray($styleArray);

        /* Write to excel, then download the file */
        $writer = new Xlsx($spreadsheet);
        $filename = $Laporan . date('dmYHis');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    /**
     * Get data position after
     * add, edit, or delete
     * 
     * @param Model $model
     * @param string $modelTable
     * 
     * @return mixed
     */
    function getPosition(Model $model, string $modelTable, bool $isDeleting = false)
    {
        $data = new stdClass();

        $indexRow = request()->indexRow ?? 1;
        $limit = request()->limit ?? 10;
        $page = request()->page ?? 1;

        $temporaryTable = $model->createTemp($modelTable);

        if ($isDeleting) {
            if ($page == 1) {
                $position = $indexRow + 1;
            } else {
                $page = $page - 1;
                $row = $page * $limit;
                $position = $indexRow + $row + 1;
            }

            if (!DB::table($temporaryTable)->where('position', '=', $position)->exists()) {
                $position -= 1;
            }

            $query = DB::table($temporaryTable)
                ->select('position', 'id')
                ->where('position', '=', $position)
                ->orderBy('position');
        } else {
            if ($modelTable == 'acl') {
                $query = DB::table($temporaryTable)->select('position')->where('id', $model->role_id)->orderBy('position');
            } else {
                $query = DB::table($temporaryTable)->select('position')->where('id', $model->id)->orderBy('position');
            }
        }

        if ($query->first() == null) {
            $data->position = 0;
            $data->id = 0;
        } else {
            $data = $query->first();
        }
        return $data;
    }
}
