<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKalkulasiEmkl extends Model
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

    public function getReport($periode,$jenis)
    {
        $periode = $periode ;
        $statusposting = $jenis;
        $parameter = new Parameter();
        $idstatusposting = $parameter->cekId('STATUS POSTING', 'STATUS POSTING', 'POSTING') ?? 0;
        $penerimaanTruckingHeader = '##penerimaantruckingheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $jenisorderan=db::table("jenisorder")->from(db::raw("jenisorder a with (readuncommitted)"))
        ->select(
            'a.keterangan'
        )
        ->where('a.id',$jenis)
        ->first()->keterangan ?? '';


        // dd(db::table($penerimaanTruckingDetailrekap)->get());

        $temporderan = '##temporderan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temporderan, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longtext('shipper')->nullable();
            $table->longtext('tujuan')->nullable();
            $table->longtext('container')->nullable();
            $table->longtext('jenisorder')->nullable();
            $table->longtext('marketing')->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('destination')->nullable();
            $table->longtext('nocontseal')->nullable();
            $table->longtext('lokasibongkarmuat')->nullable();
        });

        $queryorderan=db::table("jobemkl")->from(db::raw("jobemkl a with (readuncommitted)"))
        ->select (
            'a.id',
            'a.nobukti',
            'a.tglbukti',
            db::raw("isnull(b.namapelanggan,'') as shipper"),
            db::raw("isnull(c.keterangan,'') as tujuan"),
            db::raw("isnull(d.kodecontainer,'') as container"),
            db::raw("isnull(e.keterangan,'') as jenisorder"),
            db::raw("isnull(f.keterangan,'') as marketing"),
            'a.kapal',
            'a.destination',
            db::raw("trim(isnull(a.nocont,''))+' / '+trim(isnull(a.noseal,''))as nocontseal"),
            'a.lokasibongkarmuat',
        )
        ->leftjoin(db::raw("pelanggan b with (readuncommitted)"),'a.shipper_id','b.id')
        ->leftjoin(db::raw("tujuan c with (readuncommitted)"),'a.tujuan_id','c.id')
        ->leftjoin(db::raw("container d with (readuncommitted)"),'a.container_id','d.id')
        ->leftjoin(db::raw("jenisorder e with (readuncommitted)"),'a.jenisorder_id','e.id')
        ->leftjoin(db::raw("marketing f with (readuncommitted)"),'a.marketing_id','f.id')
        ->whereraw("format(a.tglbukti,'MM-yyyy')='$periode'")
        ->where('a.jenisorder_id', '=', $jenis);
      
        // dd($queryorderan->get());
        DB::table($temporderan)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'shipper',
            'tujuan',
            'container',
            'jenisorder',
            'marketing',
            'kapal',
            'destination',
            'nocontseal',
            'lokasibongkarmuat',
        ], $queryorderan);

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longtext('shipper')->nullable();
            $table->longtext('tujuan')->nullable();
            $table->longtext('container')->nullable();
            $table->longtext('jenisorder')->nullable();
            $table->longtext('marketing')->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('destination')->nullable();
            $table->longtext('nocontseal')->nullable();
            $table->longtext('lokasibongkarmuat')->nullable();
        });

        $queryTempLaporan = DB::table($temporderan)->from(
            DB::raw($temporderan . " as a")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.shipper',
                'a.tujuan',
                'a.container',
                'a.jenisorder',
                'a.marketing',
                'a.kapal',
                'a.destination',
                'a.nocontseal',
                'a.lokasibongkarmuat',

            )
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('a.nobukti', 'ASC');

  
        DB::table($tempLaporan)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'shipper',
            'tujuan',
            'container',
            'jenisorder',
            'marketing',
            'kapal',
            'destination',
            'nocontseal',
            'lokasibongkarmuat',
        ], $queryTempLaporan);



        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $parameter = new Parameter();
        
        $queryRekap = DB::table($tempLaporan)->from(
            DB::raw($tempLaporan . " as a")
        )
            ->select(
                db::raw("cast(a.tglbukti as date) as tanggal"),
                'a.nobukti',
                'a.shipper',
                'a.tujuan',
                'a.container',
                'a.jenisorder',
                'a.marketing',
                'a.kapal',
                'a.destination',
                'a.nocontseal',
                'a.lokasibongkarmuat',   
                db::raw("'' as penerima"),
                db::raw("'' as voy"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("upper('Job Emkl ". $jenisorderan." Bulan : ".$periode ."') as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
            )
            ->orderBy('a.id');


            $data = $queryRekap->get();

            // dd($data);
        return $data;
    }
}
