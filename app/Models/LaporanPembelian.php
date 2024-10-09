<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPembelian extends MyModel
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


    public function getReport($dari, $sampai, $supplierdari, $suppliersampai, $supplierdari_id, $suppliersampai_id, $status)
    {


        if ($supplierdari_id == 0 || $suppliersampai_id == 0) {
            $supplierdari_id = db::table("supplier")->from(db::raw("supplier a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->orderby('a.id', 'asc')
                ->first()->id ?? 0;

            $suppliersampai_id = db::table("supplier")->from(db::raw("supplier a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->orderby('a.id', 'desc')
                ->first()->id ?? 0;
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

        // dd($status);
        if ($status == 'ORDER PEMBELIAN') {
            $getPOStok = DB::table('parameter')
                ->where('grp', 'PO STOK')
                ->where('subgrp', 'PO STOK')
                ->value('text');
            if ($getPOStok) {
                $penerimaanstok_id = $getPOStok;

                $result2 = DB::table('penerimaanstokheader AS a')
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                        DB::raw("ISNULL(c.namasupplier, '') AS namasupplier"),
                        'b.stok_id',
                        'd.namastok',
                        'b.qty',
                        DB::raw("ISNULL(e.satuan, '') AS satuan"),
                        'b.keterangan',
                        DB::raw("'Laporan Pembelian' as judulLaporan"),
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
                    ->where('a.supplier_id', '>=', $supplierdari_id)
                    ->where('a.supplier_id', '<=', $suppliersampai_id)
                    ->where('a.penerimaanstok_id', '=', $penerimaanstok_id)
                    ->orderBy('a.id')
                    ->orderBy('b.id');
                // dd($result2->get());
                // Rest of the code
            }
        } elseif ($status == 'HISTORY ORDER PEMBELIAN') {
            $getPOStok = DB::table('parameter')
                ->where('grp', 'PO STOK')
                ->where('subgrp', 'PO STOK')
                ->value('text');

            if ($getPOStok) {


                $penerimaanstok_id = $getPOStok;
                $result2 = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader AS a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                        DB::raw("ISNULL(c.namasupplier, '') AS namasupplier"),
                        'b.stok_id',
                        'd.namastok',
                        'b.qty',
                        DB::raw("ISNULL(e.satuan, '') AS satuan"),
                        'b.keterangan',
                        DB::raw("'Laporan Pembelian' as judulLaporan"),
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
                    ->where('a.supplier_id', '>=', $supplierdari_id)
                    ->where('a.supplier_id', '<=', $suppliersampai_id)
                    ->where('a.penerimaanstok_id', '=', $penerimaanstok_id)
                    ->orderBy('a.id')
                    ->orderBy('b.id');
                // dd($result2->get());

            }
        } elseif ($status == 'PEMBELIAN') {
            $getPOStok = DB::table('parameter')
                ->where('grp', 'SPB STOK')
                ->where('subgrp', 'SPB STOK')
                ->value('text');
            if ($getPOStok) {
                $penerimaanstok_id = $getPOStok;
                $result2 = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader AS a with (readuncommitted)"))
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
                        DB::raw("'Laporan Pembelian' as judulLaporan"),
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
                    ->where('a.supplier_id', '>=', $supplierdari_id)
                    ->where('a.supplier_id', '<=', $suppliersampai_id)
                    ->where('a.penerimaanstok_id', '=', $penerimaanstok_id)
                    ->orderBy('a.id')
                    ->orderBy('b.id');
                // dd($result2->get());

            }
        } elseif ($status == 'REKAP PEMBELIAN PER SUPPLIER') {
            $getPOStok = DB::table('parameter')
                ->where('grp', 'SPB STOK')
                ->where('subgrp', 'SPB STOK')
                ->value('text');

            if ($getPOStok) {
                $penerimaanstok_id = $getPOStok;

                $Tempbeli = '##Tempbeli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($Tempbeli, function ($table) {
                    $table->string('nobuktihutang', 50);
                    $table->dateTime('tglJtTempo');
                });


                $select_Tempbeli = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader AS A"))
                    ->select([
                        'b.nobukti AS nobuktihutang',
                        DB::raw('MAX(c.tgljatuhtempo) AS tglJtTempo')
                    ])
                    ->from('penerimaanstokheader AS a')
                    ->join('hutangheader AS b', 'a.hutang_nobukti', '=', 'b.nobukti')
                    ->join('hutangdetail AS c', 'a.hutang_nobukti', '=', 'c.nobukti')
                    ->where('a.tglbukti', '>=', $dari)
                    ->where('a.tglbukti', '<=', $sampai)
                    ->where('a.supplier_id', '>=', $supplierdari_id)
                    ->where('a.supplier_id', '<=', $suppliersampai_id)
                    ->whereraw("a.penerimaanstok_id in(" . $penerimaanstok_id . ",6)")
                    ->groupBy('b.nobukti');

                DB::table($Tempbeli)->insertUsing([
                    'nobuktihutang',
                    'tgljttempo',

                ], $select_Tempbeli);


                $result2 = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader AS a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.tglbukti',
                        DB::raw("ISNULL(c.namasupplier, '') AS namasupplier"),
                        'b.stok_id',
                        'd.namastok',
                        'b.qty',
                        'b.harga',
                        'b.nominaldiscount',
                        DB::raw('((b.qty * b.harga) - b.nominaldiscount) AS total'),
                        DB::raw('((b.qty * b.harga) ) AS totalharga'),
                        DB::raw("ISNULL(e.satuan, '') AS satuan"),
                        'b.keterangan',

                        DB::raw("ISNULL(f.nobukti, '') AS nobuktihutang"),
                        'g.tglJtTempo AS TglJatuhTempo',
                        DB::raw("'Laporan Pembelian' as judulLaporan"),
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
                    ->leftJoin('hutangheader AS f', 'a.hutang_nobukti', '=', 'f.nobukti')
                    ->leftJoin(DB::raw($Tempbeli . ' AS g'), 'f.nobukti', '=', 'g.nobuktihutang')
                    ->where('a.tglbukti', '>=', $dari)
                    ->where('a.tglbukti', '<=', $sampai)
                    ->where('a.supplier_id', '>=', $supplierdari_id)
                    ->where('a.supplier_id', '<=', $suppliersampai_id)
                    ->whereraw("a.penerimaanstok_id in(" . $penerimaanstok_id . ",6)")
                    ->orderBy('c.namasupplier', 'asc')
                    ->orderBy('b.id', 'asc');

                // dd($result2->get());
            }
        } elseif ($status == 'RETUR PEMBELIAN') {
            $getPOStok = DB::table('parameter')
                ->where('grp', 'RETUR STOK')
                ->where('subgrp', 'RETUR STOK')
                ->value('text');

            if ($getPOStok) {
                $pengeluaranstok_id = $getPOStok;

                $result2 = DB::table('pengeluaranstokheader')->from(DB::raw("pengeluaranstokheader AS a with (readuncommitted)"))
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
                        DB::raw("'Laporan Pembelian' as judulLaporan"),
                        DB::raw("'" . $getJudul->text . "' as judul"),
                        DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                        DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                        db::raw("'" . $disetujui . "' as disetujui"),
                        db::raw("'" . $diperiksa . "' as diperiksa"),
                    )
                    ->join('pengeluaranstokdetail AS b', 'a.nobukti', '=', 'b.nobukti')
                    ->leftJoin('supplier AS c', 'a.supplier_id', '=', 'c.id')
                    ->join('stok AS d', 'b.stok_id', '=', 'd.id')
                    ->leftJoin('satuan AS e', 'd.satuan_id', '=', 'e.id')
                    ->where('a.tglbukti', '>=', $dari)
                    ->where('a.tglbukti', '<=', $sampai)
                    ->where('a.supplier_id', '>=', $supplierdari_id)
                    ->where('a.supplier_id', '<=', $suppliersampai_id)
                    ->where('a.pengeluaranstok_id', '=', $pengeluaranstok_id)
                    ->orderBy('a.id')
                    ->orderBy('b.id');

                // dd($result2->get());
            }
        }

        $data = $result2->get();
        // dd($data);
        return $data;
    }
}
