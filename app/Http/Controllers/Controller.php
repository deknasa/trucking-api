<?php

namespace App\Http\Controllers;

use App\Events\UpdateExportProgress;
use App\Helpers\App as AppHelper;
use App\Models\Cabang;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
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
        $sheet->getStyle("A1")->getFont()->setSize(12);
        $sheet->getStyle("A1")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:' . $alphabets[count($columns) - 1] . '1');

        $sheet->setCellValue('A2', $Laporan);
        $sheet->getStyle("A2")->getFont()->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A2")->getFont()->setBold(true);
        $sheet->mergeCells('A2:' . $alphabets[count($columns) - 1] . '2');

        $i = 0;
        foreach ($columns as &$kolom) {
            if (isset($kolom['label'])) {
                $kolom['label'] = strtoupper($kolom['label']);

                $label[$i] = strtoupper($kolom['label']);
                $i++;
            }
        }

        /* Set the table header */
        foreach ($columns as $columnsIndex => $column) {
            $sheet->setCellValue($alphabets[$columnsIndex] . $tableHeaderRow, $label[$i] ?? $columnsIndex + 1);

            $sheet->getColumnDimension($alphabets[$columnsIndex])->setAutoSize(true);
        }

        /* Set the table header style */
        $sheet
            ->getStyle("A$tableHeaderRow:" . $alphabets[count($columns) - 1] . "$tableHeaderRow")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->applyFromArray($styleArray);

        $sheet
            ->getStyle("A$tableHeaderRow:" . $alphabets[count($columns) - 1] . "$tableHeaderRow")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


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
                    isset($column['index']) && $column['index'] == 'tglserviceopname' || isset($column['index']) && $column['index'] == 'tglmasuk' ||
                    isset($column['index']) && $column['index'] == 'tglexpsim' ||
                    isset($column['index']) && $column['index'] == 'tglberhentisupir' ||
                    isset($column['index']) && $column['index'] == 'tgllahir' ||
                    isset($column['index']) && $column['index'] == 'tglterbitsim' || isset($column['index']) && $column['index'] == 'tglapproval' || isset($column['index']) && $column['index'] == 'tglabsensi'

                ) {
                    if (isset($row[$column['index']])) {
                        // dd(substr($row[$column['index']],0,4));
                        if (substr($row[$column['index']], 0, 4) == '1900') {
                            $value = '';
                            $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, $value);
                        } else {
                            $value = date('d-m-Y', strtotime($row[$column['index']]));

                            $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, $value);
                            $sheet->getStyle($alphabets[$columnsIndex] . $startRow)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                        }
                    } else {
                        $value = $dataIndex + 1;
                    }
                } elseif (
                    isset($column['index']) && $column['index'] == 'kmawal' ||
                    isset($column['index']) && $column['index'] == 'kmakhirgantioli' ||
                    isset($column['index']) && $column['index'] == 'tahun' ||
                    isset($column['index']) && $column['index'] == 'isisilinder' ||
                    isset($column['index']) && $column['index'] == 'jumlahsumbu' ||
                    isset($column['index']) && $column['index'] == 'jumlahroda' ||
                    isset($column['index']) && $column['index'] == 'jumlahbanserap' ||
                    isset($column['index']) && $column['index'] == 'nominaldepositsa' ||
                    isset($column['index']) && $column['index'] == 'depositke' ||
                    isset($column['index']) && $column['index'] == 'nominalpinjamansaldoawal' ||
                    isset($column['index']) && $column['index'] == 'supirrold_id'

                ) {
                    $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, isset($column['index']) ? $row[$column['index']] : $dataIndex + 1);
                    $sheet->getStyle($alphabets[$columnsIndex] . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                } elseif (
                    isset($column['index']) && $column['index'] == 'nosim' ||
                    isset($column['index']) && $column['index'] == 'noktp' ||
                    isset($column['index']) && $column['index'] == 'nokk'

                ) {
                    $sheet->setCellValueExplicit($alphabets[$columnsIndex] . $startRow, isset($column['index']) ? $row[$column['index']] : $dataIndex + 1, DataType::TYPE_STRING);
                } elseif (
                    isset($column['index']) && $column['index'] == 'nominalsumbangan' ||
                    isset($column['index']) && $column['index'] == 'nominal'
                ) {
                    $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, isset($column['index']) ? $row[$column['index']] : $dataIndex + 1);
                    $sheet->getStyle($alphabets[$columnsIndex] . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($alphabets[$columnsIndex] . $startRow)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                } else {
                    // $sheet->setCellValue($alphabets[$columnsIndex] . $tableHeaderRow, $column['label'] ?? $columnsIndex + 1);
                    $sheet->setCellValue($alphabets[$columnsIndex] . $startRow, isset($column['index']) ? $row[$column['index']] : $dataIndex + 1);
                    $sheet->getStyle($alphabets[$columnsIndex] . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
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

    function get_client_ip()
    {


        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'IP tidak dikenali';
        if ($ipaddress == '::1') {
            $ipaddress = gethostbyname(env('APP_HOSTNAME'));
        }
        return $ipaddress;
    }

    function get_server_ip()
    {

        // $ipaddress = gethostbyname(strtolower($query->text));
        $ipaddress = gethostbyname(env('APP_HOSTNAME'));
        // $ipaddress = file_get_contents('https://api.ipify.org');

        return $ipaddress;
    }

    function ipToCheck($ipRequest)
    {
        $ipArray = [
            env('LOCAL_IP_LIST_1'),
            env('LOCAL_IP_LIST_2'),
            env('LOCAL_IP_LIST_3'),
        ];
        return in_array($ipRequest, $ipArray);
    }

    public function saveToTnl($table, $aksi, $data)
    {
        $server = config('app.api_tnl');

        $data['from'] = 'tas';
        $data['aksi'] = $aksi;
        $data['table'] = $table;
        
        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token =$accessTokenTnl;
        
        if ($accessTokenTnl != '') {
            if ($aksi == 'add') {
                $posting = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ])->post($server . $table, $data);

                // dd($posting->json());
            } else {
                $getIdTnl = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ])->post($server . 'getidtnl', $data);
                $respIdTnl = $getIdTnl->toPsrResponse();
                if ($respIdTnl->getStatusCode() == 200 && $getIdTnl->json() != '') {
                    $id = $getIdTnl->json();

                    if ($id == 0) {
                        $posting = Http::withHeaders([
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $access_token
                        ])->post($server . $table, $data);
                    } else {
                        if ($aksi == 'edit') {

                            $posting = Http::withHeaders([
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'Authorization' => 'Bearer ' . $access_token
                            ])->patch($server . $table . '/' . $id, $data);
                        }
                        if ($aksi == 'delete') {

                            $posting = Http::withHeaders([
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'Authorization' => 'Bearer ' . $access_token
                            ])->delete($server . $table . '/' . $id, $data);
                        }
                    }
                }
            }

       
            $tesResp = $posting->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $posting->json(),
            ];

            // dd($response);
            $dataResp = $posting->json();
            if ($tesResp->getStatusCode() != 201 && $tesResp->getStatusCode() != 200) {
                throw new \Exception($dataResp['message']);
            }
            return $response;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
        // selesai:
        // return true;
    }

    public function CekValidasiToTnl($table,$data)
    {
        $server = config('app.api_tnl');

        $data['from'] = 'tas';
        $data['table'] = $table;
        
        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token =$accessTokenTnl;
        
        if ($accessTokenTnl != '') {
                $posting = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ])->get($server . $table);

       
            $tesResp = $posting->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $posting->json(),
            ];

            // dd($response);
            $dataResp = $posting->json();
            if ($tesResp->getStatusCode() != 201 && $tesResp->getStatusCode() != 200) {
                throw new \Exception($dataResp['message']);
            }
            return $response;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
        // selesai:
        // return true;
    }

    public function postData($server, $method, $accessToken, $data)
    {
        $send = $this->http_request(
            $server,
            $method,
            [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            $data
        );
        return $send;
    }

    public function http_request(string $url, string $method = 'GET', array $headers = null, array $body = null): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    public function getIdTnl(Request $request)
    {
        $backSlash = " \ ";
        $controller = 'App\Http\Controllers\Api' . trim($backSlash) . $request->table . 'Controller';
        $model = 'App\Models' . trim($backSlash) . $request->table;
        $models = app($model)->where('tas_id', $request->tas_id)->first() ?? 0;

        return $models->id;
        // if($request->aksi == 'edit')
        // {
        //     $requests = 'App\Http\Requests'. trim($backSlash) . 'Update'.$request->table.'Request';
        //     $process = app($controller)->update(app($requests), $models);
        //     return $process;
        // }
        // if($request->aksi == 'delete'){
        //     $requests = 'App\Http\Requests'. trim($backSlash) . 'Destroy'.$request->table.'Request';
        //     $process = app($controller)->destroy(app($requests), $models->id);
        //     return $process;
        // }

    }
}
