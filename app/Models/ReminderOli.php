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

            DB::table($temtabel)->insertUsing([
                'nopol',
                'tanggal',
                'status',
                'km',
                'kmperjalanan',
                'statusbatas'
            ], $this->getdata($status));
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
            $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
            // dd($query->toSql());
            $this->paginate($query);
        }

        $data = $query->get();


        // } else {
        //     $data = [];
        // }

        return $data;
    }

    public function getdata($status)
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
            ->leftjoin(db::raw("trado b with (readuncommitted)"), 'a.nopol', 'b.kodetrado')
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
            $table->integer('trado_id');
            $table->string('statusreminder', 100);
            $table->date('tgl');
        });

        $querypergantian = db::table("pengeluaranstokheader")->from(DB::raw("pengeluaranstokheader a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                'e.text as statusreminder',
                db::raw("max(a.tglbukti) as tgl"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
            ->join(db::raw($Tempservicerutin . " d "), 'c.statusservicerutin', 'd.id')
            ->join(db::raw("parameter e with (readuncommitted)"), 'd.id', 'e.id')
            ->where('a.pengeluaranstok_id', $pengeluaranstok_id)
            ->groupBy('a.trado_id')
            ->groupBy('e.text');

        // dd($querypergantian->get());

        DB::table($Temppergantian)->insertUsing([
            'trado_id',
            'statusreminder',
            'tgl',
        ], $querypergantian);

        DB::update(DB::raw("UPDATE " . $Tempsaldoreminderoli . " SET tglawal=b.tgl,jarak=0 
        from " . $Tempsaldoreminderoli . " a inner join " . $Temppergantian . " b on a.trado_id=b.trado_id and a.statusreminder=b.statusreminder 
        "));

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
            $table->double('jarak',15,2);
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

        $query = DB::table($Tempsaldoreminderoli)->from(DB::raw($Tempsaldoreminderoli . " a "))
            ->select(
                'a.nopol',
                'a.tglsampai as tanggal',
                'a.statusreminder as status',
                DB::raw("(case 
                    when a.statusreminder = 'PENGGANTIAN OLI GARDAN' then $batasgardan 
                    when a.statusreminder = 'PENGGANTIAN OLI PERSNELING' then $bataspersneling
                    when a.statusreminder = 'PENGGANTIAN OLI MESIN' then $batasmesin
                    else $batassaringanhawa end) 
                    
                    as km"),
                db::raw("isnull(a.jarak,0)+isnull(a.jaraktransaksi,0) as kmperjalanan"),
                DB::raw("(CASE 
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI PERSNELING' then 
                        CASE
                            WHEN ($batasgardan - a.jarak) <= $batasmax and ($batasgardan - a.jarak) > 0 then $hampirLewat
                            WHEN ($batasgardan - a.jarak) <= 0 then $sudahLewat
                        ELSE ''
                        END
                    
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI GARDAN' then 
                        CASE
                            WHEN ($bataspersneling - a.jarak) <= $batasmax and ($bataspersneling - a.jarak) > 0 then $hampirLewat
                            WHEN ($bataspersneling - a.jarak) <= 0 then $sudahLewat
                        ELSE ''
                        END
                    
                    WHEN upper(a.statusreminder) = 'PENGGANTIAN OLI MESIN' then 
                        CASE
                            WHEN ($batasmesin - a.jarak) <= $batasmax and ($batasmesin - a.jarak) > 0 then $hampirLewat
                            WHEN ($batasmesin - a.jarak) <= 0 then $sudahLewat
                        ELSE ''
                        END

                    WHEN upper(a.statusreminder) = 'PENGGANTIAN SARINGAN HAWA' then 
                            CASE
                                WHEN ($batassaringanhawa - a.jarak) <= $batasmax and ($batassaringanhawa - a.jarak) > 0 then $hampirLewat
                                WHEN ($batassaringanhawa - a.jarak) <= 0 then $sudahLewat
                            ELSE ''
                            END
                               

                
                END) 
                as statusbatas"),
            )
            ->Join(DB::raw("trado  b with (readuncommitted)"), 'a.trado_id', 'b.id')
            ->Join(DB::raw("$tempstatus with (readuncommitted)"), 'a.statusreminder', $tempstatus . '.status')
            ->Where('b.statusaktif', 1);

        // dd($query->get());
        return $query;
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
}
