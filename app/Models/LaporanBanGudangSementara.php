<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class LaporanBanGudangSementara extends MyModel
{
    use HasFactory;

    protected $table = '';


    public function getReport()
    {

        // $data = $queryRekap->get();
   
        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->datetime('tgl')->nullable();
            $table->integer('gudang_id')->nullable();
            $table->double('qty')->nullable();
            $table->integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
        });


        DB::table($temprekapall)->insertUsing([
            'nobukti',
            'tgl',
            'gudang_id',
            'qty',
            'stok_id',
            'jumlahhari',
        ], $this->getdata());

        // dd(db::table($temprekapall)->get());
        $query=db::table($temprekapall)->from(db::raw( $temprekapall ." a"))
        ->select(
            'b.namastok as kodestok',
            'b.namastok as namabarang',
            'c.gudang',
            'a.nobukti',
            'a.tgl as tanggal',
            'a.jumlahhari as jlhhari',
        )
        ->join(db::raw("stok b with (readuncommitted)"),'a.stok_id','b.id')
        ->join(db::raw("gudang c with (readuncommitted)"),'a.gudang_id','c.id')
        ->orderby('a.jumlahhari','desc');

        // $data = [
        //     [
        //         "kodestok" => "BAUT 12",
        //         'namastok' => 'BAUT 12',
        //         'gudang' => 'GUDANG PIHAK KE-3',
        //         'nobukti' => 'PG 00035/II/2023',
        //         'tanggal' => '23/2/2023',
        //         'jlhhari' => '23'
        //     ]
        // ];
        $data=$query->get();
        return $data;
    }

    public function getdata()
    {


        $temppaja = '##temppaja' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppaja, function ($table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->nullable();
        });

        $querypja = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                'b.stok_id',
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereraw("a.pengeluaranstok_id=4");

        DB::table($temppaja)->insertUsing([
            'stok_id',
        ], $querypja);

        $tempstok = '##tempstok' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstok, function ($table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->nullable();
        });

        $querystok = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                'a.id as stok_id',
            )
            ->join(db::raw("kelompok b with (readuncommitted)"), 'a.kelompok_id', 'b.id')
            ->leftjoin(db::raw($temppaja . " c "), 'a.id', 'c.stok_id')
            ->whereraw("a.statusaktif=1")
            ->whereraw("b.kodekelompok ='BAN'")
            ->whereraw("a.statusban NOT IN (343,521)")
            ->whereraw("isnull(c.stok_id,0)=0");

        DB::table($tempstok)->insertUsing([
            'stok_id',
        ], $querystok);


        $tempstokmasuk = '##tempstokmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstokmasuk, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->datetime('tgl')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
        });

        $tempstokkeluar = '##tempstokkeluar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstokkeluar, function ($table) {
            $table->id();
            $table->string('nobukti', 100)->nullable();
            $table->datetime('tgl')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
        });

        $querystokmasuk = db::table("kartustok")->from(db::raw("kartustok a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gudang_id',
                'a.qtymasuk',
                'a.stok_id'
            )
            ->join(db::raw($tempstok . " b "), 'a.stok_id', 'b.stok_id')
            ->whereraw("a.gudang_id IN (2)")
            ->whereraw("isnull(a.qtymasuk,0)<>0");

        DB::table($tempstokmasuk)->insertUsing([
            'nobukti',
            'tgl',
            'gudang_id',
            'qty',
            'stok_id',
        ], $querystokmasuk);

        $querystokkeluar = db::table("kartustok")->from(db::raw("kartustok a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.gudang_id',
                'a.qtymasuk',
                'a.stok_id'
            )
            ->join(db::raw($tempstok . " b "), 'a.stok_id', 'b.stok_id')
            ->whereraw("a.gudang_id IN (2)")
            ->whereraw("isnull(a.qtykeluar,0)<>0")
            ->whereraw("left(a.nobukti,4)='spbs'");

        DB::table($tempstokkeluar)->insertUsing([
            'nobukti',
            'tgl',
            'gudang_id',
            'qty',
            'stok_id',
        ], $querystokkeluar);


        $tgl = date('Y-m-d');
        $tglbatas = date('Y-m-d', strtotime($tgl . ' -7 day'));

        // dump(db::table($tempstokmasuk)->get());
        // dd(db::table($tempstokkeluar)->get());
        $query = db::table($tempstokmasuk)->from(db::raw($tempstokmasuk . " a"))
            ->select(
                'a.nobukti',
                'a.tgl',
                'a.gudang_id',
                'a.qty',
                'a.stok_id',
                db::raw("datediff(dd,a.tgl,getdate()) as jumlahhari"),
            )
            ->leftjoin(db::raw($tempstokkeluar . " b"), 'a.stok_id', 'b.stok_id')
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("a.tgl<='" . $tglbatas . "'");


            // dd($query->get());
        $data = $query;
        return $data;
    }
}
