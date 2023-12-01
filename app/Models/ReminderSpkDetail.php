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

    public function get()
    {
        $stok_id = request()->stok_id ?? 0;
        $trado_id = request()->trado_id ?? 0;
        $gandengan_id = request()->gandengan_id ?? 0;
        $gudang = request()->gudang ?? '';
        $stok = request()->stok ?? '';

        $saldo = Parameter::where('grp', 'SALDO')->where('subgrp', 'SALDO')->first();

        $queryloop = DB::connection('sqlsrvlama')->table("TrStckAdj_R")->from(db::raw("TrStckAdj_R a with (readuncommitted)"))
            ->select(
                db::raw("b.fntrans as nobukti"),
                db::raw("b.ftgl as tglbukti"),
                db::raw("b.fnopol as gudang"),
                db::raw("a.fkstck as namastok"),
                db::raw("abs(a.fqty) as qty"),
                db::raw("a.fhrgsat as hargasatuan"),
                db::raw("a.ftotal as total"),
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
            $table->double('qty', 15, 2)->nullable();
            $table->double('hargasatuan', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
        });

        $queryloop = json_encode($queryloop, JSON_INVALID_UTF8_SUBSTITUTE);
        $query = json_decode($queryloop, true);
        foreach ($query as $item) {
            DB::table($tempdata)->insert([
                'nobukti' => $item['nobukti'] ?? '',
                'tglbukti' =>  $item['tglbukti'] ?? '',
                'gudang' => $item['gudang'] ?? '',
                'namastok' => $item['namastok'] ?? '',
                'qty' =>  $item['qty'] ?? 0,
                'hargasatuan' =>  $item['hargasatuan'] ?? 0,
                'total' => $item['total'] ?? 0,
            ]);
        }



        $pengeluaranstok = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $querykeluar = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                db::raw("a.nobukti as nobukti"),
                db::raw("a.tglbukti as tglbukti"),
                db::raw("'" . $gudang . "' as gudang"),
                db::raw("'" . $stok . "' as namastok"),
                db::raw("b.qty as qty"),
                db::raw("b.harga as hargasatuan"),
                db::raw("b.total as total"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
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
            'qty',
            'hargasatuan',
            'total',
        ], $querykeluar);


     

        $query = db::table($tempdata)->from(db::raw($tempdata ." a "))
        ->select(
            'a.nobukti',
            'a.tglbukti',
            'a.gudang',
            'a.namastok',
            'a.qty',
            'a.hargasatuan',
            'a.total',
        )
        ->orderby('a.tglbukti', 'desc');




        $data = $query->get();
        return $data;
    }
}
