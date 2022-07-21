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
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        ]);

        $parameter = DB::table('parameter')
            ->where('grp', $request->group)
            ->where('subgrp', $request->subgroup)
            ->first();

        if (!isset($parameter->text)) {
            return response([
                'status' => false,
                'message' => 'Parameter tidak ditemukan'
            ]);
        }
        $bulan = date('n', strtotime($request->tgl));
        $tahun = date('Y', strtotime($request->tgl));

        $text = $parameter->text;
        $lastRow = DB::table($request->table)
            // ->where('month(tgl)','=',$bulan)
            // ->where('year(tgl)','=',$tahun)
            ->count();
        $runningNumber = $this->appHelper->runningNumber($text, $lastRow);

        return response([
            'status' => true,
            'data' => $runningNumber
        ]);
    }

    /* Compatible for single table */
    public function toExcel(string $title, array $data, array $columns)
    {
        header('Access-Control-Allow-Origin: *');

        $tableHeaderRow = 2;
        $startRow = $tableHeaderRow + 1;
        $alphabets = range('A', 'Z');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Laporan ' . $title);
        $sheet->getStyle("A1")->getFont()->setSize(20);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:' . $alphabets[count($columns) - 1] . '1');

        /* Set the table header */
        foreach ($columns as $columnsIndex => $column) {
            $sheet->setCellValue($alphabets[$columnsIndex] . $tableHeaderRow, $column['label'] ?? $columnsIndex + 1);
        }

        /* Set the table header style */
        $sheet
            ->getStyle("A$tableHeaderRow:" . $alphabets[count($columns) - 1] . "$tableHeaderRow")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF02c4f5');

        $totalRows = count($data);

        /* Write each cell */
        foreach ($data as $dataIndex => $row) {
            $progress = ($dataIndex + 1) * 100 / $totalRows;
            event(new UpdateExportProgress($progress));

            foreach ($columns as $columnsIndex => $column) {
                $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, isset($column['index']) ? $row[$column['index']] : $dataIndex + 1);
            }

            $startRow++;
        }

        /* Write to excel, then download the file */
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan' . $title . date('dmYHis');

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
        $indexRow = request()->indexRow ?? 1;
        $limit = request()->limit ?? 10;
        $page = request()->page ?? 1;
        $sortname = request()->sortIndex ?? "id";
        $sortorder = request()->sortOrder ?? "asc";

        $temporaryTable = '##temp' . rand(1, 10000);
        $columns = Schema::getColumnListing($modelTable);

        $query = DB::table($modelTable);
        
        $model->setRequestParameters();
        
        $models = $model->sort($query);

        Schema::create($temporaryTable, function (Blueprint $table) use ($columns) {
            $table->increments('position');

            foreach ($columns as $column) {
                if (in_array($column, ['created_at', 'updated_at'])) {
                    $table->dateTime($column)->default('1900/1/1');
                } else {
                    $table->string($column, 3000)->nullable();
                }
            }

            $table->index('id');
        });

        DB::table($temporaryTable)->insertUsing($columns, $models);

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
            $query = DB::table($temporaryTable)->select('position')->where('id', $model->id)->orderBy('position');
        }
        
        $data = $query->first();

        return $data;
    }
}
