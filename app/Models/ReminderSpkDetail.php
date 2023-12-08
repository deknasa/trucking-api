<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReminderSpkDetail extends MyModel
{
    use HasFactory;

    public function get($getdetail, $stok_id, $trado_id, $gandengan_id, $gudang, $stok, $qty, $total, $nobukti, $tglbukti, $keterangan, $hargasatuan, $satuan, $persentasedisc, $nominaldisc)
    {


        $saldo = Parameter::where('grp', 'SALDO')->where('subgrp', 'SALDO')->first();

        $queryloop = DB::connection('sqlsrvlama')->table("TrStckAdj_R")->from(db::raw("TrStckAdj_R a with (readuncommitted)"))
            ->select(
                db::raw("b.fntrans as nobukti"),
                db::raw("b.ftgl as tglbukti"),
                db::raw("b.fnopol as gudang"),
                db::raw("a.fkstck as namastok"),
                db::raw("a.fsat as satuan"),
                db::raw("abs(a.fqty) as qty"),
                db::raw("a.fhrgsat as hargasatuan"),
                db::raw("a.ftotal as total"),
                db::raw("a.FPDisc as persentasediscount"),
                db::raw("a.FNDisc as nominaldiscount"),
                db::raw("a.Fket as keterangan"),
            )
            ->join(db::raw("TrStckAdj_h b with (readuncommitted)"), 'a.fntrans', 'b.fntrans')
            ->whereRaw("a.fkstck='" . $stok . "'")
            ->whereRaw("b.fnopol='" . $gudang . "'")
            ->whereRaw("b.ftgl<='" . $saldo->text . "'")
            ->orderby('b.ftgl', 'desc')
            ->get();

        // dd($queryloop);


        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('gudang', 1000)->nullable();
            $table->string('namastok', 500)->nullable();
            $table->string('satuan', 100)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('hargasatuan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
        });

        $queryloop = json_encode($queryloop, JSON_INVALID_UTF8_SUBSTITUTE);
        $query = json_decode($queryloop, true);
        foreach ($query as $item) {
            DB::table($tempdata)->insert([
                'nobukti' => $item['nobukti'] ?? '',
                'tglbukti' =>  $item['tglbukti'] ?? '',
                'gudang' => $item['gudang'] ?? '',
                'namastok' => $item['namastok'] ?? '',
                'satuan' => $item['satuan'] ?? '',
                'qty' =>  $item['qty'] ?? 0,
                'hargasatuan' =>  $item['hargasatuan'] ?? 0,
                'total' => $item['total'] ?? 0,
                'persentasediscount' => $item['persentasediscount'] ?? 0,
                'nominaldiscount' => $item['nominaldiscount'] ?? 0,
                'keterangan' => $item['keterangan'] ?? 0,
            ]);
        }



        $pengeluaranstok = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $querykeluar = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                db::raw("a.nobukti as nobukti"),
                db::raw("a.tglbukti as tglbukti"),
                db::raw("'" . $gudang . "' as gudang"),
                db::raw("'" . $stok . "' as namastok"),
                db::raw("d.satuan as satuan"),
                db::raw("b.qty as qty"),
                db::raw("b.harga as hargasatuan"),
                db::raw("b.total as total"),
                db::raw("b.persentasediscount"),
                db::raw("b.nominaldiscount"),
                db::raw("b.keterangan"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->join(db::raw("satuan d with (readuncommitted)"), 'c.satuan_id', 'd.id')
            ->where("b.stok_id", $stok_id)
            ->whereRaw("(a.trado_id=" . $trado_id . " or a.gandengan_id=" . $gandengan_id . ")")
            ->whereRaw("a.tglbukti>'" . $saldo->text . "'")
            ->where("a.pengeluaranstok_id", $pengeluaranstok->text)
            ->orderby('a.tglbukti', 'desc');

        DB::table($tempdata)->insertUsing([
            'nobukti',
            'tglbukti',
            'gudang',
            'namastok',
            'satuan',
            'qty',
            'hargasatuan',
            'total',
            'persentasediscount',
            'nominaldiscount',
            'keterangan',
        ], $querykeluar);



        $query = db::table($tempdata)->from(db::raw($tempdata . " a "))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gudang',
                'a.namastok',
                'a.satuan',
                'a.qty',
                'a.hargasatuan',
                db::raw("abs(a.total) as total"),
                'a.persentasediscount',
                'a.nominaldiscount',
                'a.keterangan',
                db::raw("'$nobukti' as nobukti_header"),
                db::raw("'$tglbukti' as tglbukti_header"),
                db::raw("'$gudang' as gudang_header"),
                db::raw("'$trado_id' as trado_id_header"),
                db::raw("'$gandengan_id' as gandengan_id_header"),
                db::raw("'$stok_id' as stok_id_header"),
                db::raw("'$stok' as stok_header"),
                db::raw($qty . " as qty_header"),
                db::raw($total . " as total_header"),
                db::raw("'$keterangan' as keterangan_header"),
                db::raw($hargasatuan . " as hargasatuan_header"),
                db::raw("'$satuan' as satuan_header"),
                db::raw($persentasedisc . " as persentasediscount_header"),
                db::raw($nominaldisc . " as nominaldiscount_header"),
            )
            ->orderby('a.tglbukti', 'desc');



        if ($getdetail == 0) {
            $data = $query->get();
        } else {
            $data = $query;
        }
        return $data;
    }

    public function getdetail()
    {
        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->id();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('gudang')->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('gandengan_id')->nullable();
            $table->integer('stok_id')->nullable();
            $table->string('stok', 1000)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('hargasatuan', 15, 2)->nullable();
            $table->string('satuan')->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
        });

        DB::table($tempdata)->insertUsing([
            'nobukti',
            'tglbukti',
            'gudang',
            'trado_id',
            'gandengan_id',
            'stok_id',
            'stok',
            'qty',
            'total',
            'keterangan',
            'hargasatuan',
            'satuan',
            'persentasediscount',
            'nominaldiscount'
        ], (new ReminderSpk())->get(1));

        $queryloop = DB::table($tempdata)->from(db::raw($tempdata . " a "))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gudang',
                'a.trado_id',
                'a.gandengan_id',
                'a.stok_id',
                'a.stok',
                'a.qty',
                'a.total',
                'a.keterangan',
                'a.hargasatuan',
                'a.satuan',
                'a.persentasediscount',
                'a.nominaldiscount'
            )
            ->orderby('a.id', 'asc')
            ->get();

        $tempdatadetail = '##tempdatadetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatadetail, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('gudang', 1000)->nullable();
            $table->string('namastok', 500)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('hargasatuan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();


            $table->string('nobukti_header', 50)->nullable();
            $table->date('tglbukti_header')->nullable();
            $table->string('gudang_header', 1000)->nullable();
            $table->integer('trado_id_header')->nullable();
            $table->integer('gandengan_id_header')->nullable();
            $table->integer('stok_id_header')->nullable();
            $table->string('stok_header', 1000)->nullable();
            $table->double('qty_header', 15, 2)->nullable();
            $table->double('total_header', 15, 2)->nullable();
            $table->longText('keterangan_header')->nullable();
            $table->double('hargasatuan_header', 15, 2)->nullable();
            $table->string('satuan_header')->nullable();
            $table->double('persentasediscount_header', 15, 2)->nullable();
            $table->double('nominaldiscount_header', 15, 2)->nullable();
        });

        $queryloop = json_encode($queryloop, JSON_INVALID_UTF8_SUBSTITUTE);
        $query = json_decode($queryloop, true);
        foreach ($query as $item) {
            DB::table($tempdatadetail)->insertUsing([
                'nobukti',
                'tglbukti',
                'gudang',
                'namastok',
                'satuan',
                'qty',
                'hargasatuan',
                'total',
                'persentasediscount',
                'nominaldiscount',
                'keterangan',
                'nobukti_header',
                'tglbukti_header',
                'gudang_header',
                'trado_id_header',
                'gandengan_id_header',
                'stok_id_header',
                'stok_header',
                'qty_header',
                'total_header',
                'keterangan_header',
                'hargasatuan_header',
                'satuan_header',
                'persentasediscount_header',
                'nominaldiscount_header',
            ], $this->get(1, $item['stok_id'], $item['trado_id'], $item['gandengan_id'], $item['gudang'], $item['stok'], $item['qty'], $item['total'], $item['nobukti'], $item['tglbukti'], $item['keterangan'], $item['hargasatuan'], $item['satuan'], $item['persentasediscount'], $item['nominaldiscount']));
        }

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = db::table($tempdatadetail)->from(db::raw($tempdatadetail . " a"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gudang',
                'a.namastok',
                'a.satuan',
                'a.qty',
                'a.hargasatuan',
                'a.total',
                'a.persentasediscount',
                'a.nominaldiscount',
                'a.keterangan',
                'a.nobukti_header',
                'a.tglbukti_header',
                'a.gudang_header',
                'a.trado_id_header',
                'a.gandengan_id_header',
                'a.stok_id_header',
                'a.stok_header',
                'a.qty_header',
                'a.total_header',
                'a.keterangan_header',
                'a.hargasatuan_header',
                'a.satuan_header',
                'a.persentasediscount_header',
                'a.nominaldiscount_header',
                DB::raw("'" . $getJudul->text . "' as judul"),
            )
            ->orderby('a.id', 'asc');


        $data = $query->get();
        return $data;
    }
}
