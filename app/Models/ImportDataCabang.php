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
        $cabang = Cabang::where('id', $data['cabang'])->first();

        $cabangMemo = json_decode($cabang->memo, TRUE);

        // if (empty($cabangMemo['URL']) || empty($cabangMemo['USER']) || empty($cabangMemo['PASSWORD'])) {
        //     dd('kosing');
        // }else {
        //     dd('ada');
        // }
        $urlCabang = env($cabangMemo['URL']);
        $userCabang = env($cabangMemo['USER']);
        $passwordCabang = env($cabangMemo['PASSWORD']);

        // if (empty($urlCabang) || empty($userCabang) || empty($passwordCabang)) {
        //     dd('kosing');
        // }else {
        //     dd('ada');
        // }
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
            ->get($urlCabang . "jurnalumumpusatheader/importdatacabang?periode=" . $data['periode']);

        $data = $jurnalUmum->json()['data'];

        // $nobukti = [];

        foreach ($data as $jurnal) {
            // dd($jurnal['nobukti']);
            $jurnalRequest = [
                'header_id' => $jurnal['header_id'],
                'header_nobukti' => $jurnal['header_nobukti'],
                'header_tglbukti' => $jurnal['header_tglbukti'],
                'header_keterangan' => $jurnal['header_keterangan'],
                'header_postingdari' => $jurnal['header_postingdari'],
                'header_statusapproval' => $jurnal['header_statusapproval'],
                'header_userapproval' => $jurnal['header_userapproval'],
                'header_tglapproval' => $jurnal['header_tglapproval'],
                'header_statusformat' => $jurnal['header_statusformat'],
                'header_info' => $jurnal['header_info'],
                'header_modifiedby' => $jurnal['header_modifiedby'],
                'header_created_at' => $jurnal['header_created_at'],
                'header_updated_at' => $jurnal['header_updated_at'],
                'header_cabang' => $jurnal['header_cabang'],
                'header_cabang_id' => $jurnal['header_cabang_id'],
                'detail_id' => $jurnal['detail_id'],
                'detail_jurnalumumpusat_id' => $jurnal['detail_jurnalumumpusat_id'],
                'detail_nobukti' => $jurnal['detail_nobukti'],
                'detail_tglbukti' => $jurnal['detail_tglbukti'],
                'detail_coa' => $jurnal['detail_coa'],
                'detail_coamain' => $jurnal['detail_coamain'],
                'detail_nominal' => $jurnal['detail_nominal'],
                'detail_keterangan' => $jurnal['detail_keterangan'],
                'detail_baris' => $jurnal['detail_baris'],
                'detail_info' => $jurnal['detail_info'],
                'detail_modifiedby' => $jurnal['detail_modifiedby'],
                'detail_created_at' => $jurnal['detail_created_at'],
                'detail_updated_at' => $jurnal['detail_updated_at'],
            ];
        }
        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);

        dd($data);
    }
}
