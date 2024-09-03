<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPemakaianStok extends MyModel
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

    public function getReport($bulan, $tahun)
    {


        // dari kartu stok

        $tgl = '01-' . $bulan . '-' . $tahun;

        $tgldari = date('Y-m-d', strtotime($tgl));
        $tgl2 = date('t-m-Y', strtotime($tgl));
        $tglsampai = date('Y-m-d', strtotime($tgl2));
        $tglsampai1 = date('Y-m-d', strtotime('+1 days', strtotime($tgl2)));

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

        DB::delete(DB::raw("delete " . $temprekapall . " from " . $temprekapall . " as a where isnull(a.nilaikeluar,0)=0 and  isnull(a.qtykeluar,0)=0
        "));

        // 

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $cmpy = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->value('text');


        // $Templaporan = '##Templaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($Templaporan, function ($table) {
        //     $table->string('nobukti', 500);
        //     $table->date('tglbukti');
        //     $table->string('kodetrado', 500);
        //     $table->string('namastok', 500);
        //     $table->double('qty');
        //     $table->double('nominal');
        //     $table->double('harga');
        //     $table->string('satuan', 300);
        //     $table->longText('Keterangan');
        // });


        // $query = DB::table('pengeluaranstokdetail')->from(DB::raw("pengeluaranstokdetail  AS a WITH (READUNCOMMITTED)"))

        //     ->select(
        //         'b.nobukti',
        //         'b.tglbukti',
        //         db::raw("
        //         (case when isnull(c.kodetrado,'')<>'' then isnull(c.kodetrado,'')
        //                 when isnull(c1.kodegandengan,'')<>'' then isnull(c1.kodegandengan,'')
        //                 when isnull(c2.gudang,'')<>'' then isnull(c2.gudang,'')
        //                 else  '' end)
        //         as kodetrado
        //         "),
        //         'd.namastok',
        //         'a.qty',
        //         db::raw("isnull(a.total,0) as nominal"),
        //         db::raw("isnull(a.harga,0) as harga"),
        //         db::raw("isnull(e.satuan,'') as satuan"),
        //         'a.keterangan'

        //     )

        //     ->join(DB::raw("pengeluaranstokheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
        //     ->leftjoin(db::raw("trado as c with (readuncommitted)"), 'b.trado_id', 'c.id')
        //     ->leftjoin(db::raw("gandengan as c1 with (readuncommitted)"), 'b.gandengan_id', 'c1.id')
        //     ->leftjoin(db::raw("gudang as c2 with (readuncommitted)"), 'b.gudang_id', 'c2.id')
        //     ->leftjoin(db::raw("stok as d with (readuncommitted)"), 'a.stok_id', 'd.id')
        //     ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
        //     ->whereRaw("MONTH(b.tglbukti) = " . $bulan . " AND YEAR(b.tglbukti) = " . $tahun)
        //     ->whereRaw("b.pengeluaranstok_id in (1,3)")
        //     ->OrderBy('b.tglbukti', 'asc');


        // DB::table($Templaporan)->insertUsing([
        //     'nobukti',
        //     'tglbukti',
        //     'kodetrado',
        //     'namastok',
        //     'qty',
        //     'nominal',
        //     'harga',
        //     'satuan',
        //     'Keterangan',
        // ], $query);

        // $query = DB::table($Templaporan)->from(DB::raw($Templaporan . "  AS a"))



        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $query = DB::table($temprekapall)->from(DB::raw($temprekapall . "  AS a"))

            ->select(
                'a.nobukti',
                'a.tglbukti',
                db::raw("
                (case when isnull(f.kodetrado,'')<>'' then isnull(f.kodetrado,'')
                        when isnull(g.gudang,'')<>'' then isnull(g.gudang,'')
                        when isnull(h.kodegandengan,'')<>'' then isnull(h.kodegandengan,'')
                        when isnull(f2.kodetrado,'')<>'' then isnull(f2.kodetrado,'')
                        when isnull(g2.gudang,'')<>'' then isnull(g2.gudang,'')
                        when isnull(h2.kodegandengan,'')<>'' then isnull(h2.kodegandengan,'')                        
                else '' end) as kodetrado
                "),
                // 'a.namabarang as namastok',
                db::raw("isnull(c1.kodekelompok,'')+' - '+trim(a.namabarang) as namastok"),

                'a.qtykeluar as qty',
                'a.nilaikeluar as nominal',
                db::raw("round((a.nilaikeluar/a.qtykeluar),2) as harga"),
                db::raw("isnull(c.satuan,'') as satuan"),
                db::raw("isnull(d.keterangan,isnull(d1.keterangan,'')) as keterangan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->join(db::raw("kelompok c1 with (readuncommitted)"),'b.kelompok_id','c1.id')
            ->leftjoin(db::raw("satuan c with (readuncommitted)"), 'b.satuan_id', 'c.id')
            ->leftjoin(DB::raw("pengeluaranstokdetail as d"), function ($join) {
                $join->on('a.nobukti', '=', 'd.nobukti');
                $join->on('a.stok_id', '=', 'd.stok_id');
            })
            ->leftjoin(db::raw("pengeluaranstokheader e with (readuncommitted)"), 'd.nobukti', 'e.nobukti')
            ->leftjoin(db::raw("trado f with (readuncommitted)"), 'e.trado_id', 'f.id')
            ->leftjoin(db::raw("gudang g with (readuncommitted)"), 'e.gudang_id', 'g.id')
            ->leftjoin(db::raw("gandengan h with (readuncommitted)"), 'e.gandengan_id', 'h.id')
            ->leftjoin(db::raw("penerimaanstokheader e2 with (readuncommitted)"), 'a.nobukti', 'e2.nobukti')
            ->leftjoin(db::raw("trado f2 with (readuncommitted)"), 'e2.tradoke_id', 'f2.id')
            ->leftjoin(db::raw("gudang g2 with (readuncommitted)"), 'e2.gudangke_id', 'g2.id')
            ->leftjoin(db::raw("gandengan h2 with (readuncommitted)"), 'e2.gandenganke_id', 'h2.id')
            ->leftjoin(DB::raw("penerimaanstokdetail as d1"), function ($join) {
                $join->on('a.nobukti', '=', 'd1.nobukti');
                $join->on('a.stok_id', '=', 'd1.stok_id');
            })

            // ->OrderBy('f.kodetrado', 'asc')
            // ->OrderBy('g.gudang', 'asc')
            // ->OrderBy('h.kodegandengan', 'asc')
            ->orderBy(db::raw("
            (case when isnull(f.kodetrado,'')<>'' then isnull(f.kodetrado,'')
                    when isnull(g.gudang,'')<>'' then isnull(g.gudang,'')
                    when isnull(h.kodegandengan,'')<>'' then isnull(h.kodegandengan,'')
                    when isnull(f2.kodetrado,'')<>'' then isnull(f2.kodetrado,'')
                    when isnull(g2.gudang,'')<>'' then isnull(g2.gudang,'')
                    when isnull(h2.kodegandengan,'')<>'' then isnull(h2.kodegandengan,'')                        
            else '' end)"), 'asc')
            ->OrderBy('a.tglbukti', 'asc')
            ->get();

        // dd($query->tosql());



        // return [$data1, $data2];
        return $query;
    }
}
