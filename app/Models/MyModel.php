<?php

namespace App\Models;

use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class MyModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        if (!isset($this->toUppercase) || $this->toUppercase) {
            if (is_string($value)) {
                return $this->attributes[$key] = strtoupper($value);
            }
        }
    }

    public function setRequestParameters()
    {
        $this->params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];
    }

    public function lockAndDestroy($identifier, string $field = 'id'): Model
    {
        $table = $this->getTable();
        $model = $this->where($field, $identifier)->lockForUpdate()->first();

        if ($model) {
            $isDeleted = $model->where($field, $identifier)->delete();

            if ($isDeleted) {
                return $model;
            }

            throw new Exception("Error deleting '$field' '$identifier' in '$table'");
        }

        throw new ModelNotFoundException("No data found for '$field' '$identifier' in '$table'");
    }

    private function mustUppercase($key): bool
    {
        return true;
    }

    
    public function saveToTnl($table, $aksi, $data)
    {
        $server = config('app.api_tnl');

        $data['from'] = 'tas';
        $data['aksi'] = $aksi;
        $data['table'] = $table;
        // $getToken = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json'
        // ])
        //     ->post($server . 'token', [
        //         'user' => 'ADMIN',
        //         'password' => getenv('PASSWORD_TNL'),
        //         'ipclient' => '',
        //         'ipserver' => '',
        //         'latitude' => '',
        //         'longitude' => '',
        //         'browser' => '',
        //         'os' => '',
        //     ]);

        // if ($getToken->getStatusCode() == '404') {
        //     throw new \Exception("Akun Tidak Terdaftar di Trucking TNL");
        // } else if ($getToken->getStatusCode() == '200') {
        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token =$accessTokenTnl;
        if ($accessTokenTnl != '') {
            // $access_token = json_decode($getToken, TRUE)['access_token'];
           
            if ($aksi == 'add') {
                // dump($server);
                // dump($table);
                dd($access_token);
                $posting = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ])->post($server . $table, $data);
                // dd('test');
                // $posting = $this->postData($server . $table, 'POST', $access_token, $data);
                // dd($posting);
                // $posting = json_decode($posting, TRUE);
                // if (array_key_exists('status', $posting)) {
                //     goto selesai;
                // } else {
                //     throw new \Exception($posting['message']);
                // }
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
            // dd($posting);
            $tesResp = $posting->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $posting->json(),
            ];

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
