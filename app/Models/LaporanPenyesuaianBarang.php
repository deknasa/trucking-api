<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPenyesuaianBarang extends MyModel
{
    use HasFactory;

    protected $table = 'laporanpenyesuaianbarang';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getReport($dari, $sampai)
    {

        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';


        $pengeluaranStok = PengeluaranStok::where('kodepengeluaran', '=', 'SPK')->first();

        $pengeluaranstok_id = $pengeluaranStok->id;

        $pengeluaranStokHeader = '##pengeluaranstokheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranStokHeader, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->BigInteger('pengeluaranstok_id')->nullable();
            $table->BigInteger('trado_id')->nullable();
            $table->BigInteger('gudang_id')->nullable();
            $table->BigInteger('gandengan_id')->nullable();
            $table->BigInteger('supir_id')->nullable();
            $table->BigInteger('supplier_id')->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('servicein_nobukti', 50)->nullable();
            $table->BigInteger('kerusakan_id')->nullable();
            $table->integer('statuspotongretur')->nullable();
            $table->BigInteger('bank_id')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->dateTime('tglkasmasuk')->nullable();
            $table->string('hutangbayar_nobukti', 50)->nullable();
            $table->BigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $pengeluaranStokDetail = '##pengeluaranstokdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranStokDetail, function ($table) {
            $table->BigInteger('id');
            $table->BigInteger('pengeluaranstokheader_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->BigInteger('stok_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('harga')->nullable();
            $table->double('persentasediscount')->nullable();
            $table->double('nominaldiscount')->nullable();
            $table->double('total')->nullable();
            $table->longText('keterangan')->nullable();
            $table->BigInteger('vulkanisirke')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });

        $queryPengeluaranStokHeader = DB::table("pengeluaranstokheader")->from(
            DB::raw("pengeluaranstokheader with (readuncommitted)")
        )
            ->select(
                'id',
                'nobukti',
                'tglbukti',
                'keterangan',
                'pengeluaranstok_id',
                'trado_id',
                'gudang_id',
                'gandengan_id',
                'supir_id',
                'supplier_id',
                'pengeluaranstok_nobukti',
                'penerimaanstok_nobukti',
                'servicein_nobukti',
                'kerusakan_id',
                'statuspotongretur',
                'bank_id',
                'penerimaan_nobukti ',
                'coa',
                'postingdari ',
                'tglkasmasuk ',
                'hutangbayar_nobukti',
                'statusformat',
                'statuscetak',
                'userbukacetak',
                'tglbukacetak',
                'jumlahcetak',
                'modifiedby',
                'created_at',
                'updated_at'

            )
            ->where('tglbukti', '>=', $dari)
            ->where('tglbukti', '<=', $sampai)
            ->where('pengeluaranstok_id', '=', $pengeluaranstok_id);

        DB::table($pengeluaranStokHeader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'pengeluaranstok_id',
            'trado_id',
            'gudang_id',
            'gandengan_id',
            'supir_id',
            'supplier_id',
            'pengeluaranstok_nobukti',
            'penerimaanstok_nobukti',
            'servicein_nobukti',
            'kerusakan_id',
            'statuspotongretur',
            'bank_id',
            'penerimaan_nobukti ',
            'coa',
            'postingdari ',
            'tglkasmasuk ',
            'hutangbayar_nobukti',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $queryPengeluaranStokHeader);

        $queryPengeluaranStokDetail = DB::table("pengeluaranstokdetail")->from(
            DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.pengeluaranstokheader_id',
                'a.nobukti',
                'a.stok_id',
                'a.qty',
                'a.harga',
                'a.persentasediscount',
                'a.nominaldiscount',
                'a.total',
                'a.keterangan',
                'a.vulkanisirke',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'

            )
            ->join(DB::raw($pengeluaranStokHeader . " as b"), 'a.nobukti', 'b.nobukti');

        DB::table($pengeluaranStokDetail)->insertUsing([
            'id',
            'pengeluaranstokheader_id',
            'nobukti',
            'stok_id',
            'qty',
            'harga',
            'persentasediscount',
            'nominaldiscount',
            'total',
            'keterangan',
            'vulkanisirke',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $queryPengeluaranStokDetail);

        $tempData = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempData, function ($table) {
            $table->string('nopolisi', 500)->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->datetime('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('stok_id')->nullable();
            $table->longText('namastok')->nullable();
            $table->string('gudang', 100)->nullable();
            $table->integer('qty')->nullable();
            $table->double('harga')->nullable();
            $table->double('nominal')->nullable();
        });


        // DB::raw("max(a.keterangan) as keterangan"),

        $queryTempData1 = DB::table($pengeluaranStokHeader)->from(
            DB::raw($pengeluaranStokHeader . " as a")
        )
            ->select(
                'c.kodetrado as nopolisi',
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'b.stok_id',
                'd.namastok',
                DB::raw("'Gudang Kantor' as gudang"),
                'b.qty',
                'b.harga',
                DB::raw("(b.qty*b.harga ) as nominal"),

            )
            ->join(DB::raw($pengeluaranStokDetail . " as b"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("trado as c with (readuncommitted)"), 'a.trado_id', 'c.id')
            ->join(DB::raw("stok as d with (readuncommitted)"), 'b.stok_id', 'd.id');

        DB::table($tempData)->insertUsing([
            'nopolisi',
            'nobukti',
            'tglbukti',
            'keterangan',
            'stok_id',
            'namastok',
            'gudang',
            'qty',
            'harga',
            'nominal',
        ], $queryTempData1);

        $queryTempData2 = DB::table($pengeluaranStokHeader)->from(
            DB::raw($pengeluaranStokHeader . " as a")
        )
            ->select(
                'c.kodegandengan as nopolisi',
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'b.stok_id',
                'd.namastok',
                DB::raw("'Gudang Kantor' as gudang"),
                'b.qty',
                'b.harga',
                DB::raw("(b.qty*b.harga ) as nominal"),

            )
            ->join(DB::raw($pengeluaranStokDetail . " as b"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("gandengan as c with (readuncommitted)"), 'a.gandengan_id', 'c.id')
            ->join(DB::raw("stok as d with (readuncommitted)"), 'b.stok_id', 'd.id');

        DB::table($tempData)->insertUsing([
            'nopolisi',
            'nobukti',
            'tglbukti',
            'keterangan',
            'stok_id',
            'namastok',
            'gudang',
            'qty',
            'harga',
            'nominal',
        ], $queryTempData2);

        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DISETUJUI')
        ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

    $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
        ->select('text')
        ->where('grp', 'DIPERIKSA')
        ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';

        $results = DB::table($tempData)->from(
            DB::raw($tempData)
        )
            ->select(
                'nopolisi',
                'nobukti',
                'tglbukti',
                'keterangan',
                'stok_id',
                'namastok',
                'gudang',
                'qty',
                'harga',
                'nominal',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),

            )
            ->orderBy('tglbukti', 'ASC')
            ->orderBy('nobukti', 'ASC')
            ->orderBy('namastok', 'ASC');

        $data = $results->get();



      return $data;
    }
}
