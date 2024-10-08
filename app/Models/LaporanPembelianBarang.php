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

        if ($stokgantung == true) {
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

            $querybukti = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader as a with (readuncommitted)"))
                ->select(
                    'a.nobukti'
                )
                ->whereRaw("a.pengeluaranstok_id in (5)")
                ->whereRaw("MONTH(a.tglbukti) = " . $bulan . " AND YEAR(a.tglbukti) = " . $tahun);

            DB::table($tempbukti)->insertUsing([
                'nobukti',
            ], $querybukti);            
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
        ->join(DB::raw("pengeluaranstokheader as b with (readuncommitted)"), 'a.nobukti',  'b.nobukti')
        ->join(db::raw("pengeluaranstokdetail as c2 with (readuncommitted)"), 'a.nobukti', 'c2.nobukti')
        // ->join(db::raw("pengeluaranstokdetailfifo as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
        ->join(db::raw("pengeluaranstokdetailfifo as c "), function ($join) {
            $join->on('b.nobukti', '=', 'c.nobukti');
            $join->on('c2.stok_id', '=', 'c.stok_id');
        })
        ->join(db::raw("stok as d with (readuncommitted)"), 'c.stok_id', 'd.id')
        ->join(db::raw("kelompok c1 with (readuncommitted)"), 'd.kelompok_id', 'c1.id')

        ->leftjoin(db::raw("satuan as e with (readuncommitted)"), 'd.satuan_id', 'e.id')
        ->whereraw("b.pengeluaranstok_id in(5)")
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
