<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'orderantrucking';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function cekvalidasihapus($nobukti, $aksi)
    {

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.jobtrucking',
                'a.nobukti'
            )
            ->where('a.jobtrucking', '=', $nobukti)
            ->first();
        if ($aksi == 'delete') {
            if (isset($suratPengantar)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'Surat Pengantar',
                ];


                goto selesai;
            }
        } else {
            if ($suratPengantar != null) {
                $gajiSupir = DB::table('gajisupirdetail')
                    ->from(
                        DB::raw("gajisupirdetail as a with (readuncommitted)")
                    )
                    ->select(
                        'a.suratpengantar_nobukti'
                    )
                    ->where('a.suratpengantar_nobukti', $suratPengantar->nobukti)
                    ->first();

                if (isset($gajiSupir)) {
                    $data = [
                        'kondisi' => true,
                        'keterangan' => 'GAJI SUPIR',
                    ];


                    goto selesai;
                }
            }
        }

        $invoice = DB::table('invoicedetail')
            ->from(
                DB::raw("invoicedetail as a with (readuncommitted)")
            )
            ->select(
                'a.orderantrucking_nobukti'
            )
            ->where('a.orderantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($invoice)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'invoice',
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

    public function isEditAble($id)
    {
        $tidakBolehEdit = DB::table('orderantrucking')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        
        $query = DB::table('orderantrucking')->from(DB::raw("orderantrucking with (readuncommitted)"))
            ->select('statusapprovaledit')
            ->where('id', $id)
            ->first();

        if ($query->statusapprovaledit != $tidakBolehEdit->id) return true;
        return false;
    }

    public function todayValidation($id)
    {
        $query = DB::table('orderantrucking')->from(DB::raw("orderantrucking with (readuncommitted)"))
            ->where('id', $id)
            ->first();
        $tglbukti = strtotime($query->created_at);
        $tglbuktistr = strtotime($tglbukti);
        $limit = strtotime($tglbukti.'+1 days +12 hours +9 minutes' );
        $now = strtotime('now');
        if ($now < $limit) return true;
        return false;
    }

    public function get()
    {
        $this->setRequestParameters();

        $temporderantrucking = '##temporderantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temporderantrucking, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('nojobemkl', 50)->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('nojobemkl2', 50)->nullable();
            $table->string('nocont2', 50)->nullable();
            $table->string('noseal2', 50)->nullable();
            $table->integer('statuslangsir')->length(11)->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->string('jobtruckingasal', 500)->nullable();
            $table->integer('statusapprovalnonchargegandengan')->Length(11)->nullable();
            $table->string('userapprovalnonchargegandengan', 50)->nullable();
            $table->date('tglapprovalnonchargegandengan')->nullable();
            $table->integer('statusapprovalbukatrip')->Length(11)->nullable();
            $table->date('tglapprovalbukatrip')->nullable();
            $table->string('userapprovalbukatrip', 50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $queryorderantrucking = DB::table('orderantrucking')->from(
            DB::raw("orderantrucking a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.container_id',
                'a.agen_id',
                'a.jenisorder_id',
                'a.pelanggan_id',
                'a.tarif_id',
                'a.nominal',
                'a.nojobemkl',
                'a.nocont',
                'a.noseal',
                'a.nojobemkl2',
                'a.nocont2',
                'a.noseal2',
                'a.statuslangsir',
                'a.statusperalihan',
                'a.jobtruckingasal',
                'a.statusapprovalnonchargegandengan',
                'a.userapprovalnonchargegandengan',
                'a.tglapprovalnonchargegandengan',
                'a.statusapprovalbukatrip',
                'a.tglapprovalbukatrip',
                'a.userapprovalbukatrip',
                'a.statusformat',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            )
            ->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);


        DB::table($temporderantrucking)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'container_id',
            'agen_id',
            'jenisorder_id',
            'pelanggan_id',
            'tarif_id',
            'nominal',
            'nojobemkl',
            'nocont',
            'noseal',
            'nojobemkl2',
            'nocont2',
            'noseal2',
            'statuslangsir',
            'statusperalihan',
            'jobtruckingasal',
            'statusapprovalnonchargegandengan',
            'userapprovalnonchargegandengan',
            'tglapprovalnonchargegandengan',
            'statusapprovalbukatrip',
            'tglapprovalbukatrip',
            'userapprovalbukatrip',
            'statusformat',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $queryorderantrucking);

        $queryorderantrucking = DB::table('saldoorderantrucking')->from(
            DB::raw("saldoorderantrucking a with (readuncommitted)")
        )
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.container_id',
                'a.agen_id',
                'a.jenisorder_id',
                'a.pelanggan_id',
                'a.tarif_id',
                'a.nominal',
                'a.nojobemkl',
                'a.nocont',
                'a.noseal',
                'a.nojobemkl2',
                'a.nocont2',
                'a.noseal2',
                'a.statuslangsir',
                'a.statusperalihan',
                'a.jobtruckingasal',
                'a.statusapprovalnonchargegandengan',
                'a.userapprovalnonchargegandengan',
                'a.tglapprovalnonchargegandengan',
                'a.statusapprovalbukatrip',
                'a.tglapprovalbukatrip',
                'a.userapprovalbukatrip',
                'a.statusformat',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            )
            ->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);


        DB::table($temporderantrucking)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'container_id',
            'agen_id',
            'jenisorder_id',
            'pelanggan_id',
            'tarif_id',
            'nominal',
            'nojobemkl',
            'nocont',
            'noseal',
            'nojobemkl2',
            'nocont2',
            'noseal2',
            'statuslangsir',
            'statusperalihan',
            'jobtruckingasal',
            'statusapprovalnonchargegandengan',
            'userapprovalnonchargegandengan',
            'tglapprovalnonchargegandengan',
            'statusapprovalbukatrip',
            'tglapprovalbukatrip',
            'userapprovalbukatrip',
            'statusformat',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $queryorderantrucking);



        $query = DB::table($temporderantrucking)->from(
            DB::raw($temporderantrucking . " as orderantrucking")
        )
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'tarif.tujuan as tarif_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.memo as statuslangsir',
                'param2.memo as statusperalihan',
                'statusapprovalbukatrip.memo as statusapprovalbukatrip',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->whereBetween('orderantrucking.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter AS statusapprovalbukatrip with (readuncommitted)"), 'orderantrucking.statusapprovalbukatrip', '=', 'statusapprovalbukatrip.id')
            ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'orderantrucking.statusperalihan', '=', 'param2.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }

    public function getagentas($id)
    {
        $data = DB::table('agen')
            ->from(DB::raw("agen with (readuncommitted)"))
            ->select(
                // DB::raw("(case when jenisemkl.kodejenisemkl='TAS' then 1 else 0 end)  as statustas")
                DB::raw("(case when parameter.text='TAS' then 1 else 0 end)  as statustas"),
                'parameter.text as text',

            )
            // ->join('jenisemkl', 'jenisemkl.id', 'agen.jenisemkl')
            ->join(DB::raw("parameter with (readuncommitted)"), 'agen.statustas', '=', 'parameter.id')

            ->where('agen.id', $id)
            ->first();


        return $data;
    }

    public function getcont($id)
    {

        $queryukuran = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
            )
            ->where('grp', 'UKURANCONTAINER2X20')
            ->where('subgrp', 'UKURANCONTAINER2X20')
            ->first();

        $data = DB::table('container')
            ->from(DB::raw("container with (readuncommitted)"))
            ->select(
                DB::raw("(case when id=" . $queryukuran->text . " then 1 else 0 end)  as kodecontainer")
            )
            ->where('container.id', $id)
            ->first();


        return $data;
    }
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statuslangsir')->nullable();
            $table->unsignedBigInteger('statusperalihan')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslangsir = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS PERALIHAN')
            ->where('subgrp', '=', 'STATUS PERALIHAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusperalihan = $status->id ?? 0;


        DB::table($tempdefault)->insert(
            ["statuslangsir" => $iddefaultstatuslangsir, "statusperalihan" => $iddefaultstatusperalihan]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statuslangsir',
                'statusperalihan',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking with (readuncommitted)")
            )
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'orderantrucking.container_id',
                'container.keterangan as container',
                'orderantrucking.agen_id',
                'agen.namaagen as agen',
                'orderantrucking.jenisorder_id',
                DB::raw("isnull(jenisorderemkl.keterangan,'') as jenisorderemkl"),
                'jenisorder.keterangan as jenisorder',
                'orderantrucking.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'orderantrucking.tarif_id as tarifrincian_id',
                'tarif.tujuan as tarifrincian',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'orderantrucking.statuslangsir',
                'orderantrucking.statusperalihan',
                'orderantrucking.statusapprovalbukatrip',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("jenisorder as jenisorderemkl with (readuncommitted)"), 'orderantrucking.jenisorderemkl_id', '=', 'jenisorderemkl.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->where('orderantrucking.id', $id);

        $data = $query->first();

        return $data;
    }

    public function reminderchargegandengan()
    {


        // 
        $ptgl = '2023/1/1';

        $tempnotkota = '##tempnotkota' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempnotkota, function ($table) {
            $table->integer('id')->nullable();
            $table->string('kodekota', 1000)->nullable();
        });

        $querynotkota = DB::table('kota')->from(
            DB::raw("kota a with (readuncommitted) ")
        )
            ->select(
                'a.id',
                'a.kodekota',
            )
            ->whereRaw("a.id in(1,198,431)");

        DB::table($tempnotkota)->insertUsing([
            'id',
            'kodekota',
        ], $querynotkota);

        $tempjobtrucking = '##tempjobtrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempjobtrucking, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tgl')->nullable();
            $table->integer('sampai_id')->nullable();
        });

        $queryjobtrucking = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar a with (readuncommitted) ")
        )
            ->select(
                'a.jobtrucking',
                DB::raw("max(a.tglbukti) as tgl"),
                DB::raw("max(a.sampai_id) as sampai_id"),
            )
            ->leftjoin(DB::raw($tempnotkota . " as b "), 'a.sampai_id', 'b.id')
            ->whereRaw("a.tglbukti>='" . date('Y/m/d', strtotime($ptgl)) . "'")
            ->whereRaw("isnull(b.id,0)=0")
            ->groupby('a.jobtrucking');

        DB::table($tempjobtrucking)->insertUsing([
            'jobtrucking',
            'tgl',
            'sampai_id',
        ], $queryjobtrucking);


        $tempjobtruckingakhir = '##tempjobtruckingakhir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempjobtruckingakhir, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tgl')->nullable();
            $table->integer('dari_id')->nullable();
        });

        $queryjobtruckingakhir = DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a")
        )
            ->select(
                'a.jobtrucking',
                DB::raw("max(b.tglbukti) as tgl"),
                DB::raw("max(b.dari_id) as dari_id"),
            )
            ->join(DB::raw("suratpengantar as b with (readuncommitted) "), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw($tempnotkota . " as c "), 'b.dari_id', 'c.id')
            ->whereRaw("isnull(c.id,0)=0")
            ->groupby('a.jobtrucking');

        DB::table($tempjobtruckingakhir)->insertUsing([
            'jobtrucking',
            'tgl',
            'dari_id',
        ], $queryjobtruckingakhir);


        $queryjobtruckingakhir = DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a")
        )
            ->select(
                'a.jobtrucking',
                DB::raw("format(getdate(),'yyyy/MM/dd') as tgl"),
                DB::raw("0 as dari_id"),
            )
            ->join(DB::raw($tempjobtruckingakhir . " as b"), 'a.jobtrucking', 'b.jobtrucking')
            ->whereRaw("isnull(b.jobtrucking,'')=''")
            ->groupby('a.jobtrucking');

        DB::table($tempjobtruckingakhir)->insertUsing([
            'jobtrucking',
            'tgl',
            'dari_id',
        ], $queryjobtruckingakhir);


        $listtempjobtruckingakhir = '##listtempjobtruckingakhir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($listtempjobtruckingakhir, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
            $table->string('sampai', 500)->nullable();
            $table->string('dari', 500)->nullable();
            $table->date('tglsampai')->nullable();
            $table->date('tglkembali')->nullable();
            $table->integer('hari')->nullable();
            $table->integer('harilibur')->nullable();
        });

        $querylisttempjobtruckingakhir = DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a")
        )
            ->select(
                'a.jobtrucking',
                DB::raw("isnull(c.kodekota,'') as sampai"),
                DB::raw("isnull(d.kodekota,'') as dari"),
                DB::raw("a.tgl as tglsampai"),
                DB::raw("b.tgl as tglkembali"),
                DB::raw("datediff(d,a.tgl,b.tgl)+1 as hari"),
                DB::raw("0 as harilibur"),
            )
            ->join(DB::raw($tempjobtruckingakhir . " as b"), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw("kota as c with (readuncommitted)"), 'a.sampai_id', 'c.id')
            ->leftjoin(DB::raw("kota as d with (readuncommitted)"), 'b.dari_id', 'd.id')
            ->whereRaw("(isnull(a.sampai_id,0)=isnull(b.dari_id,0)")
            ->OrwhereRaw("isnull(b.dari_id,0)=0)")
            ->whereRaw("datediff(d,A.tgl,b.tgl)+1>6");

        DB::table($listtempjobtruckingakhir)->insertUsing([
            'jobtrucking',
            'sampai',
            'dari',
            'tglsampai',
            'tglkembali',
            'hari',
            'harilibur',
        ], $querylisttempjobtruckingakhir);

        $querylisttempjobtruckingakhirlooping = DB::table($listtempjobtruckingakhir)->from(
            DB::raw($listtempjobtruckingakhir . " a")
        )
            ->select(
                'a.jobtrucking',
                'a.tglsampai',
                'a.tglkembali',
            );


        $datadetail = json_decode($querylisttempjobtruckingakhirlooping->get(), true);

        foreach ($datadetail as $item) {
            $xtglsampai1 = date('Y-m-d', strtotime($item['tglsampai']));
            $xtglkembali1 = date('Y-m-d', strtotime($item['tglkembali']));
            $libur = 0;

            while ($xtglsampai1 <= $xtglkembali1) {

                $datepart = DB::select("select datepart(dw," . $xtglsampai1 . ") as dpart");
                $dpart = json_decode(json_encode($datepart), true)[0]['dpart'];
                if ($dpart == 1) {
                    $libur = $libur + 1;
                }
                $querylibur = DB::table('harilibur')->from(
                    db::raw("harilibur as a with (readuncommitted)")
                )
                    ->select(
                        'tgl'
                    )->where('tgl', '=', $xtglsampai1)
                    ->first();
                if (isset($querylibur)) {
                    $libur = $libur + 1;
                }


                $xtglsampai1 = date("Y-m-d", strtotime("+1 day", strtotime($xtglsampai1)));
            }
            DB::update(DB::raw("UPDATE " . $listtempjobtruckingakhir . " SET harilibur=" . $libur . " WHERE jobtrucking ='" . $item['jobtrucking'] . "'"));
        }

        DB::delete(DB::raw("delete " . $listtempjobtruckingakhir . "  WHERE (hari-harilibur)<6"));

        $templisttruckingrekap = '##templisttruckingrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templisttruckingrekap, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
        });

        $querylisttruckingrekap = DB::table($listtempjobtruckingakhir)->from(
            DB::raw($listtempjobtruckingakhir . " a")
        )
            ->select(
                'a.jobtrucking',
            );

        DB::table($templisttruckingrekap)->insertUsing([
            'jobtrucking',
        ], $querylisttruckingrekap);

        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
            $table->integer('gandengan_id')->nullable();
            $table->integer('jenisorder_id')->nullable();
            $table->integer('agen_id')->nullable();
            $table->integer('container_id')->nullable();
            $table->string('nojob', 500)->nullable();
            $table->string('nojob2', 500)->nullable();
            $table->string('nocont', 500)->nullable();
            $table->string('nocont2', 500)->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('namagudang', 500)->nullable();
            $table->string('noinvoice', 500)->nullable();
        });

        $querydata = DB::table($templisttruckingrekap)->from(
            DB::raw($templisttruckingrekap . " a")
        )
            ->select(
                'a.jobtrucking',
                DB::Raw("max(b.gandengan_id) as gandengan_id"),
                DB::Raw("max(b.jenisorder_id) as jenisOrder_id"),
                DB::Raw("max(b.agen_id) as agen_id"),
                DB::Raw("max(b.container_id) as container_id"),
                DB::Raw("max(b.nojob) as nojob"),
                DB::Raw("max(b.nojob2) as nojob2"),
                DB::Raw("max(b.nocont) as nocont"),
                DB::Raw("max(b.nocont2) as nocont2"),
                DB::Raw("max(B.trado_id) as trado_id"),
                DB::Raw("max(B.Supir_id) as supir_id"),
                DB::Raw("max(B.gudang) as namagudang"),
                DB::Raw("max(isnull(C.nobukti,'')) as noinvoice")
            )
            ->leftjoin(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw("invoicechargegandengandetail as c with (readuncommitted)"), 'a.jobtrucking', 'c.jobtrucking')
            ->whereRaw("isnull(c.jobtrucking,'')=''")
            ->groupBY('a.jobtrucking');

        DB::table($tempdata)->insertUsing([
            'jobtrucking',
            'gandengan_id',
            'jenisorder_id',
            'agen_id',
            'container_id',
            'nojob',
            'nojob2',
            'nocont',
            'nocont2',
            'trado_id',
            'supir_id',
            'namagudang',
            'noinvoice',
        ], $querydata);

        $query = DB::table($listtempjobtruckingakhir)->from(
            DB::raw($listtempjobtruckingakhir . " a")
        )
            ->select(
                'a.jobtrucking',
                DB::raw("isnull(c.kodegandengan,'') as gandengan"),
                DB::raw("a.tglsampai as tglawal"),
                DB::raw("a.tglkembali as tglkembali"),
                DB::raw("(a.hari-a.harilibur)  as jumlahhari"),
                DB::raw("isnull(d.keterangan,'') as jenisorder"),
                DB::raw("isnull(e.namaagen ,'') as namaemkl"),
                DB::raw("isnull(f.kodecontainer,'') as ukurancontainer"),
                DB::raw("isnull(b.nojob,'') as nojob"),
                DB::raw("isnull(b.nojob2,'') as nojob2"),
                DB::raw("isnull(b.nocont,'') as nocont"),
                DB::raw("isnull(b.nocont2,'') as nocont2"),
                DB::raw("isnull(g.kodetrado,'') as kodetrado"),
                DB::raw("isnull(h.namasupir ,'') as supir"),
                DB::raw("isnull(b.namagudang,'') as namagudang"),
                DB::raw("isnull(b.noinvoice,'') as noinvoice"),
                'b.trado_id',
                'b.gandengan_id',
                'b.agen_id',
            )
            ->leftjoin(DB::raw($tempdata . " as b "), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw("gandengan as c with (readuncommitted)"), 'b.gandengan_id', 'c.id')
            ->leftjoin(DB::raw("jenisorder as d with (readuncommitted)"), 'b.jenisorder_id', 'd.id')
            ->leftjoin(DB::raw("agen as e with (readuncommitted)"), 'b.agen_id', 'e.id')
            ->leftjoin(DB::raw("container as f with (readuncommitted)"), 'b.container_id', 'f.id')
            ->leftjoin(DB::raw("trado as g with (readuncommitted)"), 'b.trado_id', 'g.id')
            ->leftjoin(DB::raw("supir as h with (readuncommitted)"), 'b.supir_id', 'h.id')
            ->orderBy('a.tglsampai', 'asc');

        return $query;
    }



    public function getjumlahharilibur($ptgl1, $ptgl2)
    {
        $pjumlah = 0;
        dump($ptgl1);
        dd($ptgl2);
        $atgl1 = date('Y-m-d', strtotime($ptgl1));
        $atgl2 = date('Y-m-d', strtotime($ptgl2));

        while ($atgl1 <= $atgl2) {
            $datepart = DB::select("select datepart(dw," . $atgl1 . ") as dpart");
            $dpart = json_decode(json_encode($datepart), true)[0]['dpart'];
            if ($dpart == 1) {
                $pjumlah = $pjumlah + 1;
            }
            $querylibur = DB::table('harilibur')->from(
                db::raw("harilibur as a with (readuncommitted)")
            )
                ->select(
                    'tgl'
                )->where('tgl', '=', $atgl1)
                ->first();
            if (isset($querylibur)) {
                $pjumlah = $pjumlah + 1;
            }
            $atgl1 = date("Y-m-d", strtotime("+1 day", strtotime($atgl1)));
        }
        // dd($pjumlah);
        return $pjumlah;
    }

    public function getOrderanTrip($tglproses, $agen, $idinvoice)
    {
        $queryagen = DB::table('agen')->from(
            DB::raw("agen a with (readuncommitted)")
        )
            ->select(
                'a.kodeagen'
            )->where('a.id', '=', $agen)
            ->first();

        $queryinvoice = DB::table('invoicechargegandenganheader')->from(
            DB::raw("invoicechargegandenganheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )->where('a.id', '=', $idinvoice)
            ->first();

        if (isset($queryinvoice)) {
            $noinvoice = $queryinvoice->nobukti;
        } else {
            $noinvoice = '';
        }
        $querysp = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar a with (readuncommitted)")
        )
            ->select(
                'a.jobtrucking',
                DB::raw("max(c.kodecontainer) as container"),
                DB::raw("max(a.nojob) as nojob"),
                DB::raw("max(a.nojob2) as nojob2"),
                DB::raw("max(a.nocont) as nocont"),
                DB::raw("max(a.nocont2) as nocont2"),
                DB::raw("max(a.supir_id) as supir_id"),

            )
            ->join(db::raw("invoicechargegandengandetail b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
            ->join(db::raw("container c with (readuncommitted)"), 'a.container_id', 'c.id')
            ->groupBy('a.jobtrucking');

        $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsp, function ($table) {
            $table->string('jobtrucking', 50)->nullable();
            $table->string('container', 500)->nullable();
            $table->string('nojob', 500)->nullable();
            $table->string('nojob2', 500)->nullable();
            $table->string('nocont', 500)->nullable();
            $table->string('nocont2', 500)->nullable();
            $table->integer('supir_id')->nullable();
        });

        DB::table($tempsp)->insertUsing([
            'jobtrucking',
            'container',
            'nojob',
            'nojob2',
            'nocont',
            'nocont2',
            'supir_id',
        ], $querysp);


        $this->setRequestParameters();

        $tempdatalist = '##tempdatalist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatalist, function ($table) {
            $table->id();
            $table->string('jobtrucking', 50)->nullable();
            $table->string('gandengan', 500)->nullable();
            $table->date('tglawal')->nullable();
            $table->date('tglkembali')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->string('jenisorder', 500)->nullable();
            $table->string('namaemkl', 500)->nullable();
            $table->string('ukurancontainer', 500)->nullable();
            $table->string('nojob', 500)->nullable();
            $table->string('nojob2', 500)->nullable();
            $table->string('nocont', 500)->nullable();
            $table->string('nocont2', 500)->nullable();
            $table->string('kodetrado', 500)->nullable();
            $table->string('supir', 500)->nullable();
            $table->string('namagudang', 500)->nullable();
            $table->string('noinvoice', 500)->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('gandengan_id')->nullable();
            $table->integer('agen_id')->nullable();
        });

        $query = DB::table("invoicechargegandengandetail")->from(
            db::raw("invoicechargegandengandetail a with (readuncommitted)")
        )
            ->select(
                'a.jobtrucking',
                'b.kodegandengan as gandengan',
                DB::raw("'1900/1/1' as tglawal"),
                DB::raw("'1900/1/1' as tglkembali"),
                'a.jumlahhari',
                'a.jenisorder',
                DB::raw("'" . $queryagen->kodeagen . "' as namaemkl"),
                'c.container as ukurancontainer',
                'c.nojob',
                'c.nojob2',
                'c.nocont',
                'c.nocont2',
                'd.kodetrado',
                'e.namasupir as supir',
                'a.namagudang',
                DB::raw("'" . $noinvoice . "' as noinvoice"),
                'a.trado_id',
                'a.gandengan_id',
                DB::raw($agen . " as agen_id")
            )
            ->join(db::raw("gandengan b with (readuncommitted)"), 'a.gandengan_id', 'b.id')
            ->join(DB::raw($tempsp . " c"), 'a.jobtrucking', 'c.jobtrucking')
            ->join(db::raw("trado d with (readuncommitted)"), 'a.trado_id', 'd.id')
            ->join(db::raw("supir e with (readuncommitted)"), 'c.supir_id', 'e.id')
            ->where('invoicechargegandengan_id', '=', $idinvoice);

        // dd($query->toSql());

        DB::table($tempdatalist)->insertUsing([
            'jobtrucking',
            'gandengan',
            'tglawal',
            'tglkembali',
            'jumlahhari',
            'jenisorder',
            'namaemkl',
            'ukurancontainer',
            'nojob',
            'nojob2',
            'nocont',
            'nocont2',
            'kodetrado',
            'supir',
            'namagudang',
            'noinvoice',
            'trado_id',
            'gandengan_id',
            'agen_id',
        ],  $query);

        DB::table($tempdatalist)->insertUsing([
            'jobtrucking',
            'gandengan',
            'tglawal',
            'tglkembali',
            'jumlahhari',
            'jenisorder',
            'namaemkl',
            'ukurancontainer',
            'nojob',
            'nojob2',
            'nocont',
            'nocont2',
            'kodetrado',
            'supir',
            'namagudang',
            'noinvoice',
            'trado_id',
            'gandengan_id',
            'agen_id',
        ], $this->reminderchargegandengan());

        $query = DB::table($tempdatalist)->from(
            DB::raw($tempdatalist . " a")
        )
            ->select(
                'a.id',
                DB::raw("isnull(a.jobtrucking,'') as jobtrucking"),
                DB::raw("isnull(a.noinvoice,'') as noinvoice"),
                DB::raw("a.tglawal as tgltrip"),
                DB::raw("a.tglkembali as tglkembali"),
                DB::raw("a.jumlahhari as jumlahhari"),
                DB::raw("(a.jumlahhari-5)*300000 as nominal_detail"),
                DB::raw("isnull(a.kodetrado ,'') as nopolisi"),
                DB::raw("isnull(a.trado_id ,0) as trado_id"),
                DB::raw("isnull(a.gandengan ,'') as gandengan"),
                DB::raw("isnull(a.gandengan_id ,0) as gandengan_id"),
                DB::raw("isnull(a.jenisorder ,'') as jenisorder"),
                DB::raw("isnull(a.namagudang ,'') as namagudang"),
                DB::raw("'' as keterangan"),
            )
            ->where('a.agen_id', '=', $agen);
        $this->filterInvoice($query);

        $this->totalNominal = $query->sum(DB::raw("(a.jumlahhari-5)*300000"));
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sortInvoice($query);
        $query->skip($this->params['offset'])->take($this->params['limit']);
        $data = $query->get();
        // dd($data);

        return $data;
    }


    public function sortInvoice($query)
    {
        if ($this->params['sortIndex'] == 'nopolisi') {
            return $query->orderBy('a.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tgltrip') {
            return $query->orderBy('a.tglawal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominal_detail') {
            return $query->orderBy(DB::raw("(a.jumlahhari-5)*300000"), $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filterInvoice($query)
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'nopolisi') {
                                $query = $query->where('a.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tgltrip') {
                                $query = $query->whereRaw("format(a.tglawal,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglkembali') {
                                $query = $query->whereRaw("format(a.tglkembali,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $query = $query->whereRaw("format((a.jumlahhari-5)*300000, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'nopolisi') {
                                    $query = $query->orWhere('a.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tgltrip') {
                                    $query = $query->orWhereRaw("format(a.tglawal,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglkembali') {
                                    $query = $query->orWhereRaw("format(a.tglkembali,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominal_detail') {
                                    $query = $query->orWhereRaw("format((a.jumlahhari-5)*300000, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
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
    }


    public function agen()
    {
        return $this->belongsTo(Agen::class, 'agen_id');
    }

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }

    public function jenisorder()
    {
        return $this->belongsTo(JenisOrder::class, 'jenisorder_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function tarif()
    {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'container.keterangan as container_id',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'pelanggan.namapelanggan as pelanggan_id',
            'tarif.tujuan as tarif_id',
            $this->table.nominal,
            $this->table.nojobemkl,
            $this->table.nocont,
            $this->table.noseal,
            $this->table.nojobemkl2,
            $this->table.nocont2,
            $this->table.noseal2,
            'parameter.text as statuslangsir',
            'param2.text as statusperalihan',
            'statusapprovalbukatrip.text as statusapprovalbukatrip',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter AS statusapprovalbukatrip with (readuncommitted)"), 'orderantrucking.statusapprovalbukatrip', '=', 'statusapprovalbukatrip.id')
            ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'orderantrucking.statusperalihan', '=', 'param2.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('container_id', 1000)->nullable();
            $table->string('agen_id', 1000)->nullable();
            $table->string('jenisorder_id', 1000)->nullable();
            $table->string('pelanggan_id', 1000)->nullable();
            $table->string('tarif_id', 1000)->nullable();
            $table->string('nominal', 1000)->nullable();
            $table->string('nojobemkl', 1000)->nullable();
            $table->string('nocont', 1000)->nullable();
            $table->string('noseal', 1000)->nullable();
            $table->string('nojobemkl2', 1000)->nullable();
            $table->string('nocont2', 1000)->nullable();
            $table->string('noseal2', 1000)->nullable();
            $table->string('statuslangsir', 1000)->nullable();
            $table->string('statusperalihan', 1000)->nullable();
            $table->string('statusapprovalbukatrip', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'container_id', 'agen_id', 'jenisorder_id', 'pelanggan_id', 'tarif_id', 'nominal', 'nojobemkl', 'nocont', 'noseal', 'nojobemkl2', 'nocont2', 'noseal2', 'statuslangsir', 'statusperalihan', 'statusapprovalbukatrip', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tarif_id') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
        } else {
            return $query->orderBy('orderantrucking.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuslangsir') {
                                $query = $query->where('parameter.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'statusperalihan') {
                                $query = $query->where('param2.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'statusapprovalbukatrip') {
                                $query = $query->where('statusapprovalbukatrip.text', '=', "$filters[data]");
                            } elseif ($filters['field'] == 'agen_id') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'container_id') {
                                $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'tarif_id') {
                                $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'jenisorder_id') {
                                $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(orderantrucking.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format(orderantrucking." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(orderantrucking." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("orderantrucking.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuslangsir') {
                                $query = $query->orWhere('parameter.text', '', "$filters[data]");
                            } elseif ($filters['field'] == 'statusperalihan') {
                                $query = $query->orWhere('param2.text', '', "$filters[data]");
                            } elseif ($filters['field'] == 'statusapprovalbukatrip') {
                                $query = $query->orWhere('statusapprovalbukatrip.text', '', "$filters[data]");
                            } elseif ($filters['field'] == 'agen_id') {
                                $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'pelanggan_id') {
                                $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'container_id') {
                                $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'tarif_id') {
                                $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'jenisorder_id') {
                                $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format(orderantrucking.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(orderantrucking." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(orderantrucking." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("orderantrucking.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function getExport($dari, $sampai)
    {
        $this->setRequestParameters();

        $getParameter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text as judul',
                DB::raw("'Laporan Orderan Trucking' as judulLaporan")
            )->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'tarif.tujuan as tarif_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                DB::raw("'" . $dari . "' as tgldari"),
                DB::raw("'" . $sampai . "' as tglsampai"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))])
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'orderantrucking.statusperalihan', '=', 'param2.id');

        $data = $query->get();
        $allData = [
            'data' => $data,
            'parameter' => $getParameter
        ];
        return $allData;
    }

    public function processStore(array $data): OrderanTrucking
    {
        $orderanTrucking = new OrderanTrucking();
        $group = 'ORDERANTRUCKING';
        $subGroup = 'ORDERANTRUCKING';
        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $defaultapproval = DB::table('parameter')
            ->where('grp', 'STATUS APPROVAL')
            ->where('subgrp', 'STATUS APPROVAL')
            ->where('text', 'NON APPROVAL')
            ->first();

        $orderanTrucking->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $orderanTrucking->container_id = $data['container_id'];
        $orderanTrucking->agen_id = $data['agen_id'];
        $orderanTrucking->jenisorder_id = $data['jenisorder_id'];
        $orderanTrucking->pelanggan_id = $data['pelanggan_id'];
        $orderanTrucking->tarif_id = $data['tarifrincian_id'] ?? '';
        $orderanTrucking->nojobemkl = $data['nojobemkl'] ?? '';
        $orderanTrucking->nocont = $data['nocont'];
        $orderanTrucking->noseal = $data['noseal'];
        $orderanTrucking->nojobemkl2 = $data['nojobemkl2'] ?? '';
        $orderanTrucking->nocont2 = $data['nocont2'] ?? '';
        $orderanTrucking->noseal2 = $data['noseal2'] ?? '';
        $orderanTrucking->statuslangsir = $data['statuslangsir'];
        $orderanTrucking->statusapprovalbukatrip = $defaultapproval->id;
        $orderanTrucking->statusperalihan = $data['statusperalihan'];
        $orderanTrucking->modifiedby = auth('api')->user()->name;
        $orderanTrucking->statusformat = $format->id;

        $tarifrincian = TarifRincian::find($data['tarifrincian_id']);
        $orderanTrucking->nominal = $tarifrincian->nominal ?? '';
        $orderanTrucking->nobukti = (new RunningNumberService)->get($group, $subGroup, $orderanTrucking->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$orderanTrucking->save()) {
            throw new \Exception("Error orderan trucking.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($orderanTrucking->getTable()),
            'postingdari' => 'ENTRY ORDERAN TRUCKING',
            'idtrans' => $orderanTrucking->id,
            'nobuktitrans' => $orderanTrucking->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $orderanTrucking->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        return $orderanTrucking;
    }

    public function processUpdate(OrderanTrucking $orderanTrucking, array $data): OrderanTrucking
    {

        $defaultapproval = DB::table('parameter')
            ->where('grp', 'STATUS APPROVAL')
            ->where('subgrp', 'STATUS APPROVAL')
            ->where('text', 'NON APPROVAL')
            ->first();

        $orderanTrucking->container_id = $data['container_id'];
        $orderanTrucking->agen_id = $data['agen_id'];
        $orderanTrucking->jenisorder_id = $data['jenisorder_id'];
        $orderanTrucking->pelanggan_id = $data['pelanggan_id'];
        $orderanTrucking->tarif_id = $data['tarifrincian_id'] ?? 0;
        $orderanTrucking->nojobemkl = $data['nojobemkl'] ?? '';
        $orderanTrucking->nocont = $data['nocont'];
        $orderanTrucking->noseal = $data['noseal'];
        $orderanTrucking->nojobemkl2 = $data['nojobemkl2'] ?? '';
        $orderanTrucking->nocont2 = $data['nocont2'] ?? '';
        $orderanTrucking->noseal2 = $data['noseal2'] ?? '';
        $orderanTrucking->statuslangsir = $data['statuslangsir'];
        $orderanTrucking->statusperalihan = $data['statusperalihan'];
        $orderanTrucking->statusapprovalbukatrip = $defaultapproval->id;

        $orderanTrucking->modifiedby = auth('api')->user()->name;

        $tarifrincian = TarifRincian::from(DB::raw("tarifrincian"))->where('tarif_id', $data['tarifrincian_id'])->where('container_id', $data['container_id'])->first();
        $orderanTrucking->nominal = $tarifrincian->nominal ?? 0;

        if (!$orderanTrucking->save()) {
            throw new \Exception("Error updating orderan trucking.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($orderanTrucking->getTable()),
            'postingdari' => 'EDIT ORDERAN TRUCKING',
            'idtrans' => $orderanTrucking->id,
            'nobuktitrans' => $orderanTrucking->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $orderanTrucking->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        $get = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('id', 'nominalperalihan', 'qtyton', 'nojob', 'nocont', 'noseal', 'nojob2', 'nocont2', 'noseal2', 'pelanggan_id', 'agen_id', 'jenisorder_id', 'container_id')
            ->where('jobtrucking', $orderanTrucking->nobukti)->get();

        $datadetail = json_decode($get, true);
        if (count($datadetail) > 0) {
            foreach ($datadetail as $item) {
                $suratPengantar = [
                    'proseslain' => '1',
                    'jobtrucking' => $orderanTrucking->nobukti,
                    'nojob' =>  $data['nojobemkl'] ?? '',
                    'nocont' =>  $data['nocont'] ?? '',
                    'noseal' =>  $data['noseal'] ?? '',
                    'nojob2' =>  $data['nojobemkl2'] ?? '',
                    'nocont2' =>  $data['nocont2'] ?? '',
                    'noseal2' =>  $data['noseal2'] ?? '',
                    'container_id' => $data['container_id'],
                    'agen_id' => $data['agen_id'],
                    'jenisorder_id' => $data['jenisorder_id'],
                    'pelanggan_id' => $data['pelanggan_id'],
                    'tarif_id' => $data['tarifrincian_id'],
                    'postingdari' => 'EDIT ORDERAN TRUCKING'
                ];
                $newSuratPengantar = new SuratPengantar();
                $newSuratPengantar = $newSuratPengantar->findAll($item['id']);
                (new SuratPengantar())->processUpdate($newSuratPengantar, $suratPengantar);
            }
        }
        return $orderanTrucking;
    }

    public function processDestroy($id): OrderanTrucking
    {
        $orderanTrucking = new OrderanTrucking();
        $orderanTrucking = $orderanTrucking->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => $orderanTrucking->getTable(),
            'postingdari' => 'DELETE ORDERAN TRUCKING',
            'idtrans' => $orderanTrucking->id,
            'nobuktitrans' => $orderanTrucking->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $orderanTrucking->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $orderanTrucking;
    }

    public function processApproval(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['orderanTruckingId']); $i++) {
            $orderanTrucking = OrderanTrucking::find($data['orderanTruckingId'][$i]);
            if ($orderanTrucking->statusapprovalbukatrip == $statusApproval->id) {
                $orderanTrucking->statusapprovalbukatrip = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $orderanTrucking->statusapprovalbukatrip = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $orderanTrucking->tglapprovalbukatrip = date('Y-m-d H:i:s');
            $orderanTrucking->userapprovalbukatrip = auth('api')->user()->name;

            if (!$orderanTrucking->save()) {
                throw new \Exception('Error Un/approval orderan Trucking.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($orderanTrucking->getTable()),
                'postingdari' => "UN/APPROVAL orderan Trucking",
                'idtrans' => $orderanTrucking->id,
                'nobuktitrans' => $orderanTrucking->nobukti,
                'aksi' => $aksi,
                'datajson' => $orderanTrucking->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $orderanTrucking;
        }

        return $result;
    }

    public function processApprovalEdit(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['orderanTruckingId']); $i++) {
            $orderanTrucking = OrderanTrucking::find($data['orderanTruckingId'][$i]);
            if ($orderanTrucking->statusapprovaledit == $statusApproval->id) {
                $orderanTrucking->statusapprovaledit = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $orderanTrucking->statusapprovaledit = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $orderanTrucking->tglapprovaledit = date('Y-m-d H:i:s');
            $orderanTrucking->userapprovaledit = auth('api')->user()->name;

            if (!$orderanTrucking->save()) {
                throw new \Exception('Error Un/approval orderan Trucking.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($orderanTrucking->getTable()),
                'postingdari' => "UN/APPROVAL orderan Trucking",
                'idtrans' => $orderanTrucking->id,
                'nobuktitrans' => $orderanTrucking->nobukti,
                'aksi' => $aksi,
                'datajson' => $orderanTrucking->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $orderanTrucking;
        }

        return $result;
    }
}
