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

        $container_id = request()->container_id ?? 0;
        $jenisorder_id = request()->jenisorder_id ?? 0;
        $gandengan_id = request()->gandengan_id ?? 0;
        $pelanggan_id = request()->pelanggan_id ?? 0;
        if (request()->tarif_id == 'undefined') {
            $tarif_id = 0;
        } else {
            $tarif_id = request()->tarif_id ?? 0;
        }

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
                'b.jobtrucking'
            )->join(db::raw("suratpengantar b with (readuncommitted)"), 'a.nobukti_tripasal', 'b.nobukti')
            ->groupBy('b.jobtrucking');

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



        $edit = request()->edit ?? false;

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
                ->whereRaw("a.sampai_id in (" . $pelabuhan . ") and isnull(B.statusapprovalbukatrip,4)=4")
                ->whereRaw("isnull(c.jobtrucking,'')=''")
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");
            // ->where('a.sampai_id', '=', $pelabuhan->text);

            // dd($queryjob->get());

            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);


            $queryjob = DB::table('saldosuratpengantar')->from(
                DB::raw("suratpengantar as a with(readuncommitted)")
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
                ->whereRaw("a.sampai_id in (" . $pelabuhan . ") and isnull(B.statusapprovalbukatrip,4)=4")
                ->whereRaw("isnull(c.jobtrucking,'')=''")
                ->whereRaw("a.statuscontainer_id not in(" . $statusfullempty . ")");
            // ->where('a.sampai_id', '=', $pelabuhan->text);

            // dd($queryjob->get());

            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);            


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
                    'c.keterangan as trado',
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

                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.gandengan_id', '=', $gandengan_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id);

            if ($edit == true) {
                $querydata->where('a.dari_id', 1);
            }
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
                ->whereRaw("a.sampai_id in ($pelabuhan)");

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
                ->whereRaw("a.sampai_id in ($pelabuhan)");

            // dd(( $queryjob)->tosql());


            DB::table($tempselesai)->insertUsing([
                'jobtrucking',
            ], $queryjob);

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
                ->where('a.container_id', '=', $container_id)
                ->where('a.jenisorder_id', '=', $jenisorder_id)
                ->where('a.pelanggan_id', '=', $pelanggan_id)
                ->where('a.tarif_id', '=', $tarif_id);
            // dd($querydata->get());
            // dd($querydata->where('a.jobtrucking','JT 0040/III/2024')->get());
            if ($edit == 'true') {
                $querydata->whereRaw("a.dari_id in ($pelabuhan)");
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
            $querydata->where('a.container_id', '=', $container_id);
            $querydata->where('a.jenisorder_id', '=', $jenisorder_id);
            $querydata->where('a.gandengan_id', '=', $gandengan_id);
            // dd($querydata->get()); 
            $querydata->where('a.pelanggan_id', '=', $pelanggan_id);


            // $querydata->where('a.tarif_id', '=', request()->tarif_id);
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
