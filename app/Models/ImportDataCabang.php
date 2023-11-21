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
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $cabang = Cabang::where('id', $data['cabang'])->first();
        $statusImportTimpa = Parameter::where('grp', 'STATUSIMPORT')->where('text', 'HAPUS DAN TIMPA DATA JIKA SUDAH ADA')->first();
        $statusImportSisip = Parameter::where('grp', 'STATUSIMPORT')->where('text', 'HANYA TAMBAHKAN DATA YANG BELUM DATA ADA SAJA')->first();



        $cabangMemo = json_decode($cabang->memo, TRUE);



        $urlCabang = env($cabangMemo['URL']);
        $userCabang = env($cabangMemo['USER']);
        $passwordCabang = env($cabangMemo['PASSWORD']);
        $web = $cabangMemo['WEB'] ?? 'YA';
        $encode = $cabangMemo['ENCODE'] ?? 'UTF-8';
        $singkatan = $cabangMemo['SINGKATAN'] ?? '';

        $periode1=date('Y-m-d', strtotime('01-'.$data['periode']));
        

        if ($data['import'] == $statusImportTimpa->id) {
            
            // DB::delete(DB::raw("delete  JurnalUmumPusatdetail from JurnalUmumPusatdetail as a inner join JurnalUmumPusatHeader b on a.nobukti=b.nobukti 
            // WHERE isnull(b.cabang_id,0)=" . $cabang->id . " and format(b.tglbukti,'MM-yyyy')='" . $data['periode'] . "'"));

            // DB::delete(DB::raw("delete  JurnalUmumPusatheader from JurnalUmumPusatheader as b 
            // WHERE isnull(b.cabang_id,0)=" . $cabang->id . " and format(b.tglbukti,'MM-yyyy')='" . $data['periode'] . "'"));

                    
            DB::delete(DB::raw("delete  JurnalUmumPusatdetail from JurnalUmumPusatdetail as a inner join JurnalUmumPusatHeader b on a.nobukti=b.nobukti 
            WHERE isnull(b.cabang_id,0)=" . $cabang->id . " and b.tglbukti>='" . $periode1. "'"));

            DB::delete(DB::raw("delete  JurnalUmumPusatheader from JurnalUmumPusatheader as b 
            WHERE isnull(b.cabang_id,0)=" . $cabang->id . " and b.tglbukti>='" . $periode1 . "'"));
            
            
        }


        // dump($cabang->id);
        // dd($data['periode']);
        DB::delete(DB::raw("delete AkunPusatDetail 
        WHERE isnull(cabang_id,0)=" . $cabang->id . " and bulan=cast(left('" . $data['periode'] . "',2) as integer) and tahun=cast(right('" . $data['periode'] . "',4) as integer)"));

        if ($web == "YA") {

            if (!$cabangMemo) {
                throw ValidationException::withMessages(["message" => "Cabang Tidak Compatible Unutk di impor"]);
            }

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



            $jurnalRequest = [];
            foreach ($konsolidasi as $item) {
                // Membuat array baru untuk setiap entri header

                if ($data['import'] == $statusImportSisip->id) {

                    $nobukticabang = $item['header_nobukti'] . '-' . $item['header_cabang'];
                    $querysisip = db::table("jurnalumumpusatheader")->from(db::raw("jurnalumumpusatheader a with (readuncommitted)"))
                        ->select()
                        ->where('a.nobukti', $nobukticabang)
                        ->first();
                    if (!isset($querysisip)) {
                        if (!array_key_exists($item['header_id'], $jurnalRequest)) {
                            $jurnalUmumPusat = new JurnalUmumPusatHeader();
                            $jurnalUmumPusat->nobukti = $item['header_nobukti'] . '-' . $singkatan;
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
                            $jurnalUmumPusat->cabang = $cabang->namacabang ?? '';

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
                } else {
                    if (!array_key_exists($item['header_id'], $jurnalRequest)) {
                        $jurnalUmumPusat = new JurnalUmumPusatHeader();
                        $jurnalUmumPusat->nobukti = $item['header_nobukti'] . '-' . $singkatan;
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
                        $jurnalUmumPusat->cabang = $cabang->namacabang ?? '';


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
            }


            // $saldoakunpusatdetail = Http::withHeaders([
            //     'Accept' => 'application/json',
            //     'Authorization' => 'Bearer ' . $access_token,
            //     'Content-Type' => 'application/json',
            // ])
            //     // ->get($urlCabang . "saldoakunpusatdetail/importdatacabang?periode=" . $data['periode'] . "&tahun=" . $itemtahun2['tahun'] . "&bulan=" . $itembulan2['bulan']);
            //     ->get($urlCabang . "saldoakunpusatdetail/importdatacabang?periode=" . $data['periode']);

            // $konsolidasisaldoakunpusatdetail = $saldoakunpusatdetail->json()['data'];
            // if (!count($konsolidasisaldoakunpusatdetail)) {
            //     throw ValidationException::withMessages(["message" => "data tidak ada"]);
            // }

            // foreach ($konsolidasisaldoakunpusatdetail as $item2) {

            //     $saldoAkunpusatdetail = new SaldoAkunPusatDetail();
            //     $saldoAkunpusatdetail->coa = $item2['coa'];
            //     $saldoAkunpusatdetail->bulan = $item2['bulan'];
            //     $saldoAkunpusatdetail->tahun = $item2['tahun'];
            //     $saldoAkunpusatdetail->nominal = $item2['nominal'];
            //     $saldoAkunpusatdetail->info = $item2['info'];
            //     $saldoAkunpusatdetail->tglbukti = $item2['tglbukti'];
            //     $saldoAkunpusatdetail->modifiedby = $item2['modifiedby'];
            //     $saldoAkunpusatdetail->created_at = $item2['created_at'];
            //     $saldoAkunpusatdetail->updated_at = $item2['updated_at'];
            //     $saldoAkunpusatdetail->cabang_id = $data['cabang'];


            //     if (!$saldoAkunpusatdetail->save()) {
            //         throw new \Exception("Error storing saldo akun pusat detail.");
            //     }
            // }

            // // saldoawalbukubesar

            // $saldoawalbukubesar = Http::withHeaders([
            //     'Accept' => 'application/json',
            //     'Authorization' => 'Bearer ' . $access_token,
            //     'Content-Type' => 'application/json',
            // ])
            //     // ->get($urlCabang . "saldoakunpusatdetail/importdatacabang?periode=" . $data['periode'] . "&tahun=" . $itemtahun2['tahun'] . "&bulan=" . $itembulan2['bulan']);
            //     ->get($urlCabang . "saldoawalbukubesar/importdatacabang?periode=" . $data['periode']);

            // $konsolidasisaldoawalbukubesar = $saldoawalbukubesar->json()['data'];
            // if (!count($konsolidasisaldoawalbukubesar)) {
            //     throw ValidationException::withMessages(["message" => "data tidak ada"]);
            // }

            // foreach ($konsolidasisaldoawalbukubesar as $item4) {

            //     $saldoAwalbukubesar = new SaldoAwalBukuBesar();
            //     $saldoAwalbukubesar->coa = $item4['coa'];
            //     $saldoAwalbukubesar->bulan = $item4['bulan'];
            //     $saldoAwalbukubesar->nominal = $item4['nominal'];
            //     $saldoAwalbukubesar->info = $item4['info'];
            //     $saldoAwalbukubesar->tglbukti = $item4['tglbukti'];
            //     $saldoAwalbukubesar->modifiedby = $item4['modifiedby'];
            //     $saldoAwalbukubesar->created_at = $item4['created_at'];
            //     $saldoAwalbukubesar->updated_at = $item4['updated_at'];
            //     $saldoAwalbukubesar->cabang_id = $data['cabang'];


            //     if (!$saldoAwalbukubesar->save()) {
            //         throw new \Exception("Error storing saldo awal buku besar.");
            //     }
            // }




            // 




            $akunpusatdetail = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ])
                ->get($urlCabang . "akunpusatdetail/importdatacabang?periode=" . $data['periode']);

            $konsolidasiakunpusatdetail = $akunpusatdetail->json()['data'];
            if (!count($konsolidasiakunpusatdetail)) {
                throw ValidationException::withMessages(["message" => "data tidak ada"]);
            }

            foreach ($konsolidasiakunpusatdetail as $item3) {
                $Akunpusatdetail = new AkunPusatDetail();
                $Akunpusatdetail->coa = $item3['coa'];
                $Akunpusatdetail->bulan = $item3['bulan'];
                $Akunpusatdetail->tahun = $item3['tahun'];
                $Akunpusatdetail->nominal = $item3['nominal'];
                $Akunpusatdetail->info = $item3['info'];
                $Akunpusatdetail->modifiedby = $item3['modifiedby'];
                $Akunpusatdetail->created_at = $item3['created_at'];
                $Akunpusatdetail->updated_at = $item3['updated_at'];
                $Akunpusatdetail->cabang_id = $data['cabang'];


                if (!$Akunpusatdetail->save()) {
                    throw new \Exception("Error storing  akun pusat detail.");
                }
            }
        } else {
            // proses dari database lama
            $month = substr($data['periode'], 0, 2);
            $year = substr($data['periode'], -4);
            $aptgl = '2023-10-01';

            $queryloop = DB::connection('sqlsrv2')->table("j_happ")->from(db::raw("j_happ a with (readuncommitted)"))
                ->select(
                    db::raw("0 as header_id"),
                    'a.fntrans as header_nobukti',
                    'b.ftgl as header_tglbukti',
                    'a.fket as header_keterangan',
                    'a.fpostfrom as header_postingdari',
                    db::raw("(case when isnull(a.fisapp,0)=1 then 3 else 4 end) as header_statusapproval"),
                    'a.appuserid as header_userapproval',
                    'a.appdate as header_tglapproval',
                    db::raw("0 as header_statusformat"),
                    db::raw("'' as header_info"),
                    'a.fuserid as header_modifiedby',
                    'a.ftglinput as header_created_at',
                    'a.ftglinput as header_updated_at',
                    db::raw("'" . $cabang->namacabang . "' as header_cabang"),
                    db::raw($cabang->id . " as header_cabang_id"),
                    db::raw("0 as detail_id"),
                    db::raw("0 as detail_jurnalumumpusat_id"),
                    'b.fntrans as detail_nobukti',
                    'b.ftgl as detail_tglbukti',
                    'b.fcoa as detail_coa',
                    'b.fcoamain as detail_coamain',
                    'b.fnominal as detail_nominal',
                    'b.fket as detail_keterangan',
                    db::raw("0 as detail_baris"),
                    db::raw("'' as detail_info"),
                    'b.fuserid as detail_modifiedby',
                    'b.ftglinput as detail_created_at',
                    'b.ftglinput as detail_updated_at',
                )
                ->join(db::raw("j_rapp b with (readuncommitted)"), 'a.fntrans', 'b.fntrans')
                ->whereRaw("MONTH(b.ftgl) = " . $month)
                ->whereRaw("YEAR(b.ftgl) = " . $year)
                ->whereRaw("a.ftgl >='" . $aptgl . "'")
                ->whereRaw("a.FKcabang='" . $singkatan . "'")

                ->orderby('a.fntrans', 'asc')
                ->orderby('b.fpostid', 'asc')
                ->get();



            $queryloop = json_encode($queryloop, JSON_INVALID_UTF8_SUBSTITUTE);
            $konsolidasi = json_decode($queryloop, true);
            // dd('test');

            $jurnalRequest = [];
            foreach ($konsolidasi as $item) {
                // Membuat array baru untuk setiap entri header

                if ($data['import'] == $statusImportSisip->id) {

                    $nobukticabang = mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8') . '-' . mb_convert_encoding($item['header_cabang'],  $encode, 'UTF-8');
                    $querysisip = db::table("jurnalumumpusatheader")->from(db::raw("jurnalumumpusatheader a with (readuncommitted)"))
                        ->select()
                        ->where('a.nobukti', $nobukticabang)
                        ->first();
                    if (!isset($querysisip)) {
                        if (!array_key_exists(mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8'), $jurnalRequest)) {
                            $jurnalUmumPusat = new JurnalUmumPusatHeader();
                            $jurnalUmumPusat->nobukti = mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->tglbukti = mb_convert_encoding($item['header_tglbukti'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->keterangan = mb_convert_encoding($item['header_keterangan'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->postingdari = mb_convert_encoding($item['header_postingdari'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->statusapproval = mb_convert_encoding($item['header_statusapproval'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->userapproval = mb_convert_encoding($item['header_userapproval'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->tglapproval = mb_convert_encoding($item['header_tglapproval'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->statusformat = mb_convert_encoding($item['header_statusformat'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->info = mb_convert_encoding($item['header_info'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->modifiedby = mb_convert_encoding($item['header_modifiedby'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->created_at = mb_convert_encoding($item['header_created_at'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->updated_at = mb_convert_encoding($item['header_updated_at'],  $encode, 'UTF-8');
                            $jurnalUmumPusat->cabang_id = $data['cabang'];
                            $jurnalUmumPusat->cabang = $cabang->namacabang ?? '';


                            if (!$jurnalUmumPusat->save()) {
                                throw new \Exception("Error storing jurnal umum pusat header.");
                            }
                            $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')] = $jurnalUmumPusat;
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
                        $jurnalUmumPusatDetail->jurnalumumpusat_id = $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')]->id;
                        $jurnalUmumPusatDetail->nobukti = $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')]->nobukti;
                        $jurnalUmumPusatDetail->tglbukti = $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')]->tglbukti;
                        $jurnalUmumPusatDetail->coa = mb_convert_encoding($item['detail_coa'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->coamain = mb_convert_encoding($item['detail_coamain'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->nominal = mb_convert_encoding($item['detail_nominal'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->keterangan = mb_convert_encoding($item['detail_keterangan'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->baris = mb_convert_encoding($item['detail_baris'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->info = mb_convert_encoding($item['detail_info'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->modifiedby = mb_convert_encoding($item['detail_modifiedby'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->created_at = mb_convert_encoding($item['detail_created_at'],  $encode, 'UTF-8');
                        $jurnalUmumPusatDetail->updated_at = mb_convert_encoding($item['detail_updated_at'],  $encode, 'UTF-8');
                        if (!$jurnalUmumPusatDetail->save()) {
                            throw new \Exception("Error storing jurnal umum pusat detail.");
                        }
                    }
                } else {
                    if (!array_key_exists(mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8'), $jurnalRequest)) {
                        $jurnalUmumPusat = new JurnalUmumPusatHeader();
                        $jurnalUmumPusat->nobukti = mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->tglbukti = mb_convert_encoding($item['header_tglbukti'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->keterangan = mb_convert_encoding($item['header_keterangan'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->postingdari = mb_convert_encoding($item['header_postingdari'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->statusapproval = mb_convert_encoding($item['header_statusapproval'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->userapproval = mb_convert_encoding($item['header_userapproval'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->tglapproval = mb_convert_encoding($item['header_tglapproval'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->statusformat = mb_convert_encoding($item['header_statusformat'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->info = mb_convert_encoding($item['header_info'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->modifiedby = mb_convert_encoding($item['header_modifiedby'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->created_at = mb_convert_encoding($item['header_created_at'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->updated_at = mb_convert_encoding($item['header_updated_at'],  $encode, 'UTF-8');
                        $jurnalUmumPusat->cabang_id = $data['cabang'];
                        $jurnalUmumPusat->cabang = $cabang->namacabang ?? '';


                        if (!$jurnalUmumPusat->save()) {
                            throw new \Exception("Error storing jurnal umum pusat header.");
                        }
                        $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')] = $jurnalUmumPusat;
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
                    $jurnalUmumPusatDetail->jurnalumumpusat_id = $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')]->id;
                    $jurnalUmumPusatDetail->nobukti = $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')]->nobukti;
                    $jurnalUmumPusatDetail->tglbukti = $jurnalRequest[mb_convert_encoding($item['header_nobukti'],  $encode, 'UTF-8')]->tglbukti;
                    $jurnalUmumPusatDetail->coa = mb_convert_encoding($item['detail_coa'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->coamain = mb_convert_encoding($item['detail_coamain'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->nominal = mb_convert_encoding($item['detail_nominal'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->keterangan = mb_convert_encoding($item['detail_keterangan'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->baris = mb_convert_encoding($item['detail_baris'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->info = mb_convert_encoding($item['detail_info'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->modifiedby = mb_convert_encoding($item['detail_modifiedby'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->created_at = mb_convert_encoding($item['detail_created_at'],  $encode, 'UTF-8');
                    $jurnalUmumPusatDetail->updated_at = mb_convert_encoding($item['detail_updated_at'],  $encode, 'UTF-8');
                    if (!$jurnalUmumPusatDetail->save()) {
                        throw new \Exception("Error storing jurnal umum pusat detail.");
                    }
                }
            }

            // dd('test');
            // set akunpusatdetail

            // dd('test');

            $bulan = substr($data['periode'], 0, 2);
            $tahun = substr($data['periode'], -4);
            $cabang_id = $cabang->id ?? 0;
            $ptgl = $tahun . '-' . $bulan . '-01';
            DB::table('akunpusatdetail')
                ->where('bulan', '<>', 0)
                ->whereRaw("cabang_id=" . $cabang_id)
                ->whereRaw("cast(trim(str(" . $tahun . "))+'/'+trim(str(" . $bulan . "))+'/1' as datetime)>='" . $ptgl . "'")
                ->delete();


            // $subquery1 = DB::table('jurnalumumpusatheader as J')
            //     ->select(
            //         'D.coamain as FCOA',
            //         DB::raw('YEAR(D.tglbukti) as FThn'),
            //         DB::raw('MONTH(D.tglbukti) as FBln'),
            //         db::raw($cabang_id . " as cabang_id"),
            //         DB::raw('round(SUM(D.nominal),2) as FNominal'),
            //     )


            //     ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
            //     ->join('mainakunpusat as C', 'C.coa', '=', 'D.coamain')
            //     ->where('D.tglbukti', '>=', $ptgl)
            //     ->where('j.cabang_id',  $cabang_id)
            //     ->groupBy('D.coamain', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

            // $subquery2 = DB::table('jurnalumumpusatheader as J')
            //     ->select(
            //         'LR.coa',
            //         DB::raw('YEAR(D.tglbukti) as FThn'),
            //         DB::raw('MONTH(D.tglbukti) as FBln'),
            //         db::raw($cabang_id . " as cabang_id"),
            //         DB::raw('round(SUM(D.nominal),2) as FNominal'),
            //     )
            //     ->join('jurnalumumpusatdetail as D', 'J.nobukti', '=', 'D.nobukti')
            //     ->join('perkiraanlabarugi as LR', function ($join) {
            //         $join->on('LR.tahun', '=', DB::raw('YEAR(J.tglbukti)'))
            //             ->on('LR.bulan', '=', DB::raw('MONTH(J.tglbukti)'));
            //     })
            //     ->whereIn('D.coamain', function ($query) {
            //         $query->select(DB::raw('DISTINCT C.coa'))
            //             ->from('maintypeakuntansi as AT')
            //             ->join('mainakunpusat as C', 'AT.kodetype', '=', 'C.Type')
            //             ->where('AT.order', '>=', 4000)
            //             ->where('AT.order', '<', 9000)

            //             ->where('C.type', '<>', 'Laba/Rugi');
            //     })
            //     ->where('D.tglbukti', '>=', $ptgl)
            //     ->where('j.cabang_id',  $cabang_id)
            //     ->groupBy('LR.coa', DB::raw('YEAR(D.tglbukti)'), DB::raw('MONTH(D.tglbukti)'));

            // $RecalKdPerkiraan = DB::table(DB::raw("({$subquery1->toSql()} UNION ALL {$subquery2->toSql()}) as V"))
            //     ->mergeBindings($subquery1)
            //     ->mergeBindings($subquery2)
            //     ->groupBy('FCOA', 'FThn', 'FBln', 'cabang_id')
            //     ->select('FCOA', 'FThn', 'FBln', 'cabang_id', DB::raw('round(SUM(FNominal),2) as FNominal'));

            $RecalKdPerkiraan=DB::connection('sqlsrv2')->table("coa_r")->from(db::raw("coa_r a with (readuncommitted)"))     
            ->select(
                'a.fcoa as coa',
                'a.fthn as tahun',
                'a.fbln as bulan',
                db::raw($cabang_id . " as cabang_id"),
                'a.fnominal as nominal',
            )   
            ->whereRaw("a.fbln = " . $bulan)
            ->whereRaw("a.fthn = " . $tahun)
            ->whereRaw("a.FKcabang='" . $singkatan . "'") 
            ->get() ;


            $RecalKdPerkiraan = json_encode($RecalKdPerkiraan, JSON_INVALID_UTF8_SUBSTITUTE);
            $konsolidasicoar = json_decode($RecalKdPerkiraan, true);
            // dd('test');

            foreach ($konsolidasicoar as $item2) {
                $akunPusatDetail = new AkunPusatDetail();
                $akunPusatDetail->coa = mb_convert_encoding($item2['coa'],  $encode, 'UTF-8');
                $akunPusatDetail->tahun = mb_convert_encoding($item2['tahun'],  $encode, 'UTF-8');
                $akunPusatDetail->bulan = mb_convert_encoding($item2['bulan'],  $encode, 'UTF-8');
                $akunPusatDetail->cabang_id = $data['cabang'];
                $akunPusatDetail->nominal = mb_convert_encoding($item2['nominal'],  $encode, 'UTF-8');


                if (!$akunPusatDetail->save()) {
                    throw new \Exception("Error storing Akun Puat Detail");
                }
            }
            // DB::table('akunpusatdetail')->insertUsing([
            //     'coa',
            //     'tahun',
            //     'bulan',
            //     'cabang_id',
            //     'nominal',

            // ], $RecalKdPerkiraan);
        }


        return "Data Periode " . $data['periode'] . " Cabang $cabang->namacabang Berhasil di Import";
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