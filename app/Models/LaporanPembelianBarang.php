<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPembelianBarang extends MyModel
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

    public function getReport($bulan, $tahun, $jenislaporan)
    {
        // dd('test');
        $parameter = new Parameter();
        $stokgantung = false;
        $idincludestokgantung = $parameter->cekId('JENIS LAPORAN', 'JENIS LAPORAN', 'INCLUDE SPAREPART GANTUNG') ?? 0;
        // dd($jenislaporan,$idincludestokgantung);
        if ($jenislaporan == $idincludestokgantung) {
            $stokgantung = true;
        } else {
            $stokgantung = false;
        }

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

        $cmpy = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->value('text');

        $idgudangkantor = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('grp', 'GUDANG KANTOR')
            ->where('subgrp', 'GUDANG KANTOR')
            ->first()->text ?? 0;

        $tempbukti = '##tempbukti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbukti, function ($table) {
            $table->string('nobukti', 100);
        });

        $querybukti = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
            ->select(
                'a.nobukti'
            )
            ->whereRaw("a.penerimaanstok_id in (3,6)")
            ->whereRaw("MONTH(a.tglbukti) = " . $bulan . " AND YEAR(a.tglbukti) = " . $tahun);

        DB::table($tempbukti)->insertUsing([
            'nobukti',
        ], $querybukti);

        $querybukti = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
            ->select(
                'a.nobukti'
            )
            ->whereRaw("a.penerimaanstok_id in (4,9)")
            ->whereRaw("a.gudang_id in (1)")
            ->whereRaw("MONTH(a.tglbukti) = " . $bulan . " AND YEAR(a.tglbukti) = " . $tahun);

        DB::table($tempbukti)->insertUsing([
            'nobukti',
        ], $querybukti);

        $querybukti = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
            ->select(
                'a.nobukti'
            )
            ->join(db::raw("gudang b with (readuncommitted)"), 'a.gudangke_id', 'b.id')
            ->whereRaw("a.penerimaanstok_id in (5)")
            ->whereRaw("MONTH(a.tglbukti) = " . $bulan . " AND YEAR(a.tglbukti) = " . $tahun)
            ->where('b.id', $idgudangkantor);

        DB::table($tempbukti)->insertUsing([
            'nobukti',
        ], $querybukti);

        $querybukti = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader as a with (readuncommitted)"))
            ->select(
                'a.nobukti'
            )
            ->join(db::raw("gudang b with (readuncommitted)"), 'a.gudang_id', 'b.id')
            ->whereRaw("a.penerimaanstok_id in (8)")
            ->whereRaw("MONTH(a.tglbukti) = " . $bulan . " AND YEAR(a.tglbukti) = " . $tahun)
            ->where('b.id', $idgudangkantor);

        DB::table($tempbukti)->insertUsing([
            'nobukti',
        ], $querybukti);


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

        if ($bulan == 12) {
            $priode = $tahun . '-01-01';
        } else {
            $ybulan = $bulan + 1;
            $priode = $tahun . '-' . $ybulan . '-01';
        }
        $priodegantung = date("Y-m-d", strtotime("-1 day", strtotime($priode)));
        if ($stokgantung == true) {



            // $bulan, $tahun

            $priode = date("Y-m-d", strtotime($priode));
            $stokdari_id = 0;
            $stoksampai_id = 0;
            $gudang_id = 1;
            $trado_id = 0;
            $gandengan_id = 0;
            $filterdata = 1;

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
            ], (new KartuStok())->getlaporan($priode, $priode, $stokdari_id, $stoksampai_id, $gudang_id, $trado_id, $gandengan_id, $filterdata, $stokgantung));

            db::update("delete " . $temprekapall . " where lokasi<>'SPAREPART GANTUNG'");
            // dd('test');
        }

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->integer('id');
            $table->string('nobukti', 100);
            $table->date('tglbukti');
            $table->longtext('namastok');
            $table->double('qty', 15, 2);
            $table->longtext('satuan');
            $table->double('harga', 15, 2);
            $table->double('nominal', 15, 2);
            $table->longtext('keterangan');
            $table->longtext('disetujui');
            $table->longtext('diperiksa');
            $table->longtext('judul');
        });

        $queryhasil = DB::table($tempbukti)->from(DB::raw($tempbukti . "  AS a "))
            ->select(
                'c.id',
                'a.nobukti',
                'b.tglbukti',
                // 'd.namastok',
                db::raw("isnull(c1.kodekelompok,'')+' - '+trim(d.namastok) as namastok"),
                'c.qty',
                db::raw("isnull(e.satuan,'') as satuan"),
                db::raw("isnull(c.harga,0) as harga"),
                db::raw("isnull(c.total,0) as nominal"),
                'c.keterangan',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                db::raw("'" . $cmpy . "' as judul"),


            )
            ->join(DB::raw("penerimaanstokheader as b with (readuncommitted)"), 'a.nobukti',  'b.nobukti')
            ->join(db::raw("penerimaanstokdetail as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->join(db::raw("stok as d with (readuncommitted)"), 'c.stok_id', 'd.id')
            ->join(db::raw("kelompok c1 with (readuncommitted)"), 'd.kelompok_id', 'c1.id')

            ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
            ->whereraw("b.penerimaanstok_id not in(8,9)")
            ->OrderBy('b.tglbukti', 'asc')
            ->OrderBy('b.nobukti', 'asc')
            ->OrderBy('c.id', 'asc');

        DB::table($temphasil)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'namastok',
            'qty',
            'satuan',
            'harga',
            'nominal',
            'keterangan',
            'disetujui',
            'diperiksa',
            'judul',
        ], $queryhasil);

        $queryhasil = DB::table($tempbukti)->from(DB::raw($tempbukti . "  AS a "))
            ->select(
                'c2.id',
                'a.nobukti',
                'b.tglbukti',
                // 'd.namastok',
                db::raw("isnull(c1.kodekelompok,'')+' - '+trim(d.namastok) as namastok"),
                'c.qty',
                db::raw("isnull(e.satuan,'') as satuan"),
                db::raw("isnull(c.penerimaanstok_harga,0) as harga"),
                db::raw("isnull(c.penerimaanstok_harga,0)*c.qty as nominal"),
                'c2.keterangan',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                db::raw("'" . $cmpy . "' as judul"),


            )
            ->join(DB::raw("penerimaanstokheader as b with (readuncommitted)"), 'a.nobukti',  'b.nobukti')
            ->join(db::raw("penerimaanstokdetail as c2 with (readuncommitted)"), 'a.nobukti', 'c2.nobukti')
            // ->join(db::raw("penerimaanstokdetailfifo as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->join(db::raw("penerimaanstokdetailfifo as c "), function ($join) {
                $join->on('b.nobukti', '=', 'c.nobukti');
                $join->on('c2.stok_id', '=', 'c.stok_id');
            })
            ->join(db::raw("stok as d with (readuncommitted)"), 'c.stok_id', 'd.id')
            ->join(db::raw("kelompok c1 with (readuncommitted)"), 'd.kelompok_id', 'c1.id')

            ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
            ->whereraw("b.penerimaanstok_id in(8,9)")
            ->OrderBy('b.tglbukti', 'asc')
            ->OrderBy('b.nobukti', 'asc')
            ->OrderBy('c.id', 'asc');

        DB::table($temphasil)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'namastok',
            'qty',
            'satuan',
            'harga',
            'nominal',
            'keterangan',
            'disetujui',
            'diperiksa',
            'judul',
        ], $queryhasil);

        $queryhasil = DB::table($temprekapall)->from(DB::raw($temprekapall . "  AS a "))
            ->select(
                'a.id',
                'a.lokasi as nobukti',
                db::raw("'" . $priodegantung . "' as tglbukti"),

                // 'd.namastok',
                db::raw("isnull(c1.kodekelompok,'')+' - '+trim(d.namastok) as namastok"),
                db::raw("isnull(a.qtymasuk,0) as qty"),
                db::raw("isnull(e.satuan,'') as satuan"),
                db::raw("isnull(a.nilaimasuk,0) as harga"),
                db::raw("isnull(a.qtymasuk,0)*isnull(a.nilaimasuk,0) as nominal"),
                'a.lokasi as keterangan',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                db::raw("'" . $cmpy . "' as judul"),


            )
            ->join(db::raw("stok as d with (readuncommitted)"), 'a.stok_id', 'd.id')
            ->join(db::raw("kelompok c1 with (readuncommitted)"), 'd.kelompok_id', 'c1.id')

            ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.id', 'asc');

        DB::table($temphasil)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'namastok',
            'qty',
            'satuan',
            'harga',
            'nominal',
            'keterangan',
            'disetujui',
            'diperiksa',
            'judul',
        ], $queryhasil);


        $query = DB::table($temphasil)->from(DB::raw($temphasil . "  AS a "))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.namastok',
                'a.qty',
                'a.satuan',
                'a.harga',
                'a.nominal',
                'a.keterangan',
                'a.disetujui',
                'a.diperiksa',
                'a.judul',

            )
            ->OrderBy('a.tglbukti', 'asc')
            ->OrderBy('a.nobukti', 'asc')
            ->OrderBy('a.id', 'asc')
            ->get();
        return $query;
    }
}
