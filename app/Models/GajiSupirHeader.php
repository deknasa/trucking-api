<?php

namespace App\Models;

use App\Services\RunningNumberService;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function cekvalidasiaksi($nobukti)
    {
        $rekap = DB::table('prosesgajisupirdetail')
            ->from(
                DB::raw("prosesgajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.gajisupir_nobukti'
            )
            ->where('a.gajisupir_nobukti', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'PROSES GAJI SUPIR ' . $rekap->nobukti,
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }

        $rekap = DB::table('pendapatansupirdetail')
            ->from(
                DB::raw("pendapatansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.nobuktirincian'
            )
            ->where('a.nobuktirincian', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'PENDAPATAN SUPIR ' . $rekap->nobukti,
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }
    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'GajiSupirHeaderController';
        $cekHitungKenek = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'GAJI SUPIR')->where('subgrp', 'HITUNG KENEK')->first();
        $cekHitungKenek = $cekHitungKenek->text;

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
                $table->string('nobukti', 1000)->nullable();
                $table->dateTime('tglbukti')->nullable();
                $table->longText('supir_id')->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->date('tgldari')->nullable();
                $table->date('tglsampai')->nullable();
                $table->double('komisisupir', 15, 2)->nullable();
                $table->double('gajikenek', 15, 2)->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
                $table->double('total', 15, 2)->nullable();
                $table->double('uangjalan', 15, 2)->nullable();
                $table->double('bbm', 15, 2)->nullable();
                $table->double('deposito', 15, 2)->nullable();
                $table->double('potonganpinjaman', 15, 2)->nullable();
                $table->double('potonganpinjamansemua', 15, 2)->nullable();
                $table->double('uangmakanberjenjang', 15, 2)->nullable();
                $table->double('uangmakanharian', 15, 2)->nullable();
                $table->longText('statuscetak')->nullable();
                $table->longText('statuscetak_text')->nullable();
                $table->string('userbukacetak', 1000)->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->date('tglbukacetak')->nullable();
                $table->string('modifiedby', 1000)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->double('nominal', 15, 2)->nullable();
                $table->double('sisa', 15, 2)->nullable();
            });

            $tempgajidetail = '##tempgajidetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgajidetail, function ($table) {
                $table->string('nobukti', 1000)->nullable();
                $table->double('komisisupir', 15, 2)->nullable();
                $table->double('gajikenek', 15, 2)->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
            });

            $querytempdetail = DB::table("gajisupirheader")->from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select(
                    'gajisupirheader.nobukti',
                    db::raw("sum(gajisupirdetail.komisisupir) as komisisupir"),
                    db::raw("sum(gajisupirdetail.gajikenek) as gajikenek"),
                    db::raw("sum(gajisupirdetail.gajisupir) as gajisupir"),
                    db::raw("sum(gajisupirdetail.biayatambahan) as biayaextra"),
                )
                ->join(DB::raw("gajisupirdetail with (readuncommitted)"), 'gajisupirheader.nobukti', 'gajisupirdetail.nobukti');

            if (request()->tgldari && request()->tglsampai) {
                $querytempdetail->whereBetween('gajisupirheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $querytempdetail->whereRaw("MONTH(gajisupirheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(gajisupirheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $querytempdetail->where("gajisupirheader.statuscetak", $statusCetak);
            }
            $querytempdetail->groupBy('gajisupirheader.nobukti');

            DB::table($tempgajidetail)->insertUsing([
                'nobukti',
                'komisisupir',
                'gajikenek',
                'gajisupir',
                'biayaextra',
            ], $querytempdetail);

            $querytemp = DB::table($this->table)->from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select(
                    'gajisupirheader.id',
                    'gajisupirheader.nobukti',
                    'gajisupirheader.tglbukti',
                    'supir.namasupir as supir_id',
                    // 'gajisupirheader.keterangan',
                    DB::raw("(case when (select text from parameter where grp='GAJI SUPIR' and subgrp='HITUNG KENEK')= 'YA' then c.gajisupir else (gajisupirheader.nominal-isnull(C.biayaextra,0)) end) as gajisupir"),
                    // db::raw("(gajisupirheader.nominal-isnull(C.biayaextra,0)) as nominal"),
                    'gajisupirheader.tgldari',
                    'gajisupirheader.tglsampai',
                    db::raw("isnull(C.komisisupir,0) as komisisupir"),
                    db::raw("isnull(C.gajikenek,0) as gajikenek"),
                    db::raw("isnull(C.biayaextra,0) as biayaextra"),
                    // db::raw("(gajisupirheader.total) as total"),
                    DB::raw("(case when (select text from parameter where grp='GAJI SUPIR' and subgrp='HITUNG KENEK')= 'YA' then gajisupirheader.total else (gajisupirheader.total+isnull(C.komisisupir,0)+isnull(C.gajikenek,0)) end) as total"),
                    // db::raw("(gajisupirheader.total+isnull(C.komisisupir,0)+isnull(C.gajikenek,0)) as total"),
                    'gajisupirheader.uangjalan',
                    'gajisupirheader.bbm',
                    'gajisupirheader.deposito',
                    'gajisupirheader.potonganpinjaman',
                    'gajisupirheader.potonganpinjamansemua',
                    DB::raw("(case when gajisupirheader.uangmakanberjenjang IS NULL then 0 else gajisupirheader.uangmakanberjenjang end) as uangmakanberjenjang"),
                    'gajisupirheader.uangmakanharian',
                    'parameter.memo as statuscetak',
                    "parameter.text as statuscetak_text",
                    'gajisupirheader.userbukacetak',
                    'gajisupirheader.jumlahcetak',
                    DB::raw('(case when (year(gajisupirheader.tglbukacetak) <= 2000) then null else gajisupirheader.tglbukacetak end ) as tglbukacetak'),
                    'gajisupirheader.modifiedby',
                    'gajisupirheader.created_at',
                    'gajisupirheader.updated_at',
                    DB::raw("(case when (select text from parameter where grp='GAJI SUPIR' and subgrp='HITUNG KENEK')= 'YA' then gajisupirheader.nominal else (gajisupirheader.total+isnull(C.komisisupir,0)+isnull(C.gajikenek,0)) end) as nominal"),
                    DB::raw('(total + uangmakanharian + isnull(uangmakanberjenjang,0) - uangjalan - potonganpinjaman - potonganpinjamansemua - deposito - bbm) as sisa')
                )

                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gajisupirheader.statuscetak', 'parameter.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
                ->leftJoin(DB::raw($tempgajidetail . " c"), 'gajisupirheader.nobukti', 'c.nobukti');

            if (request()->tgldari && request()->tglsampai) {
                $querytemp->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $querytemp->whereRaw("MONTH(gajisupirheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(gajisupirheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $querytemp->where("gajisupirheader.statuscetak", $statusCetak);
            }

            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'supir_id',
                'gajisupir',
                'tgldari',
                'tglsampai',
                'komisisupir',
                'gajikenek',
                'biayaextra',
                'total',
                'uangjalan',
                'bbm',
                'deposito',
                'potonganpinjaman',
                'potonganpinjamansemua',
                'uangmakanberjenjang',
                'uangmakanharian',
                'statuscetak',
                'statuscetak_text',
                'userbukacetak',
                'jumlahcetak',
                'tglbukacetak',
                'modifiedby',
                'created_at',
                'updated_at',
                'nominal',
                'sisa',
            ], $querytemp);
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

        // dd(db::table($temtabel)->get()) ;

        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.supir_id',
                'a.gajisupir',
                'a.tgldari',
                'a.tglsampai',
                'a.komisisupir',
                'a.gajikenek',
                'a.biayaextra',
                'a.total',
                'a.uangjalan',
                'a.bbm',
                'a.deposito',
                'a.potonganpinjaman',
                'a.potonganpinjamansemua',
                'a.uangmakanberjenjang',
                'a.uangmakanharian',
                'a.statuscetak',
                'a.statuscetak_text',
                'a.userbukacetak',
                'a.jumlahcetak',
                'a.tglbukacetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.nominal',
                'a.sisa',
            );
        // dd($query->get());



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        $tempbuktisum = '##tempbuktisum' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbuktisum, function ($table) {
            $table->string('nobukti', 100)->nullable();
        });
        $databukti = json_decode($data, true);
        foreach ($databukti as $item) {

            DB::table($tempbuktisum)->insert([
                'nobukti' => $item['nobukti'],
            ]);
        }
        $querytotal = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                db::raw("sum(a.nominal) as nominal"),
                db::raw("sum(a.komisisupir) as komisisupir"),
                db::raw("sum(a.gajikenek) as gajikenek"),
                db::raw("sum(a.biayaextra) as biayaextra"),
                db::raw("sum(a.total) as total"),
                db::raw("sum(a.uangjalan) as uangjalan"),
                db::raw("sum(a.bbm) as bbm"),
                db::raw("sum(a.deposito) as deposito"),
                db::raw("sum(a.potonganpinjaman) as potonganpinjaman"),
                db::raw("sum(a.potonganpinjamansemua) as potonganpinjamansemua"),
                db::raw("sum(a.uangmakanberjenjang) as uangmakanberjenjang"),
                db::raw("sum(a.uangmakanharian) as uangmakanharian"),
                db::raw("sum(a.sisa) as sisa"),
                db::raw("sum(a.gajisupir) as gajisupir"),
            )
            ->join(db::raw($tempbuktisum . " b "), 'a.nobukti', 'b.nobukti')
            ->first();

        $this->totalAll = $querytotal->total ?? 0;
        $this->totalUangJalan = $querytotal->uangjalan ?? 0;
        $this->totalGajiKenek = $querytotal->gajikenek ?? 0;
        $this->totalKomisiSupir = $querytotal->komisisupir ?? 0;
        $this->totalBiayaExtra = $querytotal->biayaextra ?? 0;
        $this->totalBbm = $querytotal->bbm ?? 0;
        $this->totalDeposito = $querytotal->deposito ?? 0;
        $this->totalPotPinj = $querytotal->potonganpinjaman ?? 0;
        $this->totalPotSemua = $querytotal->potonganpinjamansemua ?? 0;
        $this->totalJenjang = $querytotal->uangmakanberjenjang ?? 0;
        $this->totalMakan = $querytotal->uangmakanharian ?? 0;
        $this->totalNominal = $querytotal->nominal ?? 0;
        $this->totalGajiSupir = $querytotal->gajisupir ?? 0;

        return $data;
    }
    public function findAll($id)
    {

        $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();
        $query = DB::table('gajisupirheader')->from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.*',

                'gajisupirheader.id',
                'gajisupirheader.nobukti',
                'gajisupirheader.tglbukti',
                'gajisupirheader.supir_id',
                'supir.namasupir as supir',
                'gajisupirheader.tgldari',
                'gajisupirheader.tglsampai',
                'gajisupirheader.uangmakanberjenjang as berjenjanguangmakan',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.deposito',
                'gajisupirheader.bbm',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                DB::raw("'$parameter->text' as judul"),
                DB::raw("'Laporan Gaji Supir' as judulLaporan"),

            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('gajisupirheader.id', $id);

        $data = $query->first();

        return $data;
    }

    public function getTrip($supirId, $tglDari, $tglSampai)
    {

        $this->setRequestParameters();
        $cekPendapatan = $this->cekPendapatanSupir($supirId, $tglDari, $tglSampai);
        if ($cekPendapatan) {
            $sp = $this->createTempGetTrip($supirId, $tglDari, $tglSampai);
            $query = DB::table($sp)
                ->select(
                    DB::raw("row_number() Over(Order By $sp.nobuktitrip) as id"),
                    DB::raw("(case when $sp.nobuktitrip IS NULL then '-' else $sp.nobuktitrip end) as nobuktitrip"),
                    "$sp.tglbuktisp",
                    "$sp.trado_id",
                    "$sp.dari_id",
                    "$sp.sampai_id",
                    "$sp.nocont",
                    "$sp.nosp",
                    DB::raw("(case when $sp.ritasi_nobukti IS NULL then '-' else $sp.ritasi_nobukti end) as ritasi_nobukti"),
                    DB::raw("(case when $sp.gajisupir IS NULL then 0 else $sp.gajisupir end) as gajisupir"),
                    DB::raw("(case when $sp.gajikenek IS NULL then 0 else $sp.gajikenek end) as gajikenek"),
                    DB::raw("(case when $sp.komisisupir IS NULL then 0 else $sp.komisisupir end) as komisisupir"),
                    DB::raw("(case when $sp.tolsupir IS NULL then 0 else $sp.tolsupir end) as tolsupir"),
                    DB::raw("(case when $sp.upahritasi IS NULL then 0 else $sp.upahritasi end) as upahritasi"),
                    DB::raw("(case when $sp.biayaextra IS NULL then 0 else $sp.biayaextra end) as biayaextra"),
                    "parameter.text as statusritasi",
                    DB::raw("(case when $sp.keteranganbiaya IS NULL then '-' else $sp.keteranganbiaya end) as keteranganbiaya")
                )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', $sp . '.statusritasi');

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $query->orderBy($sp . '.nobuktitrip', 'asc');
            $query->orderBy($sp . '.tglbuktisp', 'asc');
            // $this->filterTrip($query, $sp);
            // $this->paginate($query);
            $data = $query->get();

            // dd($query->get());
            $this->totalGajiSupir = $query->sum('gajisupir');
            $this->totalGajiKenek = $query->sum('gajikenek');
            $this->totalKomisiSupir = $query->sum('komisisupir');
            $this->totalUpahRitasi = $query->sum('upahritasi');
            $this->totalBiayaExtra = $query->sum('biayaextra');
            $this->totalTolSupir = $query->sum('tolsupir');
        } else {
            $data = [];
        }
        return $data;
    }

    public function cekPendapatanSupir($supirId, $tglDari, $tglSampai)
    {
        $query = DB::table("pendapatansupirheader")->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select('supir_id', 'tgldari', 'tglsampai')
            ->where('pendapatansupirheader.supir_id', $supirId)
            ->get();

        $temppendapatan = '##temppendapatan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppendapatan, function ($table) {
            $table->date('tanggal')->nullable();
        });
        $temptanggal = '##temptanggal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptanggal, function ($table) {
            $table->date('tanggal')->nullable();
        });

        foreach ($query as $value) {
            $beginDate = new DateTime($value->tgldari);
            $endDate = new DateTime($value->tglsampai);

            while ($beginDate <= $endDate) {
                // $tanggal[] = $beginDate->format('Y-m-d');
                DB::table($temppendapatan)->insert(
                    [
                        "tanggal" => $beginDate->format('Y-m-d'),
                    ]
                );
                $beginDate->modify('+1 day');
            }
        }
        $beginTrip = new DateTime($tglDari);
        $endTrip = new DateTime($tglSampai);

        while ($beginTrip <= $endTrip) {
            // $tanggal[] = $beginTrip->format('Y-m-d');
            DB::table($temptanggal)->insert(
                [
                    "tanggal" => $beginTrip->format('Y-m-d'),
                ]
            );
            $beginTrip->modify('+1 day');
        }

        $query = DB::table($temppendapatan)->from(DB::raw("$temppendapatan with (readuncommitted)"))
            ->whereRaw("$temppendapatan.tanggal in (select tanggal from $temptanggal)")->get();
        $data = count(json_decode($query));
        $data = ($data > 0) ? false : true;
        return $data;
    }
    public function createTempBiayaTambahan($supirId, $tglDari, $tglSampai)
    {
        // $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR BIAYA TAMBAHAN')->first();

        $tempTambahan = '##tempTambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $biayaTambahan = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))
            ->select(DB::raw("suratpengantar_id, STRING_AGG(keteranganbiaya, ', ') AS keteranganbiaya, SUM(isnull(nominal, 0)) as biayaextra"))
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->where('suratpengantar.supir_id', $supirId)
            ->where('suratpengantar.tglbukti', '>=', $tglDari)
            ->where('suratpengantar.tglbukti', '<=', $tglSampai)
            ->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)")
            ->groupBy('suratpengantar_id');
        // if ($cekStatus->text == 'YA') {
        //     $biayaTambahan->where('suratpengantarbiayatambahan.statusapproval', 3);
        // }
        Schema::create($tempTambahan, function ($table) {
            $table->bigInteger('suratpengantar_id')->nullable();
            $table->string('keteranganbiaya')->nullable();
            $table->bigInteger('biayaextra')->nullable();
        });

        DB::table($tempTambahan)->insertUsing(['suratpengantar_id', 'keteranganbiaya', 'biayaextra'], $biayaTambahan);
        return $tempTambahan;
    }

    public function createTempBiayaTambahanEdit($gajiId, $supirId, $tglDari, $tglSampai)
    {
        $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR BIAYA TAMBAHAN')->first();

        $tempTambahan = '##tempTambahanedit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $biayaTambahan = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))
            ->select(DB::raw("suratpengantar_id, STRING_AGG(keteranganbiaya, ', ') AS keteranganbiaya, SUM(isnull(nominal, 0)) as biayaextra"))
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->where('suratpengantar.supir_id', $supirId)
            ->where('suratpengantar.tglbukti', '>=', $tglDari)
            ->where('suratpengantar.tglbukti', '<=', $tglSampai)
            ->whereRaw("suratpengantar.nobukti in(select suratpengantar_nobukti from gajisupirdetail where gajisupir_id=$gajiId)")
            ->groupBy('suratpengantar_id');
        if ($cekStatus->text == 'YA') {
            $biayaTambahan->where('suratpengantarbiayatambahan.statusapproval', 3);
        }
        Schema::create($tempTambahan, function ($table) {
            $table->bigInteger('suratpengantar_id')->nullable();
            $table->string('keteranganbiaya')->nullable();
            $table->bigInteger('biayaextra')->nullable();
        });

        DB::table($tempTambahan)->insertUsing(['suratpengantar_id', 'keteranganbiaya', 'biayaextra'], $biayaTambahan);
        return $tempTambahan;
    }
    public function createTempGetTrip($supirId, $tglDari, $tglSampai)
    {
        $getBiaya = $this->createTempBiayaTambahan($supirId, $tglDari, $tglSampai);
        $temp = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajisupir end) as gajisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajikenek end) as gajikenek"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.komisisupir end) as komisisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.tolsupir end) as tolsupir"),
                'ritasi.gaji as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'ritasi.statusritasi',

                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else biayatambahan.biayaextra end) as biayaextra"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then '-' else 
                (case when biayatambahan.biayaextra = 0 then '-' else biayatambahan.keteranganbiaya end)  end) as keteranganbiaya"),
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'suratpengantar.nobukti', 'ritasi.suratpengantar_nobukti')
            ->leftJoin(DB::raw("$getBiaya as biayatambahan with (readuncommitted)"), 'suratpengantar.id', 'biayatambahan.suratpengantar_id')
            ->where('suratpengantar.supir_id', $supirId)
            ->where('suratpengantar.tglbukti', '>=', $tglDari)
            ->where('suratpengantar.tglbukti', '<=', $tglSampai)
            ->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->string('nobuktitrip')->nullable();
            $table->date('tglbuktisp')->nullable();
            $table->string('trado_id');
            $table->string('dari_id');
            $table->string('sampai_id');
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->bigInteger('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiaya')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        // fetch ritasi yg tidak ada suratpengantar
        $fetch = Ritasi::from(DB::raw("ritasi with (readuncommitted)"))
            ->select(
                DB::raw("ritasi.tglbukti as tglbuktisp,trado.kodetrado as trado_id,kotaDari.keterangan as dari_id,kotaSampai.keterangan as sampai_id, ritasi.gaji as upahritasi, ritasi.nobukti as ritasi_nobukti,ritasi.statusritasi")
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('ritasi.supir_id', $supirId)
            ->where('ritasi.tglbukti', '>=', $tglDari)
            ->where('ritasi.tglbukti', '<=', $tglSampai)
            ->whereRaw("ritasi.suratpengantar_nobukti = ''")
            ->whereRaw("ritasi.nobukti not in(select ritasi_nobukti from gajisupirdetail)");
        $tes = DB::table($temp)->insertUsing(['tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'upahritasi', 'ritasi_nobukti', 'statusritasi'], $fetch);

        return $temp;
    }

    public function getEditTrip($gajiId)
    {
        $this->setRequestParameters();
        $sp = $this->createTempEdit($gajiId);
        $query = DB::table($sp)
            ->select(

                DB::raw("row_number() Over(Order By $sp.nobuktitrip) as id"),
                DB::raw("(case when $sp.nobuktitrip IS NULL then '-' else $sp.nobuktitrip end) as nobuktitrip"),
                "$sp.tglbuktisp",
                "$sp.trado_id",
                "$sp.dari_id",
                "$sp.sampai_id",
                "$sp.nocont",
                "$sp.nosp",
                DB::raw("(case when $sp.ritasi_nobukti IS NULL then '-' else $sp.ritasi_nobukti end) as ritasi_nobukti"),
                DB::raw("(case when $sp.uangmakanberjenjang IS NULL then 0 else $sp.uangmakanberjenjang end) as uangmakanberjenjang"),
                DB::raw("(case when $sp.gajisupir IS NULL then 0 else $sp.gajisupir end) as gajisupir"),
                DB::raw("(case when $sp.gajikenek IS NULL then 0 else $sp.gajikenek end) as gajikenek"),
                DB::raw("(case when $sp.komisisupir IS NULL then 0 else $sp.komisisupir end) as komisisupir"),
                DB::raw("(case when $sp.tolsupir IS NULL then 0 else $sp.tolsupir end) as tolsupir"),
                DB::raw("(case when $sp.upahritasi IS NULL then 0 else $sp.upahritasi end) as upahritasi"),
                DB::raw("(case when $sp.biayaextra IS NULL then 0 else $sp.biayaextra end) as biayaextra"),
                "parameter.text as statusritasi",
                DB::raw("(case when $sp.keteranganbiaya IS NULL then '-' else $sp.keteranganbiaya end) as keteranganbiaya")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', $sp . '.statusritasi');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy($sp . '.nobuktitrip', $this->params['sortOrder']);
        } else {
            $query->orderBy($sp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }

        $this->filterTrip($query, $sp);
        $this->paginate($query);
        $data = $query->get();

        // dd($query->get());
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajiKenek = $query->sum('gajikenek');
        $this->totalKomisiSupir = $query->sum('komisisupir');
        $this->totalUpahRitasi = $query->sum('upahritasi');
        $this->totalBiayaExtra = $query->sum('biayaextra');
        $this->totalTolSupir = $query->sum('tolsupir');
        return $data;
    }

    public function createTempEdit($gajiId)
    {

        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);


        Schema::create($temp, function ($table) {
            $table->string('nobuktitrip')->nullable();
            $table->date('tglbuktisp')->nullable()->nullable();
            $table->string('trado_id')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('uangmakanberjenjang')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->bigInteger('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiaya')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'ritasi.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('gajisupirdetail.suratpengantar_nobukti', '-')
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        return $temp;
    }
    public function selectColumns()
    {

        $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;
        Schema::create($temtabel, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->longText('supir_id')->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->double('bbm', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('potonganpinjaman', 15, 2)->nullable();
            $table->double('potonganpinjamansemua', 15, 2)->nullable();
            $table->double('uangmakanberjenjang', 15, 2)->nullable();
            $table->double('uangmakanharian', 15, 2)->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetak_text')->nullable();
            $table->string('userbukacetak', 1000)->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
        });

        $tempgajidetail = '##tempgajidetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgajidetail, function ($table) {
            $table->string('nobukti', 1000)->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
        });
        $querytempdetail = DB::table("gajisupirheader")->from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.nobukti',
                db::raw("sum(gajisupirdetail.komisisupir) as komisisupir"),
                db::raw("sum(gajisupirdetail.gajikenek) as gajikenek"),
                db::raw("sum(gajisupirdetail.gajisupir) as gajisupir"),
                db::raw("sum(gajisupirdetail.biayatambahan) as biayaextra"),
            )
            ->join(DB::raw("gajisupirdetail with (readuncommitted)"), 'gajisupirheader.nobukti', 'gajisupirdetail.nobukti');

        if (request()->tgldari && request()->tglsampai) {
            $querytempdetail->whereBetween('gajisupirheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $querytempdetail->groupBy('gajisupirheader.nobukti');

        DB::table($tempgajidetail)->insertUsing([
            'nobukti',
            'komisisupir',
            'gajikenek',
            'gajisupir',
            'biayaextra',
        ], $querytempdetail);

        $querytemp = DB::table($this->table)->from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.id',
                'gajisupirheader.nobukti',
                'gajisupirheader.tglbukti',
                'supir.namasupir as supir_id',
                // 'gajisupirheader.keterangan',
                DB::raw("(case when (select text from parameter where grp='GAJI SUPIR' and subgrp='HITUNG KENEK')= 'YA' then c.gajisupir else (gajisupirheader.nominal-isnull(C.biayaextra,0)) end) as gajisupir"),
                'gajisupirheader.tgldari',
                'gajisupirheader.tglsampai',
                db::raw("isnull(C.komisisupir,0) as komisisupir"),
                db::raw("isnull(C.gajikenek,0) as gajikenek"),
                db::raw("isnull(C.biayaextra,0) as biayaextra"),
                DB::raw("(case when (select text from parameter where grp='GAJI SUPIR' and subgrp='HITUNG KENEK')= 'YA' then gajisupirheader.total else (gajisupirheader.total+isnull(C.komisisupir,0)+isnull(C.gajikenek,0)) end) as total"),
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.deposito',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                DB::raw("(case when gajisupirheader.uangmakanberjenjang IS NULL then 0 else gajisupirheader.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirheader.uangmakanharian',
                'parameter.memo as statuscetak',
                "parameter.text as statuscetak_text",
                'gajisupirheader.userbukacetak',
                'gajisupirheader.jumlahcetak',
                DB::raw('(case when (year(gajisupirheader.tglbukacetak) <= 2000) then null else gajisupirheader.tglbukacetak end ) as tglbukacetak'),
                'gajisupirheader.modifiedby',
                'gajisupirheader.created_at',
                'gajisupirheader.updated_at',
                DB::raw("(case when (select text from parameter where grp='GAJI SUPIR' and subgrp='HITUNG KENEK')= 'YA' then gajisupirheader.nominal else (gajisupirheader.total+isnull(C.komisisupir,0)+isnull(C.gajikenek,0)) end) as nominal"),
                DB::raw('(total + uangmakanharian + isnull(uangmakanberjenjang,0) - uangjalan - potonganpinjaman - potonganpinjamansemua - deposito - bbm) as sisa')
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'gajisupirheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw($tempgajidetail . " c"), 'gajisupirheader.nobukti', 'c.nobukti');

        DB::table($temtabel)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'supir_id',
            'gajisupir',
            'tgldari',
            'tglsampai',
            'komisisupir',
            'gajikenek',
            'biayaextra',
            'total',
            'uangjalan',
            'bbm',
            'deposito',
            'potonganpinjaman',
            'potonganpinjamansemua',
            'uangmakanberjenjang',
            'uangmakanharian',
            'statuscetak',
            'statuscetak_text',
            'userbukacetak',
            'jumlahcetak',
            'tglbukacetak',
            'modifiedby',
            'created_at',
            'updated_at',
            'nominal',
            'sisa',
        ], $querytemp);

        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.supir_id',
                'a.gajisupir',
                'a.tgldari',
                'a.tglsampai',
                'a.komisisupir',
                'a.gajikenek',
                'a.biayaextra',
                'a.total',
                'a.uangjalan',
                'a.bbm',
                'a.deposito',
                'a.potonganpinjaman',
                'a.potonganpinjamansemua',
                'a.uangmakanberjenjang',
                'a.uangmakanharian',
                'a.statuscetak',
                'a.statuscetak_text',
                'a.userbukacetak',
                'a.jumlahcetak',
                'a.tglbukacetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.nominal',
                'a.sisa',
            );
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->dateTime('tglbukti')->nullable();
            $table->longText('supir_id')->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->double('bbm', 15, 2)->nullable();
            $table->double('deposito', 15, 2)->nullable();
            $table->double('potonganpinjaman', 15, 2)->nullable();
            $table->double('potonganpinjamansemua', 15, 2)->nullable();
            $table->double('uangmakanberjenjang', 15, 2)->nullable();
            $table->double('uangmakanharian', 15, 2)->nullable();
            $table->longText('statuscetak')->nullable();
            $table->longText('statuscetak_text')->nullable();
            $table->string('userbukacetak', 1000)->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
            $table->increments('position');
        });

        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'supir_id',
            'gajisupir',
            'tgldari',
            'tglsampai',
            'komisisupir',
            'gajikenek',
            'biayaextra',
            'total',
            'uangjalan',
            'bbm',
            'deposito',
            'potonganpinjaman',
            'potonganpinjamansemua',
            'uangmakanberjenjang',
            'uangmakanharian',
            'statuscetak',
            'statuscetak_text',
            'userbukacetak',
            'jumlahcetak',
            'tglbukacetak',
            'modifiedby',
            'created_at',
            'updated_at',
            'nominal',
            'sisa',
        ], $models);

        return $temp;
    }

    public function getPinjSemua()
    {
        $temp = $this->createTempPinjSemua();
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingdetail.nobukti as pinjSemua_nobukti,row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,$temp.tglbukti as pinjSemua_tglbukti,pengeluarantruckingdetail.supir_id, 'SEMUA' as pinjSemua_supir,pengeluarantruckingdetail.keterangan as pinjSemua_keterangan,$temp.sisa as pinjSemua_sisa, null as nominalPS"))
            // ->distinct('pengeluarantruckingheader.tglbukti')
            ->join(DB::raw("$temp with (readuncommitted)"), $temp . '.nobukti', 'pengeluarantruckingdetail.nobukti')
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->orderBy($temp . '.tglbukti', 'asc')
            ->orderBy($temp . '.nobukti', 'asc')
            ->where("$temp.sisa", '>', '0')
            ->whereRaw("(pengeluarantruckingdetail.supir_id = 0 OR pengeluarantruckingdetail.supir_id IS NULL)");

        return $query->get();
    }

    public function createTempPinjSemua()
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $tglBukti = date('Y-m-d', strtotime(request()->tglbukti));

        $fetch = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, pengeluarantruckingheader.tglbukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pengeluarantruckingdetail with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', 'pengeluarantruckingheader.nobukti')
            ->whereRaw("(pengeluarantruckingdetail.supir_id = 0 OR pengeluarantruckingdetail.supir_id IS NULL)")
            ->where("pengeluarantruckingheader.pengeluarantrucking_id", 1)
            ->where("pengeluarantruckingheader.tglbukti", "<=", $tglBukti)
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'sisa'], $fetch);


        return $temp;
    }


    public function getPinjPribadi($supir_id)
    {
        $tempPribadi = $this->createTempPinjPribadi($supir_id);

        $tglBukti = date('Y-m-d', strtotime(request()->tglbukti));
        $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By pengeluarantruckingheader.tglbukti asc,pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti as pinjPribadi_tglbukti,pengeluarantruckingdetail.nobukti as pinjPribadi_nobukti,pengeluarantruckingdetail.keterangan as pinjPribadi_keterangan," . $tempPribadi . ".sisa as pinjPribadi_sisa, null as nominalPP"))
            ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
            ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->where("pengeluarantruckingheader.pengeluarantrucking_id", 1)
            ->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
            ->where("pengeluarantruckingheader.tglbukti", "<=", $tglBukti)
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            })
            ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
            ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');

        return $query->get();
    }

    public function createTempPinjPribadi($supir_id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            // ->leftJoin(DB::raw("penerimaantruckingdetail with (readuncommitted)"), 'penerimaantruckingdetail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingdetail.nobukti')
            ->whereRaw("pengeluarantruckingdetail.supir_id = $supir_id")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'sisa'], $fetch);


        return $temp;
    }

    public function getUangJalan($supir_id, $dari, $sampai)
    {
        $query = AbsensiSupirHeader::from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(DB::raw("SUM(absensisupirdetail.uangjalan) as uangjalan"))
            ->leftJoin(DB::raw("absensisupirdetail with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->whereRaw("absensisupirheader.tglbukti >= '$dari'")
            ->whereRaw("absensisupirheader.tglbukti <= '$sampai'")
            ->whereRaw("absensisupirdetail.supir_id = $supir_id");

        return $query->first();
    }

    public function getAllEditTrip($gajiId, $supir_id, $dari, $sampai)
    {
        $this->setRequestParameters();
        $tempRIC = $this->createTempGetRIC($gajiId, $supir_id, $dari, $sampai);
        $query = DB::table($tempRIC)
            ->select(
                DB::raw("row_number() Over(Order By $tempRIC.nobuktitrip) as id"),
                DB::raw("(case when $tempRIC.nobuktitrip IS NULL then '-' else $tempRIC.nobuktitrip end) as nobuktitrip"),
                "$tempRIC.tglbuktisp",
                "$tempRIC.trado_id",
                "$tempRIC.dari_id",
                "$tempRIC.sampai_id",
                "$tempRIC.nocont",
                "$tempRIC.nosp",
                DB::raw("(case when $tempRIC.ritasi_nobukti IS NULL then '-' else $tempRIC.ritasi_nobukti end) as ritasi_nobukti"),
                DB::raw("(case when $tempRIC.uangmakanberjenjang IS NULL then 0 else $tempRIC.uangmakanberjenjang end) as uangmakanberjenjang"),
                DB::raw("(case when $tempRIC.gajisupir IS NULL then 0 else $tempRIC.gajisupir end) as gajisupir"),
                DB::raw("(case when $tempRIC.gajikenek IS NULL then 0 else $tempRIC.gajikenek end) as gajikenek"),
                DB::raw("(case when $tempRIC.komisisupir IS NULL then 0 else $tempRIC.komisisupir end) as komisisupir"),
                DB::raw("(case when $tempRIC.tolsupir IS NULL then 0 else $tempRIC.tolsupir end) as tolsupir"),
                DB::raw("(case when $tempRIC.upahritasi IS NULL then 0 else $tempRIC.upahritasi end) as upahritasi"),
                DB::raw("(case when $tempRIC.biayaextra IS NULL then 0 else $tempRIC.biayaextra end) as biayaextra"),
                "parameter.text as statusritasi",
                DB::raw("(case when $tempRIC.keteranganbiaya IS NULL then '-' else $tempRIC.keteranganbiaya end) as keteranganbiaya")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', $tempRIC . '.statusritasi');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($tempRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $tempRIC);
        $this->paginate($query);
        $data = $query->get();

        // dd($query->get());
        $this->totalGajiSupir = $query->sum('gajisupir');
        $this->totalGajiKenek = $query->sum('gajikenek');
        $this->totalKomisiSupir = $query->sum('komisisupir');
        $this->totalUpahRitasi = $query->sum('upahritasi');
        $this->totalBiayaExtra = $query->sum('biayaextra');
        $this->totalTolSupir = $query->sum('tolsupir');
        return $data;
    }

    public function createTempGetRIC($gajiId, $supir_id, $dari, $sampai)
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $getBiaya = $this->createTempBiayaTambahanEdit($gajiId, $supir_id, $dari, $sampai);
        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else biayatambahan.biayaextra end) as biayaextra"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then '-' else biayatambahan.keteranganbiaya end) as keteranganbiaya"),
                // 'gajisupirdetail.biayatambahan as biayaextra',
                // 'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("$getBiaya as biayatambahan with (readuncommitted)"), 'suratpengantar.id', 'biayatambahan.suratpengantar_id')
            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('suratpengantar.tglbukti', '>=', $dari)
            ->where('suratpengantar.tglbukti', '<=', $sampai)
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        Schema::create($temp, function ($table) {
            $table->string('nobuktitrip')->nullable();
            $table->date('tglbuktisp')->nullable()->nullable();
            $table->string('trado_id')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('uangmakanberjenjang')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->bigInteger('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiaya')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.suratpengantar_nobukti as nobuktitrip',
                'ritasi.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan as keteranganbiaya'
            )
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('gajisupirdetail.suratpengantar_nobukti', '-')
            ->where('ritasi.tglbukti', '>=', $dari)
            ->where('ritasi.tglbukti', '<=', $sampai)
            ->where('gajisupirdetail.gajisupir_id', $gajiId);

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $getBiaya = $this->createTempBiayaTambahan($supir_id, $dari, $sampai);
        $fetch = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',

                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajisupir end) as gajisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.gajikenek end) as gajikenek"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.komisisupir end) as komisisupir"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else suratpengantar.tolsupir end) as tolsupir"),
                'ritasi.gaji as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'ritasi.statusritasi',
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then 0 else biayatambahan.biayaextra end) as biayaextra"),
                DB::raw("(case when ritasi.suratpengantar_urutke > 1 then '-' else biayatambahan.keteranganbiaya end) as keteranganbiaya"),
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'suratpengantar.nobukti', 'ritasi.suratpengantar_nobukti')
            ->leftJoin(DB::raw("$getBiaya as biayatambahan with (readuncommitted)"), 'suratpengantar.id', 'biayatambahan.suratpengantar_id')
            ->where('suratpengantar.supir_id', $supir_id)
            ->where('suratpengantar.tglbukti', '>=', $dari)
            ->where('suratpengantar.tglbukti', '<=', $sampai)
            ->where(function ($query) {
                $query->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)")
                    ->orWhereRaw("ritasi.nobukti not in(select ritasi_nobukti from gajisupirdetail)");
            });

        $tes = DB::table($temp)->insertUsing(['nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiaya'], $fetch);

        $fetch = Ritasi::from(DB::raw("ritasi with (readuncommitted)"))
            ->select(
                DB::raw("ritasi.tglbukti as tglbuktisp,trado.kodetrado as trado_id,kotaDari.keterangan as dari_id,kotaSampai.keterangan as sampai_id, ritasi.gaji as upahritasi,ritasi.nobukti as ritasi_nobukti,ritasi.statusritasi")
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'ritasi.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'ritasi.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->where('ritasi.supir_id', $supir_id)
            ->where('ritasi.tglbukti', '>=', $dari)
            ->where('ritasi.tglbukti', '<=', $sampai)
            ->whereRaw("ritasi.suratpengantar_nobukti = ''")
            ->whereRaw("ritasi.nobukti not in(select ritasi_nobukti from gajisupirdetail)");
        $tes = DB::table($temp)->insertUsing(['tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'upahritasi', 'ritasi_nobukti', 'statusritasi'], $fetch);

        return $temp;
    }

    public function createTempGetSP($supir_id, $dari, $sampai)
    {
        $temp = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti as nobuktitrip',
                'suratpengantar.tglbukti as tglbuktisp',
                'trado.kodetrado as trado_id',
                'kotaDari.keterangan as dari_id',
                'kotaSampai.keterangan as sampai_id',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.komisisupir'
            )
            ->leftJoin(DB::raw("kota as kotaDari with (readuncommitted)"), 'suratpengantar.dari_id', 'kotaDari.id')
            ->leftJoin(DB::raw("kota as kotaSampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'kotaSampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->where('suratpengantar.supir_id', $supir_id)
            ->where('suratpengantar.tglbukti', '>=', $dari)
            ->where('suratpengantar.tglbukti', '<=', $sampai)
            ->whereRaw("suratpengantar.nobukti not in(select suratpengantar_nobukti from gajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobuktitrip');
            $table->date('tglbuktisp')->nullable();
            $table->string('trado_id');
            $table->string('dari_id');
            $table->string('sampai_id');
            $table->string('nocont');
            $table->string('nosp');
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobuktitrip', 'tglbuktisp', 'trado_id', 'dari_id', 'sampai_id', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir'], $fetch);

        return $temp;
    }

    public function getAbsensi($supir_id, $tglDari, $tglSampai)
    {
        $this->setRequestParameters();

        $temp = '##tempAbsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // LEPAS ROW NUMBER DULU, BARU INPUT CREATE TEMP, BARU KASIH ROW NUMBER
        Schema::create($temp, function ($table) {
            $table->string('absensi_nobukti');
            $table->date('absensi_tglbukti')->nullable();
            $table->float('absensi_uangjalan')->nullable();
            $table->integer('absensi_tradoid')->nullable();
            $table->string('absensi_trado')->nullable();
        });

        // $fetch = DB::table("absensisupirdetail")->from(DB::raw("absensisupirdetail with (readuncommitted)"))
        //     ->select(DB::raw("max(absensisupirheader.nobukti) as absensi_nobukti"), DB::raw("max(absensisupirheader.tglbukti) as absensi_tglbukti"), DB::raw("sum(absensisupirdetail.uangjalan) as absensi_uangjalan"))
        //     ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
        //     ->whereBetween('absensisupirheader.tglbukti', [$tglDari, $tglSampai])
        //     ->where('absensisupirdetail.supir_id', $supir_id)
        //     ->whereRaw("absensisupirheader.nobukti not in (select absensisupir_nobukti from gajisupiruangjalan where supir_id=$supir_id)")
        //     ->groupBy('absensisupirdetail.supir_id');

        $fetch = DB::table("absensisupirdetail")->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select('absensisupirheader.nobukti as absensi_nobukti', 'absensisupirheader.tglbukti as absensi_tglbukti', 'absensisupirdetail.uangjalan as absensi_uangjalan', 'absensisupirdetail.trado_id as absensi_tradoid', 'trado.kodetrado as absensi_trado')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->whereBetween('absensisupirheader.tglbukti', [$tglDari, $tglSampai])
            ->where('absensisupirdetail.supir_id', $supir_id)
            ->where('absensisupirdetail.uangjalan', '!=', 0)
            ->whereRaw("absensisupirdetail.trado_id not in (select trado_id from gajisupiruangjalan where supir_id=$supir_id and absensisupir_nobukti=absensisupirheader.nobukti)");

        DB::table($temp)->insertUsing(['absensi_nobukti', 'absensi_tglbukti', 'absensi_uangjalan', 'absensi_tradoid', 'absensi_trado'], $fetch);

        $query = DB::table($temp)->from(DB::raw("$temp as a with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By a.absensi_nobukti) as absensi_id"),
                'a.absensi_nobukti',
                'a.absensi_tglbukti',
                'a.absensi_uangjalan',
                'a.absensi_tradoid',
                'a.absensi_trado',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterAbsensi($query, 'a', 'absensisupirheader');
        $this->paginate($query);
        $data = $query->get();
        $this->totalUangJalan = $query->sum('a.absensi_uangjalan');
        return $data;
    }


    public function getEditAbsensi($id)
    {
        $this->setRequestParameters();
        $temp = '##tempAbsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // LEPAS ROW NUMBER DULU, BARU INPUT CREATE TEMP, BARU KASIH ROW NUMBER
        Schema::create($temp, function ($table) {
            $table->bigInteger('absensi_id');
            $table->bigInteger('gajisupir_id');
            $table->string('absensi_nobukti');
            $table->date('absensi_tglbukti')->nullable();
            $table->float('absensi_uangjalan')->nullable();
            $table->integer('absensi_tradoid')->nullable();
            $table->string('absensi_trado')->nullable();
        });
        $fetch = DB::table("gajisupiruangjalan")->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By gajisupiruangjalan.absensisupir_nobukti) as absensi_id"),
                'gajisupiruangjalan.gajisupir_id as gajisupir_id',
                'gajisupiruangjalan.absensisupir_nobukti as absensi_nobukti',
                'absensisupirheader.tglbukti as absensi_tglbukti',
                'gajisupiruangjalan.nominal as absensi_uangjalan',
                'gajisupiruangjalan.trado_id as absensi_tradoid',
                'trado.kodetrado as absensi_trado'
            )
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'gajisupiruangjalan.absensisupir_nobukti')
            ->join(DB::raw("trado with (readuncommitted)"), 'trado.id', 'gajisupiruangjalan.trado_id')
            ->where('gajisupiruangjalan.gajisupir_id', $id);

        DB::table($temp)->insertUsing(['absensi_id', 'gajisupir_id', 'absensi_nobukti', 'absensi_tglbukti', 'absensi_uangjalan', 'absensi_tradoid', 'absensi_trado'], $fetch);
        $query = DB::table($temp)->from(DB::raw("$temp as a"));
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy('a.absensi_id', $this->params['sortOrder']);
        } else {
            $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }

        $this->filterAbsensi($query, 'gajisupiruangjalan');
        $this->paginate($query);
        $data = $query->get();
        $this->totalUangJalan = $query->sum('a.absensi_uangjalan');
        return $data;
    }

    public function getAllEditAbsensi($id, $supir_id, $dari, $sampai)
    {
        $this->setRequestParameters();
        $temp = '##tempAbsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $getUangjalan = DB::table("gajisupiruangjalan")->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select(
                'gajisupiruangjalan.gajisupir_id as gajisupir_id',
                'gajisupiruangjalan.absensisupir_nobukti as absensi_nobukti',
                'absensisupirheader.tglbukti as absensi_tglbukti',
                'gajisupiruangjalan.nominal as absensi_uangjalan',
                'gajisupiruangjalan.trado_id as absensi_tradoid',
                'trado.kodetrado as absensi_trado'
            )
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'gajisupiruangjalan.absensisupir_nobukti')
            ->join(DB::raw("trado with (readuncommitted)"), 'trado.id', 'gajisupiruangjalan.trado_id')
            ->whereBetween('absensisupirheader.tglbukti', [$dari, $sampai])
            ->where('gajisupiruangjalan.gajisupir_id', $id);
        Schema::create($temp, function ($table) {
            $table->bigInteger('gajisupir_id')->nullable();
            $table->string('absensi_nobukti');
            $table->date('absensi_tglbukti')->nullable();
            $table->float('absensi_uangjalan')->nullable();
            $table->integer('absensi_tradoid')->nullable();
            $table->string('absensi_trado')->nullable();
        });

        DB::table($temp)->insertUsing(['gajisupir_id', 'absensi_nobukti', 'absensi_tglbukti', 'absensi_uangjalan', 'absensi_tradoid', 'absensi_trado'], $getUangjalan);

        $fetch = DB::table("absensisupirdetail")->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select('absensisupirheader.nobukti as absensi_nobukti', 'absensisupirheader.tglbukti as absensi_tglbukti', 'absensisupirdetail.uangjalan as absensi_uangjalan', 'absensisupirdetail.trado_id as absensi_tradoid', 'trado.kodetrado as absensi_trado')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->whereBetween('absensisupirheader.tglbukti', [$dari, $sampai])
            ->where('absensisupirdetail.supir_id', $supir_id)
            ->where('absensisupirdetail.uangjalan', '!=', 0)
            ->whereRaw("absensisupirdetail.trado_id not in (select trado_id from gajisupiruangjalan where supir_id=$supir_id and absensisupir_nobukti=absensisupirheader.nobukti)");

        DB::table($temp)->insertUsing(['absensi_nobukti', 'absensi_tglbukti', 'absensi_uangjalan', 'absensi_tradoid', 'absensi_trado'], $fetch);

        $query = DB::table($temp)->from(DB::raw("$temp as a with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By a.absensi_nobukti) as absensi_id"),
                "a.gajisupir_id",
                "a.absensi_nobukti",
                "a.absensi_tglbukti",
                "a.absensi_uangjalan",
                'a.absensi_tradoid',
                'a.absensi_trado'
            );

        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterAbsensi($query, $temp);
        $this->paginate($query);
        $data = $query->get();

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalUangJalan = $query->sum('a.absensi_uangjalan');
        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function sortforposition($query)
    {
        return $query->orderBy('gajisupirheader.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('a.statuscetak_text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'total' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'deposito' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'uangmakanharian' || $filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'komisisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'biayaextra' || $filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query->orWhere('a.statuscetak_text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'total' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'deposito' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'uangmakanharian' || $filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'komisisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'biayaextra' || $filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
                            } else {
                                // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
        if (request()->cetak && request()->periode) {
            $query->where('a.statuscetak', '<>', request()->cetak)
                ->whereYear('a.tglbukti', '=', request()->year)
                ->whereMonth('a.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }


    public function filterforPosition($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'total' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'deposito' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'uangmakanharian' || $filters['field'] == 'uangmakanberjenjang') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            // $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supir_id') {
                                $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total' || $filters['field'] == 'uangjalan' || $filters['field'] == 'bbm' || $filters['field'] == 'deposito' || $filters['field'] == 'potonganpinjaman' || $filters['field'] == 'potonganpinjamansemua' || $filters['field'] == 'uangmakanharian' || $filters['field'] == 'uangmakanberjenjang') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
        if (request()->cetak && request()->periode) {
            $query->where('a.statuscetak', '<>', request()->cetak)
                ->whereYear('a.tglbukti', '=', request()->year)
                ->whereMonth('a.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function filterTrip($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusritasi') {
                                $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra') {
                                $query = $query->whereRaw("format(" . $table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusritasi') {
                                $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra') {
                                $query = $query->orWhereRaw("format(" . $table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('gajisupirheader.statuscetak', '<>', request()->cetak)
                ->whereYear('gajisupirheader.tglbukti', '=', request()->year)
                ->whereMonth('gajisupirheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function filterAbsensi($query, $table1, $table2 = null, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'absensi_uangjalan') {
                                // if ($table1 == 'absensisupirdetail') {
                                $query = $query->whereRaw("format(a.absensi_uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                // } else {
                                //     $query = $query->whereRaw("format($table1.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                // }
                            } else if ($filters['field'] == 'absensi_nobukti') {
                                // if ($table2 != null) {
                                $query = $query->where('a.absensi_nobukti', 'LIKE', "%$filters[data]%");
                                // } else {
                                //     $query = $query->where($table1 . '.absensisupir_nobukti', 'LIKE', "%$filters[data]%");
                                // }
                            } else if ($filters['field'] == 'absensi_trado') {
                                $query = $query->where('a.absensi_trado', 'LIKE', "%$filters[data]%");
                            } else {
                                // if ($table1 == 'absensisupirdetail' || $table1 == 'gajisupiruangjalan') {
                                $query = $query->whereRaw("format(a.absensi_tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else {
                                //     $query = $query->whereRaw("format($table1.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // }
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'absensi_uangjalan') {
                                // if ($table1 == 'absensisupirdetail') {
                                $query = $query->orWhereRaw("format(a.absensi_uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                // } else {
                                //     $query = $query->orWhereRaw("format($table1.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                // }
                            } else if ($filters['field'] == 'absensi_nobukti') {
                                // if ($table2 != null) {
                                $query = $query->orWhere('a.absensi_nobukti', 'LIKE', "%$filters[data]%");
                                // } else {
                                //     $query = $query->orWhere($table1 . '.absensisupir_nobukti', 'LIKE', "%$filters[data]%");
                                // }
                            } else if ($filters['field'] == 'absensi_trado') {
                                $query = $query->orWhere('a.absensi_trado', 'LIKE', "%$filters[data]%");
                            } else {
                                // if ($table1 == 'absensisupirdetail' || $table1 == 'gajisupiruangjalan') {
                                $query = $query->orWhereRaw("format(a.absensi_tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else {
                                //     $query = $query->orWhereRaw("format($table1.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // }
                            }
                        }
                    }

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

    public function validasiBayarPotSemua($nobukti)
    {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->where("pengeluarantruckingdetail.supir_id", 0)
            ->where("pengeluarantruckingdetail.nobukti", $nobukti);

        return $fetch->first();
    }
    public function validasiBayarPotPribadi($nobukti)
    {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->where("pengeluarantruckingdetail.nobukti", $nobukti);

        return $fetch->first();
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.id',
                'gajisupirheader.nobukti',
                'gajisupirheader.tglbukti',
                'supir.namasupir as supir_id',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'gajisupirheader.total',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.deposito',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.uangmakanberjenjang',
                DB::raw('(total + uangmakanharian + uangmakanberjenjang - uangjalan - potonganpinjaman - potonganpinjamansemua - deposito - bbm) as sisa'),
                DB::raw('(case when (year(gajisupirheader.tglbukacetak) <= 2000) then null else gajisupirheader.tglbukacetak end ) as tglbukacetak'),
                DB::raw("'Laporan Rincian Gaji Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'gajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where("$this->table.id", $id);

        $data = $query->first();
        return $data;
    }

    public function processStore(array $data): GajiSupirHeader
    {
        $group = 'RINCIAN GAJI SUPIR BUKTI';
        $subGroup = 'RINCIAN GAJI SUPIR BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $gajiSupirHeader = new GajiSupirHeader();
        $gajiSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $gajiSupirHeader->supir_id = $data['supir_id'];
        $gajiSupirHeader->nominal = '';
        $gajiSupirHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $gajiSupirHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $gajiSupirHeader->total = '';
        $gajiSupirHeader->uangjalan = $data['uangjalan'] ?? 0;
        $gajiSupirHeader->bbm = $data['nomBBM'] ?? 0;
        $gajiSupirHeader->potonganpinjaman = ($data['nominalPP']) ? array_sum($data['nominalPP']) : 0;
        $gajiSupirHeader->deposito = $data['nomDeposito'] ?? 0;
        $gajiSupirHeader->potonganpinjamansemua = ($data['nominalPS']) ? array_sum($data['nominalPS']) : 0;
        $gajiSupirHeader->komisisupir = ($data['rincian_komisisupir']) ? array_sum($data['rincian_komisisupir']) : 0;
        $gajiSupirHeader->tolsupir = ($data['rincian_tolsupir']) ? array_sum($data['rincian_tolsupir']) : 0;
        $gajiSupirHeader->voucher = $data['voucher'] ?? 0;
        $gajiSupirHeader->uangmakanharian = $data['uangmakanharian'] ?? 0;
        $gajiSupirHeader->uangmakanberjenjang = $data['uangmakanberjenjang'] ?? 0;
        $gajiSupirHeader->pinjamanpribadi = 0;
        $gajiSupirHeader->gajiminus = 0;
        $gajiSupirHeader->uangJalantidakterhitung = 0;
        $gajiSupirHeader->statusformat = $format->id;
        $gajiSupirHeader->statuscetak = $statusCetak->id;
        $gajiSupirHeader->modifiedby = auth('api')->user()->user;
        $gajiSupirHeader->info = html_entity_decode(request()->info);
        $gajiSupirHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $gajiSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


        if (!$gajiSupirHeader->save()) {
            throw new \Exception('Error storing gaji supir');
        }

        $gajiSupirDetails = [];
        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
        $komisi_gajisupir = $params->text;
        $total = 0;
        $urut = 1;
        for ($i = 0; $i < count($data['rincianId']); $i++) {
            if ($komisi_gajisupir == 'YA') {
                $total = $total + $data['rincian_gajisupir'][$i] + $data['rincian_upahritasi'][$i] + $data['rincian_biayaextra'][$i];
            } else {
                $total = $total + $data['rincian_gajisupir'][$i] + $data['rincian_gajikenek'][$i] + $data['rincian_upahritasi'][$i] + $data['rincian_biayaextra'][$i];
            }

            $gajiSupirDetail = (new GajiSupirDetail())->processStore($gajiSupirHeader, [
                'nominaldeposito' => 0,
                'nourut' => $urut,
                'suratpengantar_nobukti' => $data['rincian_nobukti'][$i],
                'ritasi_nobukti' => $data['rincian_ritasi'][$i],
                'komisisupir' => $data['rincian_komisisupir'][$i],
                'tolsupir' => $data['rincian_tolsupir'][$i],
                'voucher' => $data['voucher'][$i] ?? 0,
                'novoucher' => $data['novoucher'][$i] ?? 0,
                'gajisupir' => $data['rincian_gajisupir'][$i],
                'gajikenek' => $data['rincian_gajikenek'][$i],
                'gajiritasi' => $data['rincian_upahritasi'][$i],
                'biayatambahan' => $data['rincian_biayaextra'][$i],
                'keteranganbiayatambahan' => $data['rincian_keteranganbiaya'][$i],
                'nominalpengembalianpinjaman' => 0,
                'uangmakanberjenjang' => ($data['uangmakanjenjang'][$i] == null) ? 0 : $data['uangmakanjenjang'][$i],
            ]);

            $gajiSupirDetails[] = $gajiSupirDetail->toArray();
            $urut++;
        }
        $nominal = ($total - $gajiSupirHeader->uangjalan - $gajiSupirHeader->bbm - $gajiSupirHeader->potonganpinjaman - $gajiSupirHeader->potonganpinjamansemua - $gajiSupirHeader->deposito) + $gajiSupirHeader->uangmakanharian + $gajiSupirHeader->uangmakanberjenjang;

        $gajiSupirHeader->nominal = $nominal;
        $gajiSupirHeader->total = $total;

        $gajiSupirHeader->save();

        $gajiSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $gajiSupirHeader->getTable(),
            'postingdari' => 'ENTRY GAJI SUPIR HEADER',
            'idtrans' => $gajiSupirHeader->id,
            'nobuktitrans' => $gajiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $gajiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => $gajiSupirDetail->getTable(),
            'postingdari' => 'ENTRY GAJI SUPIR DETAIL',
            'idtrans' => $gajiSupirHeaderLogTrail['id'],
            'nobuktitrans' => $gajiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $gajiSupirDetails,
            'modifiedby' => auth('api')->user()->user
        ]);
        if ($data['pinjSemua']) {
            $fetchFormatPS = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'PJP')
                ->first();
            $pengeluarantruckingheader_nobuktiPS = [];
            $nominalPS = [];
            $supirPS = [];
            $keteranganPS = [];
            for ($i = 0; $i < count($data['pinjSemua']); $i++) {
                $supirPS[] = 0;
                $pengeluarantruckingheader_nobuktiPS[] = $data['pinjSemua_nobukti'][$i];
                $nominalPS[] = $data['nominalPS'][$i];
                $keteranganPS[] = "PINJAMAN SUPIR " . $data['supir'] . ' ' . $data['pinjSemua_keterangan'][$i];
            }

            $penerimaanTruckingHeaderPS = [
                'tanpaprosesnobukti' => '2',
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'penerimaantrucking_id' => $fetchFormatPS->id,
                'supirheader_id' => $data['supir_id'],
                'bank_id' => 0,
                'coa' => $fetchFormatPS->coapostingkredit,
                'penerimaan_nobukti' => '',
                'postingdari' => 'ENTRY GAJI SUPIR',
                'supir_id' => $supirPS,
                'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiPS,
                'keterangan' => $keteranganPS,
                'nominal' => $nominalPS
            ];

            $penerimaanPS = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderPS);

            for ($i = 0; $i < count($data['pinjSemua']); $i++) {
                $gajiSupirPelunasanPS = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'penerimaantrucking_nobukti' => $penerimaanPS->nobukti,
                    'pengeluarantrucking_nobukti' => $data['pinjSemua_nobukti'][$i],
                    'supir_id' => 0,
                    'nominal' => $data['nominalPS'][$i]
                ];
                (new GajiSupirPelunasanPinjaman())->processStore($gajiSupirPelunasanPS);
            }
        }

        if ($data['pinjPribadi']) {
            $fetchFormatPP = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'PJP')
                ->first();
            $pengeluarantruckingheader_nobuktiPP = [];
            $nominalPP = [];
            $supirPP = [];
            $keteranganPP = [];
            for ($i = 0; $i < count($data['pinjPribadi']); $i++) {
                $supirPP[] = $gajiSupirHeader->supir_id;
                $pengeluarantruckingheader_nobuktiPP[] = $data['pinjPribadi_nobukti'][$i];
                $nominalPP[] = $data['nominalPP'][$i];
                $keteranganPP[] = "PINJAMAN SUPIR " . $data['supir'] . ' ' . $data['pinjPribadi_keterangan'][$i];
            }

            $penerimaanTruckingHeaderPP = [
                'tanpaprosesnobukti' => '2',
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'penerimaantrucking_id' => $fetchFormatPP->id,
                'supirheader_id' => $data['supir_id'],
                'bank_id' => 0,
                'coa' => $fetchFormatPP->coapostingkredit,
                'penerimaan_nobukti' => '',
                'postingdari' => 'ENTRY GAJI SUPIR',
                'supir_id' => $supirPP,
                'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiPP,
                'keterangan' => $keteranganPP,
                'nominal' => $nominalPP
            ];

            $penerimaanPP = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderPP);

            for ($i = 0; $i < count($data['pinjPribadi']); $i++) {
                $gajiSupirPelunasanPP = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'penerimaantrucking_nobukti' => $penerimaanPP->nobukti,
                    'pengeluarantrucking_nobukti' => $data['pinjPribadi_nobukti'][$i],
                    'supir_id' => $gajiSupirHeader->supir_id,
                    'nominal' => $data['nominalPP'][$i]
                ];
                (new GajiSupirPelunasanPinjaman())->processStore($gajiSupirPelunasanPP);
            }
        }

        if ($data['nomDeposito'] != 0) {
            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();

            $supirDPO[] = $gajiSupirHeader->supir_id;
            $pengeluarantruckingheader_nobuktiDPO[] = '';
            $nominalDPO[] = $data['nomDeposito'];
            $keteranganDPO[] = "DEPOSITO SUPIR " . $data['supir'] . " PERIODE " . $data['tgldari'] . " S/D " . $data['tglsampai'] . " " . $data['ketDeposito'];

            $penerimaanTruckingHeaderDPO = [
                'tanpaprosesnobukti' => '2',
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'penerimaantrucking_id' => $fetchFormatDPO->id,
                'bank_id' => 0,
                'coa' => $fetchFormatDPO->coapostingkredit,
                'penerimaan_nobukti' => '',
                'postingdari' => 'ENTRY GAJI SUPIR',
                'supir_id' => $supirDPO,
                'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiDPO,
                'keterangan' => $keteranganDPO,
                'nominal' => $nominalDPO
            ];

            $penerimaanDPO = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderDPO);

            $gajiSupirDPO = [
                'gajisupir_id' => $gajiSupirHeader->id,
                'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                'penerimaantrucking_nobukti' => $penerimaanDPO->nobukti,
                'pengeluarantrucking_nobukti' => '',
                'supir_id' => $gajiSupirHeader->supir_id,
                'nominal' => $data['nomDeposito']
            ];
            (new GajiSupirDeposito())->processStore($gajiSupirDPO);
        }

        if ($data['nomBBM'] != 0) {
            $fetchFormatBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'BBM')
                ->first();

            $supirBBM[] = $gajiSupirHeader->supir_id;
            $pengeluarantruckingheader_nobuktiBBM[] = '';
            $nominalBBM[] = $data['nomBBM'];
            $keteranganBBM[] = "HUTANG BBM SUPIR " . $data['supir'] . " PERIODE " . $data['tgldari'] . " S/D " . $data['tglsampai'] . " " . $data['ketBBM'];

            $penerimaanTruckingHeaderBBM = [
                'tanpaprosesnobukti' => '2',
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'penerimaantrucking_id' => $fetchFormatBBM->id,
                'bank_id' => 0,
                'coa' => $fetchFormatBBM->coadebet,
                'penerimaan_nobukti' => '',
                'postingdari' => 'ENTRY GAJI SUPIR',
                'supir_id' => $supirBBM,
                'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiBBM,
                'keterangan' => $keteranganBBM,
                'nominal' => $nominalBBM
            ];

            $penerimaanBBM = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderBBM);

            $gajiSupirBBM = [
                'gajisupir_id' => $gajiSupirHeader->id,
                'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                'penerimaantrucking_nobukti' => $penerimaanBBM->nobukti,
                'pengeluarantrucking_nobukti' => '',
                'supir_id' => $gajiSupirHeader->supir_id,
                'nominal' => $data['nomBBM']
            ];

            (new GajiSupirBBM())->processStore($gajiSupirBBM);

            // $coakredit_detail[] = $fetchFormatBBM->coakredit;
            // $coadebet_detail[] = $fetchFormatBBM->coadebet;
            // $nominal_detail[] = $data['nomBBM'];
            // $keterangan_detail[] = $data['ketBBM'];

            // $jurnalRequest = [
            //     'tanpaprosesnobukti' => 1,
            //     'nobukti' => $penerimaanBBM->nobukti,
            //     'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            //     'postingdari' => "ENTRY GAJI SUPIR",
            //     'statusformat' => "0",
            //     'coakredit_detail' => $coakredit_detail,
            //     'coadebet_detail' => $coadebet_detail,
            //     'nominal_detail' => $nominal_detail,
            //     'keterangan_detail' => $keterangan_detail
            // ];
            // (new JurnalUmumHeader())->processStore($jurnalRequest);
        }
        if ($data['absensi_nobukti']) {
            for ($i = 0; $i < count($data['absensi_nobukti']); $i++) {
                $gajiSupirUangJalan = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'absensisupir_nobukti' => $data['absensi_nobukti'][$i],
                    'supir_id' => $data['supir_id'],
                    'trado_id' => $data['absensi_trado_id'][$i],
                    'nominal' => $data['absensi_uangjalan'][$i]
                ];

                (new GajisUpirUangJalan())->processStore($gajiSupirUangJalan);
            }
        }

        return $gajiSupirHeader;
    }

    public function processUpdate(GajiSupirHeader $gajiSupirHeader, array $data): GajiSupirHeader
    {

        $nobuktiold = DB::table('gajisupirheader')->from(
            DB::raw("gajisupirheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.id', $gajiSupirHeader->id)
            ->first();



        $group = 'RINCIAN GAJI SUPIR BUKTI';
        $subGroup = 'RINCIAN GAJI SUPIR BUKTI';

        $querycek = DB::table('gajisupirheader')->from(
            DB::raw("gajisupirheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.id', $gajiSupirHeader->id)
            ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
            ->first();

        if (isset($querycek)) {
            $nobukti = $querycek->nobukti;
        } else {
            $nobukti = (new RunningNumberService)->get($group, $subGroup, $gajiSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        }


        $gajiSupirHeader->supir_id = $data['supir_id'];
        $gajiSupirHeader->nominal = '';
        $gajiSupirHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $gajiSupirHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $gajiSupirHeader->total = '';
        $gajiSupirHeader->uangjalan = $data['uangjalan'] ?? 0;
        $gajiSupirHeader->bbm = $data['nomBBM'] ?? 0;
        $gajiSupirHeader->potonganpinjaman = ($data['nominalPP']) ? array_sum($data['nominalPP']) : 0;
        $gajiSupirHeader->deposito = $data['nomDeposito'] ?? 0;
        $gajiSupirHeader->potonganpinjamansemua = ($data['nominalPS']) ? array_sum($data['nominalPS']) : 0;
        $gajiSupirHeader->komisisupir = ($data['rincian_komisisupir']) ? array_sum($data['rincian_komisisupir']) : 0;
        $gajiSupirHeader->tolsupir = ($data['rincian_tolsupir']) ? array_sum($data['rincian_tolsupir']) : 0;
        $gajiSupirHeader->voucher = $data['voucher'] ?? 0;
        $gajiSupirHeader->uangmakanharian = $data['uangmakanharian'] ?? 0;
        $gajiSupirHeader->uangmakanberjenjang = $data['uangmakanberjenjang'] ?? 0;
        $gajiSupirHeader->pinjamanpribadi = 0;
        $gajiSupirHeader->gajiminus = 0;
        $gajiSupirHeader->uangJalantidakterhitung = 0;

        $gajiSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $gajiSupirHeader->nobukti = $nobukti;

        $gajiSupirHeader->modifiedby = auth('api')->user()->name;
        $gajiSupirHeader->info = html_entity_decode(request()->info);

        if (!$gajiSupirHeader->save()) {
            throw new \Exception('Error update gaji supir');
        }

        GajiSupirDetail::where('gajisupir_id', $gajiSupirHeader->id)->delete();

        $gajiSupirDetails = [];
        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
        $komisi_gajisupir = $params->text;
        $total = 0;
        $urut = 1;
        for ($i = 0; $i < count($data['rincianId']); $i++) {
            if ($komisi_gajisupir == 'YA') {
                $total = $total + $data['rincian_gajisupir'][$i] + $data['rincian_upahritasi'][$i] + $data['rincian_biayaextra'][$i];
            } else {
                $total = $total + $data['rincian_gajisupir'][$i] + $data['rincian_gajikenek'][$i] + $data['rincian_upahritasi'][$i] + $data['rincian_biayaextra'][$i];
            }
            $gajiSupirDetail = (new GajiSupirDetail())->processStore($gajiSupirHeader, [
                'nominaldeposito' => 0,
                'nourut' => $urut,
                'suratpengantar_nobukti' => $data['rincian_nobukti'][$i],
                'ritasi_nobukti' => $data['rincian_ritasi'][$i],
                'komisisupir' => $data['rincian_komisisupir'][$i],
                'tolsupir' => $data['rincian_tolsupir'][$i],
                'voucher' => $data['voucher'][$i] ?? 0,
                'novoucher' => $data['novoucher'][$i] ?? 0,
                'gajisupir' => $data['rincian_gajisupir'][$i],
                'gajikenek' => $data['rincian_gajikenek'][$i],
                'gajiritasi' => $data['rincian_upahritasi'][$i],
                'biayatambahan' => $data['rincian_biayaextra'][$i],
                'keteranganbiayatambahan' => $data['rincian_keteranganbiaya'][$i],
                'nominalpengembalianpinjaman' => 0,
                'uangmakanberjenjang' => ($data['uangmakanjenjang'][$i] == null) ? 0 : $data['uangmakanjenjang'][$i],
            ]);

            $gajiSupirDetails[] = $gajiSupirDetail->toArray();
            $urut++;
        }
        $nominal = ($total - $gajiSupirHeader->uangjalan - $gajiSupirHeader->bbm - $gajiSupirHeader->potonganpinjaman - $gajiSupirHeader->potonganpinjamansemua - $gajiSupirHeader->deposito) + $gajiSupirHeader->uangmakanharian + $gajiSupirHeader->uangmakanberjenjang;

        $gajiSupirHeader->nominal = $nominal;
        $gajiSupirHeader->total = $total;

        $gajiSupirHeader->save();

        $gajiSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $gajiSupirHeader->getTable(),
            'postingdari' => 'EDIT GAJI SUPIR HEADER',
            'idtrans' => $gajiSupirHeader->id,
            'nobuktitrans' => $gajiSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $gajiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => $gajiSupirDetail->getTable(),
            'postingdari' => 'EDIT GAJI SUPIR DETAIL',
            'idtrans' => $gajiSupirHeaderLogTrail['id'],
            'nobuktitrans' => $gajiSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $gajiSupirDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        if ($data['pinjSemua']) {

            $fetchFormatPS = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'PJP')
                ->first();

            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', '0')->first();

            // jika ada maka update
            if ($fetchPS != null) {

                $pengeluaranPS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();

                GajiSupirPelunasanPinjaman::where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', '0')->delete();

                $pengeluarantruckingheader_nobuktiPS = [];
                $nominalPS = [];
                $supirPS = [];
                $keteranganPS = [];

                for ($i = 0; $i < count($data['pinjSemua']); $i++) {
                    $supirPS[] = 0;
                    $pengeluarantruckingheader_nobuktiPS[] = $data['pinjSemua_nobukti'][$i];
                    $nominalPS[] = $data['nominalPS'][$i];
                    $keteranganPS[] = "PINJAMAN SUPIR " . $data['supir'] . ' ' . $data['pinjSemua_keterangan'][$i];
                }

                $penerimaanTruckingHeaderPS = [
                    'tanpaprosesnobukti' => '2',
                    'from' => 'ric',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatPS->id,
                    'supirheader_id' => $data['supir_id'],
                    'bank_id' => 0,
                    'coa' => $fetchFormatPS->coapostingkredit,
                    'postingdari' => 'EDIT GAJI SUPIR',
                    'supir_id' => $supirPS,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiPS,
                    'keterangan' => $keteranganPS,
                    'nominal' => $nominalPS
                ];

                $newPenerimaanTruckingPS = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPS = $newPenerimaanTruckingPS->findAll($pengeluaranPS->id);
                $penerimaanPS = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPS, $penerimaanTruckingHeaderPS);

                for ($i = 0; $i < count($data['pinjSemua']); $i++) {
                    $gajiSupirPelunasanPS = [
                        'gajisupir_id' => $gajiSupirHeader->id,
                        'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                        'penerimaantrucking_nobukti' => $penerimaanPS->nobukti,
                        'pengeluarantrucking_nobukti' => $data['pinjSemua_nobukti'][$i],
                        'supir_id' => 0,
                        'nominal' => $data['nominalPS'][$i]
                    ];
                    (new GajiSupirPelunasanPinjaman())->processStore($gajiSupirPelunasanPS);
                }
            } else {
                // jika tidak ada, maka insert

                $pengeluarantruckingheader_nobuktiPS = [];
                $nominalPS = [];
                $supirPS = [];
                $keteranganPS = [];
                for ($i = 0; $i < count($data['pinjSemua']); $i++) {
                    $supirPS[] = 0;
                    $pengeluarantruckingheader_nobuktiPS[] = $data['pinjSemua_nobukti'][$i];
                    $nominalPS[] = $data['nominalPS'][$i];
                    $keteranganPS[] =  "PINJAMAN SUPIR " . $data['supir'] . ' ' . $data['pinjSemua_keterangan'][$i];
                }

                $penerimaanTruckingHeaderPS = [
                    'tanpaprosesnobukti' => '2',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatPS->id,
                    'supirheader_id' => $data['supir_id'],
                    'bank_id' => 0,
                    'coa' => $fetchFormatPS->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'EDIT GAJI SUPIR',
                    'supir_id' => $supirPS,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiPS,
                    'keterangan' => $keteranganPS,
                    'nominal' => $nominalPS
                ];

                $penerimaanPS = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderPS);

                for ($i = 0; $i < count($data['pinjSemua']); $i++) {
                    $gajiSupirPelunasanPS = [
                        'gajisupir_id' => $gajiSupirHeader->id,
                        'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                        'penerimaantrucking_nobukti' => $penerimaanPS->nobukti,
                        'pengeluarantrucking_nobukti' => $data['pinjSemua_nobukti'][$i],
                        'supir_id' => 0,
                        'nominal' => $data['nominalPS'][$i]
                    ];
                    (new GajiSupirPelunasanPinjaman())->processStore($gajiSupirPelunasanPS);
                }
            }
        } else {
            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', '0')->first();

            if ($fetchPS != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();

                if (isset($getPenerimaanTrucking)) {
                    (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, 'EDIT GAJI SUPIR');
                }

                $getDetailGSPS = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', '0')->get();
                if (isset($getDetailGSPS)) {
                    foreach ($getDetailGSPS as $key => $value) {
                        (new GajiSupirPelunasanPinjaman())->processDestroy($value->id, 'EDIT GAJI SUPIR');
                    }
                }
            }
        }

        if ($data['pinjPribadi']) {

            $fetchFormatPP = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'PJP')
                ->first();

            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->first();

            // jika ada maka edit
            if ($fetchPP != null) {

                $pengeluaranPP = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();

                GajiSupirPelunasanPinjaman::where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->delete();

                $pengeluarantruckingheader_nobuktiPP = [];
                $nominalPP = [];
                $supirPP = [];
                $keteranganPP = [];

                for ($i = 0; $i < count($data['pinjPribadi']); $i++) {
                    $supirPP[] = $gajiSupirHeader->supir_id;
                    $pengeluarantruckingheader_nobuktiPP[] = $data['pinjPribadi_nobukti'][$i];
                    $nominalPP[] = $data['nominalPP'][$i];
                    $keteranganPP[] =  "PINJAMAN SUPIR " . $data['supir'] . ' ' . $data['pinjPribadi_keterangan'][$i];
                }

                $penerimaanTruckingHeaderPP = [
                    'tanpaprosesnobukti' => '2',
                    'from' => 'ric',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatPP->id,
                    'supirheader_id' => $data['supir_id'],
                    'bank_id' => 0,
                    'coa' => $fetchFormatPP->coapostingkredit,
                    'postingdari' => 'EDIT GAJI SUPIR',
                    'supir_id' => $supirPP,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiPP,
                    'keterangan' => $keteranganPP,
                    'nominal' => $nominalPP
                ];

                $newPenerimaanTruckingPP = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingPP = $newPenerimaanTruckingPP->findAll($pengeluaranPP->id);
                $penerimaanPP = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingPP, $penerimaanTruckingHeaderPP);

                for ($i = 0; $i < count($data['pinjPribadi']); $i++) {
                    $gajiSupirPelunasanPP = [
                        'gajisupir_id' => $gajiSupirHeader->id,
                        'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                        'penerimaantrucking_nobukti' => $penerimaanPP->nobukti,
                        'pengeluarantrucking_nobukti' => $data['pinjPribadi_nobukti'][$i],
                        'supir_id' => $gajiSupirHeader->supir_id,
                        'nominal' => $data['nominalPP'][$i]
                    ];
                    (new GajiSupirPelunasanPinjaman())->processStore($gajiSupirPelunasanPP);
                }
            } else {
                // jika tidak ada, maka insert
                $pengeluarantruckingheader_nobuktiPP = [];
                $nominalPP = [];
                $supirPP = [];
                $keteranganPP = [];
                for ($i = 0; $i < count($data['pinjPribadi']); $i++) {
                    $supirPP[] = $gajiSupirHeader->supir_id;
                    $pengeluarantruckingheader_nobuktiPP[] = $data['pinjPribadi_nobukti'][$i];
                    $nominalPP[] = $data['nominalPP'][$i];
                    $keteranganPP[] =  "PINJAMAN SUPIR " . $data['supir'] . ' ' . $data['pinjPribadi_keterangan'][$i];
                }

                $penerimaanTruckingHeaderPP = [
                    'tanpaprosesnobukti' => '2',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatPP->id,
                    'supirheader_id' => $data['supir_id'],
                    'bank_id' => 0,
                    'coa' => $fetchFormatPP->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'EDIT GAJI SUPIR',
                    'supir_id' => $supirPP,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiPP,
                    'keterangan' => $keteranganPP,
                    'nominal' => $nominalPP
                ];

                $penerimaanPP = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderPP);

                for ($i = 0; $i < count($data['pinjPribadi']); $i++) {
                    $gajiSupirPelunasanPP = [
                        'gajisupir_id' => $gajiSupirHeader->id,
                        'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                        'penerimaantrucking_nobukti' => $penerimaanPP->nobukti,
                        'pengeluarantrucking_nobukti' => $data['pinjPribadi_nobukti'][$i],
                        'supir_id' => $gajiSupirHeader->supir_id,
                        'nominal' => $data['nominalPP'][$i]
                    ];
                    (new GajiSupirPelunasanPinjaman())->processStore($gajiSupirPelunasanPP);
                }
            }
        } else {
            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->first();
            if ($fetchPP != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
                if (isset($getPenerimaanTrucking)) {
                    (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, 'EDIT GAJI SUPIR');
                }
                $getDetailGSPP = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $gajiSupirHeader->supir_id)->get();
                if (isset($getDetailGSPP)) {
                    foreach ($getDetailGSPP as $key => $value) {
                        (new GajiSupirPelunasanPinjaman())->processDestroy($value->id, 'EDIT GAJI SUPIR');
                    }
                }
            }
        }

        if ($data['nomDeposito'] != 0) {

            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();

            $fetchDPO = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->first();

            // jika ada maka update
            if ($fetchDPO != null) {
                $penerimaanDepo = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchDPO->penerimaantrucking_nobukti)->first();

                $supirDPO[] = $gajiSupirHeader->supir_id;
                $pengeluarantruckingheader_nobuktiDPO[] = '';
                $nominalDPO[] = $data['nomDeposito'];
                $keteranganDPO[] = "DEPOSITO SUPIR " . $data['supir'] . " PERIODE " . $data['tgldari'] . " S/D " . $data['tglsampai'];

                $penerimaanTruckingHeaderDPO = [
                    'tanpaprosesnobukti' => '2',
                    'from' => 'ric',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatDPO->id,
                    'bank_id' => 0,
                    'coa' => $fetchFormatDPO->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'EDIT GAJI SUPIR',
                    'supir_id' => $supirDPO,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiDPO,
                    'keterangan' => $keteranganDPO,
                    'nominal' => $nominalDPO
                ];

                $newPenerimaanTruckingDPO = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingDPO = $newPenerimaanTruckingDPO->findAll($penerimaanDepo->id);
                $penerimaanDPO = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDPO, $penerimaanTruckingHeaderDPO);

                GajiSupirDeposito::where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $gajiSupirHeader->supir_id)->delete();

                $gajiSupirDPO = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'penerimaantrucking_nobukti' => $penerimaanDPO->nobukti,
                    'pengeluarantrucking_nobukti' => '',
                    'supir_id' => $gajiSupirHeader->supir_id,
                    'nominal' => $data['nomDeposito']
                ];
                (new GajiSupirDeposito())->processStore($gajiSupirDPO);
            } else {
                $supirDPO[] = $gajiSupirHeader->supir_id;
                $pengeluarantruckingheader_nobuktiDPO[] = '';
                $nominalDPO[] = $data['nomDeposito'];
                $keteranganDPO[] = "DEPOSITO SUPIR " . $data['supir'] . " PERIODE " . $data['tgldari'] . " S/D " . $data['tglsampai'];

                $penerimaanTruckingHeaderDPO = [
                    'tanpaprosesnobukti' => '2',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatDPO->id,
                    'bank_id' => 0,
                    'coa' => $fetchFormatDPO->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'ENTRY GAJI SUPIR',
                    'supir_id' => $supirDPO,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiDPO,
                    'keterangan' => $keteranganDPO,
                    'nominal' => $nominalDPO
                ];

                $penerimaanDPO = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderDPO);

                $gajiSupirDPO = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'penerimaantrucking_nobukti' => $penerimaanDPO->nobukti,
                    'pengeluarantrucking_nobukti' => '',
                    'supir_id' => $gajiSupirHeader->supir_id,
                    'nominal' => $data['nomDeposito']
                ];
                (new GajiSupirDeposito())->processStore($gajiSupirDPO);
            }
        } else {
            $fetchDPO = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->first();
            if ($fetchDPO != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchDPO->penerimaantrucking_nobukti)->first();
                if (isset($getPenerimaanTrucking)) {
                    $tes = (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, 'EDIT GAJI SUPIR');
                }
                (new GajiSupirDeposito())->processDestroy($fetchDPO->id, 'EDIT GAJI SUPIR');
            }
        }

        if ($data['nomBBM'] != 0) {
            $fetchFormatBBM = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'BBM')
                ->first();
            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->first();

            // jika ada maka update
            if ($fetchBBM != null) {
                $pengeluaranbbm = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                $supirBBM[] = $gajiSupirHeader->supir_id;
                $pengeluarantruckingheader_nobuktiBBM[] = '';
                $nominalBBM[] = $data['nomBBM'];
                $keteranganBBM[] = "HUTANG BBM SUPIR " . $data['supir'] . " PERIODE " . $data['tgldari'] . " S/D " . $data['tglsampai'];

                $penerimaanTruckingHeaderBBM = [
                    'tanpaprosesnobukti' => '2',
                    'from' => 'ric',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatBBM->id,
                    'bank_id' => 0,
                    'coa' => $fetchFormatBBM->coadebet,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'ENTRY GAJI SUPIR',
                    'supir_id' => $supirBBM,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiBBM,
                    'keterangan' => $keteranganBBM,
                    'nominal' => $nominalBBM
                ];

                $newPenerimaanTruckingBBM = new PenerimaanTruckingHeader();
                $newPenerimaanTruckingBBM = $newPenerimaanTruckingBBM->findAll($pengeluaranbbm->id);
                $penerimaanBBM = (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingBBM, $penerimaanTruckingHeaderBBM);

                GajiSupirBBM::where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $gajiSupirHeader->supir_id)->delete();

                $gajiSupirBBM = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'penerimaantrucking_nobukti' => $penerimaanBBM->nobukti,
                    'pengeluarantrucking_nobukti' => '',
                    'supir_id' => $gajiSupirHeader->supir_id,
                    'nominal' => $data['nomBBM']
                ];

                (new GajiSupirBBM())->processStore($gajiSupirBBM);

                // $coakredit_detail[] = $fetchFormatBBM->coakredit;
                // $coadebet_detail[] = $fetchFormatBBM->coadebet;
                // $nominal_detail[] = $data['nomBBM'];
                // $keterangan_detail[] = "HUTANG BBM SUPIR ".$data['supir']." PERIODE ".$data['tgldari']." S/D ".$data['tglsampai']." ".$data['ketBBM'];

                // $jurnalRequest = [
                //     'tanpaprosesnobukti' => 1,
                //     'postingdari' => "EDIT GAJI SUPIR",
                //     'coakredit_detail' => $coakredit_detail,
                //     'coadebet_detail' => $coadebet_detail,
                //     'nominal_detail' => $nominal_detail,
                //     'keterangan_detail' => $keterangan_detail
                // ];
                // $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                // $newJurnal = new JurnalUmumHeader();
                // $newJurnal = $newJurnal->find($getJurnal->id);
                // $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
            } else {
                // jika tidak ada, maka insert
                $supirBBM[] = $gajiSupirHeader->supir_id;
                $pengeluarantruckingheader_nobuktiBBM[] = '';
                $nominalBBM[] = $data['nomBBM'];
                $keteranganBBM[] = "HUTANG BBM SUPIR " . $data['supir'] . " PERIODE " . $data['tgldari'] . " S/D " . $data['tglsampai'];

                $penerimaanTruckingHeaderBBM = [
                    'tanpaprosesnobukti' => '2',
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatBBM->id,
                    'bank_id' => 0,
                    'coa' => $fetchFormatBBM->coadebet,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'EDIT GAJI SUPIR',
                    'supir_id' => $supirBBM,
                    'pengeluarantruckingheader_nobukti' => $pengeluarantruckingheader_nobuktiBBM,
                    'keterangan' => $keteranganBBM,
                    'nominal' => $nominalBBM
                ];

                $penerimaanBBM = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderBBM);

                $gajiSupirBBM = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'penerimaantrucking_nobukti' => $penerimaanBBM->nobukti,
                    'pengeluarantrucking_nobukti' => '',
                    'supir_id' => $gajiSupirHeader->supir_id,
                    'nominal' => $data['nomBBM']
                ];

                (new GajiSupirBBM())->processStore($gajiSupirBBM);

                // $coakredit_detail[] = $fetchFormatBBM->coakredit;
                // $coadebet_detail[] = $fetchFormatBBM->coadebet;
                // $nominal_detail[] = $data['nomBBM'];
                // $keterangan_detail[] = $data['ketBBM'];

                // $jurnalRequest = [
                //     'tanpaprosesnobukti' => 1,
                //     'nobukti' => $penerimaanBBM->nobukti,
                //     'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                //     'postingdari' => "EDIT GAJI SUPIR",
                //     'statusformat' => "0",
                //     'coakredit_detail' => $coakredit_detail,
                //     'coadebet_detail' => $coadebet_detail,
                //     'nominal_detail' => $nominal_detail,
                //     'keterangan_detail' => $keterangan_detail
                // ];
                // (new JurnalUmumHeader())->processStore($jurnalRequest);
            }
        } else {
            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->first();
            if ($fetchBBM != null) {
                $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();

                // $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
                if (isset($getPenerimaanTrucking)) {
                    (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, 'EDIT GAJI SUPIR');
                }
                (new GajiSupirBBM())->processDestroy($fetchBBM->id, 'EDIT GAJI SUPIR');

                // (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'EDIT GAJI SUPIR');
            }
        }

        if ($data['absensi_nobukti']) {
            $cekUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->first();

            if ($cekUangjalan != null) {
                GajisUpirUangJalan::where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->delete();
            }

            for ($i = 0; $i < count($data['absensi_nobukti']); $i++) {
                $gajiSupirUangJalan = [
                    'gajisupir_id' => $gajiSupirHeader->id,
                    'gajisupir_nobukti' => $gajiSupirHeader->nobukti,
                    'absensisupir_nobukti' => $data['absensi_nobukti'][$i],
                    'supir_id' => $data['supir_id'],
                    'trado_id' => $data['absensi_trado_id'][$i],
                    'nominal' => $data['absensi_uangjalan'][$i]
                ];

                (new GajisUpirUangJalan())->processStore($gajiSupirUangJalan);
            }
        } else {
            $cekUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                ->where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->first();

            if ($cekUangjalan != null) {
                GajisUpirUangJalan::where('gajisupir_id', $gajiSupirHeader->id)->where('supir_id', $data['supir_id'])->delete();
            }
        }

        return $gajiSupirHeader;
    }

    public function processDestroy($id, $postingDari = ''): GajiSupirHeader
    {
        $gajiSupirDetails = GajiSupirDetail::lockForUpdate()->where('gajisupir_id', $id)->get();
        $fetchDPO = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->whereRaw("gajisupir_id = $id")->first();
        $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->whereRaw("gajisupir_id = $id")->first();

        $gajiSupirHeader = new GajiSupirHeader();
        $gajiSupirHeader = $gajiSupirHeader->lockAndDestroy($id);

        $gajiSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $gajiSupirHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $gajiSupirHeader->id,
            'nobuktitrans' => $gajiSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $gajiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'GAJISUPIRDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $gajiSupirHeaderLogTrail['id'],
            'nobuktitrans' => $gajiSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $gajiSupirDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if ($fetchDPO != null) {
            $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchDPO->penerimaantrucking_nobukti)->first();
            if (isset($getPenerimaanTrucking)) {
                (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
            }
            (new LogTrail())->processStore([
                'namatabel' => 'GAJISUPIRDEPOSITO',
                'postingdari' => $postingDari,
                'idtrans' => $fetchDPO->id,
                'nobuktitrans' => $gajiSupirHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => '',
                'modifiedby' => auth('api')->user()->name
            ]);
        }

        if ($fetchBBM != null) {
            $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
            // $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $fetchBBM->penerimaantrucking_nobukti)->first();
            if (isset($getPenerimaanTrucking)) {
                (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
            }
            // (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, $postingDari);

            (new LogTrail())->processStore([
                'namatabel' => 'GAJISUPIRBBM',
                'postingdari' => $postingDari,
                'idtrans' => $fetchBBM->id,
                'nobuktitrans' => $gajiSupirHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => '',
                'modifiedby' => auth('api')->user()->name
            ]);
        }

        $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti',  $gajiSupirHeader->nobukti)->where('supir_id', $gajiSupirHeader->supir_id)->first();
        if ($fetchPP != null) {
            $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPP->penerimaantrucking_nobukti)->first();
            if (isset($getPenerimaanTrucking)) {
                (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
            }
            $getDetailGSPP = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_nobukti',  $gajiSupirHeader->nobukti)->where('supir_id', $gajiSupirHeader->supir_id)->get();
            if (isset($getDetailGSPP)) {
                foreach ($getDetailGSPP as $key => $value) {
                    (new GajiSupirPelunasanPinjaman())->processDestroy($value->id, $postingDari);
                }
            }
        }

        $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
            ->where('gajisupir_nobukti', $gajiSupirHeader->nobukti)->where('supir_id', '0')->first();
        if ($fetchPS != null) {
            $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $fetchPS->penerimaantrucking_nobukti)->first();
            if (isset($getPenerimaanTrucking)) {
                (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
            }

            $getDetailGSPS = GajiSupirPelunasanPinjaman::lockForUpdate()->where('gajisupir_nobukti', $gajiSupirHeader->nobukti)->where('supir_id', '0')->get();
            if (isset($getDetailGSPS)) {
                foreach ($getDetailGSPS as $key => $value) {
                    (new GajiSupirPelunasanPinjaman())->processDestroy($value->id, $postingDari);
                }
            }
        }

        $fetchUangJalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))->whereRaw("gajisupir_id = $id")->first();
        if ($fetchUangJalan != null) {
            $getDetailUangJalan = GajisUpirUangJalan::lockForUpdate()->where('gajisupir_nobukti', $gajiSupirHeader->nobukti)->get();
            if (isset($getDetailUangJalan)) {
                foreach ($getDetailUangJalan as $key => $value) {
                    (new GajisUpirUangJalan())->processDestroy($value->id, $postingDari);
                }
            }
        }
        return $gajiSupirHeader;
    }
}
