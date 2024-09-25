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


    public function reminderemailbanpihakke3()
    {


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

        $reminderemail = 1;
        $listtoemail = db::table("toemail")->from(db::raw("toemail a with (readuncommitted)"))
            ->select(
                'a.email'
            )
            ->where('a.reminderemail_id', $reminderemail)
            ->orderby('a.id', 'asc')
            ->get();

        $datadetailtoemail = json_decode($listtoemail, true);
        $hittoemail = 0;
        $toemail = '';
        foreach ($datadetailtoemail as $item) {

            if ($hittoemail == 0) {
                $toemail = $toemail . $item['email'];
            } else {
                $toemail = $toemail . ';' . $item['email'];
            }
            $hittoemail = $hittoemail + 1;
        }

        $listccemail = db::table("ccemail")->from(db::raw("ccemail a with (readuncommitted)"))
            ->select(
                'a.email'
            )
            ->where('a.reminderemail_id', $reminderemail)
            ->orderby('a.id', 'asc')
            ->get();

        $datadetailccemail = json_decode($listccemail, true);
        $hitccemail = 0;
        $ccemail = '';
        foreach ($datadetailccemail as $item) {

            if ($hitccemail == 0) {
                $ccemail = $ccemail . $item['email'];
            } else {
                $ccemail = $ccemail . ';' . $item['email'];
            }
            $hitccemail = $hitccemail + 1;
        }

        $listbccemail = db::table("bccemail")->from(db::raw("bccemail a with (readuncommitted)"))
            ->select(
                'a.email'
            )
            ->where('a.reminderemail_id', $reminderemail)
            ->orderby('a.id', 'asc')
            ->get();

        $datadetailbccemail = json_decode($listbccemail, true);
        $hitbccemail = 0;
        $bccemail = '';
        foreach ($datadetailbccemail as $item) {

            if ($hitbccemail == 0) {
                $bccemail = $bccemail . $item['email'];
            } else {
                $bccemail = $bccemail . ';' . $item['email'];
            }
            $hitbccemail = $hitbccemail + 1;
        }

        // $data = [
        //     (object)[
        //         "tgl"=> "2023-11-15",
        //         "gudang"=> "GUDANG PIHAK KE-3",
        //         "tanggal"=> "11-Oktober-2023",
        //         "nopg"=> "PG 0020/X/2023",
        //         "kodeban"=> "04817106",
        //         "warna"=> "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail"=> "ryan_vixy1402@yahoo.com",
        //         "judul"=> "Reminder Ban Lebih dari 7 Hari di Gdg Sementara/Pihak Ke 3 (Makassar)",
        //     ],

        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';


        $query = db::table($temprekapall)->from(db::raw($temprekapall . " a"))
            ->select(
                db::raw("format(getdate(),'yyyy-MM-dd') as tgl"),
                'c.gudang',
                db::raw("format(a.tgl,'dd-MM-yyyy') as tanggal"),
                'a.nobukti as nopg',
                'b.namastok as kodeban',
                db::raw("(case when a.jumlahhari>10 then 'RED' 
                when (a.jumlahhari)<=10 then 'YELLOW' 
                else '' end) as warna"),
                
                // db::raw("'ryan_vixy1402@yahoo.com' as toemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as ccemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as bccemail"),
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Reminder Ban Lebih dari 7 Hari di Gdg Sementara/Pihak Ke 3 (" . $cabang . ")' as judul"),
            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->join(db::raw("gudang c with (readuncommitted)"), 'a.gudang_id', 'c.id')
            ->orderby('a.jumlahhari', 'desc');

        return $query;
    }
    public function getReport()
    {

        // $data = $queryRekap->get();

        $temprekapall = '##temprekapall' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapall, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tgl')->nullable();
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

        
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        // dd(db::table($temprekapall)->get());
        $query = db::table($temprekapall)->from(db::raw($temprekapall . " a"))
            ->select(
                'b.namastok as kodestok',
                'b.namastok as namabarang',
                'c.gudang',
                'a.nobukti',
                'a.tgl as tanggal',
                'a.jumlahhari as jlhhari',                
                DB::raw("'LAPORAN BAN GUDANG SEMENTARA' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->join(db::raw("stok b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->join(db::raw("gudang c with (readuncommitted)"), 'a.gudang_id', 'c.id')
            ->orderby('a.jumlahhari', 'desc');

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
        $data = $query->get();
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
