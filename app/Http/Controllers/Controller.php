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
                ->count();
        }

        if ($type == 'RESET TAHUN') {
            $lastRow = DB::table($request->table)
                ->where(DB::raw('year(tglbukti)'), '=', $tahun)
                ->where(DB::raw('statusformat'), '=', $statusformat)
                ->count();
        }
        if ($type == '') {
            $lastRow = DB::table($request->table)
            ->where(DB::raw('statusformat'), '=', $statusformat)
            ->count();
        }

        // 

        // $staticformat='|';
        // $awal=0;

        // for ($i = 0; $i < strlen($text ); $i++) {
        //     if ($text[$i] == $staticformat AND $awal==0)  {
        //         $awal=$i;
        //     }
        //     if ($awal!=0) {
        //         if ($text[$i] == $staticformat) {
        //             $akhir=$i;
        //         }

        //     }
        // }

        // $posisi=$awal+2;
        // $jumlah=($akhir-$awal)-1;

        // $awal=$awal+1;

        // $nobukti=substr( $text,$awal,$jumlah);


        // // 
        //         $lennobukti=strlen($nobukti);



        //         if ($lennobukti==0) {
        //             $lastRow = DB::table($request->table)
        //             ->where(DB::raw('month(tglbukti)'),'=',$bulan)
        //             ->where(DB::raw('year(tglbukti)'),'=',$tahun)
        //              ->count();
        //         } else {

        //             $runningNumberuji = $this->appHelper->runningNumber($text, 0,$bulan);
        //             $lastRow = DB::table($request->table)
        //             ->where(DB::raw('month(tglbukti)'),'=',$bulan)
        //             ->where(DB::raw('year(tglbukti)'),'=',$tahun)
        //             ->where(DB::raw("substring(nobukti,CHARINDEX('".$nobukti."','". $runningNumberuji."')".','.$jumlah.')'),'=',$nobukti)
        //             ->count();
        //         }

        // dd($lastRow);
        $runningNumber = $this->appHelper->runningNumber($text, $lastRow, $bulan);

        // dd($runningNumber);
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
            $query = DB::table($temporaryTable)->select('position')->where('id', $model->id)->orderBy('position');
        }

        $data = $query->first();

        return $data;
    }
}
