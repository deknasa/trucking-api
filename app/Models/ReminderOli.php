<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReminderOli extends MyModel
{
    use HasFactory;

    public function get($status)
    {
        $this->setRequestParameters();


        // dump(request()->filter);
        // dd($filter->id);

        // if (request()->filter == $filter->id) {
        // dd('test');
        // dd($filter->text);
        $datafilter = request()->filter ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'ReminderOliController';





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
                $table->id();
                $table->longText('nopol')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('status', 100)->nullable();
                $table->double('km', 15, 2)->nullable();
                $table->double('kmperjalanan', 15, 2)->nullable();
                $table->integer('statusbatas')->nullable();
            });


            $tempgorupby = '##tempgorupby' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgorupby, function ($table) {
                $table->id();
                $table->longText('nopol')->nullable();
                $table->integer('trado_id')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('status', 100)->nullable();
                $table->double('km', 15, 2)->nullable();
                $table->double('kmperjalanan', 15, 2)->nullable();
                $table->integer('statusbatas')->nullable();
            });

            DB::table($tempgorupby)->insertUsing([
                'nopol',
                'trado_id',
                'tanggal',
                'status',
                'km',
                'kmperjalanan',
                'statusbatas'
            ], $this->getdata());


            $querytempgorupby = DB::table($tempgorupby)->select(
                'nopol',
                db::raw('max(tanggal) as tanggal'),
                'status',
                db::raw('max(km) as km'),
                db::raw('max(kmperjalanan) as kmperjalanan'),
                db::raw('max(isnull(statusbatas,0)) as statusbatas'),

            )->groupBy(
                'nopol',
                'status',
            )->orderBy('statusbatas', 'desc');


            DB::table($temtabel)->insertUsing([
                'nopol',
                'tanggal',
                'status',
                'km',
                'kmperjalanan',
                'statusbatas'
            ], $querytempgorupby);
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

        $forExport = request()->forExport ?? false;
        $getStatus = DB::table(DB::raw("parameter"))->from(
            DB::raw("parameter with (readuncommitted)")
        )->where('id', $status)->first();

        if ($forExport) {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $query = DB::table(DB::raw($temtabel))->from(
                DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
            )
                ->select(
                    'a.nopol',
                    'a.tanggal',
                    'a.status',
                    'a.statusbatas',
                    'a.km',
                    'a.kmperjalanan',
                    DB::raw("'Laporan Reminder Oli' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                );

            if ($getStatus != null) {
                if ($getStatus->text == 'PERGANTIAN BATERE') {
                    $query->where('a.status', 'PENGGANTIAN AKI');
                } else if ($getStatus->text == 'PERGANTIAN OLI GARDAN') {
                    $query->where('a.status', 'PENGGANTIAN OLI GARDAN');
                } else if ($getStatus->text == 'PERGANTIAN OLI MESIN') {
                    $query->where('a.status', 'PENGGANTIAN OLI MESIN');
                } else if ($getStatus->text == 'PERGANTIAN OLI PERSNELING') {
                    $query->where('a.status', 'PENGGANTIAN OLI PERSNELING');
                } else if ($getStatus->text == 'PERGANTIAN SARINGAN HAWA') {
                    $query->where('a.status', 'PENGGANTIAN SARINGAN HAWA');
                }
            }
        } else {
            $query = DB::table(DB::raw($temtabel))->from(
                DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
            )
                ->select(
                    'a.nopol',
                    'a.tanggal',
                    'a.status',
                    'a.statusbatas',
                    'a.km',
                    'a.kmperjalanan',
                    'parameter.memo as statusbatas',
                )
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'a.statusbatas', 'parameter.id');

            $this->filter($query);
            if ($getStatus != null) {
                if ($getStatus->text == 'PERGANTIAN BATERE') {
                    $query->where('a.status', 'PENGGANTIAN AKI');
                } else if ($getStatus->text == 'PERGANTIAN OLI GARDAN') {
                    $query->where('a.status', 'PENGGANTIAN OLI GARDAN');
                } else if ($getStatus->text == 'PERGANTIAN OLI MESIN') {
                    $query->where('a.status', 'PENGGANTIAN OLI MESIN');
                } else if ($getStatus->text == 'PERGANTIAN OLI PERSNELING') {
                    $query->where('a.status', 'PENGGANTIAN OLI PERSNELING');
                } else if ($getStatus->text == 'PERGANTIAN SARINGAN HAWA') {
                    $query->where('a.status', 'PENGGANTIAN SARINGAN HAWA');
                }
            }

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            // $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
            $query->orderBy('a.id', 'asc');
            // dd($query->toSql());
            $this->paginate($query);
        }

        $data = $query->get();


        // } else {
        //     $data = [];
        // }

        return $data;
    }

    public function getdata()
    {

        $batasgardan = Parameter::where('grp', 'BATAS PERGANTIAN OLI GARDAN')->where('subgrp', 'BATAS PERGANTIAN OLI GARDAN')->first()->text;
        $bataspersneling = Parameter::where('grp', 'BATAS PERGANTIAN OLI PERSNELING')->where('subgrp', 'BATAS PERGANTIAN OLI PERSNELING')->first()->text;
        $batasmesin = Parameter::where('grp', 'BATAS PERGANTIAN OLI MESIN')->where('subgrp', 'BATAS PERGANTIAN OLI MESIN')->first()->text;
        $batassaringanhawa = Parameter::where('grp', 'BATAS PERGANTIAN SARINGAN HAWA')->where('subgrp', 'BATAS PERGANTIAN SARINGAN HAWA')->first()->text;
        $batasmax = Parameter::where('grp', 'BATAS MAX PERGANTIAN OLI')->where('subgrp', 'BATAS MAX PERGANTIAN OLI')->first()->text;

        $sudahLewat = Parameter::where('grp', 'STATUS PERGANTIAN')->where('text', 'SUDAH MELEWATI BATAS')->first()->id;
        $hampirLewat = Parameter::where('grp', 'STATUS PERGANTIAN')->where('text', 'HAMPIR MELEWATI BATAS')->first()->id;

        $tempstatus = '##tempstatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstatus, function ($table) {
            $table->longText('status')->nullable();
            $table->double('batas')->nullable();
        });

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI GARDAN',
            'batas' => $batasgardan,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI PERSNELING',
            'batas' => $bataspersneling,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI MESIN',
            'batas' => $batasmesin,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN SARINGAN HAWA',
            'batas' => $batassaringanhawa,
        ]);

        $Tempsaldoreminderoli = '##Tempsaldoreminderoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempsaldoreminderoli, function ($table) {
            $table->integer('id');
            $table->integer('trado_id');
            $table->string('nopol', 1000);
            $table->string('statusreminder', 100);
            $table->date('tglawal');
            $table->date('tglsampai');
            $table->double('jarak', 15, 2);
            $table->double('jaraktransaksi', 15, 2)->nullable();
        });


        $querysaldo = db::table("saldoreminderpergantian")->from(DB::raw("saldoreminderpergantian a with (readuncommitted)"))
            ->select(
                'a.id',
                db::raw("isnull(b.id,0) as trado_id"),
                'a.nopol',
                'a.statusreminder',
                'a.tglawal',
                'a.tglsampai',
                'a.jarak',
            )
            ->join(db::raw("trado b with (readuncommitted)"), 'a.trado_id', 'b.id')
            ->where("b.statusaktif","1")
            ->orderby('a.id', 'asc');

        //   dd($querysaldo->get());

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $querysaldo);

        $tglsaldo = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'SALDO')
            ->where('a.subgrp', 'SALDO')
            ->first()->text ?? '1900-01-01';

        $tglsaldoawal = date("Y-m-d", strtotime("+1 day", strtotime($tglsaldo)));

        $Tempservicerutin = '##Tempservicerutin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempservicerutin, function ($table) {
            $table->integer('id');
        });
        $queryservicerutin = db::table("parameter")->from(DB::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.id',
            )
            ->where('a.grp', 'STATUS SERVICE RUTIN')
            ->where('a.subgrp', 'STATUS SERVICE RUTIN')
            ->orderby('a.id', 'asc');

        DB::table($Tempservicerutin)->insertUsing([
            'id',
        ], $queryservicerutin);


        $pengeluaranstok_id = 1;


        $Temppergantian = '##Temppergantian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppergantian, function ($table) {
            $table->string('nobukti', 100);
            $table->integer('trado_id');
            $table->string('statusreminder', 100);
            $table->date('tgl');
        });



        $parameter = new Parameter();
        $idgantioli = $parameter->cekId('STATUS OLI', 'STATUS OLI', 'GANTI') ?? 0;
        $idservicerutinsaringanhawa = $parameter->cekId('STATUS SERVICE RUTIN', 'STATUS SERVICE RUTIN', 'PERGANTIAN SARINGAN HAWA') ?? 0;
        $idservicerutinbatere = $parameter->cekId('STATUS SERVICE RUTIN', 'STATUS SERVICE RUTIN', 'PERGANTIAN BATERE') ?? 0;

        $querypergantian = db::table("pengeluaranstokheader")->from(DB::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                db::raw("max(a.nobukti) as nobukti"),
                'a.trado_id',
                db::raw("(case when e.text='PERGANTIAN BATERE' then 'PENGGANTIAN AKI'
                                when e.text='PERGANTIAN OLI GARDAN' then 'PENGGANTIAN OLI GARDAN'
                                when e.text='PERGANTIAN OLI MESIN' then 'PENGGANTIAN OLI MESIN'
                                when e.text='PERGANTIAN OLI PERSNELING' then 'PENGGANTIAN OLI PERSNELING'
                                when e.text='PERGANTIAN SARINGAN HAWA' then 'PENGGANTIAN SARINGAN HAWA'
                    else '' end) as statusreminder
                "),
                db::raw("max(a.tglbukti) as tgl"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->join(db::raw($Tempservicerutin . " d "), 'c.statusservicerutin', 'd.id')
            ->join(db::raw("parameter e with (readuncommitted)"), 'd.id', 'e.id')
            ->where('a.pengeluaranstok_id', $pengeluaranstok_id)
            ->where('b.statusoli', $idgantioli)
            ->whereraw("isnull(a.trado_id,0)<>0")
            ->whereraw("isnull(d.id,0) not in(" . $idservicerutinsaringanhawa . "," . $idservicerutinbatere . ")")
            ->groupBy('a.trado_id')
            ->groupBy('e.text');

        // dd($querypergantian->get());

        DB::table($Temppergantian)->insertUsing([
            'nobukti',
            'trado_id',
            'statusreminder',
            'tgl',
        ], $querypergantian);

        $querypergantian = db::table("pengeluaranstokheader")->from(DB::raw("pengeluaranstokheader a with (readuncommitted)"))
        ->select(
            db::raw("max(a.nobukti) as nobukti"),
            'a.trado_id',
            db::raw("(case when e.text='PERGANTIAN BATERE' then 'PENGGANTIAN AKI'
                            when e.text='PERGANTIAN OLI GARDAN' then 'PENGGANTIAN OLI GARDAN'
                            when e.text='PERGANTIAN OLI MESIN' then 'PENGGANTIAN OLI MESIN'
                            when e.text='PERGANTIAN OLI PERSNELING' then 'PENGGANTIAN OLI PERSNELING'
                            when e.text='PERGANTIAN SARINGAN HAWA' then 'PENGGANTIAN SARINGAN HAWA'
                else '' end) as statusreminder
            "),
            db::raw("max(a.tglbukti) as tgl"),
        )
        ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
        ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
        ->join(db::raw($Tempservicerutin . " d "), 'c.statusservicerutin', 'd.id')
        ->join(db::raw("parameter e with (readuncommitted)"), 'd.id', 'e.id')
        ->where('a.pengeluaranstok_id', $pengeluaranstok_id)
        ->whereraw("isnull(a.trado_id,0)<>0")
        ->whereraw("isnull(d.id,0) in(" . $idservicerutinsaringanhawa . "," . $idservicerutinbatere . ")")
        ->groupBy('a.trado_id')
        ->groupBy('e.text');

    // dd($querypergantian->get());

    DB::table($Temppergantian)->insertUsing([
        'nobukti',
        'trado_id',
        'statusreminder',
        'tgl',
    ], $querypergantian);        

        DB::update(DB::raw("UPDATE " . $Tempsaldoreminderoli . " SET tglawal=b.tgl,jarak=0 
        from " . $Tempsaldoreminderoli . " a inner join " . $Temppergantian . " b on a.trado_id=b.trado_id and a.statusreminder=b.statusreminder 
        "));

        // dump(db::table($Temppergantian)->get());
        // dd(db::table($Tempsaldoreminderoli)->whereraw("trado_id=4")->get());


        $param1 = 'PENGGANTIAN OLI MESIN';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);
        //         

        // PENGGANTIAN OLI PERSNELING
        // PENGGANTIAN SARINGAN HAWA

        $param1 = 'PENGGANTIAN OLI GARDAN';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);

        // 
        // PENGGANTIAN SARINGAN HAWA

        $param1 = 'PENGGANTIAN OLI PERSNELING';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);

        // 

        $param1 = 'PENGGANTIAN SARINGAN HAWA';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);

        // dd(db::table($TempPENGGANTIAN)->get());

        // dd(db::table($Tempsaldoreminderoli)->get());

        $Temptradotransakdi = '##Temptradotransakdi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temptradotransakdi, function ($table) {
            $table->integer('trado_id');
            $table->double('jarak', 15, 2)->nullable();
            $table->date('tgl');
            $table->string('statusreminder', 100);
        });


        $param1 = 'PENGGANTIAN OLI MESIN';
        $querytradoolimesin = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN OLI MESIN' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradoolimesin);

        $param1 = 'PENGGANTIAN OLI GARDAN';
        $querytradooligardan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN OLI GARDAN' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradooligardan);

        $param1 = 'PENGGANTIAN OLI PERSNELING';
        $querytradoolipersneling = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN OLI PERSNELING' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradoolipersneling);

        $param1 = 'PENGGANTIAN SARINGAN HAWA';
        $querytradosaringanhawa = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN SARINGAN HAWA' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradosaringanhawa);

        $param1 = 'PENGGANTIAN AKI';
        $querytradoaki = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN AKI' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradoaki);

        DB::update(DB::raw("UPDATE " . $Tempsaldoreminderoli . " SET jaraktransaksi=b.jarak,tglsampai=b.tgl
        from " . $Tempsaldoreminderoli . " a inner join " . $Temptradotransakdi . " b on a.trado_id=b.trado_id and upper(a.statusreminder)=upper(b.statusreminder) 
        "));

        // dump(db::table($Temptradotransakdi)->whereraw("trado_id=3")->get());
        // dd(db::table($Tempsaldoreminderoli)->whereraw("trado_id=3")->get());

        //         

        // 
        // 

        // dd(db::table($Tempsaldoreminderoli)->get());
        // dd(db::table($tempstatus)->get());


        $Tempsaldoreminderolirekap = '##Tempsaldoreminderolirekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempsaldoreminderolirekap, function ($table) {
            $table->string('nopol', 1000)->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 1000)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->string('statusbatas', 100)->nullable();
            $table->integer('urutid')->nullable();
        });
        $parameter = new Parameter();
        $tglsaldo = $parameter->cekText('SALDO', 'SALDO') ?? '1900-01-01';

        $query = DB::table($Tempsaldoreminderoli)->from(DB::raw($Tempsaldoreminderoli . " a "))
            ->select(
                'a.nopol',
                'a.trado_id',
                db::raw("isnull(c.tgl,'".$tglsaldo."') as tanggal"),
                'a.statusreminder as status',
                DB::raw("(case 
                    when a.statusreminder = 'PENGGANTIAN OLI GARDAN' then $batasgardan 
                    when a.statusreminder = 'PENGGANTIAN OLI PERSNELING' then $bataspersneling
                    when a.statusreminder = 'PENGGANTIAN OLI MESIN' then $batasmesin
                    else $batassaringanhawa end) 
                    
                    as km"),
                db::raw("(isnull(a.jarak,0)+isnull(a.jaraktransaksi,0)) as kmperjalanan"),
                DB::raw("(CASE 
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI PERSNELING' then 
                        CASE
                            WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                            WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                        ELSE ''
                        END
                    
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI GARDAN' then 
                        CASE
                            WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                            WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                        ELSE ''
                        END
                    
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI MESIN' then 
                        CASE
                            WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                            WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                        ELSE ''
                        END

                    WHEN upper(a.statusreminder) = 'PENGGANTIAN SARINGAN HAWA' then 
                            CASE
                                WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                                WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                            ELSE ''
                            END
                               

                
                END) 
                as statusbatas"),
                db::raw("
                (CASE 
                WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI PERSNELING' then 
                    CASE
                        WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                        WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                    ELSE 0
                    END
                
                WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI GARDAN' then 
                    CASE
                        WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                        WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                    ELSE 0
                    END
                
                WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI MESIN' then 
                    CASE
                        WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                        WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                    ELSE 0
                    END
    
                WHEN upper(a.statusreminder) = 'PENGGANTIAN SARINGAN HAWA' then 
                        CASE
                            WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                            WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                        ELSE 0
                        END
                           
    
            
            END) as urutid")
            )
            ->Join(DB::raw("trado  b with (readuncommitted)"), 'a.trado_id', 'b.id')
            ->Join(DB::raw($tempstatus . " e"), 'a.statusreminder',   'e.status')
            // ->leftJoin(DB::raw($Temppergantian . " c"), 'b.id', 'c.trado_id')
            ->leftjoin(DB::raw($Temppergantian . " c"), function ($join) {
                $join->on('c.trado_id', '=', 'b.id');
                $join->on('c.statusreminder', '=', 'e.status');
            })
            // ->Where('b.id', 33)
            ->Where('b.statusaktif', 1);

        // dump(db::table($Tempsaldoreminderoli)->where('trado_id',33)->get());
        // dump(db::table($Temppergantian)->where('trado_id',33)->get());

        // dd($query->get());


        // dd(db::table($Tempsaldoreminderolirekap)->where('nopol','B 9211 BEI')->get());

        DB::table($Tempsaldoreminderolirekap)->insertUsing([
            'nopol',
            'trado_id',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas',
            'urutid',
        ], $query);

        $query = db::table($Tempsaldoreminderolirekap)->from(db::raw($Tempsaldoreminderolirekap . " a"))
            ->select(
                'a.nopol',
                'a.trado_id',
                'a.tanggal',
                'a.status',
                'a.km',
                'a.kmperjalanan',
                'a.statusbatas',
            )

            ->orderby('a.urutid', 'desc');


        // dd(db::table($Tempsaldoreminderolirekap)->where('nopol','B 9211 BEI')->get());
        // dd($query->get());
        return $query;
    }

    public function reminderemailolimesin()
    {

        $batasmax = Parameter::where('grp', 'BATAS MAX PERGANTIAN OLI')->where('subgrp', 'BATAS MAX PERGANTIAN OLI')->first()->text ?? '1000';

        $tempolimesin = '##tempolimesin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempolimesin, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        $tempgorupby = '##tempgorupby' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgorupby, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        DB::table($tempgorupby)->insertUsing([
            'nopol',
            'trado_id',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $this->getdata());


        $querytempgorupby = DB::table($tempgorupby)->select(
            'nopol',
            db::raw('max(tanggal) as tanggal'),
            'status',
            db::raw('max(km) as km'),
            db::raw('max(kmperjalanan) as kmperjalanan'),
            db::raw('max(isnull(statusbatas,0)) as statusbatas'),

        )->groupBy(
            'nopol',
            'status',
        )->orderBy('statusbatas', 'desc');

        DB::table($tempolimesin)->insertUsing([
            'nopol',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $querytempgorupby);

        DB::delete(DB::raw("delete " . $tempolimesin . " from " . $tempolimesin . " as a WHERE a.status not in('PENGGANTIAN OLI MESIN')"));
        DB::delete(DB::raw("delete " . $tempolimesin . " from " . $tempolimesin . " as a WHERE (a.km-a.kmperjalanan)>" . $batasmax));

        $pjlhhariremind = 30;
        $tglremind = DB::select("select format(DATEADD(d," . $pjlhhariremind . ",GETDATE()),'yyyy/MM/dd') as dadd");
        $ptglremind = json_decode(json_encode($tglremind), true)[0]['dadd'];

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

        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';

        $query = db::table($tempolimesin)->from(db::raw($tempolimesin . " a"))
            ->select(
                db::raw("format(getdate(),'yyyy-MM-dd') as tgl"),
                'a.nopol as kodetrado',
                db::raw("format(a.tanggal,'dd-MM-yyyy') as tanggal"),
                db::raw("format(a.km,'#,#0.00') as batasganti"),
                db::raw("format(a.kmperjalanan,'#,#0.00') as kberjalan"),
                db::raw("status as Keterangan"),
                db::raw("(case when a.kmperjalanan>=a.km then 'RED' 
                           when (a.km-a.kmperjalanan)<=" . $batasmax . " then 'YELLOW' 
                           else '' end) as warna"),

                // db::raw("'ryan_vixy1402@yahoo.com' as toemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as ccemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as bccemail"),
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Reminder Penggantian Oli Mesin (" . $cabang . ")' as judul"),
            )
            ->orderby('a.id', 'asc');

        // dd($query->get());
        return $query;
    }

    public function reminderemailolipersneling()
    {
        $batasmax = Parameter::where('grp', 'BATAS MAX PERGANTIAN OLI')->where('subgrp', 'BATAS MAX PERGANTIAN OLI')->first()->text ?? '1000';


        $tempolipersneling = '##tempolipersneling' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempolipersneling, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        $tempgorupby = '##tempgorupby' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgorupby, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        DB::table($tempgorupby)->insertUsing([
            'nopol',
            'trado_id',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $this->getdata());


        $querytempgorupby = DB::table($tempgorupby)->select(
            'nopol',
            db::raw('max(tanggal) as tanggal'),
            'status',
            db::raw('max(km) as km'),
            db::raw('max(kmperjalanan) as kmperjalanan'),
            db::raw('max(isnull(statusbatas,0)) as statusbatas'),

        )->groupBy(
            'nopol',
            'status',
        )->orderBy('statusbatas', 'desc');
        DB::table($tempolipersneling)->insertUsing([
            'nopol',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $querytempgorupby);

        DB::delete(DB::raw("delete " . $tempolipersneling . " from " . $tempolipersneling . " as a WHERE a.status not in('PENGGANTIAN OLI PERSNELING')"));
        DB::delete(DB::raw("delete " . $tempolipersneling . " from " . $tempolipersneling . " as a WHERE (a.km-a.kmperjalanan)>" . $batasmax));



        $pjlhhariremind = 30;
        $tglremind = DB::select("select format(DATEADD(d," . $pjlhhariremind . ",GETDATE()),'yyyy/MM/dd') as dadd");
        $ptglremind = json_decode(json_encode($tglremind), true)[0]['dadd'];

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

        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';

        $query = db::table($tempolipersneling)->from(db::raw($tempolipersneling . " a"))
            ->select(
                db::raw("format(getdate(),'yyyy-MM-dd') as tgl"),
                'a.nopol as kodetrado',
                db::raw("format(a.tanggal,'dd-MM-yyyy') as tanggal"),
                db::raw("format(a.km,'#,#0.00') as batasganti"),
                db::raw("format(a.kmperjalanan,'#,#0.00') as kberjalan"),
                db::raw("status as Keterangan"),
                db::raw("(case when a.kmperjalanan>=a.km then 'RED' 
                           when (a.km-a.kmperjalanan)<=" . $batasmax . " then 'YELLOW' 
                           else '' end) as warna"),

                // db::raw("'ryan_vixy1402@yahoo.com' as toemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as ccemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as bccemail"),
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Reminder Penggantian Oli Persneling (" . $cabang . ")' as judul"),
            )
            ->orderby('a.id', 'asc');

        // dd($query->get());
        return $query;
    }

    public function reminderemailoligardan()
    {
        $batasmax = Parameter::where('grp', 'BATAS MAX PERGANTIAN OLI')->where('subgrp', 'BATAS MAX PERGANTIAN OLI')->first()->text ?? '1000';

        $tempoligardan = '##tempoligardan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempoligardan, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        $tempgorupby = '##tempgorupby' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgorupby, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        DB::table($tempgorupby)->insertUsing([
            'nopol',
            'trado_id',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $this->getdata());


        $querytempgorupby = DB::table($tempgorupby)->select(
            'nopol',
            db::raw('max(tanggal) as tanggal'),
            'status',
            db::raw('max(km) as km'),
            db::raw('max(kmperjalanan) as kmperjalanan'),
            db::raw('max(isnull(statusbatas,0)) as statusbatas'),

        )->groupBy(
            'nopol',
            'status',
        )->orderBy('statusbatas', 'desc');

        DB::table($tempoligardan)->insertUsing([
            'nopol',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $querytempgorupby);

        DB::delete(DB::raw("delete " . $tempoligardan . " from " . $tempoligardan . " as a WHERE a.status not in('PENGGANTIAN OLI GARDAN')"));
        DB::delete(DB::raw("delete " . $tempoligardan . " from " . $tempoligardan . " as a WHERE (a.km-a.kmperjalanan)>" . $batasmax));


        $pjlhhariremind = 30;
        $tglremind = DB::select("select format(DATEADD(d," . $pjlhhariremind . ",GETDATE()),'yyyy/MM/dd') as dadd");
        $ptglremind = json_decode(json_encode($tglremind), true)[0]['dadd'];

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

        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';

        $query = db::table($tempoligardan)->from(db::raw($tempoligardan . " a"))
            ->select(
                db::raw("format(getdate(),'yyyy-MM-dd') as tgl"),
                'a.nopol as kodetrado',
                db::raw("format(a.tanggal,'dd-MM-yyyy') as tanggal"),
                db::raw("format(a.km,'#,#0.00') as batasganti"),
                db::raw("format(a.kmperjalanan,'#,#0.00') as kberjalan"),
                db::raw("status as Keterangan"),
                db::raw("(case when a.kmperjalanan>=a.km then 'RED' 
                           when (a.km-a.kmperjalanan)<=" . $batasmax . " then 'YELLOW' 
                           else '' end) as warna"),

                // db::raw("'ryan_vixy1402@yahoo.com' as toemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as ccemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as bccemail"),
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Reminder Penggantian Oli Gardan (" . $cabang . ")' as judul"),
            )
            ->orderby('a.id', 'asc');

        // dd($query->get());
        return $query;
    }

    public function reminderemailsaringanhawa()
    {
        $batasmax = Parameter::where('grp', 'BATAS MAX PERGANTIAN OLI')->where('subgrp', 'BATAS MAX PERGANTIAN OLI')->first()->text ?? '1000';

        $tempsaringanhawa = '##tempsaringanhawa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsaringanhawa, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        $tempgorupby = '##tempgorupby' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempgorupby, function ($table) {
            $table->id();
            $table->longText('nopol')->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 100)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->integer('statusbatas')->nullable();
        });

        DB::table($tempgorupby)->insertUsing([
            'nopol',
            'trado_id',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $this->getdata());


        $querytempgorupby = DB::table($tempgorupby)->select(
            'nopol',
            db::raw('max(tanggal) as tanggal'),
            'status',
            db::raw('max(km) as km'),
            db::raw('max(kmperjalanan) as kmperjalanan'),
            db::raw('max(isnull(statusbatas,0)) as statusbatas'),

        )->groupBy(
            'nopol',
            'status',
        )->orderBy('statusbatas', 'desc');

        DB::table($tempsaringanhawa)->insertUsing([
            'nopol',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas'
        ], $querytempgorupby);

        DB::delete(DB::raw("delete " . $tempsaringanhawa . " from " . $tempsaringanhawa . " as a WHERE a.status not in('PENGGANTIAN SARINGAN HAWA')"));
        DB::delete(DB::raw("delete " . $tempsaringanhawa . " from " . $tempsaringanhawa . " as a WHERE (a.km-a.kmperjalanan)>" . $batasmax));


        $pjlhhariremind = 30;
        $tglremind = DB::select("select format(DATEADD(d," . $pjlhhariremind . ",GETDATE()),'yyyy/MM/dd') as dadd");
        $ptglremind = json_decode(json_encode($tglremind), true)[0]['dadd'];

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

        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';

        $query = db::table($tempsaringanhawa)->from(db::raw($tempsaringanhawa . " a"))
            ->select(
                db::raw("format(getdate(),'yyyy-MM-dd') as tgl"),
                'a.nopol as kodetrado',
                db::raw("format(a.tanggal,'dd-MM-yyyy') as tanggal"),
                db::raw("format(a.km,'#,#0.00') as batasganti"),
                db::raw("format(a.kmperjalanan,'#,#0.00') as kberjalan"),
                db::raw("status as Keterangan"),
                db::raw("(case when a.kmperjalanan>=a.km then 'RED' 
                           when (a.km-a.kmperjalanan)<=" . $batasmax . " then 'YELLOW' 
                           else '' end) as warna"),

                // db::raw("'ryan_vixy1402@yahoo.com' as toemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as ccemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as bccemail"),
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Reminder Penggantian Saringan Hawa (" . $cabang . ")' as judul"),
            )
            ->orderby('a.id', 'asc');

        // dd($query->get());
        return $query;
    }

    public function reminderemailservicerutin()
    {

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

        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';

        //

        $tempdatatrado = '##tempdatatrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatatrado, function ($table) {
            $table->id();
            $table->unsignedBigInteger('trado_id')->nullable();
        });

        $querydatatrado = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.id as trado_id',
            )
            ->whereRaw("a.statusaktif=1");

        DB::table($tempdatatrado)->insertUsing([
            'trado_id',
        ], $querydatatrado);

        $tempdataservicein = '##tempdataservicein' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdataservicein, function ($table) {
            $table->id();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->datetime('tglbukti')->nullable();
        });


        $querydataservicein = db::table($tempdatatrado)->from(db::raw($tempdatatrado . " a"))
            ->select(
                'a.trado_id',
                db::raw("max(b.tglbukti) as tglbukti")
            )
            ->join(db::raw("serviceinheader b with (readuncommitted)"), 'a.trado_id', 'b.trado_id')
            ->groupBy('a.trado_id');

        DB::table($tempdataservicein)->insertUsing([
            'trado_id',
            'tglbukti',
        ], $querydataservicein);


        $tempjadwalservice = '##tempjadwalservice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempjadwalservice, function ($table) {
            $table->id();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->datetime('tglservice')->nullable();
            $table->datetime('tglserviceberikut')->nullable();
        });


        $queryloop = db::table($tempdatatrado)->from(db::raw($tempdatatrado . " a "))
            ->select(
                'a.trado_id',
                'b.tglbukti',
            )
            ->leftjoin(db::raw($tempdataservicein . " b"), 'a.trado_id', 'b.trado_id')
            ->whereRaw("year(isnull(b.tglbukti,'1900/1/1'))<>1900")
            ->orderBy('a.trado_id', 'asc')
            ->get();

        $datadetail = json_decode($queryloop, true);
        foreach ($datadetail as $item) {
            $chitung = 0;
            $dhitung = 0;
            $btglservice = $item['tglbukti'];
            while ($chitung <= 30) {
                $btglservice = date('Y-m-d', strtotime($btglservice . '+1 days'));

                $datepart = DB::select("select datepart(dw," . $btglservice . ") as dpart");
                $dpart = json_decode(json_encode($datepart), true)[0]['dpart'];

                $querylibur = DB::table('harilibur')->from(
                    db::raw("harilibur as a with (readuncommitted)")
                )
                    ->select(
                        'tgl'
                    )->where('tgl', '=', $btglservice)
                    ->first();

                if (($dpart != 1)  && (!isset($querylibur))) {
                    $dhitung = $dhitung + 1;
                    if ($dhitung <= 14) {
                        $ctglservice = $btglservice;
                    }
                }
                $chitung = $chitung + 1;
            }
            DB::table($tempjadwalservice)->insert(
                [
                    'trado_id' => $item['trado_id'],
                    'tglservice' => $item['tglbukti'],
                    'tglserviceberikut' => $ctglservice,
                ]
            );
        }



        $query = db::table($tempjadwalservice)->from(db::raw($tempjadwalservice . " a"))
            ->select(
                'b.kodetrado',
                db::raw("format(a.tglservice,'dd-MM-yyyy') as tanggaldari"),
                db::raw("format(a.tglserviceberikut,'dd-MM-yyyy') as tanggalsampai"),
                db::raw("'' as keterangan"),
                db::raw("'yellow' as warna"),
                // db::raw("'ryan_vixy1402@yahoo.com' as toemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as ccemail"),
                // db::raw("'ryan_vixy1402@yahoo.com' as bccemail"),
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Jadwal Service Rutin (" . $cabang . ")' as judul"),
            )

            ->join(db::raw("trado b with (readuncommitted)"), 'a.trado_id', 'b.id')
            ->orderBy('a.tglserviceberikut', 'asc');

        return $query;

        // 
    }



    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusbatas') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tanggal') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'km' || $filters['field'] == 'kmperjalanan') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusbatas') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tanggal') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'km' || $filters['field'] == 'kmperjalanan') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function getdata2($trado_id)
    {

        $batasgardan = Parameter::where('grp', 'BATAS PERGANTIAN OLI GARDAN')->where('subgrp', 'BATAS PERGANTIAN OLI GARDAN')->first()->text;
        $bataspersneling = Parameter::where('grp', 'BATAS PERGANTIAN OLI PERSNELING')->where('subgrp', 'BATAS PERGANTIAN OLI PERSNELING')->first()->text;
        $batasmesin = Parameter::where('grp', 'BATAS PERGANTIAN OLI MESIN')->where('subgrp', 'BATAS PERGANTIAN OLI MESIN')->first()->text;
        $batassaringanhawa = Parameter::where('grp', 'BATAS PERGANTIAN SARINGAN HAWA')->where('subgrp', 'BATAS PERGANTIAN SARINGAN HAWA')->first()->text;
        $batasmax = Parameter::where('grp', 'BATAS MAX PERGANTIAN OLI')->where('subgrp', 'BATAS MAX PERGANTIAN OLI')->first()->text;

        $sudahLewat = Parameter::where('grp', 'STATUS PERGANTIAN')->where('text', 'SUDAH MELEWATI BATAS')->first()->id;
        $hampirLewat = Parameter::where('grp', 'STATUS PERGANTIAN')->where('text', 'HAMPIR MELEWATI BATAS')->first()->id;

        $tempstatus = '##tempstatus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempstatus, function ($table) {
            $table->longText('status')->nullable();
            $table->double('batas')->nullable();
        });

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI GARDAN',
            'batas' => $batasgardan,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI PERSNELING',
            'batas' => $bataspersneling,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN OLI MESIN',
            'batas' => $batasmesin,
        ]);

        DB::table($tempstatus)->insert([
            'status' => 'PENGGANTIAN SARINGAN HAWA',
            'batas' => $batassaringanhawa,
        ]);

        $Tempsaldoreminderoli = '##Tempsaldoreminderoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempsaldoreminderoli, function ($table) {
            $table->integer('id');
            $table->integer('trado_id')->nullable();
            $table->string('nopol', 1000)->nullable();
            $table->string('statusreminder', 100)->nullable();
            $table->date('tglawal')->nullable();
            $table->date('tglsampai')->nullable();
            $table->double('jarak', 15, 2)->nullable();
            $table->double('jaraktransaksi', 15, 2)->nullable();
        });


        $querysaldo = db::table("saldoreminderpergantian")->from(DB::raw("saldoreminderpergantian a with (readuncommitted)"))
            ->select(
                'a.id',
                db::raw("isnull(b.id,0) as trado_id"),
                'a.nopol',
                'a.statusreminder',
                'a.tglawal',
                'a.tglsampai',
                'a.jarak',
            )
            ->leftjoin(db::raw("trado b with (readuncommitted)"), 'a.trado_id', 'b.id')
            ->where('b.id', $trado_id)
            ->where('b.statusaktif', 1)
            ->orderby('a.id', 'asc');

        //   dd($querysaldo->get());

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $querysaldo);

        $tglsaldo = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'SALDO')
            ->where('a.subgrp', 'SALDO')
            ->first()->text ?? '1900-01-01';

        $tglsaldoawal = date("Y-m-d", strtotime("+1 day", strtotime($tglsaldo)));

        $Tempservicerutin = '##Tempservicerutin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempservicerutin, function ($table) {
            $table->integer('id');
        });
        $queryservicerutin = db::table("parameter")->from(DB::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.id',
            )
            ->where('a.grp', 'STATUS SERVICE RUTIN')
            ->where('a.subgrp', 'STATUS SERVICE RUTIN')
            ->orderby('a.id', 'asc');

        DB::table($Tempservicerutin)->insertUsing([
            'id',
        ], $queryservicerutin);


        $pengeluaranstok_id = 1;


        $Temppergantian = '##Temppergantian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temppergantian, function ($table) {
            $table->string('nobukti', 100);
            $table->integer('trado_id');
            $table->string('statusreminder', 100)->nullable();
            $table->date('tgl')->nullable();
        });

        $querypergantian = db::table("pengeluaranstokheader")->from(DB::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                db::raw("max(a.nobukti) as nobukti"),
                'a.trado_id',
                db::raw("(case when e.text='PERGANTIAN BATERE' then 'PENGGANTIAN AKI'
                                when e.text='PERGANTIAN OLI GARDAN' then 'PENGGANTIAN OLI GARDAN'
                                when e.text='PERGANTIAN OLI MESIN' then 'PENGGANTIAN OLI MESIN'
                                when e.text='PERGANTIAN OLI PERSNELING' then 'PENGGANTIAN OLI PERSNELING'
                                when e.text='PERGANTIAN SARINGAN HAWA' then 'PENGGANTIAN SARINGAN HAWA'
                    else '' end) as statusreminder
                "),
                db::raw("max(a.tglbukti) as tgl"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->join(db::raw($Tempservicerutin . " d "), 'c.statusservicerutin', 'd.id')
            ->join(db::raw("parameter e with (readuncommitted)"), 'd.id', 'e.id')
            ->where('a.pengeluaranstok_id', $pengeluaranstok_id)
            // ->whereraw("isnull(a.trado_id,0)<>0")
            ->where('a.trado_id', $trado_id)
            ->groupBy('a.trado_id')
            ->groupBy('e.text');

        // dd($querypergantian->get());

        DB::table($Temppergantian)->insertUsing([
            'nobukti',
            'trado_id',
            'statusreminder',
            'tgl',
        ], $querypergantian);

        DB::update(DB::raw("UPDATE " . $Tempsaldoreminderoli . " SET tglawal=b.tgl,jarak=0 
        from " . $Tempsaldoreminderoli . " a inner join " . $Temppergantian . " b on a.trado_id=b.trado_id and a.statusreminder=b.statusreminder 
        "));

        // dump(db::table($Temppergantian)->get());
        // dd(db::table($Tempsaldoreminderoli)->whereraw("trado_id=4")->get());


        $param1 = 'PENGGANTIAN OLI MESIN';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);
        //         

        // PENGGANTIAN OLI PERSNELING
        // PENGGANTIAN SARINGAN HAWA

        $param1 = 'PENGGANTIAN OLI GARDAN';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);

        // 
        // PENGGANTIAN SARINGAN HAWA

        $param1 = 'PENGGANTIAN OLI PERSNELING';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);

        // 

        $param1 = 'PENGGANTIAN SARINGAN HAWA';
        $queryrimderernonsaldo = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                db::raw("0 as id"),
                'a.id as trado_id',
                'a.kodetrado as nopol',
                db::raw("'" . $param1 . "' as statusreminder"),
                db::raw("'1900/1/1' as tglawal"),
                db::raw("'1900/1/1' as tglsampai"),
                db::raw("0 as jarak"),
            )
            ->leftjoin(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->whereraw("isnull(b.trado_id,0)=0");

        DB::table($Tempsaldoreminderoli)->insertUsing([
            'id',
            'trado_id',
            'nopol',
            'statusreminder',
            'tglawal',
            'tglsampai',
            'jarak',
        ], $queryrimderernonsaldo);

        // dd(db::table($TempPENGGANTIAN)->get());

        // dd(db::table($Tempsaldoreminderoli)->get());

        $Temptradotransakdi = '##Temptradotransakdi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Temptradotransakdi, function ($table) {
            $table->integer('trado_id');
            $table->double('jarak', 15, 2)->nullable();
            $table->date('tgl')->nullable();
            $table->string('statusreminder', 100)->nullable();
        });


        $param1 = 'PENGGANTIAN OLI MESIN';
        $querytradoolimesin = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN OLI MESIN' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->where('a.trado_id', $trado_id)
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradoolimesin);

        $param1 = 'PENGGANTIAN OLI GARDAN';
        $querytradooligardan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN OLI GARDAN' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->where('a.trado_id', $trado_id)
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradooligardan);

        $param1 = 'PENGGANTIAN OLI PERSNELING';
        $querytradoolipersneling = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN OLI PERSNELING' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->where('a.trado_id', $trado_id)
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradoolipersneling);

        $param1 = 'PENGGANTIAN SARINGAN HAWA';
        $querytradosaringanhawa = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN SARINGAN HAWA' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->where('a.trado_id', $trado_id)
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradosaringanhawa);

        $param1 = 'PENGGANTIAN AKI';
        $querytradoaki = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw("sum(a.jarak) as jarak"),
                db::raw("max(a.tglbukti) as tgl"),
                db::raw("'PENGGANTIAN AKI' as statusreminder")
            )
            ->join(DB::raw($Tempsaldoreminderoli . " as b"), function ($join) use ($param1) {
                $join->on('a.trado_id', '=', 'b.trado_id');
                $join->on('b.statusreminder', '=', DB::raw("'" . $param1 . "'"));
            })
            ->where('a.trado_id', $trado_id)
            ->whereRaw("a.tglBukti>=b.tglawal")
            ->groupby('a.trado_id');


        DB::table($Temptradotransakdi)->insertUsing([
            'trado_id',
            'jarak',
            'tgl',
            'statusreminder',
        ], $querytradoaki);

        DB::update(DB::raw("UPDATE " . $Tempsaldoreminderoli . " SET jaraktransaksi=b.jarak,tglsampai=b.tgl
        from " . $Tempsaldoreminderoli . " a inner join " . $Temptradotransakdi . " b on a.trado_id=b.trado_id and upper(a.statusreminder)=upper(b.statusreminder) 
        "));

        // dump(db::table($Temptradotransakdi)->whereraw("trado_id=3")->get());
        // dd(db::table($Tempsaldoreminderoli)->whereraw("trado_id=3")->get());

        //         

        // 
        // 

        // dd(db::table($Tempsaldoreminderoli)->get());
        // dd(db::table($tempstatus)->get());


        $Tempsaldoreminderolirekap = '##Tempsaldoreminderolirekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Tempsaldoreminderolirekap, function ($table) {
            $table->string('nopol', 1000)->nullable();
            $table->integer('trado_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 1000)->nullable();
            $table->double('km', 15, 2)->nullable();
            $table->double('kmperjalanan', 15, 2)->nullable();
            $table->string('statusbatas', 100)->nullable();
            $table->integer('urutid')->nullable();
        });

        $query = DB::table($Tempsaldoreminderoli)->from(DB::raw($Tempsaldoreminderoli . " a "))
            ->select(
                'a.nopol',
                'a.trado_id',
                db::raw("isnull(c.tgl,'2023/9/30') as tanggal"),
                'a.statusreminder as status',
                DB::raw("(case 
                    when a.statusreminder = 'PENGGANTIAN OLI GARDAN' then $batasgardan 
                    when a.statusreminder = 'PENGGANTIAN OLI PERSNELING' then $bataspersneling
                    when a.statusreminder = 'PENGGANTIAN OLI MESIN' then $batasmesin
                    else $batassaringanhawa end) 
                    
                    as km"),
                db::raw("(isnull(a.jarak,0)+isnull(a.jaraktransaksi,0)) as kmperjalanan"),
                DB::raw("(CASE 
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI PERSNELING' then 
                        CASE
                            WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                            WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                        ELSE ''
                        END
                    
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI GARDAN' then 
                        CASE
                            WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                            WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                        ELSE ''
                        END
                    
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI MESIN' then 
                        CASE
                            WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                            WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                        ELSE ''
                        END

                    WHEN upper(a.statusreminder) = 'PENGGANTIAN SARINGAN HAWA' then 
                            CASE
                                WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then $hampirLewat
                                WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then $sudahLewat
                            ELSE ''
                            END
                               

                
                END) 
                as statusbatas"),
                db::raw("
                (CASE 
                WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI PERSNELING' then 
                    CASE
                        WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                        WHEN ($bataspersneling - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                    ELSE 0
                    END
                
                WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI GARDAN' then 
                    CASE
                        WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                        WHEN ($batasgardan - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                    ELSE 0
                    END
                
                WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI MESIN' then 
                    CASE
                        WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                        WHEN ($batasmesin - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                    ELSE 0
                    END
    
                WHEN upper(a.statusreminder) = 'PENGGANTIAN SARINGAN HAWA' then 
                        CASE
                            WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= $batasmax and ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) > 0 then 1
                            WHEN ($batassaringanhawa - (isnull(a.jarak,0)+isnull(a.jaraktransaksi,0))) <= 0 then 2
                        ELSE 0
                        END
                           
    
            
            END) as urutid")
            )
            ->Join(DB::raw("trado  b with (readuncommitted)"), 'a.trado_id', 'b.id')
            ->Join(DB::raw($tempstatus . " e"), 'a.statusreminder',   'e.status')
            // ->leftJoin(DB::raw($Temppergantian . " c"), 'b.id', 'c.trado_id')
            ->leftjoin(DB::raw($Temppergantian . " c"), function ($join) {
                $join->on('c.trado_id', '=', 'b.id');
                $join->on('c.statusreminder', '=', 'e.status');
            })
            ->Where('b.id', $trado_id)
            ->Where('b.statusaktif', 1);

        // dump(db::table($Tempsaldoreminderoli)->where('trado_id',33)->get());
        // dump(db::table($Temppergantian)->where('trado_id',33)->get());

        // dd($query->get());


        // dd(db::table($Tempsaldoreminderolirekap)->where('nopol','B 9211 BEI')->get());

        DB::table($Tempsaldoreminderolirekap)->insertUsing([
            'nopol',
            'trado_id',
            'tanggal',
            'status',
            'km',
            'kmperjalanan',
            'statusbatas',
            'urutid',
        ], $query);

        $query = db::table($Tempsaldoreminderolirekap)->from(db::raw($Tempsaldoreminderolirekap . " a"))
            ->select(
                'a.nopol',
                'a.trado_id',
                'a.tanggal',
                'a.status',
                'a.km',
                'a.kmperjalanan',
                'a.statusbatas',
            )

            ->orderby('a.urutid', 'desc');


        // dd(db::table($Tempsaldoreminderolirekap)->where('nopol','B 9211 BEI')->get());
        // dd($query->get());
        return $query;
    }
}
