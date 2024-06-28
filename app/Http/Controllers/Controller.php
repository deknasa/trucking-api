<?php

namespace App\Http\Controllers;

use App\Events\UpdateExportProgress;
use App\Helpers\App as AppHelper;
use App\Models\Cabang;
use App\Models\MyModel;
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

    function like_match($pattern, $subject)
    {
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
        return (bool) preg_match("/^{$pattern}$/i", $subject);
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

    public function saveToTnlConnection($table, $aksi, $data)
    {

    }
    public function saveToTnl($table, $aksi, $data)
    {
        $server = config('app.api_tnl');

        $data['from'] = 'tas';
        $data['aksi'] = $aksi;
        $data['table'] = $table;

        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token = $accessTokenTnl;

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

    public function CekValidasiToTnl($table, $data)
    {
        $server = config('app.api_tnl');

        $data['from'] = 'tas';
        $data['table'] = $table;

        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token = $accessTokenTnl;
        // dd($server . $table);

        if ($accessTokenTnl != '') {
            $posting = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ])->Post($server . $table, $data);


            $tesResp = $posting->toPsrResponse();
            // dd($posting->json());
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

    public function batalEditingBy()
    {
        $id = request()->id ?? '';
        $table = request()->table ?? '';
        $aksi = request()->aksi ?? '';
        // $tablelink = request()->tablelink ?? '';
        return response([
            // 'data' => (new MyModel())->updateEditingBy($table, $id, $aksi,$tablelink),
            'data' => (new MyModel())->updateEditingBy($table, $id, $aksi),
        ]);
    }

    public function SaveTnlNew($table, $aksi, $data)
    {
        $backSlash = " \ ";

        $model = 'App\Models' . trim($backSlash) . $table;
        $models = app($model);
        $models->setConnection('srvtnl');
        // $idheader=0
        DB::connection('srvtnl')->beginTransaction();
        try {
            if ($table=='tarif') {
                $parent=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.tujuan'
                        )
                        ->where('a.tas_id', $data['parent_id'])->first();

                $upahsupir=db::connection('srvtnl')->table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['upahsupir_id'])->first();

                $kota=db::connection('srvtnl')->table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota',
                )
                ->where('a.tas_id', $data['kota_id'])->first();

                $zona=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zona_id'])->first();

                $jenisorder=db::connection('srvtnl')->table("jenisorder")->from(db::raw("jenisorder a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['jenisorder_id'])->first();

                $data['parent_id'] = $parent->id ?? 0;
                $data['parent'] = $parent->tujuan ?? '';
                $data['upahsupir_id'] = $upahsupir->id ?? 0;
                $data['kota_id'] = $kota->id ?? 0;
                $data['kota'] = $kota->kodekota ?? '';
                $data['zona_id'] = $zona->id ?? 0;
                $data['zona'] = $zona->zona ?? '';
                $data['jenisorder_id'] = $jenisorder->id ?? 0;
            }
            if ($table=='tarifrincian') {
                $container=db::connection('srvtnl')->table("container")->from(db::raw("container a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodecontainer'
                )
                ->where('a.tas_id', $data['container_id'])->first();

                $data['container'] = $container->kodecontainer ?? '';
                $data['container_id'] = $container->id ?? 0;

            }
            if ($table=='upahsupir') {
                $parent=db::connection('srvtnl')->table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['parent_id'])->first(); 


                $kotadari=db::connection('srvtnl')->table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota',
                )
                ->where('a.tas_id', $data['kotadari_id'])->first();

                $tarif=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarif_id'])->first();     

                $tarifmuatan=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifmuatan_id'])->first();   

                $tarifbongkaran=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifbongkaran_id'])->first();                 

                $tarifimport=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifimport_id'])->first();                 

                $tarifexport=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifexport_id'])->first();                 

                $kotasampai=db::connection('srvtnl')->table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota',
                )
                ->where('a.tas_id', $data['kotasampai_id'])->first();                

                $zona=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zona_id'])->first();                

                $zonadari=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zonadari_id'])->first();    

                $zonasampai=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zonasampai_id'])->first();                  

                $data['parent_id'] = $parent->id ?? 0;
                $data['parent'] = $parent->tujuan ?? '';                
                $data['kotadari_id'] = $kotadari->id ?? 0;
                $data['kotadari'] = $kotadari->kodekota ?? '';                
                $data['tarif_id'] = $tarif->id ?? 0;
                $data['tarif'] = $tarif->tujuan ?? '';
                $data['tarifmuatan_id'] = $tarifmuatan->id ?? 0;
                $data['tarifbongkaran_id'] = $tarifbongkaran->id ?? 0;
                $data['tarifimport_id'] = $tarifimport->id ?? 0;
                $data['tarifexport_id'] = $tarifexport->id ?? 0;
                $data['kotasampai_id'] = $kotasampai->id ?? 0;
                $data['kotasampai'] = $kotasampai->kodekota ?? '';                
                $data['zona_id'] = $zona->id ?? 0;
                $data['zona'] = $zona->kodekota ?? '';                
                $data['zonadari_id'] = $zonadari->id ?? 0;
                $data['zonasampai_id'] = $zonasampai->id ?? 0;

            }
            if ($table=='upahsupirrincian') {
                $container=db::connection('srvtnl')->table("container")->from(db::raw("container a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodecontainer'
                )
                ->where('a.tas_id', $data['container_id'])->first();

                $statuscontainer=db::connection('srvtnl')->table("statuscontainer")->from(db::raw("statuscontainer a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodestatuscontainer'
                )
                ->where('a.tas_id', $data['statuscontainer_id'])->first();                

                $data['container'] = $container->kodecontainer ?? '';
                $data['container_id'] = $container->id ?? 0;
                $data['statuscontainer'] = $statuscontainer->kodestatuscontainer ?? '';
                $data['statuscontainer_id'] = $statuscontainer->id ?? 0;

            }     
            
            if ($table=='subkelompok') {
                $kelompok=db::connection('srvtnl')->table("kelompok")->from(db::raw("kelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['kelompok_id'])->first();

                $data['kelompok_id'] = $kelompok->id ?? 0;
                
            }
            if ($table=='kategori') {
                $subkelompok=db::connection('srvtnl')->table("subkelompok")->from(db::raw("subkelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['subkelompok_id'])->first();

                $data['subkelompok_id'] = $subkelompok->id ?? 0;
                
            }            

            if ($table=='stok') {
                $subkelompok=db::connection('srvtnl')->table("subkelompok")->from(db::raw("subkelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['subkelompok_id'])->first();

                $kelompok=db::connection('srvtnl')->table("kelompok")->from(db::raw("kelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['kelompok_id'])->first();                

                $kategori=db::connection('srvtnl')->table("kategori")->from(db::raw("kategori a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['kategori_id'])->first();                

                $merk=db::connection('srvtnl')->table("merk")->from(db::raw("merk a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['merk_id'])->first();                

                $jenistrado=db::connection('srvtnl')->table("jenistrado")->from(db::raw("jenistrado a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['jenistrado_id'])->first();                

                $satuan=db::connection('srvtnl')->table("satuan")->from(db::raw("satuan a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['satuan_id'])->first();                

                $data['subkelompok_id'] = $subkelompok->id ?? 0;
                $data['kelompok_id'] = $kelompok->id ?? 0;
                $data['kategori_id'] = $kategori->id ?? 0;
                $data['merk_id'] = $merk->id ?? 0;
                $data['jenistrado_id'] = $jenistrado->id ?? 0;
                $data['satuan_id'] = $satuan->id ?? 0;
                
            }    
            if ($table=='supir') {
                $mandor=db::connection('srvtnl')->table("mandor")->from(db::raw("mandor a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['mandor_id'])->first(); 
                $supirold=db::connection('srvtnl')->table("supir")->from(db::raw("supir a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['supirold_id'])->first(); 

                $data['mandor_id'] = $mandor->id ?? 0;                
                $data['supirold_id'] = $supirold->id ?? 0;                
            }        
            $data['from'] = 'tas';
            
            if ($aksi == 'add') {
                $datasimpan=$models->processStore($data, $models);
                $getId=$datasimpan->id;
            } else {
                $getId = $models->where('tas_id', $data['tas_id'])->first()->id ?? 0;
                $getstatusaktif = $models->where('tas_id', $data['tas_id'])->first()->statusaktif ?? 0;
                $data['statusaktif'] = $getstatusaktif;
                // dd($getId);
                // if (!$getId) {
                //     $models->processStore($data, $models);
                // } else {
                if ($getId!=0) {
                    if ($aksi == 'edit') {
                        $findModels = $models->findOrFail($getId);
                        $models->processUpdate($findModels, $data);
                    }
                    if ($aksi == 'delete') {
                        
                        $findModels = $models->findOrFail($getId);

                        $models->processDestroy($findModels);
                    }
                }
                // }
            }
            DB::connection('srvtnl')->commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $data,
                'id' => $getId,
            ], 201);
        } catch (\Throwable $th) {
            DB::connection('srvtnl')->rollBack();

            throw $th;
        }
    }

    public function SaveTnlMasterDetail($table, $aksi, $data)
    {
        $backSlash = " \ ";

        $model = 'App\Models' . trim($backSlash) . $table;
        $models = app($model);
        $models->setConnection('srvtnl');
        
        // $idheader=0
        DB::connection('srvtnl')->beginTransaction();
        try {
            if ($table=='tarif') {
                $parent=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.tujuan'
                        )
                        ->where('a.tas_id', $data['parent_id'])->first();

                $upahsupir=db::connection('srvtnl')->table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['upahsupir_id'])->first();

                $kota=db::connection('srvtnl')->table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota',
                )
                ->where('a.tas_id', $data['kota_id'])->first();

                $zona=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zona_id'])->first();

                $jenisorder=db::connection('srvtnl')->table("jenisorder")->from(db::raw("jenisorder a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['jenisorder_id'])->first();

                $data['parent_id'] = $parent->id ?? 0;
                $data['parent'] = $parent->tujuan ?? '';
                $data['upahsupir_id'] = $upahsupir->id ?? 0;
                $data['kota_id'] = $kota->id ?? 0;
                $data['kota'] = $kota->kodekota ?? '';
                $data['zona_id'] = $zona->id ?? 0;
                $data['zona'] = $zona->zona ?? '';
                $data['jenisorder_id'] = $jenisorder->id ?? 0;
                for ($i=0; $i < count($data['container_id']); $i++) { 
                    $container=db::connection('srvtnl')->table("container")->from(db::raw("container a with (readuncommitted)"))
                    ->select(
                        'a.id',
                        'a.kodecontainer'
                    )
                    ->where('a.tas_id', $data['container_id'][$i])->first();
    
                    $data['container'][$i] = $container->kodecontainer ?? '';
                    $data['container_id'][$i] = $container->id ?? 0;

                }
            }
            if ($table=='tarifrincian') {
                $container=db::connection('srvtnl')->table("container")->from(db::raw("container a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodecontainer'
                )
                ->where('a.tas_id', $data['container_id'])->first();

                $data['container'] = $container->kodecontainer ?? '';
                $data['container_id'] = $container->id ?? 0;

            }
            if ($table=='upahsupir') {
                $parent=db::connection('srvtnl')->table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['parent_id'])->first(); 


                $kotadari=db::connection('srvtnl')->table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota',
                )
                ->where('a.tas_id', $data['kotadari_id'])->first();

                $tarif=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarif_id'])->first();     

                $tarifmuatan=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifmuatan_id'])->first();   

                $tarifbongkaran=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifbongkaran_id'])->first();                 

                $tarifimport=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifimport_id'])->first();                 

                $tarifexport=db::connection('srvtnl')->table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->where('a.tas_id', $data['tarifexport_id'])->first();                 

                $kotasampai=db::connection('srvtnl')->table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota',
                )
                ->where('a.tas_id', $data['kotasampai_id'])->first();                

                $zona=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zona_id'])->first();                

                $zonadari=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zonadari_id'])->first();    

                $zonasampai=db::connection('srvtnl')->table("zona")->from(db::raw("zona a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.zona',
                )
                ->where('a.tas_id', $data['zonasampai_id'])->first();                  

                $data['parent_id'] = $parent->id ?? 0;
                $data['parent'] = $parent->tujuan ?? '';                
                $data['kotadari_id'] = $kotadari->id ?? 0;
                $data['kotadari'] = $kotadari->kodekota ?? '';                
                $data['tarif_id'] = $tarif->id ?? 0;
                $data['tarif'] = $tarif->tujuan ?? '';
                $data['tarifmuatan_id'] = $tarifmuatan->id ?? 0;
                $data['tarifbongkaran_id'] = $tarifbongkaran->id ?? 0;
                $data['tarifimport_id'] = $tarifimport->id ?? 0;
                $data['tarifexport_id'] = $tarifexport->id ?? 0;
                $data['kotasampai_id'] = $kotasampai->id ?? 0;
                $data['kotasampai'] = $kotasampai->kodekota ?? '';                
                $data['zona_id'] = $zona->id ?? 0;
                $data['zona'] = $zona->kodekota ?? '';                
                $data['zonadari_id'] = $zonadari->id ?? 0;
                $data['zonasampai_id'] = $zonasampai->id ?? 0;
                
                for ($i=0; $i < count($data['container_id']); $i++) { 
                    $container=db::connection('srvtnl')->table("container")->from(db::raw("container a with (readuncommitted)"))
                    ->select(
                        'a.id',
                        'a.kodecontainer'
                    )
                    ->where('a.tas_id', $data['container_id'][$i])->first();
    
                    $statuscontainer=db::connection('srvtnl')->table("statuscontainer")->from(db::raw("statuscontainer a with (readuncommitted)"))
                    ->select(
                        'a.id',
                        'a.kodestatuscontainer'
                    )
                    ->where('a.tas_id', $data['statuscontainer_id'][$i])->first();                
    
                    $data['container'][$i] = $container->kodecontainer ?? '';
                    $data['container_id'][$i] = $container->id ?? 0;
                    $data['statuscontainer'][$i] = $statuscontainer->kodestatuscontainer ?? '';
                    $data['statuscontainer_id'][$i] = $statuscontainer->id ?? 0;
                }
            }
            if ($table=='upahsupirrincian') {
                $container=db::connection('srvtnl')->table("container")->from(db::raw("container a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodecontainer'
                )
                ->where('a.tas_id', $data['container_id'])->first();

                $statuscontainer=db::connection('srvtnl')->table("statuscontainer")->from(db::raw("statuscontainer a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodestatuscontainer'
                )
                ->where('a.tas_id', $data['statuscontainer_id'])->first();                

                $data['container'] = $container->kodecontainer ?? '';
                $data['container_id'] = $container->id ?? 0;
                $data['statuscontainer'] = $statuscontainer->kodestatuscontainer ?? '';
                $data['statuscontainer_id'] = $statuscontainer->id ?? 0;

            }     
            
            if ($table=='subkelompok') {
                $kelompok=db::connection('srvtnl')->table("kelompok")->from(db::raw("kelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['kelompok_id'])->first();

                $data['kelompok_id'] = $kelompok->id ?? 0;
                
            }
            if ($table=='kategori') {
                $subkelompok=db::connection('srvtnl')->table("subkelompok")->from(db::raw("subkelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['subkelompok_id'])->first();

                $data['subkelompok_id'] = $subkelompok->id ?? 0;
                
            }            

            if ($table=='stok') {
                $subkelompok=db::connection('srvtnl')->table("subkelompok")->from(db::raw("subkelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['subkelompok_id'])->first();

                $kelompok=db::connection('srvtnl')->table("kelompok")->from(db::raw("kelompok a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['kelompok_id'])->first();                

                $kategori=db::connection('srvtnl')->table("kategori")->from(db::raw("kategori a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['kategori_id'])->first();                

                $merk=db::connection('srvtnl')->table("merk")->from(db::raw("merk a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['merk_id'])->first();                

                $jenistrado=db::connection('srvtnl')->table("jenistrado")->from(db::raw("jenistrado a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['jenistrado_id'])->first();                

                $satuan=db::connection('srvtnl')->table("satuan")->from(db::raw("satuan a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['satuan_id'])->first();                

                $data['subkelompok_id'] = $subkelompok->id ?? 0;
                $data['kelompok_id'] = $kelompok->id ?? 0;
                $data['kategori_id'] = $kategori->id ?? 0;
                $data['merk_id'] = $merk->id ?? 0;
                $data['jenistrado_id'] = $jenistrado->id ?? 0;
                $data['satuan_id'] = $satuan->id ?? 0;
                
            }    
            if ($table=='supir') {
                $mandor=db::connection('srvtnl')->table("mandor")->from(db::raw("mandor a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['mandor_id'])->first(); 
                $supirold=db::connection('srvtnl')->table("supir")->from(db::raw("supir a with (readuncommitted)"))
                ->select(
                    'a.id',
                )
                ->where('a.tas_id', $data['supirold_id'])->first(); 

                $data['mandor_id'] = $mandor->id ?? 0;                
                $data['supirold_id'] = $supirold->id ?? 0;                
            }        
            // if ($table =="mandor") {
            //     for ($i=0; $i < count($data['users']); $i++) { 
            //         $mandor=db::connection('srvtnl')->table("user")->from(db::raw("user a with (readuncommitted)"))
            //         ->select(
            //             'a.id',
            //         )
            //         ->where('a.tas_id', $data['users'][$id])->first(); 
            //     }

            // }
            $data['from'] = 'tas';
            
            if ($aksi == 'add') {
                $datasimpan=$models->processStore($data, $models,'srvtnl');
                $getId=$datasimpan->id;
            } else {
                $getId = $models->where('tas_id', $data['tas_id'])->first()->id ?? 0;
                $getstatusaktif = $models->where('tas_id', $data['tas_id'])->first()->statusaktif ?? 0;
                $data['statusaktif'] = $getstatusaktif;
                // dd($getId);
                // if (!$getId) {
                //     $models->processStore($data, $models);
                // } else {
                if ($getId!=0) {
                    if ($aksi == 'edit') {
                        $findModels = $models->findOrFail($getId);
                        $models->processUpdate($findModels, $data,'srvtnl');
                    }
                    if ($aksi == 'delete') {
                        
                        $findModels = $models->findOrFail($getId);

                        $models->processDestroy($findModels,'srvtnl');
                    }
                }
                // }
            }
            DB::connection('srvtnl')->commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $data,
                'id' => $getId,
            ], 201);
        } catch (\Throwable $th) {
            DB::connection('srvtnl')->rollBack();

            throw $th;
        }
    }

    public function SaveTnlNewDetail($table, $aksi, $data,$tableheader,$fieldheader)
    {
        $backSlash = " \ ";

        $model = 'App\Models' . trim($backSlash) . $table;
        $models = app($model);
        $models->setConnection('srvtnl');
        $modelheader = 'App\Models' . trim($backSlash) . $tableheader;
        $modelheaders = app($modelheader);
        $modelheaders->setConnection('srvtnl');
        // $idheader=0
        DB::connection('srvtnl')->beginTransaction();
        try {
            if ($aksi == 'add') {
                $datasimpan=$models->processStore($data, $models);
                $getId=$datasimpan->id;
            } else {
                $getIdHeader = $modelheaders->where('tas_id', $data['tas_id'])->first()->id ?? 0;
                $getId = $models->where('tas_id', $data['tas_id'])->first()->id ?? 0;
                // dd($getId);
                // if (!$getId) {
                //     $models->processStore($data, $models);
                // } else {
                    // dd($getIdHeader);
                if ($getId!=0) {
                    if ($aksi == 'edit') {
                        $findModels = $models->findOrFail($getId);
                        $models->processUpdate($findModels, $data);
                    }
                    
                }
                if ($getIdHeader!=0) {
                    if ($aksi == 'delete') {
                        // dd($models);
                        // $findModels = $models->whereraw($fieldheader."=".$getIdHeader);
                        // dd($findmodels->tosql());
                        // $getIddetail = $models->where('tas_id', $data['tas_id'])->first()->id ?? 0;
                        // $findModels = $models->findOrFail($getIdHeader);
                        $models->processDestroy($models, $getIdHeader);
                  
                    }
                }                
                // }
            }
            DB::connection('srvtnl')->commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan.',
                'data' => $data,
                'id' => $getId,
            ], 201);
        } catch (\Throwable $th) {
            DB::connection('srvtnl')->rollBack();

            throw $th;
        }
    }    

    // 
  
 
}
