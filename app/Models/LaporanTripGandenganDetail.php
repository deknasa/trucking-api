<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanTripGandenganDetail extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function getReport($gandengandari_id, $gandengansampai_id, $dari, $sampai){
        
        // dd($dari, $sampai, $gandengandari_id, $gandengansampai_id);
        $Tempsuratpengantar = '##Tempsuratpengantar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempsuratpengantar, function ($table) {
            $table->date('tglbukti')->nullable(); 
            $table->string('nosp', 1000)->nullable();
            $table->integer('supir_id')->nullable();
            $table->integer('gandengan_id')->nullable();
            $table->integer('container_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('upah_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('nocont', 1000)->nullable();
        });

        $select_suratpengantar = DB::table('suratpengantar AS A')
        ->select([
            'A.tglbukti as tanggal',
            'A.nosp',
            'A.supir_id',
            'A.Gandengan_id',
            'A.Container_id',
            'A.trado_id',
            'A.upah_id',
            'A.keterangan',
            'A.nocont',          
        ])
        ->where('A.tglbukti', '>=', $dari)
        ->where('A.tglbukti', '<=', $sampai);
        if($gandengandari_id != 0 && $gandengansampai_id !=0){
            $select_suratpengantar  
            ->where('A.gandengan_id', '>=', $gandengandari_id)
            ->where('A.gandengan_id', '<=', $gandengansampai_id);
        }
 
        // dd($select_suratpengantar->get());
         
        DB::table($Tempsuratpengantar)->insertUsing([
            'tglbukti',
            'nosp',
            'supir_id',
            'gandengan_id',
            'container_id',
            'trado_id',
            'upah_id',
            'keterangan',
            'nocont',
        ], $select_suratpengantar);
  
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

            $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


        $select_Tempsuratpengantar = DB::table('Tempsuratpengantar as A')->from(DB::raw($Tempsuratpengantar . " AS A"))
        ->select([
            DB::raw("B.keterangan as gandengan"),
            "A.tglbukti as tanggal",
            "A.nosp",
            "C.namasupir as supir",
            "A.nocont",
            DB::raw("D.kodetrado as noplat"),
            DB::raw("(ltrim(rtrim(G.keterangan))+' - '+ltrim(rtrim(H.keterangan))) as rute"),
            "F.kodecontainer as cont",
            "A.keterangan",
            DB::raw("'LAPORAN TRIP GANDENGAN DETAIL' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
        ])
        ->join('gandengan as B', 'A.gandengan_id', '=', 'B.id')
        ->join('supir as C', 'A.supir_id', '=', 'C.id')
        ->join('trado as D', 'A.trado_id', '=', 'D.id')
        ->join('upahsupir as E', 'A.upah_id', '=', 'E.id')
        ->join('container as F', 'A.container_id', '=', 'F.id')
        ->leftJoin('kota as G', 'E.kotadari_id', '=', 'G.id')
        ->leftJoin('kota as H', 'E.kotasampai_id', '=', 'H.id')
        ->where('A.tglbukti', '>=', $dari)
        ->where('A.tglbukti', '<=', $sampai)
        ->orderBy('B.keterangan', 'asc')
        ->orderBy('A.tglbukti', 'asc')
        ->orderBy('C.namasupir', 'asc')
        ->orderBy('D.kodetrado', 'asc');
    
        if($gandengandari_id != 0 && $gandengansampai_id !=0){
            $select_suratpengantar  
            ->where('A.gandengan_id', '>=', $gandengandari_id)
            ->where('A.gandengan_id', '<=', $gandengansampai_id);
        }
      
        $data = $select_Tempsuratpengantar->get();
        return $data;
        // dd($select_Tempsuratpengantar->get());

    }
    // public function getReport($gandengandari_id, $gandengansampai_id)
    // {
    //     $Tempsuratpengantar = '##Tempsuratpengantar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    //     Schema::create($Tempsuratpengantar, function ($table) {
    //         $table->date('tglbukti');
    //         $table->string('nosp', 1000);
    //         $table->integer('supir_id');
    //         $table->integer('gandengan_id');
    //         $table->integer('container_id');
    //         $table->integer('trado_id');
    //         $table->integer('upah_id');
    //         $table->longText('keterangan');
    //         $table->string('nocont', 1000);
    //     });
     
    //     $select_suratpengantar = DB::table('suratpengantar')->from(DB::raw("suratpengantar AS A WITH (READUNCOMMITTED)"))
    //     ->select([
    //         'A.tglbukti',
    //         'A.nosp',
    //         'A.supir_id',
    //         'A.Gandengan_id',
    //         'A.Container_id',
    //         'A.trado_id',
    //         'A.upah_id',
    //         'A.keterangan',
    //         'A.nocont',          
    //     ])
    //     ->where('A.gandengan_id', '>', $gandengandari_id)
    //     ->where('A.gandengan_id', '<', $gandengansampai_id)
    //     ->where('A.tglbukti', '>', $gandengandari)
    //     ->where('A.tglbukti', '<', $gandengansampai);
    //     dd($select_suratpengantar->get());
        






























        
        // DB::table($Tempsuratpengantar)->insertUsing([
        //     'tglbukti',
        //     'nosp',
        //     'supir_id',
        //     'gandengan_id',
        //     'container_id',
        //     'trado_id',
        //     'upah_id',
        //     'keterangan',
        //     'nocont',
        // ], $select_suratpengantar);

        
    
        // $select_Tempsuratpengantar = DB::table('Tempsuratpengantar')->from(DB::raw($Tempsuratpengantar . " AS a"))
        // ->select([
        //     DB::raw("B.keterangan as gandengan"),
        //     "A.tglbukti",
        //     "A.nosp",
        //     "C.namasupir",
        //     "A.nocont",
        //     DB::raw("D.kodetrado as nopol"),
        //     DB::raw("(ltrim(rtrim(g.keterangan))+' - '+ltrim(rtrim(h.keterangan))) as rute"),
        //     "F.kodecontainer",
        //     "A.keterangan"

        // ])
        // ->join('gandengan as B', 'A.gandengan_id', '=', 'B.id')
        // ->join('supir as c', 'A.supir_id', '=', 'C.id')
        // ->join('trado as D', 'A.trado_id', '=', 'd.id')
        // ->join('upahsupir as E', 'A.upah_id', '=', 'e.id')
        // ->join('container as F', 'A.container_id', '=', 'e.id')
        // ->leftJoin(DB::raw("(select * from kota with (readuncommitted)) as G"), 'E.kotagandengandari_id', '=', 'G.id')
        // ->leftJoin(DB::raw("(select * from kota with (readuncommitted)) as H"), 'E.kotasampai_id', '=', 'H.id')
        // ->where('A.gandengan_id', '>=', $gandengandari_id)
        // ->where('A.gandengan_id', '<=', $gandengansampai_id)
        // ->where('A.tglbukti', '>=', $gandengandari)
        // ->where('A.tglbukti', '<=', $gandengansampai)
        // ->orderBy('B.keterangan', 'asc')
        // ->orderBy('A.tglbukti', 'asc')
        // ->orderBy('c.namasupir', 'asc')
        // ->orderBy('d.kodetrado', 'asc');
        
        // // dd($select_Tempsuratpengantar->get());

        // $data = $select_Tempsuratpengantar->get();
        // return $data;
        // dd($data);







       
    }
