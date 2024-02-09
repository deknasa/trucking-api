<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalStokReuse extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'approvalstokreuse';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function processStokReuseStore(Stok $stok) : ApprovalStokReuse {
        $approvalStokReuse = new ApprovalStokReuse();
        $checkStok = $approvalStokReuse->where('stok_id',$stok->id)->first();
        if ($checkStok) {
            $approvalStokReuse = $checkStok;
        }
        
        $approvalStokReuse->stok_id = $stok->id;
        $approvalStokReuse->info = html_entity_decode(request()->info);
        $approvalStokReuse->modifiedby = auth('api')->user()->name;
        $approvalStokReuse->updated_at = date('Y-m-d H:i:s');
        $approvalStokReuse->save();
        return $approvalStokReuse;

    }

    public function getReport($stok_id) {
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $pengeluaranStok = new PengeluaranStokDetail();
        $checkStok = $pengeluaranStok->select(
            "pengeluaranstokdetail.nobukti",
            "pengeluaranstokdetail.stok_id",
            "stok.namastok",
            "pengeluaranstokheader.tglbukti",
            "pengeluaranstokheader.gudang_id",
            "gudang.gudang",
            "pengeluaranstokheader.trado_id",
            "trado.kodetrado",
            "pengeluaranstokheader.gandengan_id",
            "gandengan.kodegandengan",
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
        )
        ->leftJoin(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokdetail.pengeluaranstokheader_id', 'pengeluaranstokheader.id')
        ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluaranstokdetail.stok_id', 'stok.id')
        ->leftJoin(DB::raw("gudang with (readuncommitted)"), 'pengeluaranstokheader.gudang_id', 'gudang.id')
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluaranstokheader.trado_id', 'trado.id')
        ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'pengeluaranstokheader.gandengan_id', 'gandengan.id')
        ->where("pengeluaranstokdetail.stok_id",$stok_id)
        ->where("pengeluaranstokheader.pengeluaranstok_id",$spk->text)->get();
        
        return $checkStok;
    }

}
