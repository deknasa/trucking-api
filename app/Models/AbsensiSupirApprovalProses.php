<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbsensiSupirApprovalProses extends Model
{
    use HasFactory;

    protected $table = 'absensisupirapprovalproses';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function processStore(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader,array $data) {
        
        $jenisKendaraanTangki = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id','text')->where('grp', 'STATUS JENIS KENDARAAN')->where('subgrp', 'STATUS JENIS KENDARAAN')->where('text', 'TANGKI')->first();
        $jenisKendaraanGandengan = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id','text')->where('grp', 'STATUS JENIS KENDARAAN')->where('subgrp', 'STATUS JENIS KENDARAAN')->where('text', 'GANDENGAN')->first();

        $coaKreditApproval = DB::table('parameter')->where('grp', 'JURNAL APPROVAL ABSENSI SUPIR')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($coaKreditApproval->memo, true);
        
        $coaDebetApproval = DB::table('parameter')->where('grp', 'JURNAL APPROVAL ABSENSI SUPIR')->where('subgrp', 'DEBET')->first();
        $memoDebet = json_decode($coaDebetApproval->memo, true);
        
        $absenKGT =  AbsensiSupirProses::where('nobukti',$absensiSupirApprovalHeader->absensisupir_nobukti)->get();
        $bank = DB::table('bank')->where('coa', $memoKredit['JURNAL'])->first();

        foreach ($absenKGT as $absenproses) {
                        
            $kasGantungDetail = DB::table('kasgantungdetail')->where('nobukti', $absenproses->kasgantung_nobukti)->first();
            $kasGantungRequest = [
                "tglbukti" => $absensiSupirApprovalHeader->tglbukti,
                "penerima" => '',
                "bank_id" => $bank->id,
                "postingdari" => 'ENTRY ABSENSI SUPIR APPROVAL PROSESS',
                "from" => 'AbsensiSupirApprovalHeader',
                "coakredit" => [$memoKredit['JURNAL']],
                "coadebet" => [$memoDebet['JURNAL']],
                "nominal" => [$kasGantungDetail->nominal],
                "keterangan_detail" => [$kasGantungDetail->keterangan],
            ];
            $kasGantung = KasGantungHeader::where('nobukti', $absenproses->kasgantung_nobukti)->lockForUpdate()->first();
            $kasGantungHeader = (new KasGantungHeader())->processUpdate($kasGantung, $kasGantungRequest);
            
            $absensiSupirApprovalProses = new AbsensiSupirApprovalProses;
            $absensiSupirApprovalProses->nobukti = $absensiSupirApprovalHeader->nobukti;
            $absensiSupirApprovalProses->absensisupirapproval_id = $absensiSupirApprovalHeader->id;
            $absensiSupirApprovalProses->pengeluaran_nobukti = $kasGantung->pengeluaran_nobukti;
            $absensiSupirApprovalProses->coakaskeluar = $memoKredit['JURNAL'];
            $absensiSupirApprovalProses->keterangan = $kasGantungDetail->keterangan;
            $absensiSupirApprovalProses->nominal = $kasGantungDetail->nominal;
            $absensiSupirApprovalProses->statusjeniskendaraan = $absenproses->statusjeniskendaraan;
            $absensiSupirApprovalProses->modifiedby =  auth('api')->user()->name;
            $absensiSupirApprovalProses->info = html_entity_decode(request()->info);
            $absensiSupirApprovalProses->save();
        }
    }


    public function processDestroy(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader,string $postingdari=""){
        $absensiSupirProses = AbsensiSupirApprovalProses::where('absensisupirapproval_id', $absensiSupirApprovalHeader->id);
        
        if ($absensiSupirProses->count()) {
            foreach ($absensiSupirProses->get() as $proses) {

                $pengeluaran = $proses->pengeluaran_nobukti;
                $kasGantung = KasGantungHeader::where('pengeluaran_nobukti', $pengeluaran)->lockForUpdate()->first();
        
                $kasGantung->pengeluaran_nobukti = '';
                $kasGantung->coakaskeluar = '';
                $kasGantung->save();
        
        
                $pengeluaran = PengeluaranHeader::where('nobukti', $proses->pengeluaran_nobukti)->lockForUpdate()->first();
                (new PengeluaranHeader())->processDestroy($pengeluaran->id, 'Absensi Supir Approval');
            }
            $absensiSupirProses->lockForUpdate()->delete();
        }
    }
    
}
