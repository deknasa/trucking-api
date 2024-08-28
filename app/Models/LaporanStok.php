<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\KartuStok;

class LaporanStok extends MyModel
{
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

    public function getReport($bulan, $tahun)
    {


        $tgl = '01-' . $bulan . '-' . $tahun;

        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));
        $tglsampai1 = date('Y-m-d', strtotime('+1 days', strtotime($tgl2)));



        // $tglsampai= date("Y-m-d", strtotime("+1 day", strtotime($tgldari)));


        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->longText('lokasi')->nullable();
            $table->string('kodebarang', 1000)->nullable();
            $table->string('namabarang', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->string('kategori_id', 500)->nullable();
            $table->double('qtymasuk', 15, 2)->nullable();
            $table->double('nilaimasuk', 15, 2)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->double('nilaikeluar', 15, 2)->nullable();
            $table->double('qtysaldo', 15, 2)->nullable();
            $table->double('nilaisaldo', 15, 2)->nullable();
            $table->string('modifiedby', 100)->nullable();
            $table->integer('urutfifo')->nullable();
            $table->integer('iddata')->nullable();       
            $table->datetime('tglinput')->nullable();
     
        });

        $tempstoktransaksi = '##tempstoktransaksi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstoktransaksi, function ($table) {
            $table->id();
            $table->string('kodebarang', 1000)->nullable();
        });



        $idgudangkantor = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('grp', 'GUDANG KANTOR')
            ->where('subgrp', 'GUDANG KANTOR')
            ->first()->text ?? 0;


        $filtergudang = Parameter::where('grp', 'STOK PERSEDIAAN')->where('subgrp', 'STOK PERSEDIAAN')->where('text', 'GUDANG')->first();

        $trado_id = 0;
        $gandengan_id = 0;


        $stokdari_id = 0;
        $stoksampai_id = 0;

        // dd($filter);
        $kartustok = new KartuStok();
        $stokgantung=true;
        DB::table($temprekapall)->insertUsing([
            'stok_id',
            'gudang_id',
            'trado_id',
            'gandengan_id',
            'lokasi',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
            'urutfifo',
            'iddata',     
            'tglinput',       
        ], (new KartuStok())->getlaporan($tgldari, $tglsampai, $stokdari_id, $stoksampai_id, $idgudangkantor, $trado_id, $gandengan_id, $filtergudang,$stokgantung));

        $temphistory = '##temphistory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphistory, function ($table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
        });  

        $queryhistory=db::table($temprekapall)->from(db::raw($temprekapall ." a "))
        ->select (
            'a.stok_id',
            'a.gudang_id',
            'a.trado_id',
            'a.gandengan_id',            
        //     'a.qtykeluar',            
        //     'a.qtymasuk',            
        )
        ->whereraw("(a.qtykeluar>0 or a.qtymasuk>0 or a.qtysaldo>0)")
        // ->where('a.stok_id',6965);
        ->groupby('a.stok_id')
        ->groupby('a.gudang_id')
        ->groupby('a.trado_id')
        ->groupby('a.gandengan_id');
        // dd(db::table($queryhistory)->where('stok_id',6965)->get());
        // dd($queryhistory->get());

        DB::table($temphistory)->insertUsing([
            'stok_id',
            'gudang_id',
            'trado_id',
            'gandengan_id',
        ], $queryhistory);

        // dd(db::table($temphistory)->where('stok_id',6965)->get());

        DB::delete(DB::raw("delete " . $temprekapall . " from " . $temprekapall . " as a left outer join " . $temphistory . " b on isnull(a.stok_id,0)=isnull(b.stok_id,0) and isnull(a.gudang_id,0)=isnull(b.gudang_id,0)
        and isnull(a.trado_id,0)=isnull(b.trado_id,0) and isnull(a.gandengan_id,0)=isnull(b.gandengan_id,0) 
        where isnull(b.stok_id,0)=0
        "));



        $tempstok = '##tempstok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstok, function ($table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
        });        

        $querystok=db::table($temprekapall)->from(db::raw($temprekapall ." a "))
        ->select (
            'a.stok_id',
            'a.gudang_id',
            'a.trado_id',
            'a.gandengan_id',            
        )
        ->groupby('a.stok_id')
        ->groupby('a.gudang_id')
        ->groupby('a.trado_id')
        ->groupby('a.gandengan_id');

        DB::table($tempstok)->insertUsing([
            'stok_id',
            'gudang_id',
            'trado_id',
            'gandengan_id',
        ], $querystok);

        // dump(db::table($tempstok)->where('stok_id',6944)->get());
        // dd(db::table($temprekapall)->where('stok_id',6944)->get());

        DB::delete(DB::raw("delete " . $tempstok . " from " . $tempstok . " as a inner join " . $temprekapall . " b on isnull(a.stok_id,0)=isnull(b.stok_id,0) and isnull(a.gudang_id,0)=isnull(b.gudang_id,0)
        and isnull(a.trado_id,0)=isnull(b.trado_id,0) and isnull(a.gandengan_id,0)=isnull(b.gandengan_id,0) and isnull(b.nobukti,'')='SALDO AWAL'
        "));

        $querysaldoawal=db::table($tempstok)->from(db::raw($tempstok ." a "))
        ->select (
            'a.stok_id',
            'a.gudang_id',
            'a.trado_id',
            'a.gandengan_id',
            db::raw("'' as lokasi"),
            db::raw("isnull(b.namastok,'') as kodebarang"),
            db::raw("isnull(b.namastok,'') as namabarang"),
            db::raw("'". $tgldari ."' as tglbukti"),
            db::raw("'SALDO AWAL' as nobukti"),
            db::raw("'' as kategori_id"),
            db::raw("0 as qtymasuk"),
            db::raw("0 as nilaimasuk"),
            db::raw("0 as qtykeluar"),
            db::raw("0 as nilaikeluar"),
            db::raw("0 as qtysaldo"),
            db::raw("0 as nilaisaldo"),
            db::raw("0 as modifiedby"),
        )
        ->join(db::raw("stok b with (readuncommitted)"),'a.stok_id','b.id');


        DB::table($temprekapall)->insertUsing([
            'stok_id',
            'gudang_id',
            'trado_id',
            'gandengan_id',
            'lokasi',
            'kodebarang',
            'namabarang',
            'tglbukti',
            'nobukti',
            'kategori_id',
            'qtymasuk',
            'nilaimasuk',
            'qtykeluar',
            'nilaikeluar',
            'qtysaldo',
            'nilaisaldo',
            'modifiedby',
        ], $querysaldoawal);

        // dd(db::table($temprekapall)->where('stok_id',6944)->get());




        // $querystoktransaksi = DB::table($temprekapall)->from(db::raw($temprekapall . " as a"))
        //     ->select(
        //         'a.kodebarang',
        //     )
        //     ->whereRaw("upper(a.nobukti)<>'SALDO AWAL'")
        //     ->groupby('a.kodebarang');


        // DB::table($tempstoktransaksi)->insertUsing([
        //     'kodebarang',
        // ],  $querystoktransaksi);

        //  DB::delete(DB::raw("delete " . $temprekapall . " from " . $temprekapall . " as a left outer join " . $tempstoktransaksi . " b on a.kodebarang=b.kodebarang 
        //                      WHERE isnull(b.kodebarang,'')='' and isnull(a.qtysaldo,0)=0"));



        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($temprekapall)->from(
            DB::raw($temprekapall . " a")
        )
            ->select(
                DB::raw("'Laporan Saldo Inventory' as header"),
                'a.lokasi',
                'a.lokasi as namalokasi',
                DB::raw("'' as kategori"),
                DB::raw("'" . $tgldari . "' as tgldari"),
                DB::raw("'" . $tglsampai . "' as tglsampai"),
                DB::raw("'' as vulkanisirke"),
                DB::raw("isnull(c.keterangan,isnull(d.keterangan,'')) as keterangan"),
                'a.nobukti as nobukti',
                'a.kodebarang as id',
                'a.kodebarang',
                db::raw("(case when isnull(b.keterangan,'')='' then a.namabarang else isnull(b.keterangan,'') end) as namabarang"),
                'a.tglbukti as tglbukti',
                db::raw("(isnull(a.qtymasuk,0)+
                (case when a.nobukti='SALDO AWAL' then 
                isnull(a.qtysaldo,0) else 0 end)
                ) as qtymasuk"),
                db::raw("isnull(a.nilaimasuk,0)  as nominalmasuk"),
                'a.qtykeluar as qtykeluar',
                'a.nilaikeluar as nominalkeluar',
                // 'a.qtysaldo as qtysaldo',
                // 'a.nilaisaldo as nominalsaldo',
                db::raw("(case when (row_number() Over(partition BY a.namabarang Order By a.namabarang,a.tglbukti,a.tglinput))=1 then a.qtysaldo else 0 end) as qtysaldo"),
                db::raw("(case when (row_number() Over(partition BY a.namabarang Order By a.namabarang,a.tglbukti,a.tglinput))=1 then a.nilaisaldo else 0 end) as nominalsaldo"),
                // db::raw("0 as qtysaldo"),
                // db::raw("0 as nominalsaldo"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                db::raw("(case when (row_number() Over(partition BY a.stok_id Order By a.namabarang,a.stok_id,a.tglbukti,a.tglinput))=1 then 1 else 0 end) as baris"),
                // db::raw("(case when a.nobukti='SALDO AWAL' then 1 else 0 end) as baris"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                // 'a.stok_id',
                // db::raw("row_number() Over(partition BY a.stok_id Order By a.namabarang,a.stok_id,a.tglbukti,a.tglinput) as baris_urut")

            )
            ->join(db::raw("stok b with (readuncommitted)"),'a.stok_id','b.id')
            ->leftjoin(db::raw("pengeluaranstokdetail c with (readuncommitted) "), function ($join) {
                $join->on('a.nobukti', '=', 'c.nobukti');
                $join->on('a.stok_id', '=', 'c.stok_id');
            })            
            ->leftjoin(db::raw("penerimaanstokdetail d with (readuncommitted) "), function ($join) {
                $join->on('a.nobukti', '=', 'd.nobukti');
                $join->on('a.stok_id', '=', 'd.stok_id');
            })            
            ->orderBy(db::raw("(case when isnull(b.keterangan,'')='' then a.namabarang else isnull(b.keterangan,'') end) "), 'asc')
            ->orderBy('a.stok_id', 'asc')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.tglinput', 'asc')
            ->orderBy(db::raw("(case when UPPER(isnull(a.nobukti,''))='SALDO AWAL' then '' else isnull(a.nobukti,'') end)"), 'asc');


            // $query = DB::table($temprekapall)->from(
            //     DB::raw($temprekapall . " a")
            // )
            //     ->select(
            //         DB::raw("'Laporan Saldo Inventory' as header"),
            //         db::raw("max(a.lokasi) as lokasi"),
            //         db::raw("max(a.lokasi) as namalokasi"),
            //         DB::raw("'' as kategori"),
            //         DB::raw("'" . $tgldari . "' as tgldari"),
            //         DB::raw("'" . $tglsampai . "' as tglsampai"),
            //         DB::raw("'' as vulkanisirke"),
            //         DB::raw("max(isnull(c.keterangan,isnull(d.keterangan,''))) as keterangan"),
            //         db::raw("max(a.nobukti) as nobukti"),
            //         'a.kodebarang as id',
            //         'a.kodebarang',
            //         db::raw("max((case when isnull(b.keterangan,'')='' then a.namabarang else isnull(b.keterangan,'') end)) as namabarang"),
            //         db::raw("max(a.tglbukti) as tglbukti"),
            //         db::raw("sum((isnull(a.qtymasuk,0)+
            //         (case when a.nobukti='SALDO AWAL' then 
            //         isnull(a.qtysaldo,0) else 0 end)
            //         )) as qtymasuk"),
            //         db::raw("sum((isnull(a.nilaimasuk,0)+
            //         (case when a.nobukti='SALDO AWAL' then 
            //         isnull(a.nilaisaldo,0) else 0 end)
            //         )) as nominalmasuk"),                    
            //         // db::raw("sum(isnull(a.nilaimasuk,0))  as nominalmasuk"),
            //         db::raw("sum(isnull(a.qtykeluar,0))  as qtykeluar"),
            //         db::raw("sum(isnull(a.nilaikeluar,0))  as nominalkeluar"),
            //         db::raw("sum(isnull(a.qtysaldo,0))  as qtysaldo"),
            //         db::raw("sum(isnull(a.nilaisaldo,0))  as nominalsaldo"),
            //         db::raw("'" . $disetujui . "' as disetujui"),
            //         db::raw("'" . $diperiksa . "' as diperiksa"),
            //         db::raw("0 as baris"),
            //         DB::raw("'" . $getJudul->text . "' as judul"),
    
            //     )
            //     ->join(db::raw("stok b with (readuncommitted)"),'a.stok_id','b.id')
            //     ->leftjoin(db::raw("pengeluaranstokdetail c with (readuncommitted) "), function ($join) {
            //         $join->on('a.nobukti', '=', 'c.nobukti');
            //         $join->on('a.stok_id', '=', 'c.stok_id');
            //     })            
            //     ->leftjoin(db::raw("penerimaanstokdetail d with (readuncommitted) "), function ($join) {
            //         $join->on('a.nobukti', '=', 'd.nobukti');
            //         $join->on('a.stok_id', '=', 'd.stok_id');
            //     })            
            //     ->groupBY('a.kodebarang');
    

        // 'header' => 'Laporan Saldo Inventory',
        //         'lokasi' => 'GUDANG',
        //         'namalokasi' => 'GUDANG KANTOR',
        //         'kategori' => 'sparepart',
        //         'tgldari' => '2023-07-20',
        //         'tglsampai' => '2023-07-20',
        //         'stokdari' => 'BAN DALAM SWALLOW 900',
        //         'stoksampai' => 'BAN DALAM SWALLOW 900',
        //         'vulkanisirke' => 'Vul Ke: 0',
        //         'kodebarang' => '04819203',
        //         'namabarang' => 'BAN DALAM SWALLOW 900',
        //         'tanggal' => '08-07-2023',
        //         'qty' => '200',
        //         'satuan' => 'buah',
        //         'nominal' => '8300000',

        // dd(DB::table($temprekapall)->get());
        $data = $query->get();
        return $data;
    }
}
