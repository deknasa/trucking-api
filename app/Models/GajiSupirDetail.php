<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GajiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirdetail';
    protected $tempTable = '';
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function get()
    {

        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $formatCetak = (new Parameter())->cekText('FORMAT CETAK', 'GAJI SUPIR');
            if ($formatCetak == 'FORMAT 1') {
                $tempritasi = '##tempritasi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

                $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw(" a.nobukti,b.nobukti as ritasi_nobukti,parameter.text + ' (' +dari.kodekota+' - '+sampai.kodekota+')' as statusritasi, isnull(a.gajiritasi,0) as gajiritasi")

                    )
                    ->join(db::raw("ritasi as b with (readuncommitted)"), 'a.ritasi_nobukti', 'b.nobukti')
                    ->join(db::raw("kota as dari with (readuncommitted)"), 'b.dari_id', 'dari.id')
                    ->join(db::raw("kota as sampai with (readuncommitted)"), 'b.sampai_id', 'sampai.id')
                    ->join(db::raw("parameter with (readuncommitted)"), 'b.statusritasi', 'parameter.id')
                    ->where('a.gajisupir_id', request()->gajisupir_id);

                Schema::create($tempritasi, function ($table) {
                    $table->string('nobukti')->nullable();
                    $table->string('ritasi_nobukti')->nullable();
                    $table->longText('statusritasi')->nullable();
                    $table->double('gajiritasi', 15, 2)->nullable();
                });
                DB::table($tempritasi)->insertUsing(['nobukti', 'ritasi_nobukti', 'statusritasi', 'gajiritasi'], $fetch);
                $query->select(
                    $this->table .  '.nobukti',
                    $this->table . '.suratpengantar_nobukti',
                    'suratpengantar.nosp',
                    'suratpengantar.penyesuaian',
                    'statuscontainer.kodestatuscontainer',
                    db::raw("(case when gajisupirdetail.urutextra = 1 then dari.kodekota else '' end) as dari"),
                    db::raw("(case when gajisupirdetail.urutextra = 1 then sampai.kodekota else '' end) as sampai"),
                    'container.kodecontainer',
                    'suratpengantar.liter',
                    'suratpengantar.nocont',
                    'suratpengantar.nocont2',
                    'suratpengantar.noseal',
                    'suratpengantar.noseal2',
                    'suratpengantar.tglsp',
                    'agen.namaagen as agen',
                    'ritasi.statusritasi',
                    $this->table . '.uangmakanberjenjang',
                    $this->table . '.gajisupir',
                    $this->table . '.gajikenek',
                    DB::raw("({$this->table}.gajisupir + {$this->table}.gajikenek) as borongan"),
                    'ritasi.gajiritasi as upahritasi',
                    $this->table . '.biayatambahan as biayaextra'
                )
                    ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.suratpengantar_nobukti', 'suratpengantar.nobukti')
                    ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
                    ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
                    ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id')
                    ->leftJoin(DB::raw("$tempritasi as ritasi with (readuncommitted)"), $this->table . '.ritasi_nobukti', 'ritasi.ritasi_nobukti')
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
                    ->leftJoin(DB::raw("agen with (readuncommitted)"), 'suratpengantar.agen_id', 'agen.id')
                    ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-');

                $query->where($this->table . '.gajisupir_id', '=', request()->gajisupir_id)
                    ->orderBy('gajisupirdetail.id');
            } else if ($formatCetak == 'FORMAT 3') {
                $temptrip = '##temptrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

                $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw(" a.nobukti,sp.nobukti as suratpengantar_nobukti,b.nobukti as ritasi_nobukti, b.tglbukti,parameter.text + ' (' +dari.kodekota+' - '+sampai.kodekota+')' as tujuan, sp.liter, isnull(a.gajiritasi,0) as gajiritasi")

                    )
                    ->join(db::raw("ritasi as b with (readuncommitted)"), 'a.ritasi_nobukti', 'b.nobukti')
                    ->join(db::raw("kota as dari with (readuncommitted)"), 'b.dari_id', 'dari.id')
                    ->join(db::raw("kota as sampai with (readuncommitted)"), 'b.sampai_id', 'sampai.id')
                    ->join(db::raw("parameter with (readuncommitted)"), 'b.statusritasi', 'parameter.id')
                    ->join(db::raw("suratpengantar as sp with (readuncommitted)"), 'b.suratpengantar_nobukti', 'sp.nobukti')
                    ->where('a.gajisupir_id', request()->gajisupir_id);

                Schema::create($temptrip, function ($table) {
                    $table->string('nobukti')->nullable();
                    $table->string('suratpengantar_nobukti')->nullable();
                    $table->string('ritasi_nobukti')->nullable();
                    $table->date('tglbukti')->nullable();
                    $table->longText('tujuan')->nullable();
                    $table->string('qty')->nullable();
                    $table->longText('nocontseal')->nullable();
                    $table->longText('emkl')->nullable();
                    $table->string('spfull', 255)->nullable();
                    $table->string('spempty', 255)->nullable();
                    $table->double('liter', 15, 2)->nullable();
                    $table->double('borongan', 15, 2)->nullable();
                    $table->double('gajiritasi', 15, 2)->nullable();
                    $table->double('biayaextra', 15, 2)->nullable();
                });
                DB::table($temptrip)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'ritasi_nobukti', 'tglbukti', 'tujuan', 'liter', 'gajiritasi'], $fetch);


                $fetch = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
                    ->select(
                        db::raw("gajisupirdetail.nobukti, sp.nobukti as suratpengantar_nobukti, sp.tglbukti,dari.kodekota+' - '+sampai.kodekota + (case when isnull(sp.penyesuaian,'') != '' then ' ('+sp.penyesuaian+')' else '' end) as tujuan, container.kodecontainer as qty, sp.nocont + (case when isnull(sp.nocont2,'')!='' then ','+sp.nocont2 else '' end) as nocontseal, agen.kodeagen as emkl, (case when sp.statuscontainer_id = 1 then sp.nosp else '' end) as spfull,(case when sp.statuscontainer_id = 2 then sp.nosp else '' end) as spempty, sp.liter, (gajisupirdetail.gajisupir + gajisupirdetail.gajikenek) as borongan, 0 as gajiritasi, gajisupirdetail.biayatambahan as biayaextra")
                    )
                    ->leftJoin(DB::raw("suratpengantar as sp with (readuncommitted)"), $this->table . '.suratpengantar_nobukti', 'sp.nobukti')
                    ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'sp.statuscontainer_id', 'statuscontainer.id')
                    ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'sp.dari_id', 'dari.id')
                    ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'sp.sampai_id', 'sampai.id')
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'sp.container_id', 'container.id')
                    ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
                    ->where('gajisupirdetail.gajisupir', '!=', 0)
                    ->where('gajisupirdetail.gajisupir_id', '=', request()->gajisupir_id)
                    ->orderBy('gajisupirdetail.id');

                DB::table($temptrip)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'tglbukti', 'tujuan', 'qty', 'nocontseal', 'emkl', 'spfull', 'spempty', 'liter', 'borongan', 'gajiritasi', 'biayaextra'], $fetch);
                $query = DB::table($temptrip)->orderBy($temptrip . '.suratpengantar_nobukti');
                // dd(DB::table($temptrip)->orderBy($temptrip.'.suratpengantar_nobukti')->get());
            } else {
                $tempbiaya = '##tempbiaya' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

                $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail as a with (readuncommitted)"))
                    ->select(
                        db::raw("max(a.nobukti) as nobukti"),
                        'suratpengantar_nobukti',
                        db::raw(" STRING_AGG(
                            CASE 
                                WHEN a.biayatambahan > 0 THEN a.keteranganbiayatambahan 
                                ELSE a.keteranganbiayaextrasupir  
                            END, 
                            ', '
                        ) AS keteranganbiayaextrasupir"),
                        // DB::raw("STRING_AGG(cast(a.keteranganbiayaextrasupir  as nvarchar(max)), ', ') as keteranganbiayaextrasupir"),
                        DB::raw("sum(isnull(a.biayatambahan,0)+isnull(nominalbiayaextrasupir,0)) as nominalbiayaextrasupir"),
                        DB::raw("sum(a.gajisupir + a.gajikenek) as borongan"),

                    )
                    ->where('a.gajisupir_id', request()->gajisupir_id)
                    ->groupBy('a.suratpengantar_nobukti');

                Schema::create($tempbiaya, function ($table) {
                    $table->string('nobukti')->nullable();
                    $table->string('suratpengantar_nobukti')->nullable();
                    $table->longText('keteranganbiayaextrasupir')->nullable();
                    $table->double('nominalbiayaextrasupir', 15, 2)->nullable();
                    $table->double('borongan', 15, 2)->nullable();
                });
                DB::table($tempbiaya)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'keteranganbiayaextrasupir', 'nominalbiayaextrasupir', 'borongan'], $fetch);
                $query = db::table($tempbiaya)->from(DB::raw("$tempbiaya as a with (readuncommitted)"))
                    ->select(
                        'a.nobukti',
                        'a.suratpengantar_nobukti',
                        'suratpengantar.nosp',
                        'suratpengantar.penyesuaian',
                        'statuscontainer.kodestatuscontainer',
                        'dari.kodekota as dari',
                        'sampai.kodekota as sampai',
                        'container.kodecontainer',
                        'suratpengantar.liter',
                        'suratpengantar.nocont',
                        'suratpengantar.tglsp',
                        'agen.namaagen as agen',
                        'a.keteranganbiayaextrasupir',
                        'a.nominalbiayaextrasupir',
                        'a.borongan'
                    )
                    ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'a.suratpengantar_nobukti', 'suratpengantar.nobukti')
                    ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
                    ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
                    ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id')
                    ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
                    ->leftJoin(DB::raw("agen with (readuncommitted)"), 'suratpengantar.agen_id', 'agen.id');
            }
            return $query->get();
        } else {
            $tempDetail = $this->createTemp();
            $this->tempTable = $tempDetail;
            $tempQuery = DB::table($tempDetail)->from(DB::raw("$tempDetail with (readuncommitted)"));
            $tempQuery->select(
                "$tempDetail.nobukti",
                "$tempDetail.suratpengantar_nobukti",
                "$tempDetail.tglsp",
                "$tempDetail.dari",
                "$tempDetail.sampai",
                "$tempDetail.trado",
                "$tempDetail.nocont",
                "$tempDetail.nosp",
                "$tempDetail.uangmakanberjenjang",
                "$tempDetail.gajisupir",
                "$tempDetail.gajikenek",
                "$tempDetail.komisisupir",
                "$tempDetail.tolsupir",
                "$tempDetail.upahritasi",
                "$tempDetail.ritasi_nobukti",
                "$tempDetail.statusritasi",
                "$tempDetail.biayaextra",
                "$tempDetail.keteranganbiayatambahan",
                "$tempDetail.biayaextrasupir_nobukti",
                "$tempDetail.biayaextrasupir_nominal",
                "$tempDetail.biayaextrasupir_keterangan",
                db::raw("cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadersuratpengantar"),
                db::raw("cast(cast(format((cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadersuratpengantar"),
                db::raw("cast((format(ritasi.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderritasi"),
                db::raw("cast(cast(format((cast((format(ritasi.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderritasi"),
                db::raw("cast((format(biayaextrasupirheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderbiayaextrasupir"),
                db::raw("cast(cast(format((cast((format(biayaextrasupirheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderbiayaextrasupir"),
                db::raw("$tempDetail.total"),
                db::raw("statusapprovaltrip.memo as statusapprovaltrip"),
                db::raw("statusapprovalritasi.memo as statusapprovalritasi"),

            )
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $tempDetail . '.suratpengantar_nobukti', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("biayaextrasupirheader with (readuncommitted)"), $tempDetail . '.biayaextrasupir_nobukti', 'biayaextrasupirheader.nobukti')
                ->leftJoin(DB::raw("ritasi with (readuncommitted)"), $tempDetail . '.ritasi_nobukti', 'ritasi.nobukti')
                ->leftJoin(db::raw("parameter as statusapprovaltrip with (readuncommitted)"), $tempDetail.'.statusapprovaltrip', 'statusapprovaltrip.id')
                ->leftJoin(db::raw("parameter as statusapprovalritasi with (readuncommitted)"), $tempDetail.'.statusapprovalritasi', 'statusapprovalritasi.id');
            $tempQuery->orderBy($tempDetail . '.' . $this->params['sortIndex'], $this->params['sortOrder']);

            $this->filter($tempQuery, $tempDetail);

            $this->totalRows = $tempQuery->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($tempQuery);

            $tempbuktisum = '##tempbuktisum' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbuktisum, function ($table) {
                $table->string('nobukti', 100)->nullable();
            });
            $databukti = json_decode($tempQuery->get(), true);
            foreach ($databukti as $item) {

                DB::table($tempbuktisum)->insert([
                    'nobukti' => $item['suratpengantar_nobukti'],
                ]);
            }
            $querytotal = DB::table($tempDetail)->from(DB::raw($tempDetail . " a "))
                ->select(
                    db::raw("sum(a.total) as total"),
                    db::raw("sum(a.gajisupir) as gajisupir"),
                    db::raw("sum(a.gajikenek) as gajikenek"),
                    db::raw("sum(a.komisisupir) as komisisupir"),
                    db::raw("sum(a.upahritasi) as upahritasi"),
                    db::raw("sum(a.biayaextra) as biayaextra"),
                    db::raw("sum(a.tolsupir) as tolsupir"),
                    db::raw("sum(a.uangmakanberjenjang) as uangmakanberjenjang"),
                    db::raw("sum(a.biayaextrasupir_nominal) as biayaextrasupir_nominal"),
                )
                // ->join(db::raw($tempbuktisum . " b "), 'a.suratpengantar_nobukti', 'b.nobukti')
                ->first();


            $this->total = $querytotal->total ?? 0;
            $this->totalGajiSupir = $querytotal->gajisupir ?? 0;
            $this->totalGajiKenek = $querytotal->gajikenek ?? 0;
            $this->totalKomisiSupir = $querytotal->komisisupir ?? 0;
            $this->totalUpahRitasi = $querytotal->upahritasi ?? 0;
            $this->totalBiayaExtra = $querytotal->biayaextra ?? 0;
            $this->totalTolSupir = $querytotal->tolsupir ?? 0;
            $this->totalUangMakanBerjenjang = $querytotal->uangmakanberjenjang ?? 0;
            $this->totalBiayaExtraSupirNominal = $querytotal->biayaextrasupir_nominal ?? 0;
            return $tempQuery->get();
        }
    }


    public function createTemp()
    {

        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));



        $tempritasi = '##tempritasi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempritasi, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('statusritasi')->nullable();
            $table->bigInteger('statusapprovalritasi')->nullable();
        });
        $queryRitasi = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail as gsd with (readuncommitted)"))
            ->select(db::raw("ritasi.nobukti,(parameter.text + ' ' + dari.kodekota + ' - '+sampai.kodekota) as statusritasi, (case when isnull(ritasi.statusapprovalmandor,'')='' then 4 else ritasi.statusapprovalmandor end) as statusapprovalritasi"))
            ->join(db::raw("ritasi with (readuncommitted)"), 'ritasi.nobukti', 'gsd.ritasi_nobukti')
            ->leftJoin(db::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', 'parameter.id')
            ->leftJoin(db::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', 'dari.id')
            ->leftJoin(db::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', 'sampai.id')
            ->where('gsd.gajisupir_id', request()->gajisupir_id);

        DB::table($tempritasi)->insertUsing(['nobukti', 'statusritasi', 'statusapprovalritasi'], $queryRitasi);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti as nobukti',
                'gajisupirdetail.suratpengantar_nobukti',
                'suratpengantar.tglbukti as tglsp',
                'dari.keterangan as dari',
                'sampai.keterangan as sampai',
                'trado.kodetrado as trado',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'ritasi.statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan',
                db::raw("(gajisupirdetail.gajisupir+gajisupirdetail.gajikenek+gajisupirdetail.komisisupir+gajisupirdetail.biayatambahan+ isnull(gajisupirdetail.nominalbiayaextrasupir,0)) as total"),
                'gajisupirdetail.biayaextrasupir_nobukti',
                DB::raw("isnull(gajisupirdetail.nominalbiayaextrasupir, 0) as biayaextrasupir_nominal"),
                'gajisupirdetail.keteranganbiayaextrasupir as biayaextrasupir_keterangan',
                DB::raw("(case when isnull(suratpengantar.statusapprovalmandor,'')='' then 4 else suratpengantar.statusapprovalmandor end) as statusapprovaltrip"),
                DB::raw("(case when isnull(ritasi.statusapprovalritasi,'')='' then '' else ritasi.statusapprovalritasi end) as statusapprovalritasi")

            )
            ->join(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin(DB::raw("$tempritasi as ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')

            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('gajisupirdetail.gajisupir_id', request()->gajisupir_id);


        Schema::create($temp, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('suratpengantar_nobukti')->nullable();
            $table->date('tglsp')->nullable()->nullable();
            $table->string('dari')->nullable();
            $table->string('sampai')->nullable();
            $table->string('trado')->nullable();
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->double('uangmakanberjenjang', 15, 2)->nullable();
            $table->double('gajisupir', 15, 2)->nullable();
            $table->double('gajikenek', 15, 2)->nullable();
            $table->double('komisisupir', 15, 2)->nullable();
            $table->double('tolsupir', 15, 2)->nullable();
            $table->double('upahritasi', 15, 2)->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->string('statusritasi')->nullable();
            $table->double('biayaextra', 15, 2)->nullable();
            $table->string('keteranganbiayatambahan')->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->string('biayaextrasupir_nobukti')->nullable();
            $table->float('biayaextrasupir_nominal')->nullable();
            $table->longText('biayaextrasupir_keterangan')->nullable();
            $table->bigInteger('statusapprovaltrip')->nullable();
            $table->bigInteger('statusapprovalritasi')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'tglsp', 'dari', 'sampai', 'trado', 'nocont', 'nosp', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiayatambahan', 'total', 'biayaextrasupir_nobukti', 'biayaextrasupir_nominal', 'biayaextrasupir_keterangan', 'statusapprovaltrip', 'statusapprovalritasi'], $fetch);


        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti as nobukti',
                'gajisupirdetail.suratpengantar_nobukti',
                'saldosuratpengantar.tglbukti as tglsp',
                'dari.keterangan as dari',
                'sampai.keterangan as sampai',
                'trado.kodetrado as trado',
                'saldosuratpengantar.nocont',
                'saldosuratpengantar.nosp',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan',
                db::raw("(gajisupirdetail.gajisupir+gajisupirdetail.gajikenek+gajisupirdetail.komisisupir+gajisupirdetail.biayatambahan) as total"),
                db::raw("4 as statusapprovaltrip"),
                // db::raw("4 as statusapprovalritasi")

            )
            ->join(DB::raw("saldosuratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'saldosuratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'saldosuratpengantar.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'saldosuratpengantar.sampai_id', 'sampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'saldosuratpengantar.trado_id', 'trado.id')

            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('gajisupirdetail.gajisupir_id', request()->gajisupir_id);
        $tes = DB::table($temp)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'tglsp', 'dari', 'sampai', 'trado', 'nocont', 'nosp', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'biayaextra', 'keteranganbiayatambahan', 'total', 'statusapprovaltrip'], $fetch);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti as nobukti',
                'gajisupirdetail.suratpengantar_nobukti',
                'ritasi.tglbukti as tglsp',
                'dari.keterangan as dari',
                'sampai.keterangan as sampai',
                'trado.kodetrado as trado',
                DB::raw("(case when gajisupirdetail.uangmakanberjenjang IS NULL then 0 else gajisupirdetail.uangmakanberjenjang end) as uangmakanberjenjang"),
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                db::raw("(parameter.text + ' ' + dari.kodekota + ' - '+sampai.kodekota) as statusritasi"),
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan',
                db::raw("(gajisupirdetail.gajisupir+gajisupirdetail.gajikenek+gajisupirdetail.komisisupir+gajisupirdetail.biayatambahan) as total"),
                db::raw("(case when isnull(ritasi.statusapprovalmandor,'')='' then '' else ritasi.statusapprovalmandor end) as statusapprovalritasi")


            )
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', 'sampai.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', 'trado.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', 'parameter.id')

            ->where('gajisupirdetail.suratpengantar_nobukti', '-')
            ->where('gajisupirdetail.gajisupir_id', request()->gajisupir_id);

        $tes = DB::table($temp)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'tglsp', 'dari', 'sampai', 'trado', 'uangmakanberjenjang', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiayatambahan', 'total', 'statusapprovalritasi'], $fetch);

        return $temp;
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($tempQuery, $tempDetail, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $tempQuery->where(function ($tempQuery) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra' || $filters['field'] == 'biayaextrasupir_nominal') {
                                $query = $tempQuery->whereRaw("format(" . $this->tempTable . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglsp') {
                                $query = $tempQuery->whereRaw("format(" . $this->tempTable . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'statusapprovaltrip') {
                                $query = $tempQuery->where('statusapprovaltrip.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovalritasi') {
                                $query = $tempQuery->where('statusapprovalritasi.text', '=', "$filters[data]");
                            } else {
                                $tempQuery = $tempQuery->where($this->tempTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":

                    $tempQuery->where(function ($tempQuery) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'uangmakanberjenjang' || $filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra' || $filters['field'] == 'biayaextrasupir_nominal') {
                                $query = $tempQuery->orWhereRaw("format(" . $this->tempTable . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglsp') {
                                $query = $tempQuery->orWhereRaw("format(" . $this->tempTable . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            }  else if ($filters['field'] == 'statusapprovaltrip') {
                                $query = $tempQuery->orWhere('statusapprovaltrip.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovalritasi') {
                                $query = $tempQuery->orWhere('statusapprovalritasi.text', '=', "$filters[data]");
                            } else {
                                $tempQuery = $tempQuery->orWhere($this->tempTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                default:

                    break;
            }

            $this->totalRows = $tempQuery->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $tempQuery;
    }

    public function paginate($tempQuery)
    {
        return $tempQuery->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function processStore(GajiSupirHeader $gajiSupirHeader, array $data): GajiSupirDetail
    {
        $gajiSupirDetail = new GajiSupirDetail();
        $gajiSupirDetail->gajisupir_id = $gajiSupirHeader->id;
        $gajiSupirDetail->nobukti = $gajiSupirHeader->nobukti;
        $gajiSupirDetail->nominaldeposito = $data['nominaldeposito'];
        $gajiSupirDetail->nourut = $data['nourut'];
        $gajiSupirDetail->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $gajiSupirDetail->ritasi_nobukti = $data['ritasi_nobukti'];
        $gajiSupirDetail->komisisupir = $data['komisisupir'];
        $gajiSupirDetail->tolsupir = $data['tolsupir'];
        $gajiSupirDetail->voucher = $data['voucher'];
        $gajiSupirDetail->novoucher = $data['novoucher'];
        $gajiSupirDetail->gajisupir = $data['gajisupir'];
        $gajiSupirDetail->gajikenek = $data['gajikenek'];
        $gajiSupirDetail->gajiritasi = $data['gajiritasi'];
        $gajiSupirDetail->biayatambahan = $data['biayatambahan'];
        $gajiSupirDetail->keteranganbiayatambahan = $data['keteranganbiayatambahan'];
        $gajiSupirDetail->nominalpengembalianpinjaman = $data['nominalpengembalianpinjaman'];
        $gajiSupirDetail->uangmakanberjenjang = $data['uangmakanberjenjang'];
        $gajiSupirDetail->biayaextrasupir_nobukti = $data['biayaextrasupir_nobukti'];
        $gajiSupirDetail->nominalbiayaextrasupir = $data['nominalbiayaextrasupir'];
        $gajiSupirDetail->keteranganbiayaextrasupir = $data['keteranganbiayaextrasupir'];

        $gajiSupirDetail->modifiedby = auth('api')->user()->name;
        $gajiSupirDetail->info = html_entity_decode(request()->info);

        if (!$gajiSupirDetail->save()) {
            throw new \Exception("Error storing gaji supir detail.");
        }

        return $gajiSupirDetail;
    }
}
