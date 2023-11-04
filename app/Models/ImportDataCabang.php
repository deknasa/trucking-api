<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportDataCabang extends Model
{
    use HasFactory;


    public function processStore(array $data)
    {
        $cabang = Cabang::where('id',$data['cabang'])->first();
       
        $cabangMemo = json_decode($cabang->memo,TRUE);

        if (empty($cabangMemo['URL']) || empty($cabangMemo['USER']) || empty($cabangMemo['PASSWORD'])) {
            dd('kosing');
        }else {
            dd('ada');
        }
        $urlCabang = env($cabangMemo['URL']);
        $userCabang = env($cabangMemo['USER']);
        $passwordCabang = env($cabangMemo['PASSWORD']);

        if (empty($urlCabang) || empty($userCabang) || empty($passwordCabang)) {
            dd('kosing');
        }else {
            dd('ada');
        }
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
        ->post($urlCabang . 'token', [
            'user' => $userCabang,
            'password' => $passwordCabang,
            'ipclient' => '',
            'ipserver' => '',
            'latitude' => '',
            'longitude' => '',
            'browser' => '',
            'os' => '',
        ]);
        $access_token = json_decode($getToken, TRUE)['access_token'];

        $jurnalUmum = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])
        ->get($urlCabang . "jurnalumumpusatheader?priode=" . date('Y-m-d',strtotime($data['priode'])) . "&approve=" . 3 . "&limit=0" );
    
        $data = $jurnalUmum->json()['data'];

        foreach ($data as $jurnal) {
            dd($jurnal['nobukti']);
        }

        dd($data);
    }

}
