<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class LogAbsensi extends MyModel
{
    use HasFactory;
    protected $table = 'logabsensi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getdata($tgldari, $tglsampai)
    {

        $datacabang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'CABANG')
            ->where('subgrp', 'CABANG')
            ->first()->text ?? '';

        if ($datacabang == 'MEDAN') {
            DB::update(DB::raw("UPDATE logabsensi SET id=288 from logabsensi where id=280"));
        }
        $ptgl1 = $tgldari;
        $ptgl2 = $tglsampai;
        $tempdataabsen = '##tempdataabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdataabsen, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('personname')->nullable();
            $table->datetime('tgl')->nullable();
            $table->time('jam')->nullable();
            $table->time('jamdetail')->nullable();
            $table->integer('urut')->nullable();
        });


        $querytempdataabsen = DB::table("logabsensi")->from(
            DB::raw("logabsensi a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("upper(a.personname) as personname"),
                DB::raw("a.[date] as tgl"),
                DB::raw("cast(left(cast(a.[time] as varchar(100)),5) as time)"),
                DB::raw("a.[time]"),
                DB::raw("ROW_NUMBER() OVER(PARTITION BY  a.id,a.[date],cast(left(cast(a.[time] as varchar(100)),5) as time) ORDER BY  a.[time])  as urut")
            )
            ->whereRaw("a.[date]>='" . $ptgl1 . "' and a.[date]<='" . $ptgl2 . "'");


        DB::table($tempdataabsen)->insertUsing([
            'id',
            'personname',
            'tgl',
            'jam',
            'jamdetail',
            'urut',
        ], $querytempdataabsen);
        // 

        $tempdataabsenrekap = '##tempdataabsenrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdataabsenrekap, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('personname')->nullable();
            $table->datetime('tgl')->nullable();
            $table->time('jam')->nullable();
        });

        $querytempdataabsenrekap = DB::table($tempdataabsen)->from(
            DB::raw($tempdataabsen . " a ")
        )
            ->select(
                'a.id',
                'a.personname',
                'a.tgl',
                'a.jam'
            )
            ->groupby('a.id')
            ->groupby('a.personname')
            ->groupby('a.tgl')
            ->groupby('a.jam');

        DB::table($tempdataabsenrekap)->insertUsing([
            'id',
            'personname',
            'tgl',
            'jam',
        ], $querytempdataabsenrekap);

        $tempkaryawan = '##tempkaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkaryawan, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('namakaryawan')->nullable();
        });

        $querytempkaryawan = DB::table($tempdataabsenrekap)->from(
            DB::raw($tempdataabsenrekap . " a ")
        )
            ->select(
                'a.id',
                db::raw("max(a.personname) as namakaryawan")
            )
            ->groupby('a.id');

        DB::table($tempkaryawan)->insertUsing([
            'id',
            'namakaryawan',
        ], $querytempkaryawan);

        $tempshift = '##tempshift' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempshift, function ($table) {
            $table->integer('hari')->nullable();
            $table->time('jammasukmulai', 7)->nullable();
            $table->time('jammasuk', 7)->nullable();
            $table->time('jampulang', 7)->nullable();
            $table->Time('batasjammasuk')->nullable();
        });


        DB::table($tempshift)->insert([
            'hari' => 2,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 3,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 4,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 5,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 6,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 7,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '12:00',
            'batasjammasuk' => '11:30',
        ]);

        $tempdataabsenhasil = '##tempdataabsenhasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdataabsenhasil, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('personname')->nullable();
            $table->datetime('tgl')->nullable();
            $table->time('jam')->nullable();
            $table->time('jammasukmulai')->nullable();
            $table->time('jammasuk')->nullable();
            $table->time('jampulang')->nullable();
            $table->time('batasjammasuk')->nullable();
            $table->string('statusabsen', 1000)->nullable();
        });

   
        $querytempdataabsenhasil = DB::table($tempdataabsenrekap)->from(
            DB::raw($tempdataabsenrekap . " a ")
        )
            ->select(
                'a.id',
                'a.personname',
                'a.tgl',
                'a.jam',
                db::raw("isnull(b.jammasukmulai,'00:00:00.0000000') as jammasukmulai"),
                db::raw("isnull(b.jammasuk,'00:00:00.0000000') as jammasuk"),
                db::raw("isnull(b.jampulang,'00:00:00.0000000') as jampulang"),
                db::raw("isnull(b.batasjammasuk,'00:00:00.0000000') as batasjammasuk"),
                db::raw("(case when isnull(b.hari,0)=0 then 'LIBUR'
                      when a.jam<=b.batasjammasuk then 'MASUK'
                      when a.jam>b.batasjammasuk then 'PULANG'
                      when year(isnull(c.tgl,'1900/1/1'))=1900  then 'LIBUR'
                      else '' end) as statusabsen")
            )
            ->leftjoin(db::raw($tempshift . " b"), db::raw("datepart(dw,a.tgl)"), 'b.hari')
            ->leftjoin(db::raw("harilibur c with (readuncommitted)"), 'a.tgl', 'c.tgl');

            // dd($querytempdataabsenhasil->get());

        DB::table($tempdataabsenhasil)->insertUsing([
            'id',
            'personname',
            'tgl',
            'jam',
            'jammasukmulai',
            'jammasuk',
            'jampulang',
            'batasjammasuk',
            'statusabsen',
        ], $querytempdataabsenhasil);

      
        $tempdataabsenhasil2 = '##tempdataabsenhasil2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdataabsenhasil2, function ($table) {
            $table->integer('urut')->nullable();
            $table->integer('id')->nullable();
            $table->longtext('personname')->nullable();
            $table->datetime('tgl')->nullable();
            $table->time('jam')->nullable();
            $table->time('jammasukmulai')->nullable();
            $table->time('jammasuk')->nullable();
            $table->time('jampulang')->nullable();
            $table->time('batasjammasuk')->nullable();
            $table->string('statusabsen', 1000)->nullable();
        });

        $querytempdataabsenhasil2 = DB::table($tempdataabsenhasil)->from(
            DB::raw($tempdataabsenhasil . " a ")
        )
            ->select(
                db::raw("ROW_NUMBER() OVER(PARTITION BY  a.id,a.tgl,a.statusabsen ORDER BY  a.jam)  as urut"),
                'a.id',
                'a.personname',
                'a.tgl',
                'a.jam',
                'a.jammasukmulai',
                'a.jammasuk',
                'a.jampulang',
                'a.batasjammasuk',
                'a.statusabsen'

            );



        DB::table($tempdataabsenhasil2)->insertUsing([
            'urut',
            'id',
            'personname',
            'tgl',
            'jam',
            'jammasukmulai',
            'jammasuk',
            'jampulang',
            'batasjammasuk',
            'statusabsen',
        ], $querytempdataabsenhasil2);

        DB::delete(DB::raw("delete " . $tempdataabsen . " where urut<>1"));


        $templogabsen = '##templogabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templogabsen, function ($table) {
            $table->integer('urut')->nullable();
            $table->integer('id')->nullable();
            $table->longtext('personname')->nullable();
            $table->datetime('tgl')->nullable();
            $table->time('jam')->nullable();
            $table->string('jadwalkerja', 1000)->nullable();
            $table->string('statusabsen', 1000)->nullable();
            $table->longtext('terlambatmasuk')->nullable();
            $table->longtext('terlambatpulang')->nullable();
            $table->longtext('cepatmasuk')->nullable();
            $table->longtext('cepatpulang')->nullable();
        });


        $querytemplogabsen = DB::table($tempdataabsenhasil2)->from(
            DB::raw($tempdataabsenhasil2 . " a ")
        )
            ->select(
                'a.urut',
                'a.id',
                'a.personname',
                'a.tgl',
                'b.jamdetail as jam',
                db::raw("trim(cast(left(a.jammasuk,5) as varchar(100)))+' : '+trim(cast(left(a.jampulang,5) as varchar(100))) as jadwalkerja"),
                db::raw("'HADIR' AS statusabsen"),
                db::raw("(case when a.statusabsen='MASUK' and a.urut=1 and b.jamdetail>a.jammasukmulai then 
                    right('0' + convert(varchar(9),(datediff(second,a.jammasukmulai,b.jamdetail) / 3600 )),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,a.jammasukmulai,b.jamdetail) / 60) % 60 ),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,a.jammasukmulai,b.jamdetail) % 60 )),2)
                    else '' end) as terlambatmasuk"),
                db::raw("(case when a.statusabsen='PULANG' and a.urut=1 and b.jamdetail>a.jampulang then 
                    right('0' + convert(varchar(9),(datediff(second,a.jampulang,b.jamdetail) / 3600 )),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,a.jampulang,b.jamdetail) / 60) % 60 ),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,a.jampulang,b.jamdetail) % 60 )),2)
                    else '' end) as terlambatpulang"),
                db::raw("(case when a.statusabsen='MASUK' and a.urut=1 and b.jamdetail<a.jammasukmulai then 
                        right('0' + convert(varchar(9),(datediff(second,b.jamdetail,a.jammasukmulai) / 3600 )),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,b.jamdetail,a.jammasukmulai) / 60) % 60 ),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,b.jamdetail,a.jammasukmulai) % 60 )),2)
                    else '' end) as cepatmasuk"),
                db::raw("(case when a.statusabsen='PULANG' and a.urut=1 and b.jamdetail<a.jampulang then 
                            right('0' + convert(varchar(9),(datediff(second,b.jamdetail,a.jampulang) / 3600 )),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,b.jamdetail,a.jampulang) / 60) % 60 ),2) + ':' 
                                + right('0' + convert(varchar(2),(datediff(second,b.jamdetail,a.jampulang) % 60 )),2)
                    else '' end) as cepatpulang")

            )
            ->join(DB::raw($tempdataabsen . " as b"), function ($join) {
                $join->on('a.id', '=', 'b.id');
                $join->on('a.tgl', '=', 'b.tgl');
                $join->on('a.jam', '=', 'b.jam');
            });

        DB::table($templogabsen)->insertUsing([
            'urut',
            'id',
            'personname',
            'tgl',
            'jam',
            'jadwalkerja',
            'statusabsen',
            'terlambatmasuk',
            'terlambatpulang',
            'cepatmasuk',
            'cepatpulang',
        ], $querytemplogabsen);


        $temphadir = '##temphadir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphadir, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('personname')->nullable();
            $table->datetime('tgl')->nullable();
            $table->string('jadwalkerja', 1000)->nullable();
            $table->string('statusabsen', 1000)->nullable();
            $table->longtext('logabsensi')->nullable();
            $table->longtext('terlambatmasuk')->nullable();
            $table->longtext('terlambatpulang')->nullable();
            $table->longtext('cepatmasuk')->nullable();
            $table->longtext('cepatpulang')->nullable();
        });

        $querytemphadir = DB::table($templogabsen)->from(
            DB::raw($templogabsen . " a ")
        )
            ->select(
                'a.id',
                db::raw("max(a.personname) as personname"),
                'a.tgl',
                db::raw("max(a.jadwalkerja) as jadwalkerja"),
                db::raw("max(a.statusabsen) as statusabsen"),
                db::raw("isnull(STRING_AGG(cast(left(a.jam,8) as nvarchar(max)), ', '),'') as logabsensi"),
                db::raw("max(a.terlambatmasuk) as terlambatmasuk"),
                db::raw("max(a.terlambatpulang) as terlambatpulang"),
                db::raw("max(a.cepatmasuk) as cepatmasuk"),
                db::raw("max(a.cepatpulang) as cepatpulang")

            )
            ->groupby('a.id')
            ->groupby('a.tgl');

        DB::table($temphadir)->insertUsing([
            'id',
            'personname',
            'tgl',
            'jadwalkerja',
            'statusabsen',
            'logabsensi',
            'terlambatmasuk',
            'terlambatpulang',
            'cepatmasuk',
            'cepatpulang',
        ], $querytemphadir);

        $temptanggal = '##temptanggal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptanggal, function ($table) {
            $table->datetime('tgl')->nullable();
        });

        while ($ptgl1 <= $ptgl2) {
            DB::table($temptanggal)->insert([
                'tgl' => $ptgl1,
            ]);
            $ptgl1 = date("Y-m-d", strtotime("+1 day", strtotime($ptgl1)));
        }

        $tempdatakaryawan = '##tempdatakaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatakaryawan, function ($table) {
            $table->integer('id')->nullable();
            $table->longtext('namakaryawan')->nullable();
            $table->datetime('tgl')->nullable();
        });

        $querytempdatakaryawan = DB::table($tempkaryawan)->from(
            DB::raw($tempkaryawan . " a ")
        )
            ->select(
                'a.id',
                'a.namakaryawan',
                'b.tgl'

            )
            ->crossjoin(db::raw($temptanggal . " b"));

        DB::table($tempdatakaryawan)->insertUsing([
            'id',
            'namakaryawan',
            'tgl',
        ], $querytempdatakaryawan);


        $temphadirrekap = '##temphadirrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphadirrekap, function ($table) {
            $table->id();            
            $table->longtext('karyawan')->nullable();
            $table->datetime('tanggal')->nullable();
            $table->string('jadwalkerja', 1000)->nullable();
            $table->string('statusabsen', 1000)->nullable();
            $table->string('jamkerja', 1000)->nullable();
            $table->longtext('terlambatmasuk')->nullable();
            $table->longtext('terlambatpulang')->nullable();
            $table->longtext('cepatmasuk')->nullable();
            $table->longtext('cepatpulang')->nullable();
            $table->longtext('logwaktu')->nullable();
        });

        $querytemphadirrekap = DB::table($tempdatakaryawan)->from(
            DB::raw($tempdatakaryawan . " a ")
        )
            ->select(
                'a.namakaryawan',
                'a.tgl',
                db::raw("(case when isnull(c.hari,0)=0  then 'LIBUR' else trim(cast(left(c.jammasuk,5) as varchar(100)))+' : '+trim(cast(left(c.jampulang,5) as varchar(100))) end) as jadwalkerja"),
                db::raw("(case when  isnull(c.hari,0)=0  then 'LIBUR'
           when  d.tgl<>null  then 'LIBUR'
           else isnull(b.statusabsen,'ABSEN') end) as statusabsen"),
                db::raw("(case when isnull(c.hari,0)=0  then 'LIBUR' else trim(cast(left(c.jammasuk,5) as varchar(100)))+' : '+trim(cast(left(c.jampulang,5) as varchar(100))) end) as jamkerja"),
                db::raw("isnull(b.terlambatmasuk,'') as terlambatmasuk"),
                db::raw("isnull(b.terlambatpulang,'') as terlambatmasuk"),
                db::raw("isnull(b.cepatmasuk,'') as cepatmasuk"),
                db::raw("isnull(b.cepatpulang,'') as cepatpulang"),
                db::raw("isnull(b.logabsensi,'') as logabsensi")

            )
            ->leftjoin(DB::raw($temphadir . " as b"), function ($join) {
                $join->on('a.id', '=', 'b.id');
                $join->on('a.tgl', '=', 'b.tgl');
            })
            ->leftjoin(db::raw($tempshift . " c"), db::raw("datepart(dw,a.tgl)"), 'c.hari')
            ->leftjoin(db::raw("harilibur d with (readuncommitted)"), 'a.tgl', 'd.tgl')
            ->orderby('a.tgl','asc')
            ->orderby('a.namakaryawan','asc');

        DB::table($temphadirrekap)->insertUsing([
            'karyawan',
            'tanggal',
            'jadwalkerja',
            'statusabsen',
            'jamkerja',
            'terlambatmasuk',
            'terlambatpulang',
            'cepatmasuk',
            'cepatpulang',
            'logwaktu',
        ], $querytemphadirrekap);

        $query = DB::table($temphadirrekap)->from(
            db::raw($temphadirrekap . " a")
        )

        
            ->select(
                'a.id',
                'a.karyawan',
                'a.tanggal',
                'a.jadwalkerja',
                'a.statusabsen',
                'a.jamkerja',
                'a.cepatmasuk',
                'a.cepatpulang',        
                'a.terlambatmasuk',
                'a.terlambatpulang',
                'a.logwaktu',
            )
            ->orderby('a.id','asc');

            

        return $query;
    }

    public function getdataold($tgldari, $tglsampai)
    {

        $datacabang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'CABANG')
            ->where('subgrp', 'CABANG')
            ->first()->text ?? '';

        if ($datacabang == 'MEDAN') {
            DB::update(DB::raw("UPDATE logabsensi SET id=288 from logabsensi where id=280"));
        }




        $tempwaktu = '##tempwaktu' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempwaktu, function ($table) {
            $table->integer('id')->nullable();
            $table->date('tgl')->nullable();
            $table->string('jam', 10)->nullable();
            $table->time('waktu')->nullable();
        });


        $querywaktu = DB::table("logabsensi")->from(
            DB::raw("logabsensi a with (readuncommitted)")
        )
            ->select(
                'a.id',
                DB::raw("a.[date] as tgl"),
                DB::raw("left(a.[time],2) as jam"),
                DB::raw("min(a.[time]) as waktu"),
            )
            ->whereRaw("a.[date]>='" . $tgldari . "' and a.[date]<='" . $tglsampai . "'")
            ->groupby("a.id")
            ->groupby(db::raw("a.[date]"))
            ->groupby(db::raw("left(a.[time],2)"));


        DB::table($tempwaktu)->insertUsing([
            'id',
            'tgl',
            'jam',
            'waktu',
        ], $querywaktu);


        $tempwakturekap = '##tempwakturekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempwakturekap, function ($table) {
            $table->integer('id')->nullable();
            $table->date('tgl')->nullable();
            $table->longText('waktu')->nullable();
        });



        $querywaktu1 = DB::table($tempwaktu)->from(
            DB::raw($tempwaktu . " a ")
        )
            ->select(
                'a.id',
                'a.tgl'
            )
            ->groupBy('id')
            ->groupBy('tgl');

        $datadetail = json_decode($querywaktu1->get(), true);
        // dd('test');


        foreach ($datadetail as $item) {
            $querywaktu2 = DB::table($tempwaktu)->from(
                DB::raw($tempwaktu . " a ")
            )
                ->select(
                    DB::raw("left(cast(a.waktu as varchar(50)),8)  as waktu"),
                )
                ->where('a.id', '=', $item['id'])
                ->where('a.tgl', '=', $item['tgl'])
                ->orderby('a.waktu', 'asc');
            $datadetail2 = json_decode($querywaktu2->get(), true);
            $hit = 0;
            $waktu = '';
            foreach ($datadetail2 as $item2) {
                $hit = $hit + 1;
                if ($hit == 1) {
                    $waktu = $waktu . $item2['waktu'];
                } else {
                    $waktu = $waktu . ', ' . $item2['waktu'];
                }
            }
            $hit = 0;
            DB::table($tempwakturekap)->insert([
                'id' => $item['id'],
                'tgl' => $item['tgl'],
                'waktu ' => $waktu,
            ]);
        }



        $tempshift = '##tempshift' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempshift, function ($table) {
            $table->integer('hari')->nullable();
            $table->time('jammasukmulai')->nullable();
            $table->time('jammasuk')->nullable();
            $table->time('jampulang')->nullable();
            $table->time('batasjammasuk')->nullable();
        });

        $tempshiftkaryawan = '##tempshiftkaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempshiftkaryawan, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->integer('hari')->nullable();
            $table->time('jammasukmulai')->nullable();
            $table->time('jammasuk')->nullable();
            $table->time('jampulang')->nullable();
            $table->time('batasjammasuk')->nullable();
        });




        DB::table($tempshift)->insert([
            'hari' => 2,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 3,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 4,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 5,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 6,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '17:00',
            'batasjammasuk' => '12:45',
        ]);
        DB::table($tempshift)->insert([
            'hari' => 7,
            'jammasukmulai' => '08:30:59',
            'jammasuk' => '08:30',
            'jampulang' => '12:00',
            'batasjammasuk' => '11:30',
        ]);


        $tempkaryawan = '##tempkaryawan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkaryawan, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->longText('karyawan')->nullable();
        });

        $querykaryawan = DB::table("logabsensi")->from(
            DB::raw("logabsensi a with (readuncommitted)")
        )
            ->select(
                'a.id as idabsen',
                DB::raw("max(a.[personname]) as karyawan")
            )
            ->groupBy('a.id');

        DB::table($tempkaryawan)->insertUsing([
            'idabsen',
            'karyawan',
        ], $querykaryawan);

        $idawal = DB::table($tempkaryawan)->from(
            db::raw($tempkaryawan . " a")
        )
            ->select(
                'idabsen',
            )
            ->orderBy('idabsen', 'asc')
            ->first();

        $idakhir = DB::table($tempkaryawan)->from(
            db::raw($tempkaryawan . " a")
        )
            ->select(
                'idabsen',
            )
            ->orderBy('idabsen', 'desc')
            ->first();

        $pid = $idawal->idabsen;
        $pidakhir = $idakhir->idabsen;

        $tempdatahadir = '##tempdatahadir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatahadir, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->string('karyawan', 1000)->nullable();
            $table->date('tgl')->nullable();
        });


        $atgl1 = $tgldari;
        $atgl2 = $tglsampai;

        while ($atgl1 <= $atgl2) {
            $query2 = DB::table($tempkaryawan)->from(
                db::raw($tempkaryawan . " a")
            )
                ->select(
                    'a.idabsen',
                    'a.karyawan',
                    DB::raw("'" . $atgl1 . "' as tgl")
                );

            DB::table($tempdatahadir)->insertUsing([
                'idabsen',
                'karyawan',
                'tgl',
            ], $query2);

            $atgl1 = date("Y-m-d", strtotime("+1 day", strtotime($atgl1)));
        }


        $templogabsensipusatmasuk = '##templogabsensipusatmasuk' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templogabsensipusatmasuk, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->string('personname', 500)->nullable();
            $table->dateTime('tgljam')->nullable();
            $table->date('tgl')->nullable();
            $table->time('jam')->nullable();
        });

        $querylogabsensipusatmasuk = DB::table('logabsensi')->from(
            db::raw("logabsensi a with (readuncommitted)")
        )
            ->select(
                'a.id',
                db::raw("upper(a.personname)as personname"),
                db::raw("min(a.[datetime]) as tgljam"),
                db::raw("(a.[date]) as tgl"),
                db::raw("min(a.[time]) as jam"),
            )
            ->join(DB::raw($tempshift . " b"), DB::raw("datepart(dw,a.[datetime])"), 'b.hari')
            ->whereRaw("(a.[date]>='" . $tgldari . "' and a.[date]<='" . $tglsampai . "')")
            ->whereRaw("(a.id>=" . $pid . " and a.id<=" . $pidakhir . ")")
            ->groupBy('a.id')
            ->groupBy('a.personname')
            ->groupBy(db::raw("a.[date]"))
            ->orderBy(db::raw("cast(A.id as integer)"), 'asc')
            ->orderBy(db::raw("a.[date]"), 'asc');

        DB::table($templogabsensipusatmasuk)->insertUsing([
            'idabsen',
            'personname',
            'tgljam',
            'tgl',
            'jam',
        ], $querylogabsensipusatmasuk);

        $templogabsensipusatpulang = '##templogabsensipusatpulang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templogabsensipusatpulang, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->string('personname', 500)->nullable();
            $table->dateTime('tgljam')->nullable();
            $table->date('tgl')->nullable();
            $table->time('jam')->nullable();
        });


        $querylogabsensipusatkeluar = DB::table('logabsensi')->from(
            db::raw("logabsensi a with (readuncommitted)")
        )
            ->select(
                'a.id',
                db::raw("upper(a.personname)as personname"),
                db::raw("max(a.[datetime]) as tgljam"),
                db::raw("(a.[date]) as tgl"),
                db::raw("max(a.[time]) as jam"),
            )
            ->join(DB::raw($tempshift . " b"), DB::raw("datepart(dw,a.[datetime])"), 'b.hari')
            ->whereRaw("(a.[date]>='" . $tgldari . "' and a.[date]<='" . $tglsampai . "')")
            ->whereRaw("(a.[date]>=b.batasjammasuk)")
            ->whereRaw("(a.id>=" . $pid . " and a.id<=" . $pidakhir . ")")
            ->groupBy('a.id')
            ->groupBy('a.personname')
            ->groupBy(db::raw("a.[date]"))
            ->orderBy(db::raw("cast(A.id as integer)"), 'asc')
            ->orderBy(db::raw("a.[date]"), 'asc');

        // dd($querylogabsensipusatkeluar->get());
        DB::table($templogabsensipusatpulang)->insertUsing([
            'idabsen',
            'personname',
            'tgljam',
            'tgl',
            'jam',
        ], $querylogabsensipusatkeluar);

        $temptgl = '##temptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptgl, function ($table) {
            $table->date('tgl')->nullable();
            $table->integer('idabsen')->nullable();
        });


        $querydatatempkaryawan = DB::table($tempkaryawan)->from(
            db::raw($tempkaryawan . " a ")
        )
            ->select(
                'a.idabsen'
            )
            ->whereRaw("(a.idabsen>=" . $pid . " and a.idabsen<=" . $pidakhir . ")")
            ->groupby('a.idabsen')
            ->orderBy('a.idabsen', 'asc');

        $datadetail = json_decode($querydatatempkaryawan->get(), true);

        foreach ($datadetail as $item) {

            $atgl1 = $tgldari;
            $atgl2 = $tglsampai;

            while ($atgl1 <= $atgl2) {

                DB::table($temptgl)->insert([
                    'tgl' => date('Y-m-d', strtotime($atgl1)),
                    'idabsen' => $item['idabsen'],
                ]);

                $atgl1 = date("Y-m-d", strtotime("+1 day", strtotime($atgl1)));
            }
            $queryshiftkaryawan = DB::table($tempshift)->from(
                DB::raw($tempshift . " a")
            )
                ->select(
                    db::raw($item['idabsen'] . " as idabsen"),
                    'a.hari',
                    'a.jammasukmulai',
                    'a.jammasuk',
                    'a.jampulang',
                    'a.batasjammasuk',

                );

            DB::table($tempshiftkaryawan)->insertUsing([
                'idabsen',
                'hari',
                'jammasukmulai',
                'jammasuk',
                'jampulang',
                'batasjammasuk',

            ], $queryshiftkaryawan);
        }





        $tempcuti = '##tempcuti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcuti, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->dateTime('tglcuti')->nullable();
        });


        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->date('tgl')->nullable();
            $table->integer('idabsen')->nullable();
            $table->string('karyawan', 255)->nullable();
            $table->time('jamshiftmasuk')->nullable();
            $table->time('jamshiftpulang')->nullable();
            $table->time('jammasuk')->nullable();
            $table->time('jampulang')->nullable();
            $table->double('selisihmasuk')->nullable();
            $table->double('selisihpulang')->nullable();
        });


        $querytemprekap = DB::table($tempdatahadir)->from(
            db::raw($tempdatahadir . " a")
        )
            ->select(
                db::raw("a.tgl"),
                db::raw("a.idabsen"),
                db::raw("a.karyawan"),
                db::raw("left(e.jammasuk,5) as jammasuk"),
                db::raw("left(e.jampulang,5) as jammpulang"),
                db::raw("c.jam"),
                db::raw("isnull(d.jam,'00:00') as jampulang"),
                db::raw("(cast(left(cast(isnull(c.jam,'00:00:00') as varchar(8)),2) as integer)*3600+
        cast(substring(cast(isnull(c.jam,'00:00:00') as varchar(8)),4,2) as integer)*60+
        cast(substring(cast(isnull(c.jam,'00:00:00') as varchar(8)),7,2) as integer))-
        (cast(left(cast(isnull(e.jammasukmulai,'00:00:00') as varchar(8)),2) as integer)*3600+
        cast(substring(cast(isnull(e.jammasukmulai,'00:00:00') as varchar(8)),4,2) as integer)*60+
        cast(substring(cast(isnull(e.jammasukmulai,'00:00:00') as varchar(8)),7,2) as integer))
        as selisihmasuk"),
                db::raw("(cast(left(cast(isnull(d.jam,'00:00:00') as varchar(8)),2) as integer)*3600+
        cast(substring(cast(isnull(d.jam,'00:00:00') as varchar(8)),4,2) as integer)*60+
        cast(substring(cast(isnull(d.jam,'00:00:00') as varchar(8)),7,2) as integer))-
        (cast(left(cast(isnull(e.jampulang,'00:00:00') as varchar(8)),2) as integer)*3600+
        cast(substring(cast(isnull(e.jampulang,'00:00:00') as varchar(8)),4,2) as integer)*60+
        cast(substring(cast(isnull(e.jampulang,'00:00:00') as varchar(8)),7,2) as integer))
        as selisihpulang"),
            )
            ->join(db::raw($tempkaryawan . " b "), 'a.idabsen', 'b.idabsen')
            ->leftjoin(DB::raw($templogabsensipusatmasuk . " as c"), function ($join) {
                $join->on('a.idabsen', '=', 'c.idabsen');
                $join->on('a.tgl', '=', 'c.tgl');
            })
            ->leftjoin(DB::raw($templogabsensipusatpulang . " as d"), function ($join) {
                $join->on('a.idabsen', '=', 'd.idabsen');
                $join->on('a.tgl', '=', 'd.tgl');
            })
            ->join(DB::raw($tempshiftkaryawan . " as e"), function ($join) {
                $join->on('a.idabsen', '=', 'e.idabsen');
                $join->on(db::raw("datepart(dw,a.[tgl])"), '=', 'e.hari');
            })
            ->whereRaw("(c.idabsen>=" . $pid . " and c.idabsen<=" . $pidakhir . ")");

        // dd($querytemprekap->get());
        DB::table($temprekap)->insertUsing([
            'tgl',
            'idabsen',
            'karyawan',
            'jamshiftmasuk',
            'jamshiftpulang',
            'jammasuk',
            'jampulang',
            'selisihmasuk',
            'selisihpulang',

        ], $querytemprekap);


        // dd(db::table($temptgl)->get());

        $querytemphasil = DB::table($temptgl)->from(
            db::raw($temptgl . " a")
        )
            ->select(
                'a.idabsen',
                'h.karyawan',
                DB::raw("a.tgl as tanggal"),
                db::raw("(case when datepart(dw,A.tgl)=1 then 'Libur' 
              when year(isnull(e.tgl,'1900/1/1'))<>1900 then 'Libur' 
                 else left(cast(d.jammasuk  as varchar(12)),5)+ ' - ' + left(cast(d.jAMpuLANG as varchar(12)),5) end) as jadwalkerja"),
                db::raw("(case when datepart(dw,A.tgl)=1 then 'Libur' 
              when year(isnull(E.tgl,'1900/1/1'))<>1900 then 'Libur' 
               when year(isnull(F.tglcuti,'1900/1/1'))<>1900 then 'Cuti'
               when year(isnull(b.tgl,'1900/1/1'))<>1900 or year(isnull(C.tgl,'1900/1/1'))<>1900 then 'Hadir'
     
                 else 'Absen' end) as statusabsen"),
                db::raw("(case when (case when datepart(dw,A.tgl)=1 then 'Libur' 
                    when year(isnull(E.tgl,'1900/1/1'))<>1900 then 'Libur' 
                    when year(isnull(F.tglcuti,'1900/1/1'))<>1900 then 'Cuti'
                    when year(isnull(b.tgl,'1900/1/1'))<>1900 or year(isnull(C.tgl,'1900/1/1'))<>1900 then 'Hadir'

                        else 'Absen' end) in('Hadir') then 

        (case when left(cast(isnull(B.jam,'00:00')  as varchar(12)),5)='00:00' then 'Tidak Absen Masuk' else left(cast(isnull(B.jam,'00:00')  as varchar(12)),5) end)+ ' - ' + 
        (case when left(cast(isnull(C.jAM,'00:00') as varchar(12)),5) ='00:00' then 'Tidak Absen Pulang' else left(cast(isnull(C.jAM,'00:00') as varchar(12)),5) end)
        else  
        (case when datepart(dw,A.tgl)=1 then 'Libur' 
                    when year(isnull(E.tgl,'1900/1/1'))<>1900 then 'Libur' 
                    when year(isnull(F.tglcuti,'1900/1/1'))<>1900 then 'Cuti'
                    when year(isnull(b.tgl,'1900/1/1'))<>1900 or year(isnull(C.tgl,'1900/1/1'))<>1900 then 'Hadir'

                        else 'Absen' end)
        end)
        as jamkerja"),
                db::raw("(case when left(cast(isnull(b.jAM,'00:00') as varchar(12)),5) ='00:00' then '-' else 
        (case when g.selisihmasuk<0 then CONVERT(char(8), DATEADD(second, abs(g.selisihmasuk), ''), 108) else '-' end) end) as cepatmasuk"),
                db::raw("(case when left(cast(isnull(C.jAM,'00:00') as varchar(12)),5) ='00:00' then '-' else 
        (case when g.selisihpulang<0 then CONVERT(char(8), DATEADD(second, abs(g.selisihpulang), ''), 108) else '-' end)  end) as cepatpulang"),
                db::raw("(case when left(cast(isnull(b.jAM,'00:00') as varchar(12)),5) ='00:00' then '-' else 
        (case when g.selisihmasuk>0 then CONVERT(char(8), DATEADD(second, abs(g.selisihmasuk), ''), 108) else '-' end) end) as terlambatmasuk"),
                db::raw("(case when left(cast(isnull(C.jAM,'00:00') as varchar(12)),5) ='00:00' then '-' else 
        (case when g.selisihpulang>0 then CONVERT(char(8), DATEADD(second, abs(g.selisihpulang), ''), 108) else '-' end)  end) as terlambatpulang"),
                db::raw("isnull(i.waktu,'') as logwaktu ")
            )
            ->leftjoin(DB::raw($templogabsensipusatmasuk . " as b"), function ($join) {
                $join->on('a.idabsen', '=', 'b.idabsen');
                $join->on('a.tgl', '=', 'b.tgl');
            })
            ->leftjoin(DB::raw($templogabsensipusatpulang . " as c"), function ($join) {
                $join->on('a.idabsen', '=', 'c.idabsen');
                $join->on('a.tgl', '=', 'c.tgl');
            })
            ->leftjoin(DB::raw($tempshiftkaryawan . " as d"), function ($join) {
                $join->on('a.idabsen', '=', 'd.idabsen');
                $join->on(db::raw("datepart(dw,a.[tgl])"), '=', 'd.hari');
            })
            ->leftjoin(db::raw("harilibur e with (readuncommitted)"), 'a.tgl', 'e.tgl')
            ->leftjoin(DB::raw($tempcuti . " as f"), function ($join) {
                $join->on('a.idabsen', '=', 'f.idabsen');
                $join->on('a.tgl', '=', 'f.tglcuti');
            })
            ->leftjoin(DB::raw($temprekap . " as g"), function ($join) {
                $join->on('a.idabsen', '=', 'g.idabsen');
                $join->on('a.tgl', '=', 'g.tgl');
            })
            ->leftjoin(DB::raw($tempwakturekap . " as i"), function ($join) {
                $join->on('a.idabsen', '=', 'i.id');
                $join->on('a.tgl', '=', 'i.tgl');
            })
            ->join(db::raw($tempkaryawan . " h "), 'a.idabsen', 'h.idabsen')
            // ->where('a.idabsen',288)
            ->orderBy('h.karyawan', 'asc')
            ->orderBy('a.tgl', 'asc');

        // dd(db::table($tempwakturekap)->where('id',288)->get());
        // dd($querytemphasil->get());


        $tempwaktu = '##tempwaktu' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempwaktu, function ($table) {
            $table->integer('idabsen')->nullable();
            $table->date('tgl')->nullable();
            $table->string('logwaktu', 1000)->nullable();
        });
        $querywaktu = DB::table($temptgl)->from(
            db::raw($temptgl . " a")
        )
            ->select(
                'a.idabsen',
                DB::raw("a.tgl"),
                db::raw("isnull(i.waktu,'') as logwaktu ")
            )

            ->leftjoin(DB::raw($tempwakturekap . " as i"), function ($join) {
                $join->on('a.idabsen', '=', 'i.id');
                $join->on('a.tgl', '=', 'i.tgl');
            })
            ->join(db::raw($tempkaryawan . " h "), 'a.idabsen', 'h.idabsen')
            // ->where('a.idabsen',288)
            ->orderBy('a.idabsen', 'asc')
            ->orderBy('a.tgl', 'asc');

        DB::table($tempwaktu)->insertUsing([
            'idabsen',
            'tgl',
            'logwaktu',

        ], $querywaktu);




        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function (Blueprint $table) {
            $table->integer('idabsen')->nullable();
            $table->string('karyawan', 255)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('jadwalkerja', 1000)->nullable();
            $table->string('statusabsen', 1000)->nullable();
            $table->string('jamkerja', 1000)->nullable();
            $table->string('cepatmasuk', 100)->nullable();
            $table->string('cepatpulang', 100)->nullable();
            $table->string('terlambatmasuk', 100)->nullable();
            $table->string('terlambatpulang', 100)->nullable();
            $table->longText('logwaktu', 100)->nullable();
        });

        DB::table($temphasil)->insertUsing([
            'idabsen',
            'karyawan',
            'tanggal',
            'jadwalkerja',
            'statusabsen',
            'jamkerja',
            'cepatmasuk',
            'cepatpulang',
            'terlambatmasuk',
            'terlambatpulang',
            'logwaktu',

        ], $querytemphasil);

        // ->leftjoin(DB::raw("karyawanlogabsensi as j"), function ($join) {
        //     $join->on('a.idabsen', '=', 'j.idabsen');
        //     $join->on(DB::raw("(a.tgl<=(case when year(isnull(j.tglresign,'1900/1/1'))=1900 then '1900/1/1' else j.tglresign end) or j.statusaktif=2)"));
        //     // 'a.tgl', '>=', db::raw("(case when year(isnull(j.tglresign,'1900/1/1'))=1900 then '1900/1/1' else j.tglresign end)")

        // })
        // ->whereraw("isnull(j.idabsen,0)=0")

        DB::table($temphasil, 'a')
            ->Join(db::raw("karyawanlogabsensi b with (readuncommitted)"), 'a.idabsen', '=', 'b.idabsen')
            ->whereRaw("a.tanggal<=isnull(b.tglresign,'1900/1/1')")
            ->OrwhereRaw("isnull(b.statusaktif,2)=2")
            ->delete();


        $query = DB::table($temphasil)->from(
            db::raw($temphasil . " a")
        )
            ->select(
                'a.karyawan',
                'a.tanggal',
                db::raw("max(a.jadwalkerja) as jadwalkerja"),
                db::raw("max(a.statusabsen) as statusabsen"),
                db::raw("max(a.jamkerja) as jamkerja"),
                db::raw("max(a.cepatmasuk) as cepatmasuk"),
                db::raw("max(a.cepatpulang) as cepatpulang"),
                db::raw("max(a.terlambatmasuk) as terlambatmasuk"),
                db::raw("max(a.terlambatpulang) as terlambatpulang"),
                db::raw("min(i.logwaktu) as logwaktu"),
            )
            ->leftjoin(DB::raw($tempwaktu . " as i"), function ($join) {
                $join->on('a.idabsen', '=', 'i.idabsen');
                $join->on('a.tanggal', '=', 'i.tgl');
            })
            // ->whereraw("(a.tanggal>=)")
            // $tgldari, $tglsampai
            // ->where('a.idabsen', 288)
            ->groupby('a.karyawan')
            ->groupby('a.tanggal');




        // dd($query->get());
        // dd(db::table($temphasil)->where('idabsen',288)->get());
        // dd(db::table($tempwakturekap)->where('id',288)->get());
        // dd('test');
        // dd(db::table($tempshiftkaryawan)->get());
        // dd(db::table($temptgl)->get());

        // dd($query->get());
        return $query;
    }
    public function get($tgldari, $tglsampai)
    {
        $this->setRequestParameters();
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'LogAbsensiController';

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->integer('id')->nullable();
                $table->string('karyawan', 255)->nullable();
                $table->date('tanggal')->nullable();
                $table->string('jadwalkerja', 1000)->nullable();
                $table->string('statusabsen', 1000)->nullable();
                $table->string('jamkerja', 1000)->nullable();
                $table->string('cepatmasuk', 100)->nullable();
                $table->string('cepatpulang', 100)->nullable();
                $table->string('terlambatmasuk', 100)->nullable();
                $table->string('terlambatpulang', 100)->nullable();
                $table->longText('logwaktu', 100)->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'id',
                'karyawan',
                'tanggal',
                'jadwalkerja',
                'statusabsen',
                'jamkerja',
                'cepatmasuk',
                'cepatpulang',
                'terlambatmasuk',
                'terlambatpulang',
                'logwaktu',
            ], $this->getdata($tgldari, $tglsampai));
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        // $temprekapdata = '##temprekapdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temprekapdata, function ($table) {
        //     $table->id();
        //     $table->string('karyawan', 255)->nullable();
        //     $table->date('tanggal')->nullable();
        //     $table->string('jadwalkerja', 1000)->nullable();
        //     $table->string('statusabsen', 1000)->nullable();
        //     $table->string('jamkerja', 1000)->nullable();
        //     $table->string('cepatmasuk', 100)->nullable();
        //     $table->string('cepatpulang', 100)->nullable();
        //     $table->string('terlambatmasuk', 100)->nullable();
        //     $table->string('terlambatpulang', 100)->nullable();
        //     $table->longText('logwaktu', 100)->nullable();
        // });
        // // dd($this->getdata($tgldari, $tglsampai)->get());
        // DB::table($temprekapdata)->insertUsing([
        //     'karyawan',
        //     'tanggal',
        //     'jadwalkerja',
        //     'statusabsen',
        //     'jamkerja',
        //     'cepatmasuk',
        //     'cepatpulang',
        //     'terlambatmasuk',
        //     'terlambatpulang',
        //     'logwaktu',
        // ], $this->getdata($tgldari, $tglsampai));



        // dd(db::table($temprekapdata)->get());
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $query = db::table($temtabel)->from(
            db::raw($temtabel . " a")
        )
            ->select(
                'a.karyawan',
                'a.tanggal',
                'a.jadwalkerja',
                'a.statusabsen',
                'a.jamkerja',
                'a.cepatmasuk',
                'a.cepatpulang',
                'a.terlambatmasuk',
                'a.terlambatpulang',
                'a.logwaktu',
                DB::raw("'Laporan Log Absensi' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            );
        // dd('test');
        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
            // dd($query->tosql());
        $this->filter($query);

        $this->paginate($query);
        $data = $query->get();
        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        if ($filters['field'] == 'tanggal') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            if ($filters['field'] == 'tanggal') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
