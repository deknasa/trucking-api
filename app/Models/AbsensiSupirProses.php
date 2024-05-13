<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbsensiSupirProses extends Model
{
    use HasFactory;

    protected $table = 'absensisupirproses';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function processStore(AbsensiSupirHeader $absensiSupir ,array $data) {
        $jenisKendaraanTangki = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id','text')->where('grp', 'STATUS JENIS KENDARAAN')->where('subgrp', 'STATUS JENIS KENDARAAN')->where('text', 'TANGKI')->first();
        $jenisKendaraanGandengan = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id','text')->where('grp', 'STATUS JENIS KENDARAAN')->where('subgrp', 'STATUS JENIS KENDARAAN')->where('text', 'GANDENGAN')->first();
        

        $kasGantungRequest = [
            "tglbukti" => $absensiSupir->tglbukti,
            "penerima" => '',
            "coakaskeluar" => '',
            "pengeluaran_nobukti" => '',
            "postingdari" => 'Absensi Supir Proses',
            'proseslain' => 'absensisupir',
        ];


        //cari absensiSupirProses
        $absensiSupirProses = AbsensiSupirProses::where('absensi_id',$absensiSupir->id);
        //jika belum buat baru
        if (!AbsensiSupirProses::where('absensi_id',$absensiSupir->id)->where('statusjeniskendaraan', $jenisKendaraanTangki->id)->count()) {
            
            if ($data['rowTotalTangki'] >0) {
                $absensiSupirProsesTangki = new AbsensiSupirProses;
                $absensiSupirProsesTangki->absensi_id = $absensiSupir->id;
                $absensiSupirProsesTangki->nobukti = $absensiSupir->nobukti;
                $absensiSupirProsesTangki->statusjeniskendaraan = $jenisKendaraanTangki->id;
                $absensiSupirProsesTangki->keterangan = $data['keteranganTangki'];
                $absensiSupirProsesTangki->modifiedby = auth('api')->user()->name;
                $absensiSupirProsesTangki->info = html_entity_decode(request()->info);
                $absensiSupirProsesTangki->save();
            }
        }
        if (!AbsensiSupirProses::where('absensi_id',$absensiSupir->id)->where('statusjeniskendaraan', $jenisKendaraanGandengan->id)->count()) {    
            if ($data['rowTotalGandengan'] >0) {
                
                $absensiSupirProsesGandengan = new AbsensiSupirProses;
                $absensiSupirProsesGandengan->absensi_id = $absensiSupir->id;
                $absensiSupirProsesGandengan->nobukti = $absensiSupir->nobukti;
                $absensiSupirProsesGandengan->statusjeniskendaraan = $jenisKendaraanGandengan->id;
                $absensiSupirProsesGandengan->keterangan = $data['keteranganGandengan'];                
                $absensiSupirProsesGandengan->modifiedby = auth('api')->user()->name;
                $absensiSupirProsesGandengan->info = html_entity_decode(request()->info);
                $absensiSupirProsesGandengan->save();
            }
        }

        //cari yang sesuai jeniskendaraan dan ambil kasgantung_nobukti nya
        $absensiSupirProsesTangki = AbsensiSupirProses::where('absensi_id',$absensiSupir->id)->where('statusjeniskendaraan', $jenisKendaraanTangki->id)->first();
        $absensiSupirProsesGandengan = AbsensiSupirProses::where('absensi_id',$absensiSupir->id)->where('statusjeniskendaraan', $jenisKendaraanGandengan->id)->first();

        //cari kasgantung dari absensiSupirProses
        $kasGantungTangki =null;
        if ($absensiSupirProsesTangki) {
            if ($absensiSupirProsesTangki->kasgantung_nobukti) {
                $kasGantungTangki = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))->where('nobukti', $absensiSupirProsesTangki->kasgantung_nobukti)->first();
            }
        }
        $kasGantungGandengan =null;
        if ($absensiSupirProsesGandengan) {
            if ($absensiSupirProsesGandengan->kasgantung_nobukti) {
                $kasGantungGandengan = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))->where('nobukti', $absensiSupirProsesGandengan->kasgantung_nobukti)->first();
            }
        }
       
        //jika ada absensi tangki
        if ($data['rowTotalTangki'] >0) {
            $bank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select('id')->where('kodebank', '=', 'KAS TRUCKING TNL')->first();
            $kasGantungRequest["bank_id"] = $bank->id;
            $kasGantungRequest["nominal"] = [$data['uangJalanTangki']];
            $kasGantungRequest["keterangan_detail"] = [$data['keteranganTangki']];
            //update kasgantung jika ada ,tambahkan jika belum ada
            if($kasGantungTangki){
                $kasGantungHeaderTangki = (new KasGantungHeader())->processUpdate($kasGantungTangki, $kasGantungRequest);                
            }else{
                $kasGantungHeaderTangki = (new KasGantungHeader())->processStore($kasGantungRequest);
            }
            $absensiSupirProsesTangki->nominal = $data['uangJalanTangki'];
            $absensiSupirProsesTangki->kasgantung_nobukti = $kasGantungHeaderTangki->nobukti;
            $absensiSupirProsesTangki->save();
        }else {
            if ($absensiSupirProsesTangki) {
                $absensiSupirProsesTangki->delete();
            }
            if ($kasGantungTangki) {
                (new KasGantungHeader())->processDestroy($kasGantungTangki->id, ($postingdari == "") ? $postingdari : strtoupper('DELETE ABSENSI SUPIR proses'));
            }
        }
        //jika ada absensi gandengan
        if ($data['rowTotalGandengan'] >0) {
            $bank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select('id')->where('kodebank', '=', 'KAS TRUCKING')->first();
            $kasGantungRequest["bank_id"] = $bank->id;
            $kasGantungRequest["nominal"] = [$data['uangJalanGandengan']];
            $kasGantungRequest["keterangan_detail"] = [$data['keteranganGandengan']];
            //update kasgantung jika ada ,tambahkan jika belum ada
            if($kasGantungGandengan){
                $kasGantungHeaderGandengan = (new KasGantungHeader())->processUpdate($kasGantungGandengan, $kasGantungRequest);                
            }else{
                $kasGantungHeaderGandengan = (new KasGantungHeader())->processStore($kasGantungRequest);
            }
            // dd($kasGantungHeaderGandengan);
            $absensiSupirProsesGandengan->nominal = $data['uangJalanGandengan'];
            $absensiSupirProsesGandengan->kasgantung_nobukti = $kasGantungHeaderGandengan->nobukti;
            $absensiSupirProsesGandengan->save();
        }else {
            if ($absensiSupirProsesGandengan) {
                $absensiSupirProsesGandengan->delete();
            }
            if ($kasGantungGandengan) {
                (new KasGantungHeader())->processDestroy($kasGantungGandengan->id, ($postingdari == "") ? $postingdari : strtoupper('DELETE ABSENSI SUPIR proses'));
            }
        }
    }

    public function processDestroy(AbsensiSupirHeader $absensiSupir,string $postingdari=""){
        $absensiSupirProses = AbsensiSupirProses::where('absensi_id', $absensiSupir->id)->get();

        if ($absensiSupirProses) {
            foreach ($absensiSupirProses as $proses) {
                /*DELETE EXISTING JURNAL*/
                $kasGantungHeader = KasGantungHeader::where('nobukti', $proses->kasgantung_nobukti)->first();
               
                if ($kasGantungHeader) {
                    (new KasGantungHeader())->processDestroy($kasGantungHeader->id, ($postingdari == "") ? $postingdari : strtoupper('DELETE Absensi Supir Proses'));
                }
            }
            AbsensiSupirProses::where('absensi_id', $absensiSupir->id)->lockForUpdate()->delete();
        }
    }

    public function getKgtAbsensi($nobukti) {
        
    }
}
