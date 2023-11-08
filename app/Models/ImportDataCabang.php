<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportDataCabang extends Model
{
    use HasFactory;


    public function processStore(array $data)
    {
        $cabang = Cabang::where('id', $data['cabang'])->first();
        $statusImportTimpa = Parameter::where('grp', 'STATUSIMPORT')->where('text', 'HAPUS DAN TIMPA DATA JIKA SUDAH ADA')->first();
        $statusImportSisip = Parameter::where('grp', 'STATUSIMPORT')->where('text', 'HANYA TAMBAHKAN DATA YANG BELUM DATA ADA SAJA')->first();



        $cabangMemo = json_decode($cabang->memo, TRUE);

        if (!$cabangMemo) {
            throw ValidationException::withMessages(["message" => "Cabang Tidak Compatible Unutk di impor"]);
        }

        $urlCabang = env($cabangMemo['URL']);
        $userCabang = env($cabangMemo['USER']);
        $passwordCabang = env($cabangMemo['PASSWORD']);

        if (empty($urlCabang) || empty($userCabang) || empty($passwordCabang)) {
            throw ValidationException::withMessages(["message" => "Cabang Tidak Compatible Unutk di impor"]);
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
            ->get($urlCabang . "jurnalumumpusatheader/importdatacabang?periode=" . $data['periode']);

        $konsolidasi = $jurnalUmum->json()['data'];
        if (!count($konsolidasi)) {
            throw ValidationException::withMessages(["message" => "data tidak ada"]);
        }

        DB::delete(DB::raw("delete  JurnalUmumPusatdetail from JurnalUmumPusatdetail as a inner join JurnalUmumPusatHeader b on a.nobukti=b.nobukti 
        WHERE isnull(b.cabang_id,0)=".$cabang->id." and format(b.tglbukti,'MM-yyyy')='" .$data['periode']. "'"));

        DB::delete(DB::raw("delete  JurnalUmumPusatheader from JurnalUmumPusatheader as b 
        WHERE isnull(b.cabang_id,0)=".$cabang->id." and format(b.tglbukti,'MM-yyyy')='" .$data['periode']. "'"));

        $jurnalRequest = [];
        foreach ($konsolidasi as $item) {
            // Membuat array baru untuk setiap entri header
            if (!array_key_exists($item['header_id'], $jurnalRequest)) {
                $jurnalUmumPusat = new JurnalUmumPusatHeader();
                $jurnalUmumPusat->nobukti = $item['header_nobukti'].'-'.$item['header_cabang'];
                $jurnalUmumPusat->tglbukti = $item['header_tglbukti'];
                $jurnalUmumPusat->keterangan = $item['header_keterangan'];
                $jurnalUmumPusat->postingdari = $item['header_postingdari'];
                $jurnalUmumPusat->statusapproval = $item['header_statusapproval'];
                $jurnalUmumPusat->userapproval = $item['header_userapproval'];
                $jurnalUmumPusat->tglapproval = $item['header_tglapproval'];
                $jurnalUmumPusat->statusformat = $item['header_statusformat'];
                $jurnalUmumPusat->info = $item['header_info'];
                $jurnalUmumPusat->modifiedby = $item['header_modifiedby'];
                $jurnalUmumPusat->created_at = $item['header_created_at'];
                $jurnalUmumPusat->updated_at = $item['header_updated_at'];
                $jurnalUmumPusat->cabang_id = $data['cabang'];
               
                if (!$jurnalUmumPusat->save()) {
                    throw new \Exception("Error storing jurnal umum pusat header.");
                }
                $jurnalRequest[$item['header_id']] = $jurnalUmumPusat;
                $jurnalUmumPusatHeaderLogTrail = (new LogTrail())->processStore([
                    'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
                    'postingdari' => 'ENTRY JURNAL UMUM PUSAT HEADER',
                    'idtrans' => $jurnalUmumPusat->id,
                    'nobuktitrans' => $jurnalUmumPusat->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $jurnalUmumPusat->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
        
            }
            // Menambahkan detail ke dalam entri header yang sesuai
            $jurnalUmumPusatDetail = new JurnalUmumPusatDetail();
            $jurnalUmumPusatDetail->jurnalumumpusat_id = $jurnalRequest[$item['header_id']]->id;
            $jurnalUmumPusatDetail->nobukti = $jurnalRequest[$item['header_id']]->nobukti;
            $jurnalUmumPusatDetail->tglbukti = $jurnalRequest[$item['header_id']]->tglbukti;
            $jurnalUmumPusatDetail->coa = $item['detail_coa'];
            $jurnalUmumPusatDetail->coamain = $item['detail_coamain'];
            $jurnalUmumPusatDetail->nominal = $item['detail_nominal'];
            $jurnalUmumPusatDetail->keterangan = $item['detail_keterangan'];
            $jurnalUmumPusatDetail->baris = $item['detail_baris'];
            $jurnalUmumPusatDetail->info = $item['detail_info'];
            $jurnalUmumPusatDetail->modifiedby = $item['detail_modifiedby'];
            $jurnalUmumPusatDetail->created_at = $item['detail_created_at'];
            $jurnalUmumPusatDetail->updated_at = $item['detail_updated_at'];
            if (!$jurnalUmumPusatDetail->save()) {
                throw new \Exception("Error storing jurnal umum pusat detail.");
            }
    
    
        }
        

       
        return "Data Periode ".$data['periode']." Cabang $cabang->namacabang Berhasil di Import";
    }

}

// foreach ($data as $key=>$item) {
//     // Membuat array baru untuk setiap entri header
//     if (!array_key_exists($item['header_id'], $jurnalRequest)) {
//         $jurnalRequest[$item['header_id']] = [
//             'header_id' => $item['header_id'],
//             'nobukti' => $item['header_nobukti'],
//             'tglbukti' => $item['header_tglbukti'],
//             'keterangan' => $item['header_keterangan'],
//             'postingdari' => $item['header_postingdari'],
//             'statusapproval' => $item['header_statusapproval'],
//             'userapproval' => $item['header_userapproval'],
//             'tglapproval' => $item['header_tglapproval'],
//             'statusformat' => $item['header_statusformat'],
//             'info' => $item['header_info'],
//             'modifiedby' => $item['header_modifiedby'],
//             'created_at' => $item['header_created_at'],
//             'updated_at' => $item['header_updated_at'],
//             'cabang' => $item['header_cabang'],
//             'cabang_id' => $item['header_cabang_id'],
//             'details' => []
//         ];
//     }

//     // Menambahkan detail ke dalam entri header yang sesuai
//     $jurnalRequest[$item['header_id']]['details'][] = [
//         'header_id' => $jurnalRequest[$item['header_id']]['header_id'],
//         'detail_jurnalumumpusat_id' => $item['detail_jurnalumumpusat_id'],
//         'detail_nobukti' => $item['detail_nobukti'],
//         'detail_tglbukti' => $item['detail_tglbukti'],
//         'detail_coa' => $item['detail_coa'],
//         'detail_coamain' => $item['detail_coamain'],
//         'detail_nominal' => $item['detail_nominal'],
//         'detail_keterangan' => $item['detail_keterangan'],
//         'detail_baris' => $item['detail_baris'],
//         'detail_info' => $item['detail_info'],
//         'detail_modifiedby' => $item['detail_modifiedby'],
//         'detail_created_at' => $item['detail_created_at'],
//         'detail_updated_at' => $item['detail_updated_at']
//     ];
// }