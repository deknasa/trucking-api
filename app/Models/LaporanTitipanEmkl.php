<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanTitipanEmkl  extends MyModel
{
    use HasFactory;

    protected $table = 'laporantitipanemkl';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getData($tanggal,$tgldari,$tglsampai,$jenisorder)
    {

        $penerimaantrucking_id = 5;
        $pengeluarantrucking_id = 9;

        $tempdatarekap = '##tempdatarekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempdatarekap, function ($table) {
            $table->string('pengeluarantruckingheader_nobukti', 1000)->nullable();
        });

        $querydatarekap = DB::table('penerimaantruckingdetail')->from(
            DB::raw("penerimaantruckingdetail a with (readuncommitted) ")
        )
            ->select(
                'a.pengeluarantruckingheader_nobukti',
            )
            ->join(DB::raw("penerimaantruckingheader as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("b.tglbukti<='" . $tanggal . "'")
            ->where('b.penerimaantrucking_id', '=', $penerimaantrucking_id);

        DB::table($tempdatarekap)->insertUsing([
            'pengeluarantruckingheader_nobukti',
        ], $querydatarekap);


        $tempbiayatitipan = '##tempbiayatitipan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempbiayatitipan, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('jenisorder_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('suratpengantar_nobukti', 100)->nullable();
        });



        $querybiayatitipan = DB::table('pengeluarantruckingdetail')->from(
            DB::raw("pengeluarantruckingdetail a with (readuncommitted) ")
        )
            ->select(
                'b.nobukti',
                'b.tglbukti as tglbukti',
                'b.keterangan as keterangan',
                'b.jenisorder_id as jenisorder_id',
                'a.nominal as nominal',
                'a.suratpengantar_nobukti'
            )
            ->join(DB::raw("pengeluarantruckingheader as b with (readuncommitted) "), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw($tempdatarekap . " as c with (readuncommitted) "), 'a.nobukti', 'c.pengeluarantruckingheader_nobukti')
            ->whereRaw("isnull(c.pengeluarantruckingheader_nobukti,'')=''")
            ->whereRaw("b.tglbukti<='" . $tanggal . "'");

        DB::table($tempbiayatitipan)->insertUsing([
            'nobukti',
            'tglbukti',
            'keterangan',
            'jenisorder_id',
            'nominal',
            'suratpengantar_nobukti'
        ], $querybiayatitipan);

        // 

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temphasil, function ($table) {
            $table->string('fnopol', 1000)->nullable();
            $table->date('ftgl')->nullable();
            $table->string('fday', 100)->nullable();
            $table->string('ftujuan', 1000)->nullable();
            $table->string('fshipper', 1000)->nullable();
            $table->string('fcont', 1000)->nullable();
            $table->string('fnocont', 1000)->nullable();
            $table->string('fnosp', 1000)->nullable();
            $table->double('fnominal', 15, 2)->nullable();
            $table->integer('furut')->nullable();
        });



        $queryhasil = DB::table($tempbiayatitipan)->from(
            DB::raw($tempbiayatitipan . " d with (readuncommitted) ")
        )
            ->select(
                'c.kodetrado  as fnopol',
                'd.tglbukti',
                db::raw("DATENAME(weekday,d.tglbukti) as FDay"),
                db::raw("isnull(e.tujuan ,'') as FTujuan"),
                'f.namapelanggan',
                db::raw("ISNULL(g.keterangan ,'') AS FCont"),
                'b.nocont as FNoCont',
                'b.nosp as FNoSp',
                'd.nominal as FNominal',
                db::raw("row_number() Over( partition by c.kodetrado Order By c.kodetrado,d.tglbukti,b.nosp ) as FUrut")
            )
            ->leftjoin(DB::raw("suratpengantar as b with (readuncommitted) "), 'd.suratpengantar_nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("trado as c with (readuncommitted) "), 'b.trado_id', 'c.id')
            ->leftjoin(DB::raw("tarif as e with (readuncommitted) "), 'b.tarif_id', 'e.id')
            ->leftjoin(DB::raw("pelanggan as f with (readuncommitted) "), 'b.pelanggan_id', 'f.id')
            ->leftjoin(DB::raw("container as g with (readuncommitted) "), 'b.container_id', 'g.id')
            ->whereRaw("(d.tglbukti>='" . $tgldari . "' and d.tglbukti<='" . $tglsampai . "')")
            ->whereRaw("(d.jenisorder_id=" . $jenisorder . " or " . $jenisorder . "=0)")
            ->orderBy('c.kodetrado', 'asc')
            ->orderBy('b.tglbukti', 'asc')
            ->orderBy('b.nosp', 'asc');

           
        DB::table($temphasil)->insertUsing([
            'fnopol',
            'ftgl',
            'fday',
            'ftujuan',
            'fshipper',
            'fcont',
            'fnocont',
            'fnosp',
            'fnominal',
            'furut',
        ], $queryhasil);

        // dd('test)');
        $temprekapnopol = '##temprekapnopol' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapnopol, function ($table) {
            $table->string('fnopol', 50)->nullable();
        });

        $queryrekapnopol = DB::table($temphasil)->from(
            DB::raw($temphasil . " a with (readuncommitted) ")
        )
            ->select(
                'a.fnopol',
            )
            ->groupBy('a.fnopol');

        DB::table($temprekapnopol)->insertUsing([
            'fnopol',
        ], $queryrekapnopol);

        // 
        $temprekapnopollist = '##temprekapnopollist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temprekapnopollist, function ($table) {
            $table->string('fnopol', 50)->nullable();
            $table->integer('furut')->nullable();
        });

        $queryrekapnopollist = DB::table($temprekapnopol)->from(
            DB::raw($temprekapnopol . " a with (readuncommitted) ")
        )
            ->select(
                'a.fnopol',
                db::raw("row_number() Over( Order By FNoPol) as furut")
            )
            ->OrderBy('a.fnopol', 'asc');

        DB::table($temprekapnopollist)->insertUsing([
            'fnopol',
            'furut'
        ], $queryrekapnopollist);


        $query = DB::table($temphasil)->from(
            DB::raw($temphasil . " a with (readuncommitted) ")
        )
            ->select(
                db::raw("'Transporindo Agung Sejahtera' as judul"),
                db::raw("'Rekap Biaya Titipan Emkl Belum Lunas' as judullaporan"),
                'a.fnopol as trado',
                'a.ftgl as tglbukti',
                'a.fday as day',
                'a.ftujuan as tujuan',
                'a.fshipper as shipper',
                'a.fcont as container',
                'a.fnocont as nocont',
                'a.fnosp as nosp',
                'a.fnominal as nominal',
                'a.furut as urut',
                'b.furut as uruttrado'

            )
            ->join(DB::raw($temprekapnopollist . " as b with (readuncommitted) "), 'a.fnopol', 'b.fnopol')
            ->OrderBy('b.furut', 'asc')
            ->OrderBy('a.furut', 'asc');

        $data = $query->get();

        // $data = [
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "jenisorder" => "Muatan",
        //         "trado" => "BK 1234 ZXC",
        //         "trado_id" => "1",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'kemayoran',
        //         "shipper" => 'fickha sentral',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '54432',
        //         "nominal" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "jenisorder" => "Muatan",
        //         "trado" => "BK 1234 ZXC",
        //         "trado_id" => "1",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'bekasi',
        //         "shipper" => 'bintang jasa',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '65347',
        //         "nominal" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "jenisorder" => "Muatan",
        //         "trado" => "BK 1234 ZXC",
        //         "trado_id" => "1",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'cikarang',
        //         "shipper" => 'energi unggul',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '57337',
        //         "nominal" => '50000',
        //     ],
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "jenisorder" => "Muatan",
        //         "trado" => "BK 1234 ZXC",
        //         "trado_id" => "1",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'cikarang',
        //         "shipper" => 'marketama',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '78676',
        //         "nominal" => '50000',
        //     ],   
        //     [
        //         "judul" => "Transporindo Agugng Sejahtera",
        //         "judulLaporan" => "Pengembalian Titipan Emkl",
        //         "jenisorder" => "Muatan",
        //         "trado" => "BK 2234 ZXC",
        //         "trado_id" => "2",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'kemayoran',
        //         "shipper" => 'fickha sentral',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '54432',
        //         "nominal" => '50000',
        //     ],
        //     [
        //        "judul" => "Transporindo Agugng Sejahtera",
        //        "judulLaporan" => "Pengembalian Titipan Emkl",
        //        "jenisorder" => "Muatan",
        //        "trado" => "BK 2234 ZXC",
        //         "trado_id" => "2",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'bekasi',
        //         "shipper" => 'bintang jasa',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '65347',
        //         "nominal" => '50000',
        //     ],
        //     [
        //        "judul" => "Transporindo Agugng Sejahtera",
        //        "judulLaporan" => "Pengembalian Titipan Emkl",
        //        "jenisorder" => "Muatan",
        //        "trado" => "BK 2234 ZXC",
        //         "trado_id" => "2",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'cikarang',
        //         "shipper" => 'energi unggul',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '57337',
        //         "nominal" => '50000',
        //     ],
        //     [
        //        "judul" => "Transporindo Agugng Sejahtera",
        //        "judulLaporan" => "Pengembalian Titipan Emkl",
        //        "jenisorder" => "Muatan",
        //        "trado" => "BK 2234 ZXC",
        //         "trado_id" => "2",
        //         "tglbukti" => date('Y-m-d',strtotime('2023-01-15')),
        //         "day" => date('D',strtotime('2023-01-15')),
        //         "tujuan" => 'cikarang',
        //         "shipper" => 'marketama',
        //         "container_id" => '1',
        //         "container" => '20"',
        //         "nosp" => '78676',
        //         "nominal" => '50000',
        //     ],   
        // ];

        return $data;
    }
}
