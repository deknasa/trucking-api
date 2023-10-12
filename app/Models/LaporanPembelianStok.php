<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPembelianStok extends MyModel
{
    use HasFactory;

    protected $table = 'laporanpembelian';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function getReport($dari, $sampai, $stokdari, $stoksampai)
    {
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $getPOStok = DB::table('parameter')
            ->where('grp', 'SPB STOK')
            ->where('subgrp', 'SPB STOK')
            ->value('text');
        $penerimaanstok_id = $getPOStok;

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


        $result2 = DB::table('penerimaanstokheader AS a')
            ->select(
                'a.nobukti',
                'a.tglbukti',
                DB::raw("ISNULL(c.namasupplier, '') AS namasupplier"),
                'b.stok_id',
                'd.namastok',
                'b.qty',
                'b.harga',
                'b.nominaldiscount',
                DB::raw('(b.qty * b.harga - b.nominaldiscount) AS total'),
                DB::raw("ISNULL(e.satuan, '') AS satuan"),
                'b.keterangan',
                DB::raw("'Laporan Pembelian Stok' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            )
            ->join('penerimaanstokdetail AS b', 'a.nobukti', '=', 'b.nobukti')
            ->leftJoin('supplier AS c', 'a.supplier_id', '=', 'c.id')
            ->join('stok AS d', 'b.stok_id', '=', 'd.id')
            ->leftJoin('satuan AS e', 'd.satuan_id', '=', 'e.id')
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.supplier_id', '>=', $stokdari)
            ->where('a.supplier_id', '<=', $stoksampai)
            ->where('a.penerimaanstok_id', '=', $penerimaanstok_id)
            ->orderBy('a.id')
            ->orderBy('b.id');


        $data = $result2->get();
        return $data;
    }

    public function getExport($dari, $sampai, $stokdari, $stoksampai)
    {
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $getPOStok = DB::table('parameter')
            ->where('grp', 'SPB STOK')
            ->where('subgrp', 'SPB STOK')
            ->value('text');
        $penerimaanstok_id = $getPOStok;

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';


        $result2 = DB::table('penerimaanstokheader AS a')
            ->select(
                'a.nobukti',
                'a.tglbukti',
                DB::raw("ISNULL(c.namasupplier, '') AS namasupplier"),
                'b.stok_id',
                'd.namastok',
                'b.qty',
                'b.harga',
                'b.nominaldiscount',
                DB::raw('(b.qty * b.harga - b.nominaldiscount) AS total'),
                DB::raw("ISNULL(e.satuan, '') AS satuan"),
                'b.keterangan',
                DB::raw("'Laporan Pembelian Stok' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
            )
            ->join('penerimaanstokdetail AS b', 'a.nobukti', '=', 'b.nobukti')
            ->leftJoin('supplier AS c', 'a.supplier_id', '=', 'c.id')
            ->join('stok AS d', 'b.stok_id', '=', 'd.id')
            ->leftJoin('satuan AS e', 'd.satuan_id', '=', 'e.id')
            ->where('a.tglbukti', '>=', $dari)
            ->where('a.tglbukti', '<=', $sampai)
            ->where('a.supplier_id', '>=', $stokdari)
            ->where('a.supplier_id', '<=', $stoksampai)
            ->where('a.penerimaanstok_id', '=', $penerimaanstok_id)
            ->orderBy('a.id')
            ->orderBy('b.id');

        $data = $result2->get();
        return $data;
    }
}
