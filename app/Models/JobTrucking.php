<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobTrucking extends MyModel
{
    use HasFactory;


    public function get()
    {

        $this->setRequestParameters();
        //  dd(request()->trado_id);
        // dump(request()->container_id);
        // dd(request()->jenisorder_id);

        $userid = auth('api')->user()->id;

        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.user_id', $userid);

        $tempmandordetaillogin = '##mandordetaillogin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetaillogin, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });
        DB::table($tempmandordetaillogin)->insertUsing([
            'mandor_id',
        ],  $querymandor);

        $querymandor = DB::table('mandordetail as a')
            ->leftJoin(DB::raw($tempmandordetaillogin . ' as b'), 'a.mandor_id', '=', 'b.mandor_id')
            ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
            ->select('a.mandor_id');


        $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        DB::table($tempmandordetail)->insertUsing([
            'mandor_id',
        ],  $querymandor);

        // dd(db::table($tempmandordetail)->get());
        // dd(request()->tglbukti);            
        $edit = request()->edit ?? 'false';
        $idtrip = request()->idtrip ?? '';
        $container_id = request()->container_id ?? 0;
        $jenisorder_id = request()->jenisorder_id ?? 0;
        $gandengan_id = request()->gandengan_id ?? 0;
        $pelanggan_id = request()->pelanggan_id ?? 0;
        $dari_id = request()->dari_id ?? 0;
        $tglbukti = request()->tglbukti ?? '02-04-2024';
        $date = date('Y-m-d', strtotime($tglbukti));
        if (request()->tarif_id == 'undefined') {
            $tarif_id = 0;
        } else {
            $tarif_id = request()->tarif_id ?? 0;
        }
        $jobBedaTanggal = (new Parameter())->cekText('JOBTRUCKING', 'BEDA TANGGAL') ?? 'TIDAK';
        $statusgandenganid = (new Parameter())->cekId('STATUS GANDENGAN', 'STATUS GANDENGAN', 'TINGGAL GANDENGAN') ?? 0;

        $tempTripAsal = '##tempTripAsal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTripAsal, function ($table) {
            $table->string('nobukti_tripasal', 50)->nullable();
        });

        $querytripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.nobukti_tripasal'
            )
            ->whereraw("isnull(a.nobukti_tripasal,'')<>''")
            ->groupBY('a.nobukti_tripasal');

        DB::table($tempTripAsal)->insertUsing([
            'nobukti_tripasal',
        ],  $querytripasal);

        $querynonjobtampil = db::table($tempTripAsal)->from(db::raw($tempTripAsal . " a"))
            ->select(
                'b.jobtrucking',
            )->join(db::raw("suratpengantar b with (readuncommitted)"), 'a.nobukti_tripasal', 'b.nobukti')
            ->groupBy('b.jobtrucking');

        if ($idtrip != '' && $edit == 'true') {
            $getNobukti = db::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('id', $idtrip)->first();
            $querynonjobtampil->where('b.nobukti', '!=', $getNobukti->nobukti);
        }

        $tempNonTampilJobTrucking = '##tempNonTampilJobTrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNonTampilJobTrucking, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
        });

        DB::table($tempNonTampilJobTrucking)->insertUsing([
            'jobtrucking',
        ],  $querynonjobtampil);



        $trado_id = request()->trado_id ?? 0;
        $statuscontainer_id = request()->statuscontainer_id ?? 0;
        $tripasal = request()->tripasal ?? '';



        $parameter = new Parameter();
        $statusfullempty = $parameter->cekText('STATUS CONTAINER', 'STATUS CONTAINER FULL EMPTY') ?? 0;




        $statusgerobak = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS GEROBAK')
            ->where('a.subgrp', '=', 'STATUS GEROBAK')
            ->where('a.text', '=', 'BUKAN GEROBAK')
            ->first();

        $statuslongtrip = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS LONGTRIP')
            ->where('a.subgrp', '=', 'STATUS LONGTRIP')
            ->where('a.text', '=', 'LONGTRIP')
            ->first();

        $pelabuhan = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('a.grp', '=', 'PELABUHAN CABANG')
            ->where('a.subgrp', '=', 'PELABUHAN CABANG')
            ->first();
        $pelabuhanAwal = $pelabuhan->text;
        $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
        $pelabuhan = $pelabuhan->text . ',' . $idkandang;

        $isPulangLongtrip = request()->isPulangLongtrip ?? '';
        if ($isPulangLongtrip == true) {
            if ($tripasal != '') {
                goto pulanglongtrip;
            }
        }


        $temprekap = '##temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekap, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('dari_id')->nullable();
            $table->integer('sampai_id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->integer('container_id')->nullable();
            $table->integer('jenisorder_id')->nullable();
            $table->integer('pelanggan_id')->nullable();
            $table->integer('gandengan_id')->nullable();
            $table->integer('tarif_id')->nullable();
            $table->integer('statuslongtrip')->nullable();
        });


        $query = DB::table('trado')->from(
            DB::raw("trado as a with (readuncommitted)")
        )
            ->select(
                'a.keterangan'
            )
            ->where('a.id', '=', $trado_id)
            ->where('a.statusgerobak', '=', $statusgerobak->id)
            ->first();


        $tempselesai = '##tempselesai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempselesai, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
        });

        $isGandengan = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.text'
            )
            ->where('a.grp', '=', 'JOBTRUCKING')
            ->where('a.subgrp', '=', 'GANDENGAN')
            ->first();

        if ($isGandengan->text == 'TIDAK') {
            goto tidakgandengan;
        }
        if (isset($query)) {






            // START GET TRIP PULANG
            $queryjob = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking'
                )
                ->join(DB::raw("orderantrucking as b with(readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
                ->leftjoin(db::raw($tempNonTampilJobTrucking . " c"), 'a.jobtrucking', 'c.jobtrucking')
                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.gandengan_id', '=', $gandengan_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id)
                ->whereRaw("isnull(a.jobtrucking,'')<>''")
                ->whereRaw("a.sampai_id in (" . $pelabuhanAwal . ") and isnull(B.statusapprovalbukatrip,4)=4")
                ->whereRaw("isnull(c.jobtrucking,'')=''")
                ->where('a.statusgandengan', $statusgandenganid)
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");
            // ->where('a.sampai_id', '=', $pelabuhan->text);

            // dd($queryjob->toSql(), db::table($tempNonTampilJobTrucking)->get());

            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);


            $queryjob = DB::table('saldosuratpengantar')->from(
                DB::raw("saldosuratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking'
                )
                ->join(DB::raw("saldoorderantrucking as b with(readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
                ->leftjoin(db::raw($tempNonTampilJobTrucking . " c"), 'a.jobtrucking', 'c.jobtrucking')
                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.gandengan_id', '=', $gandengan_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id)
                ->whereRaw("isnull(a.jobtrucking,'')<>''")
                ->whereRaw("a.sampai_id in (" . $pelabuhanAwal . ") and isnull(B.statusapprovalbukatrip,4)=4")
                ->whereRaw("isnull(c.jobtrucking,'')=''")
                ->where('a.statusgandengan', $statusgandenganid)
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");

            // ->where('a.sampai_id', '=', $pelabuhan->text);

            // dd($queryjob->get());

            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);
            // END TRIP PULANG



            // START TRIP KANDANG
            $tempstartkandang = '##tempstartkandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstartkandang, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nobukti', 1000)->nullable();
            });
            $getQueryStartKandang = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('jobtrucking', 'nobukti')
                ->where('statuslongtrip', 66)
                ->where('dari_id', $idkandang)
                ->where('statusgandengan', $statusgandenganid)
                ->where('nobukti_tripasal', '!=', '');

            DB::table($tempstartkandang)->insertUsing([
                'jobtrucking',
                'nobukti',
            ], $getQueryStartKandang);

            $temptrippulang = '##temptrippulang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptrippulang, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nobukti', 1000)->nullable();
            });
            $getQueryPulang = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('jobtrucking', 'nobukti')
                ->where('statusgandengan', $statusgandenganid)
                ->where('sampai_id', 1);

            if ($idtrip != '' && $edit == 'true') {
                $getQueryPulang->where('nobukti', '!=', $getNobukti->nobukti);
            }

            DB::table($temptrippulang)->insertUsing([
                'jobtrucking',
                'nobukti',
            ], $getQueryPulang);

            $tempKandangBelumSelesai = '##tempKandangBelumSelesai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempKandangBelumSelesai, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nobukti', 1000)->nullable();
            });
            $getQueryKandanBelumSelesai = DB::table("$tempstartkandang")->from(DB::raw("$tempstartkandang as a with (readuncommitted)"))
                ->select('a.jobtrucking', 'a.nobukti')
                ->leftJoin(DB::raw("$temptrippulang as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                ->whereRaw("isnull(b.jobtrucking, '')=''");

            DB::table($tempKandangBelumSelesai)->insertUsing([
                'jobtrucking',
                'nobukti',
            ], $getQueryKandanBelumSelesai);

            $querydata1 = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'a.supir_id',
                    'a.trado_id',
                    'a.dari_id',
                    'a.sampai_id',
                    'a.nobukti',
                    'a.container_id',
                    'a.jenisorder_id',
                    'a.pelanggan_id',
                    'a.gandengan_id',
                    'a.tarif_id',
                    'a.statuslongtrip',

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->join(DB::raw($tempKandangBelumSelesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')
                ->where('a.sampai_id', '!=', 1)
                ->where('a.statusgandengan', $statusgandenganid)
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");


            DB::table($temprekap)->insertUsing([
                'jobtrucking',
                'tglbukti',
                'supir_id',
                'trado_id',
                'dari_id',
                'sampai_id',
                'nobukti',
                'container_id',
                'jenisorder_id',
                'pelanggan_id',
                'gandengan_id',
                'tarif_id',
                'statuslongtrip',

            ], $querydata1);

            // END TRIP KANDANG
            // dd(db::table($tempselesai)->get());

            $querydata1 = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'a.supir_id',
                    'a.trado_id',
                    'a.dari_id',
                    'a.sampai_id',
                    'a.nobukti',
                    'a.container_id',
                    'a.jenisorder_id',
                    'a.pelanggan_id',
                    'a.gandengan_id',
                    'a.tarif_id',
                    'a.statuslongtrip',

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')
                ->leftjoin(db::raw($tempNonTampilJobTrucking . " c1"), 'a.jobtrucking', 'c1.jobtrucking')
                ->whereRaw("isnull(c1.jobtrucking,'')=''")
                ->where('a.statusgandengan', $statusgandenganid)
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");
            $querydata1->whereRaw("a.dari_id=$pelabuhanAwal");

            // dd(db::table($tempNonTampilJobTrucking)->get(),$querydata1->get());

            DB::table($temprekap)->insertUsing([
                'jobtrucking',
                'tglbukti',
                'supir_id',
                'trado_id',
                'dari_id',
                'sampai_id',
                'nobukti',
                'container_id',
                'jenisorder_id',
                'pelanggan_id',
                'gandengan_id',
                'tarif_id',
                'statuslongtrip',

            ], $querydata1);

            $querydata1 = DB::table('saldosuratpengantar')->from(
                DB::raw("saldosuratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'a.supir_id',
                    'a.trado_id',
                    'a.dari_id',
                    'a.sampai_id',
                    'a.nobukti',
                    'a.container_id',
                    'a.jenisorder_id',
                    'a.pelanggan_id',
                    'a.gandengan_id',
                    'a.tarif_id',
                    'a.statuslongtrip',
                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')
                ->where('a.statusgandengan', $statusgandenganid);


            DB::table($temprekap)->insertUsing([
                'jobtrucking',
                'tglbukti',
                'supir_id',
                'trado_id',
                'dari_id',
                'sampai_id',
                'nobukti',
                'container_id',
                'jenisorder_id',
                'pelanggan_id',
                'gandengan_id',
                'tarif_id',
                'statuslongtrip',
            ], $querydata1);

            // cek belum lengkap
            $atglnow = date('Y-m-d');
            $aptgl = date('Y-m-d', strtotime($atglnow . ' -60 days'));
            $aptglbatas = date('Y-m-d', strtotime($aptgl . ' +20 days'));

            $parameter = new Parameter();
            $pelabuhanid = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? '0';
            $kandangid = $parameter->cekText('KANDANG', 'KANDANG') ?? '0';

            $tempbelumkomplit = '##tempbelumkomplit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbelumkomplit, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
            });

            $tempbelumkomplit1 = '##tempbelumkomplit1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbelumkomplit1, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->integer('dari_id')->nullable();
                $table->integer('sampai_id')->nullable();
                $table->integer('nilai1')->nullable();
                $table->integer('nilai2')->nullable();
                $table->integer('nilai3')->nullable();
                $table->integer('nilai4')->nullable();
            });

            $tempbelumkomplit2 = '##tempbelumkomplit2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbelumkomplit2, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->integer('jumlah')->nullable();
            });

            $querybelumkomplit1 = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.dari_id',
                    'a.sampai_id',
                    db::raw("(case when a.dari_id=" . $pelabuhanid . " and A.sampai_id=" . $kandangid . " THEN 1 ELSE 0 END) as nilai1"),
                    db::raw("(case when a.dari_id=" . $pelabuhanid . " and a.sampai_id<>" . $kandangid . " THEN 2 
        when a.dari_id=" . $kandangid . " and A.sampai_id NOT IN(" . $kandangid . "," . $pelabuhanid . ") THEN 1
          ELSE 0 END) as nilai2"),
                    db::raw(" (case when a.dari_id NOT IN(" . $pelabuhanid . "," . $kandangid . ") and a.sampai_id=" . $kandangid . "  THEN 1 
        when A.dari_id NOT IN(" . $pelabuhanid . "," . $kandangid . ") and a.sampai_id=" . $pelabuhanid . "  THEN 2
          ELSE 0 END) as nilai3"),
                    db::raw("(case when A.dari_id =" . $kandangid . " and A.sampai_id=" . $pelabuhanid . "  THEN 1 ELSE 0 END) as nilai4")
                )
                ->join(db::raw("orderantrucking b with (readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
                ->whereraw("a.tglbukti>='" . $aptgl . "'")
                ->whereraw("b.tglbukti>='" . $aptglbatas . "'")
                ->whereraw("isnull(a.statuslangsir,0) in(0,80)");

            DB::table($tempbelumkomplit1)->insertUsing([
                'jobtrucking',
                'dari_id',
                'sampai_id',
                'nilai1',
                'nilai2',
                'nilai3',
                'nilai4',
            ], $querybelumkomplit1);


            $querybelumkomplit2 = db::table($tempbelumkomplit1)->from(db::raw($tempbelumkomplit1 . " a "))
                ->select(
                    'a.jobtrucking',
                    db::raw("sum(a.nilai1+a.nilai2+a.nilai3+a.nilai4) as jumlah")
                )
                ->groupby('a.jobtrucking');

            DB::table($tempbelumkomplit2)->insertUsing([
                'jobtrucking',
                'jumlah',
            ], $querybelumkomplit2);

            

            $querybelumkomplit = db::table($tempbelumkomplit2)->from(db::raw($tempbelumkomplit2 . " a "))
                ->select(
                    'a.jobtrucking'
                )
                ->whereraw("a.jumlah<4");

            DB::table($tempbelumkomplit)->insertUsing([
                'jobtrucking',
            ], $querybelumkomplit);
     

            // dd(db::table($tempselesai)->where('jobtrucking','JT 0030/VII/2024')->get());

            DB::delete(DB::raw("delete  " . $tempselesai . " from " . $tempselesai . " as a inner join " . $tempbelumkomplit . " b on a.jobtrucking=b.jobtrucking "));






            $querydata = DB::table($temprekap)->from(
                DB::raw($temprekap . " as a")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'b.namasupir as supir',
                    'c.keterangan as trado',
                    'kotadr.keterangan as kotadari',
                    'kotasd.keterangan as kotasampai',
                    'a.nobukti',
                    'a.pelanggan_id',
                    'a.gandengan_id'

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')

                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.gandengan_id', '=', $gandengan_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id);
            if ($dari_id == $idkandang) {
                $querydata->where('a.dari_id', 1);
                // ->where('a.sampai_id', $idkandang);
            }
            // dd($querydata->get());
            if ($edit == 'true') {
                // $querydata->where('a.dari_id', 1);
            }
            
            // dd(db::table($tempselesai)->get());
        } else {
            tidakgandengan:
            $queryjob = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking'
                )
                ->leftjoin(db::raw($tempNonTampilJobTrucking . " c1"), 'a.jobtrucking', 'c1.jobtrucking')
                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id)
                ->whereRaw("isnull(a.jobtrucking,'')<>''")
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")")
                ->whereRaw("isnull(c1.jobtrucking,'')=''")
                ->whereRaw("a.sampai_id in ($pelabuhanAwal)");

            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);


            $queryjob = DB::table('saldosuratpengantar')->from(
                DB::raw("saldosuratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking'
                )
                ->leftjoin(db::raw($tempNonTampilJobTrucking . " c1"), 'a.jobtrucking', 'c1.jobtrucking')
                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id)
                ->whereRaw("isnull(a.jobtrucking,'')<>''")
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")")
                ->whereRaw("isnull(c1.jobtrucking,'')=''")
                ->whereRaw("a.sampai_id in ($pelabuhanAwal)");

            // dd(( $queryjob)->tosql());


            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);


            // cek belum lengkap
            $atglnow = date('Y-m-d');
            $aptgl = date('Y-m-d', strtotime($atglnow . ' -60 days'));
            $aptglbatas = date('Y-m-d', strtotime($aptgl . ' +20 days'));

            $parameter = new Parameter();
            $pelabuhanid = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? '0';
            $kandangid = $parameter->cekText('KANDANG', 'KANDANG') ?? '0';

            $tempbelumkomplit = '##tempbelumkomplit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbelumkomplit, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
            });

            $tempbelumkomplit1 = '##tempbelumkomplit1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbelumkomplit1, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->integer('dari_id')->nullable();
                $table->integer('sampai_id')->nullable();
                $table->integer('nilai1')->nullable();
                $table->integer('nilai2')->nullable();
                $table->integer('nilai3')->nullable();
                $table->integer('nilai4')->nullable();
            });

            $tempbelumkomplit2 = '##tempbelumkomplit2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbelumkomplit2, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->integer('jumlah')->nullable();
            });

            $querybelumkomplit1 = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.dari_id',
                    'a.sampai_id',
                    db::raw("(case when a.dari_id=" . $pelabuhanid . " and A.sampai_id=" . $kandangid . " THEN 1 ELSE 0 END) as nilai1"),
                    db::raw("(case when a.dari_id=" . $pelabuhanid . " and a.sampai_id<>" . $kandangid . " THEN 2 
        when a.dari_id=" . $kandangid . " and A.sampai_id NOT IN(" . $kandangid . "," . $pelabuhanid . ") THEN 1
          ELSE 0 END) as nilai2"),
                    db::raw(" (case when a.dari_id NOT IN(" . $pelabuhanid . "," . $kandangid . ") and a.sampai_id=" . $kandangid . "  THEN 1 
        when A.dari_id NOT IN(" . $pelabuhanid . "," . $kandangid . ") and a.sampai_id=" . $pelabuhanid . "  THEN 2
          ELSE 0 END) as nilai3"),
                    db::raw("(case when A.dari_id =" . $kandangid . " and A.sampai_id=" . $pelabuhanid . "  THEN 1 ELSE 0 END) as nilai4")

                )
                ->join(db::raw("orderantrucking b with (readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
                ->whereraw("a.tglbukti>='" . $aptgl . "'")
                ->whereraw("b.tglbukti>='" . $aptglbatas . "'")
                ->whereraw("isnull(a.statuslangsir,0) in(0,80)");

            DB::table($tempbelumkomplit1)->insertUsing([
                'jobtrucking',
                'dari_id',
                'sampai_id',
                'nilai1',
                'nilai2',
                'nilai3',
                'nilai4',
            ], $querybelumkomplit1);

            $querybelumkomplit2 = db::table($tempbelumkomplit1)->from(db::raw($tempbelumkomplit1 . " a "))
                ->select(
                    'a.jobtrucking',
                    db::raw("sum(a.nilai1+a.nilai2+a.nilai3+a.nilai4) as jumlah")
                )
                ->groupby('a.jobtrucking');

            DB::table($tempbelumkomplit2)->insertUsing([
                'jobtrucking',
                'jumlah',
            ], $querybelumkomplit2);

            $querybelumkomplit = db::table($tempbelumkomplit2)->from(db::raw($tempbelumkomplit2 . " a "))
                ->select(
                    'a.jobtrucking'
                )
                ->whereraw("a.jumlah<4");

            DB::table($tempbelumkomplit)->insertUsing([
                'jobtrucking',
            ], $querybelumkomplit);



            DB::delete(DB::raw("delete  " . $tempselesai . " from " . $tempselesai . " as a inner join " . $tempbelumkomplit . " b on a.jobtrucking=b.jobtrucking "));





            // START TRIP KANDANG
            $tempstartkandang = '##tempstartkandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstartkandang, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nobukti', 1000)->nullable();
            });
            $getQueryStartKandang = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('jobtrucking', 'nobukti')
                ->where('statuslongtrip', 66)
                ->where('dari_id', $idkandang)
                ->where('nobukti_tripasal', '!=', '');

            DB::table($tempstartkandang)->insertUsing([
                'jobtrucking',
                'nobukti',
            ], $getQueryStartKandang);

            $temptrippulang = '##temptrippulang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptrippulang, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nobukti', 1000)->nullable();
            });
            $getQueryPulang = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('jobtrucking', 'nobukti')
                ->where('sampai_id', 1);

            if ($idtrip != '' && $edit == 'true') {
                $getQueryPulang->where('nobukti', '!=', $getNobukti->nobukti);
            }

            DB::table($temptrippulang)->insertUsing([
                'jobtrucking',
                'nobukti',
            ], $getQueryPulang);

            $tempKandangBelumSelesai = '##tempKandangBelumSelesai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempKandangBelumSelesai, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nobukti', 1000)->nullable();
            });
            $getQueryKandanBelumSelesai = DB::table("$tempstartkandang")->from(DB::raw("$tempstartkandang as a with (readuncommitted)"))
                ->select('a.jobtrucking', 'a.nobukti')
                ->leftJoin(DB::raw("$temptrippulang as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                ->whereRaw("isnull(b.jobtrucking, '')=''");

            DB::table($tempKandangBelumSelesai)->insertUsing([
                'jobtrucking',
                'nobukti',
            ], $getQueryKandanBelumSelesai);

            $querydata1 = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'a.supir_id',
                    'a.trado_id',
                    'a.dari_id',
                    'a.sampai_id',
                    'a.nobukti',
                    'a.container_id',
                    'a.jenisorder_id',
                    'a.pelanggan_id',
                    'a.gandengan_id',
                    'a.tarif_id',
                    'a.statuslongtrip',

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->join(DB::raw($tempKandangBelumSelesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");

            // dd(db::table($tempNonTampilJobTrucking)->get(),$querydata1->get());

            DB::table($temprekap)->insertUsing([
                'jobtrucking',
                'tglbukti',
                'supir_id',
                'trado_id',
                'dari_id',
                'sampai_id',
                'nobukti',
                'container_id',
                'jenisorder_id',
                'pelanggan_id',
                'gandengan_id',
                'tarif_id',
                'statuslongtrip',

            ], $querydata1);

            // END TRIP KANDANG
            // dd(db::table($tempselesai)->tosql());

            $querydata1 = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'a.supir_id',
                    'a.trado_id',
                    'a.dari_id',
                    'a.sampai_id',
                    'a.nobukti',
                    'a.container_id',
                    'a.jenisorder_id',
                    'a.pelanggan_id',
                    'a.gandengan_id',
                    'a.tarif_id',
                    'a.statuslongtrip',

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')
                ->leftjoin(db::raw($tempNonTampilJobTrucking . " c1"), 'a.jobtrucking', 'c1.jobtrucking')
                ->whereRaw("isnull(c1.jobtrucking,'')=''")
                ->whereRaw("a.dari_id=$pelabuhanAwal")
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");
            // dd($querydata1->get());

            DB::table($temprekap)->insertUsing([
                'jobtrucking',
                'tglbukti',
                'supir_id',
                'trado_id',
                'dari_id',
                'sampai_id',
                'nobukti',
                'container_id',
                'jenisorder_id',
                'pelanggan_id',
                'gandengan_id',
                'tarif_id',
                'statuslongtrip',

            ], $querydata1);

            $querydata1 = DB::table('saldosuratpengantar')->from(
                DB::raw("saldosuratpengantar as a with(readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'a.supir_id',
                    'a.trado_id',
                    'a.dari_id',
                    'a.sampai_id',
                    'a.nobukti',
                    'a.container_id',
                    'a.jenisorder_id',
                    'a.pelanggan_id',
                    'a.gandengan_id',
                    'a.tarif_id',
                    'a.statuslongtrip',
                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking');

            // dd($querydata1->get());

            DB::table($temprekap)->insertUsing([
                'jobtrucking',
                'tglbukti',
                'supir_id',
                'trado_id',
                'dari_id',
                'sampai_id',
                'nobukti',
                'container_id',
                'jenisorder_id',
                'pelanggan_id',
                'gandengan_id',
                'tarif_id',
                'statuslongtrip',
            ], $querydata1);



            $querydata = DB::table($temprekap)->from(
                DB::raw($temprekap . " as a")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'b.namasupir as supir',
                    'c.kodetrado as trado',
                    'kotadr.keterangan as kotadari',
                    'kotasd.keterangan as kotasampai',
                    'a.nobukti',
                    'a.pelanggan_id'

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                // ->join(DB::raw($tempmandordetail . " as d1"), 'c.mandor_id', 'd1.mandor_id') //untuk surabaya dan jakarta
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking');

            // dd(DB::table($temprekap)->get());
            $querydata = DB::table($temprekap)->from(
                DB::raw($temprekap . " as a ")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'b.namasupir as supir',
                    'c.kodetrado as trado',
                    'kotadr.keterangan as kotadari',
                    'kotasd.keterangan as kotasampai',
                    'a.nobukti',
                    'a.pelanggan_id'

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->leftjoin(DB::raw($tempselesai . " as d"), 'a.jobtrucking', 'd.jobtrucking')
                // ->join(DB::raw($tempmandordetail . " as d1"), 'c.mandor_id', 'd1.mandor_id') //untuk surabaya dan jakarta

                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id);
            // dd($querydata->where('a.jobtrucking','JT 0040/III/2024')->get());
            if ($edit == 'true') {
                // $querydata->whereRaw("a.dari_id in (1)");
            }
        }

        // $this->filter($querydata);
        $querygerobak = DB::table('trado')->from(
            DB::raw("trado as a with (readuncommitted)")
        )
            ->select(
                'a.keterangan'
            )
            ->where('a.id', '=', $trado_id)
            ->where('a.statusgerobak', '=', $statusgerobak->id)
            ->first();
        $idtrip = request()->idtrip ?? '';

        if ($isGandengan->text == 'TIDAK') {
            goto tidakgandengan2;
        }

        if (isset($querygerobak)) {

            // dd(request()->gandengan_id);
            // $querydata->where('a.container_id', '=', $container_id);
            // $querydata->where('a.jenisorder_id', '=', $jenisorder_id);
            // $querydata->where('a.gandengan_id', '=', $gandengan_id);
            // // dd($querydata->get()); 
            // $querydata->where('a.pelanggan_id', '=', $pelanggan_id);


            // // $querydata->where('a.tarif_id', '=', request()->tarif_id);
            $querydata->whereRaw("isnull(a.jobtrucking,'')<>''");
            $querydata->whereRaw(DB::raw("(a.dari_id in (" . $pelabuhan . ") or a.statuslongtrip=" . $statuslongtrip->id . ")"));
            if ($edit == 'false') {
                $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
            } else {
                $getJenisOrderMuatan = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))
                    ->select('id')
                    ->where("kodejenisorder", 'MUAT')
                    ->orWhere("kodejenisorder", 'EKS')
                    ->get();


                $getJenisOrderMuatan = json_decode($getJenisOrderMuatan, true);
                foreach ($getJenisOrderMuatan as $item) {
                    $dataMuatanEksport[] = $item['id'];
                }
                $trip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('id', $idtrip)->first();
                if (in_array($trip->jenisorder_id, $dataMuatanEksport)) {
                    $queryFull = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'FULL')->first();
                    if ($trip->statuscontainer_id != $queryFull->id) {
                        if ($trip->statuscontainer_id != $statuscontainer_id) {
                            $querydata->whereRaw("isnull(a.nobukti,'') != '$trip->nobukti'");
                        } else {
                            $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
                        }
                    }
                } else {
                    $queryEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first();

                    if ($trip->statuscontainer_id != $queryEmpty->id) {
                        if ($trip->statuscontainer_id != $statuscontainer_id) {
                            $querydata->whereRaw("isnull(a.nobukti,'') != '$trip->nobukti'");
                        } else {
                            $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
                        }
                    }
                }
            }
        } else {
            tidakgandengan2:
            // $querydata->where('a.trado_id', '=', $trado_id);
            //  dd($querydata->get());
            $querydata->where('a.pelanggan_id', '=', $pelanggan_id);



            $querydata->where('a.container_id', '=', $container_id);
            $querydata->where('a.jenisorder_id', '=', $jenisorder_id);
            // $querydata->where('a.tarif_id', '=', $tarif_id);
            $querydata->whereRaw("isnull(a.jobtrucking,'')<>''");
            $querydata->whereRaw(DB::raw("(a.dari_id in (" . $pelabuhan . ") or a.statuslongtrip=" . $statuslongtrip->id . ")"));
            if ($edit == 'false') {
                $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
            } else {
                $getJenisOrderMuatan = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))
                    ->select('id')
                    ->where("kodejenisorder", 'MUAT')
                    ->orWhere("kodejenisorder", 'EKS')
                    ->get();


                $getJenisOrderMuatan = json_decode($getJenisOrderMuatan, true);
                foreach ($getJenisOrderMuatan as $item) {
                    $dataMuatanEksport[] = $item['id'];
                }
                $trip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('id', $idtrip)->first();
                if (in_array($trip->jenisorder_id, $dataMuatanEksport)) {
                    $queryFull = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'FULL')->first();
                    if ($trip->statuscontainer_id != $queryFull->id) {
                        if ($trip->statuscontainer_id != $statuscontainer_id) {
                            $querydata->whereRaw("isnull(a.nobukti,'') != '$trip->nobukti'");
                        } else {
                            $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
                        }
                    }
                } else {
                    $queryEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first();

                    if ($trip->statuscontainer_id != $queryEmpty->id) {
                        if ($trip->statuscontainer_id != $statuscontainer_id) {
                            $querydata->whereRaw("isnull(a.nobukti,'') != '$trip->nobukti'");
                        } else {
                            $querydata->whereRaw("isnull(d.jobtrucking,'')=''");
                        }
                    }
                }
            }
        }

                    // dd(db::table($tempselesai)->get());

                    // DB::delete(DB::raw("delete  " . $tempselesai . " from " . $tempselesai . " as a inner join " . $tempbelumkomplit . " b on a.jobtrucking=b.jobtrucking "));

        // dd($querydata->get());
        pulanglongtrip:
        if ($tripasal != '') {

            $querydata = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar as a ")
            )
                ->select(
                    'a.jobtrucking',
                    'a.tglbukti',
                    'b.namasupir as supir',
                    'c.kodetrado as trado',
                    'kotadr.keterangan as kotadari',
                    'kotasd.keterangan as kotasampai',
                    'a.nobukti',
                    'a.pelanggan_id'

                )
                ->leftjoin(DB::raw("supir as b with(readuncommitted)"), 'a.supir_id', 'b.id')
                ->leftjoin(DB::raw("trado as c with(readuncommitted)"), 'a.trado_id', 'c.id')
                ->leftjoin(DB::raw("kota as kotadr with(readuncommitted)"), 'a.dari_id', 'kotadr.id')
                ->leftjoin(DB::raw("kota as kotasd with(readuncommitted)"), 'a.sampai_id', 'kotasd.id')
                ->where('a.nobukti', '=', request()->tripasal);
            $this->filter($querydata);
        } else {
        //   dd(db::table($tempbelumkomplit1)->where('jobtrucking','JT 0030/VII/2024')->get());
        //   dd(db::table($tempselesai)->get());

                    DB::delete(DB::raw("delete  " . $tempselesai . " from " . $tempselesai . " as a inner join " . $tempbelumkomplit . " b on a.jobtrucking=b.jobtrucking "));

            // dd($querydata->tosql(),$tempselesai);

            $temprekapdata2 = '##temprekapdata2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temprekapdata2, function ($table) {
                $table->integer('trado_id')->nullable();
            });

            $querycek = DB::table($temprekap)->from(
                DB::raw($temprekap . " as a")
            )
                ->select(
                    'a.trado_id'
                )
                ->join(DB::raw("absensisupirheader as d2 with (readuncommitted)"), 'a.tglbukti', 'd2.tglbukti')
                ->join(DB::raw("absensisupirdetail as d3 with (readuncommitted)"), function ($join) {
                    $join->on('d2.nobukti', '=', 'd3.nobukti');
                    $join->on('a.trado_id', '=', 'd3.trado_id');
                })
                ->join(DB::raw($tempmandordetail . " as d1"), 'd3.mandor_id', 'd1.mandor_id')
                ->where('d2.tglbukti', $date)
                ->where('a.trado_id', $trado_id)
                ->groupby('a.trado_id');

            DB::table($temprekapdata2)->insertUsing([
                'trado_id',
            ], $querycek);

            // dd(db::table($temprekapdata2)->get());
            // dd($trado_id);
            if ($jobBedaTanggal == 'TIDAK') {
                $querydata->join(DB::raw($temprekapdata2 . "  as d2 "), 'a.trado_id', 'd2.trado_id'); //untuk surabaya dan jakarta
            }
            $querydata->whereraw("a.tglbukti<='" . $date . "'"); //untuk surabaya dan jakarta

            // dd($querydata->tosql());


            // $querydata->join(DB::raw("absensisupirheader as d2 with (readuncommitted)"), 'a.tglbukti', 'd2.tglbukti'); //untuk surabaya dan jakarta
            // $querydata->join(DB::raw("absensisupirdetail as d3 with (readuncommitted)"), function ($join)  {
            //             $join->on('d2.nobukti', '=', 'd3.nobukti');
            //             $join->on('a.trado_id', '=', 'd3.trado_id');
            //         });                        
            // // $querydata->join(DB::raw("absensisupirdetail as d3 with (readuncommitted)"), 'd2.nobukti', 'd3.nobukti'); //untuk surabaya dan jakarta
            // $querydata->join(DB::raw($tempmandordetail . " as d1"), 'd3.mandor_id', 'd1.mandor_id'); //untuk surabaya dan jakarta
            // $querydata->where('d2.tglbukti',$date); //untuk surabaya dan jakarta

            $this->filter($querydata);
        }

        $this->totalRows = $querydata->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($querydata);

        $this->paginate($querydata);



        $data = $querydata->get();

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
                        if ($filters['field'] == 'jobtrucking') {
                            $query = $query->where('a.jobtrucking', 'like', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'supir') {
                            $query = $query->where('b.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado') {
                            $query = $query->where('c.kodetrado', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotadari') {
                            $query = $query->where('kotadr.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotasampai') {
                            $query = $query->where('kotasd.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'nobukti') {
                            $query = $query->where('a.nobukti', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'jobtrucking') {
                            $query = $query->OrwhereRaw("(a.jobtrucking like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'tglbukti') {
                            $query = $query->OrwhereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'supir') {
                            $query = $query->Orwhere('b.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado') {
                            $query = $query->Orwhere('c.kodetrado', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotadari') {
                            $query = $query->Orwhere('kotadr.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'kotasampai') {
                            $query = $query->Orwhere('kotasd.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'nobukti') {
                            $query = $query->OrwhereRaw("a.nobukti LIKE '%$filters[data]%')");
                        } else {
                            // $query = $query->Orwhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
}
