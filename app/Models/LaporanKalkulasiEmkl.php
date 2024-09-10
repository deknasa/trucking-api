<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKalkulasiEmkl extends Model
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

    public function getReport($periode)
    {
        $periode = $periode ;
        $statusposting = $jenis;
        $parameter = new Parameter();
        $idstatusposting = $parameter->cekId('STATUS POSTING', 'STATUS POSTING', 'POSTING') ?? 0;
        $penerimaanTruckingHeader = '##penerimaantruckingheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        // dd(db::table($penerimaanTruckingDetailrekap)->get());

        $pengeluaranTruckingHeader = '##pengeluaranTruckingHeader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranTruckingHeader, function ($table) {
            $table->BigInteger('id');
            $table->string('nobukti', 50)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->string('posting', 500)->nullable();
        });

        $pengeluaranTrucking = PengeluaranTrucking::where('kodepengeluaran', '=', 'PJT')->first();

        $pengeluarantrucking_id = $pengeluaranTrucking->id;


        $pengeluaranTruckingkaryawan = PengeluaranTrucking::where('kodepengeluaran', '=', 'PJK')->first();
        $pengeluarantruckingkaryawan_id = $pengeluaranTruckingkaryawan->id;

        $queryPengeluaranTruckingHeader = DB::table("pengeluarantruckingheader")->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                db::raw("(case when " . $statusposting . "=0 then ' ( '+isnull(e.[text],'')+' ) '  else '' end) as posting"),


            )
            ->leftjoin(db::raw('parameter e with (readuncommitted)'), 'a.statusposting', 'e.id')
            ->whereraw("format(a.tglbukti,'MM-yyyy')='$periode'")
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantrucking_id)
            ->whereRaw("(a.statusposting=" . $statusposting . " or " . $statusposting . "=0)");

            // dd($queryPengeluaranTruckingHeader->get());
        DB::table($pengeluaranTruckingHeader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'posting',
        ], $queryPengeluaranTruckingHeader);

        $queryPengeluaranTruckingHeader = DB::table("pengeluarantruckingheader")->from(
            DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                db::raw("(case when " . $statusposting . "=0 then ' ( '+isnull(e.[text],'')+' ) '  else '' end) as posting"),


            )
            ->leftjoin(db::raw('parameter e with (readuncommitted)'), 'a.statusposting', 'e.id')
            ->whereraw("format(a.tglbukti,'MM-yyyy')='$periode'")
            ->where('a.pengeluarantrucking_id', '=', $pengeluarantruckingkaryawan_id)
            ->whereRaw("(a.statusposting=" . $statusposting . " or " . $statusposting . "=0)");

        DB::table($pengeluaranTruckingHeader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'posting',
        ], $queryPengeluaranTruckingHeader);


     
        $pengeluaranTruckingDetail = '##pengeluaranTruckingDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($pengeluaranTruckingDetail, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('supir_id')->nullable();
            $table->integer('karyawan_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $queryPengeluaranTruckingDetail = DB::table("pengeluarantruckingdetail")->from(
            DB::raw("pengeluarantruckingdetail as a")
        )
            ->select(
                'a.nobukti',
                DB::raw("max(a.keterangan) as keterangan"),
                DB::raw("max(a.supir_id) as supir_id"),
                DB::raw("max(a.karyawan_id) as karyawan_id"),
                DB::raw("sum(a.nominal) as nominal"),

            )
            ->join(DB::raw($pengeluaranTruckingHeader . " as b"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');


        DB::table($pengeluaranTruckingDetail)->insertUsing([
            'nobukti',
            'keterangan',
            'supir_id',
            'karyawan_id',
            'nominal',
        ], $queryPengeluaranTruckingDetail);

        // dd(db::table($pengeluaranTruckingDetail)->get());



       

        $tempLaporan = '##tempLaporan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempLaporan, function ($table) {
            $table->BigIncrements('id');
            $table->dateTime('tglbukti')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('namasupirkaryawan', 1000)->nullable();
        });

        $queryTempLaporan = DB::table($pengeluaranTruckingHeader)->from(
            DB::raw($pengeluaranTruckingHeader . " as a")
        )
            ->select(
                'a.tglbukti',
                'a.nobukti',
                db::raw("trim(c.keterangan)+isnull(a.posting,'') as keterangan"),
                db::raw("c.nominal as nominal"),
                db::raw("isnull(d.namasupir,isnull(e.namakaryawan,'')) as namasupirkaryawan")

            )
            ->join(DB::raw($pengeluaranTruckingDetail . " as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
            ->leftjoin(DB::raw("supir as d with (readuncommitted) "), 'c.supir_id', 'd.id')
            ->leftjoin(DB::raw("karyawan as e with (readuncommitted) "), 'c.karyawan_id', 'e.id')
            ->orderBy(db::raw("isnull(d.namasupir,e.namakaryawan)"), 'ASC')
            ->orderBy('a.tglbukti', 'ASC')
            ->orderBy('c.nobukti', 'ASC');

  
        DB::table($tempLaporan)->insertUsing([
            'tglbukti',
            'nobukti',
            'keterangan',
            'nominal',
            'namasupirkaryawan',
        ], $queryTempLaporan);



        $disetujui = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DISETUJUI')
            ->where('subgrp', 'DISETUJUI')->first()->text ?? '';

        $diperiksa = db::table('parameter')->from(db::raw('parameter with (readuncommitted)'))
            ->select('text')
            ->where('grp', 'DIPERIKSA')
            ->where('subgrp', 'DIPERIKSA')->first()->text ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $parameter = new Parameter();
        if ($statusposting == 0) {
            $judul1 = ' ( SEMUA )';
        } else {
            $judul1 = ' ( ' . $parameter->cekdataText($statusposting) . ' ) ' ?? '';
        }
        $queryRekap = DB::table($tempLaporan)->from(
            DB::raw($tempLaporan . " as a")
        )
            ->select(
                db::raw("cast(a.tglbukti as date) as tanggal"),
                'a.nobukti',
                'a.namasupirkaryawan',
                'a.keterangan',
                'a.nominal',
                db::raw("'" . $disetujui . "' as disetujui"),
                db::raw("'" . $diperiksa . "' as diperiksa"),
                DB::raw("upper('Laporan Pinjaman Banding Periode')+'" . $judul1 . "' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
            )
            ->orderBy('a.id');


            $data = $queryRekap->get();
        return $data;
    }
}
