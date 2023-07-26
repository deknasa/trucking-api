<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanPemakaianBan extends MyModel
{
    use HasFactory;

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



    public function getReport($dari, $sampai, $posisiAkhir, $jenisLaporan)
    {
        $dari = date('Y-m-d', strtotime(request()->dari)) ?? '1900/1/1';
        $sampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900/1/1';

        $posisiAkhir = $posisiAkhir;
        $status = $jenisLaporan;


        $kelompok_id = 1;

        $TempDataBan = '##TempDataBAn' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempDataBan, function ($table) {
            $table->integer('id')->nullable();
            $table->string('namastok', 1000)->nullable();
        });

        $queryDataBan = DB::table("stok")->from(
            DB::raw("stok as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.namastok',
            )
            ->where('kelompok_id', '=', $kelompok_id);

        DB::table($TempDataBan)->insertUsing([
            'id',
            'namastok',
        ], $queryDataBan);



        $Temphasildata = '##Temphasildata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphasildata, function ($table) {
            $table->integer('[status]')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->integer('stok_id')->nullable();
            $table->integer('gudangin_id')->nullable();
            $table->integer('tradoin_id')->nullable();
            $table->integer('gandenganin_id')->nullable();
            $table->integer('gudangout_id')->nullable();
            $table->integer('tradoout_id')->nullable();
            $table->integer('gandenganout_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryhasildata = DB::table("penerimaanstokheader")->from(
            DB::raw("penerimaanstokheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("1 as [status]"),
                'a.tglbukti',
                'a.id',
                'a.nobukti',
                'b.stok_id',
                DB::raw("(case when isnull(a.gudang_id,0)=0 then isnull(a.gudangke_id,0) else isnull(a.gudang_id,0) end) as gudangin_id"),
                DB::raw("(case when isnull(a.trado_id,0)=0 then isnull(a.tradoke_id,0) else isnull(a.trado_id,0) end) as tradodin_id"),
                DB::raw("(case when isnull(a.gandengan_id,0)=0 then isnull(a.gandenganke_id,0) else  isnull(a.gandengan_id,0) end) as gandenganin_id"),
                DB::raw("isnull(a.gudangdari_id,0) as gudangout_id"),
                DB::raw("isnull(a.tradodari_id,0) as tradoout_id"),
                DB::raw("isnull(a.gandengandari_id,0) as gandenganout_id"),
                'a.created_at',
                'a.updated_at',
            )
            ->join(DB::raw("penerimaanstokdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($TempDataBan . " as c "), 'b.stok_id', 'c.id')
            ->orderBy('a.id', 'asc');


        DB::table($Temphasildata)->insertUsing([
            '[status]',
            'tglbukti',
            'id',
            'nobukti',
            'stok_id',
            'gudangin_id',
            'tradoin_id',
            'gandenganin_id',
            'gudangout_id',
            'tradoout_id',
            'gandenganout_id',
            'created_at',
            'updated_at',
        ], $queryhasildata);


        $queryhasildata = DB::table("pengeluaranstokheader")->from(
            DB::raw("pengeluaranstokheader as a with (readuncommitted)")
        )
            ->select(
                DB::raw("2 as [status]"),
                'a.tglbukti',
                'a.id',
                'a.nobukti',
                'b.stok_id',
                DB::raw("0 as gudangin_id"),
                DB::raw("0 as tradodin_id"),
                DB::raw("0 as gandenganin_id"),
                DB::raw("isnull(a.gudang_id,0) as gudangout_id"),
                DB::raw("isnull(a.trado_id,0) as tradoout_id"),
                DB::raw("isnull(a.gandengan_id,0) as gandenganout_id"),
                'a.created_at',
                'a.updated_at',
            )
            ->join(DB::raw("pengeluaranstokdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw($TempDataBan . " as c"), 'b.stok_id', 'c.id')
            ->orderBy('a.id', 'asc');

        DB::table($Temphasildata)->insertUsing([
            '[status]',
            'tglbukti',
            'id',
            'nobukti',
            'stok_id',
            'gudangin_id',
            'tradoin_id',
            'gandenganin_id',
            'gudangout_id',
            'tradoout_id',
            'gandenganout_id',
            'created_at',
            'updated_at',
        ], $queryhasildata);


        $Temphasildata2 = '##Temphasildata2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temphasildata2, function ($table) {
            $table->integer('[status]')->nullable();
            $table->integer('id')->nullable();
            $table->integer('stok_id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('namastok', 1000)->nullable();
            $table->string('gudangdari', 1000)->nullable();
            $table->string('tradodari', 1000)->nullable();
            $table->string('gandengandari', 1000)->nullable();
            $table->string('gudangke', 1000)->nullable();
            $table->string('tradoke', 1000)->nullable();
            $table->string('gandenganke', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryhasildata2 = DB::table($Temphasildata)->from(
            DB::raw($Temphasildata . " as a ")
        )
            ->select(
                'a.[status]',
                'a.stok_id',
                'a.tglbukti',
                'a.id',
                'a.nobukti',
                'b.namastok',
                DB::raw("isnull(d.gudang,'') as gudangdari"),
                DB::raw("isnull(e.kodetrado,'') as tradodari"),
                DB::raw("isnull(f.kodegandengan,'') as gandengandari"),
                DB::raw("isnull(d1.gudang,'') as gudangke"),
                DB::raw("isnull(e1.kodetrado,'') as tradoke"),
                DB::raw("isnull(f1.kodegandengan,'') as gandenganke"),
                'a.created_at',
                'a.updated_at',
            )
            ->leftjoin(DB::raw("stok as b with (readuncommitted)"), 'a.stok_id', 'b.id')
            ->leftjoin(DB::raw("gudang as d with (readuncommitted)"), 'a.gudangin_id', 'd.id')
            ->leftjoin(DB::raw("trado as e with (readuncommitted)"), 'a.tradoin_id', 'e.id')
            ->leftjoin(DB::raw("gandengan as f with (readuncommitted)"), 'a.gandenganin_id', 'f.id')
            ->leftjoin(DB::raw("gudang as d1 with (readuncommitted)"), 'a.gudangout_id', 'd1.id')
            ->leftjoin(DB::raw("trado as e1 with (readuncommitted)"), 'a.tradoout_id', 'e1.id')
            ->leftjoin(DB::raw("gandengan as f1 with (readuncommitted)"), 'a.gandenganout_id', 'f1.id')
            ->orderBy('a.id', 'asc');




        DB::table($Temphasildata2)->insertUsing([
            '[status]',
            'stok_id',
            'tglbukti',
            'id',
            'nobukti',
            'namastok',
            'gudangdari',
            'tradodari',
            'gandengandari',
            'gudangke',
            'tradoke',
            'gandenganke',
            'created_at',
            'updated_at',
        ], $queryhasildata2);



        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->integer('[status]')->nullable();
            $table->integer('stok_id')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('tanggal', 50)->nullable();
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->string('namastok', 1000)->nullable();
            $table->string('[in]', 1000)->nullable();
            $table->string('[out]', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $querytemphasil = DB::table($Temphasildata2)->from(
            DB::raw($Temphasildata2 . " as a ")
        )
            ->select(
                'a.[status]',
                'a.stok_id',
                'a.tglbukti',
                DB::raw("format(a.tglbukti,'yyyyMMdd')+format(a.created_at,'yyyyMMddHHmmss') as tanggal"),
                'a.id',
                'a.nobukti',
                'a.namastok',
                DB::raw("
                (case when left(a.nobukti,3)='SPK' then 
        
                (case when isnull(a.gudangke,'')<>'' then isnull(a.gudangke,'')
              when isnull(a.tradoke,'')<>'' then isnull(a.tradoke,'')
              when isnull(a.gudangke,'')<>'' then isnull(a.gudangke,'')
              else  isnull(a.gudangke,'') end)
              when isnull(a.gudangdari,'')<>'' then isnull(a.gudangdari,'')
              when isnull(a.tradodari,'')<>'' then isnull(a.tradodari,'')
              when isnull(a.gudangdari,'')<>'' then isnull(a.gudangdari,'')
              else  isnull(a.gudangdari,'') end) as [IN]                
                "),
                DB::raw("
                (case when left(a.nobukti,3)='SPK' then 'GUDANG KANTOR'
                when isnull(a.gudangke,'')<>'' then isnull(a.gudangke,'')
                when isnull(a.tradoke,'')<>'' then isnull(a.tradoke,'')
                when isnull(a.gudangke,'')<>'' then isnull(a.gudangke,'')
                else  isnull(a.gudangke,'') end) as [OUT]                
                "),
                'a.created_at',
                'a.updated_at',
            );


        DB::table($temphasil)->insertUsing([
            '[status]',
            'stok_id',
            'tglbukti',
            'tanggal',
            'id',
            'nobukti',
            'namastok',
            '[in]',
            '[out]',
            'created_at',
            'updated_at',
        ], $querytemphasil);


        $temphasildata3 = '##Temphasildata3' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasildata3, function ($table) {
            $table->id();
            $table->integer('[status]')->nullable();
            $table->integer('stok_id')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('dataid')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->string('namastok', 1000)->nullable();
            $table->string('posisi', 1000)->nullable();
            $table->longText('keterangan', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('tanggal', 50)->nullable();
        });

        $temphasildata3rekap = '##Temphasildata3rekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasildata3rekap, function ($table) {
            $table->id();
            $table->integer('[status]')->nullable();
            $table->integer('stok_id')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('dataid')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->string('namastok', 1000)->nullable();
            $table->string('posisi', 1000)->nullable();
            $table->longText('keterangan', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('tanggal', 50)->nullable();
            $table->integer('urut')->nullable();
        });

        $temphasiltemp = '##temphasiltemp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasiltemp, function ($table) {
            $table->integer('[status]')->nullable();
            $table->integer('stok_id')->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->string('namastok', 1000)->nullable();
            $table->string('posisi', 1000)->nullable();
            $table->longText('keterangan', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('tanggal', 50)->nullable();
        });

        if ($status == 'ANALISA BAN') {

            $queryhasiltemp = DB::table($temphasil)->from(
                DB::raw($temphasil . " as a ")
            )
                ->select(
                    'a.[status]',
                    'a.stok_id',
                    'a.tglbukti',
                    'a.id',
                    'a.nobukti',
                    'a.namastok',
                    'a.[in] as posisi',
                    DB::raw("'IN' as keterangan"),
                    'a.created_at',
                    'a.updated_at',
                    'a.tanggal',
                );
            DB::table($temphasiltemp)->insertUsing([
                '[status]',
                'stok_id',
                'tglbukti',
                'id',
                'nobukti',
                'namastok',
                'posisi',
                'keterangan',
                'created_at',
                'updated_at',
                'tanggal',
            ], $queryhasiltemp);


            $queryhasiltemp = DB::table($temphasil)->from(
                DB::raw($temphasil . " as a ")
            )
                ->select(
                    'a.[status]',
                    'a.stok_id',
                    'a.tglbukti',
                    'a.id',
                    'a.nobukti',
                    'a.namastok',
                    'a.[out] as posisi',
                    DB::raw("'OUT' as keterangan"),
                    'a.created_at',
                    'a.updated_at',
                    'a.tanggal',
                );

            DB::table($temphasiltemp)->insertUsing([
                '[status]',
                'stok_id',
                'tglbukti',
                'id',
                'nobukti',
                'namastok',
                'posisi',
                'keterangan',
                'created_at',
                'updated_at',
                'tanggal',
            ], $queryhasiltemp);


            $queryhasildata3 = DB::table($temphasiltemp)->from(
                DB::raw($temphasiltemp . " as a ")
            )
                ->select(
                    'a.[status]',
                    'a.stok_id',
                    'a.tglbukti',
                    'a.id as dataid',
                    'a.nobukti',
                    'a.namastok',
                    'a.posisi',
                    'a.keterangan',
                    'a.created_at',
                    'a.updated_at',
                    'a.tanggal',
                )
                ->whereRaw("a.posisi<>''")
                ->whereRaw("a.keterangan='IN'")
                ->orderBy('a.tanggal', 'desc');


            DB::table($temphasildata3)->insertUsing([
                '[status]',
                'stok_id',
                'tglbukti',
                'dataid',
                'nobukti',
                'namastok',
                'posisi',
                'keterangan',
                'created_at',
                'updated_at',
                'tanggal',
            ], $queryhasildata3);

            $queryhasildata3rekap = DB::table($temphasildata3)->from(
                DB::raw($temphasildata3 . " as a ")
            )
                ->select(
                    'a.[status]',
                    'a.stok_id',
                    'a.tglbukti',
                    'a.dataid',
                    'a.nobukti',
                    'a.namastok',
                    'a.posisi',
                    'a.keterangan',
                    'a.created_at',
                    'a.updated_at',
                    'a.tanggal',
                    DB::raw("row_number() Over(partition BY A.stok_id Order By A.dataid) as urut"),

                )
                ->whereRaw("a.posisi<>''")
                ->whereRaw("a.keterangan='IN'")
                ->orderBy('a.tanggal', 'desc');

            DB::table($temphasildata3rekap)->insertUsing([
                '[status]',
                'stok_id',
                'tglbukti',
                'dataid',
                'nobukti',
                'namastok',
                'posisi',
                'keterangan',
                'created_at',
                'updated_at',
                'tanggal',
                'urut',
            ], $queryhasildata3rekap);
        }


        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($temphasildata3rekap)->from(
            DB::raw($temphasildata3rekap . " as a ")
        )
            ->select(
                'a.namastok as nobanA',
                'a.nobukti',
                DB::raw("format(a.tglbukti,'dd-MM-yyyy')as tanggal"),
                db::Raw("'' as gudang"),
                'a.posisi as posisiakhir',
                db::Raw("'' as kondisiakhir"),
                db::Raw("'' as nopg"),
                db::Raw("'' as nobanB"),
                db::Raw("'' as alasanpenggantian"),
                db::Raw("'' as vulke"),
                db::Raw("'' as noklaim"),
                db::Raw("'' as nopjt"),
                db::Raw("'' as ketafkir"),
                DB::raw("'Laporan Pemakaian Ban' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->whereRaw("a.urut=1")
            ->orderBy('a.id', 'asc');


        $data = $query->get();
        return $data;
    }
}
