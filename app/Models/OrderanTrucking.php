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

    public function get()
    {
        $this->setRequestParameters();

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
                'parameter.memo as statuslangsir',
                'param2.memo as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'orderantrucking.statuslangsir', '=', 'parameter.id')
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
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->where('orderantrucking.id', $id);

        $data = $query->first();

        return $data;
    }

    public function reminderchargegandengan()
    {


        $ptglmulai = '2022/11/1';
        $pdariid = 1;

        $tempjobtrucking = '##tempjobtrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempjobtrucking, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
        });

        $queryjobtrucking = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar a with (readuncommitted)")
        )
            ->select(
                'a.jobtrucking',
            )
            ->groupby('a.jobtrucking');


        DB::table($tempjobtrucking)->insertUsing([
            'jobtrucking',
        ], $queryjobtrucking);

        $tempawaltrip = '##tempawaltrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempawaltrip, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->date('tgl')->nullable();
            $table->string('nogandengan', 1000)->nullable();
            $table->string('trado', 1000)->nullable();
            $table->string('supir', 1000)->nullable();
            $table->string('namagudang', 1000)->nullable();
        });

        $tempakhirtrip = '##tempakhirtrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempakhirtrip, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->date('tgl')->nullable();
            $table->string('nogandengan', 1000)->nullable();
        });

        $statusnonlangsir = DB::table('parameter')->from(
            DB::raw("parameter a with (readuncommitted)")
        )->select(
            'id'
        )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('text', '=', 'BUKAN LANGSIR')
            ->first();

// dd('test');

        $queryawaltrip = DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a ")
        )
            ->select(
                'a.jobtrucking',
                db::raw("min(b.tglbukti) as tgl"),
                db::raw("max(c.kodegandengan) as nogandengan"),
                db::raw("max(d.kodetrado) as trado"),
                db::raw("max(e.namasupir) as supir"),
                db::raw("max(f.namapelanggan) as namagudang"),
            )
            ->join(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw("gandengan as c with (readuncommitted)"), 'b.gandengan_id', 'c.id')
            ->leftjoin(DB::raw("trado as d with (readuncommitted)"), 'b.trado_id', 'd.id')
            ->leftjoin(DB::raw("supir as e with (readuncommitted)"), 'b.supir_id', 'e.id')
            ->leftjoin(DB::raw("pelanggan as f with (readuncommitted)"), 'b.pelanggan_id', 'f.id')
            ->join(DB::raw("orderantrucking as g with (readuncommitted)"), 'a.jobtrucking', 'g.nobukti')
            ->where('b.dari_id', '=', $pdariid)
            ->whereRaw("isnull(g.statuslangsir,0)=". $statusnonlangsir->id)
            ->groupby('a.jobtrucking');

            // dd($queryawaltrip->get());

        DB::table($tempawaltrip)->insertUsing([
            'jobtrucking',
            'tgl',
            'nogandengan',
            'trado',
            'supir',
            'namagudang',
        ], $queryawaltrip);

        
       

        $queryawaltrip = DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a ")
        )
            ->select(
                'a.jobtrucking',
                db::raw("min(b.tglbukti) as tgl"),
                db::raw("max(c1.kodegandengan) as nogandengan"),
                db::raw("max(d.kodetrado) as trado"),
                db::raw("max(e.namasupir) as supir"),
                db::raw("max(f.namapelanggan) as namagudang"),
            )
            ->join(DB::raw("orderantrucking as c with(readuncommitted)"), function ($join) {
                $join->on('a.jobtrucking', '=', 'c.jobtruckingasal');
                $join->on(DB::raw("isnull(c.jobtruckingasal,'')"),'<>', db::raw("''"));
            })
            ->join(DB::raw("suratpengantar as b with (readuncommitted)"), 'c.jobtruckingasal', 'b.jobtrucking')
            ->leftjoin(DB::raw("gandengan as c1 with (readuncommitted)"), 'b.gandengan_id', 'c1.id')
            ->leftjoin(DB::raw("trado as d with (readuncommitted)"), 'b.trado_id', 'd.id')
            ->leftjoin(DB::raw("supir as e with (readuncommitted)"), 'b.supir_id', 'e.id')
            ->leftjoin(DB::raw("pelanggan as f with (readuncommitted)"), 'b.pelanggan_id', 'f.id')
            ->where('b.dari_id', '=', $pdariid)
            ->whereRaw("isnull(c.statuslangsir,0)=". $statusnonlangsir->id)
            ->groupby('a.jobtrucking');

            
dd($queryawaltrip->get());
           
        DB::table($tempawaltrip)->insertUsing([
            'jobtrucking',
            'tgl',
            'nogandengan',
            'trado',
            'supir',
            'namagudang',
        ], $queryawaltrip);

        dd('test');
 
        $queryakhirtrip = DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a ")
        )
            ->select(
                'b.jobtrucking as jobtrucking',
                db::raw("max(b.tglbukti) as tgl"),
                db::raw("max(c.kodegandengan) as nogandengan"),
            )
            ->join(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.jobtruckingasal', 'b.jobtrucking')
            ->leftjoin(DB::raw("gandengan as c with (readuncommitted)"), 'b.gandengan_id', 'c.id')
            ->where('b.dari_id', '=', $pdariid)
            ->groupby('a.jobtrucking');

        DB::table($tempakhirtrip)->insertUsing([
            'jobtrucking',
            'tgl',
            'nogandengan',
        ], $queryakhirtrip);


        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->string('nogandengan', 1000)->nullable();
            $table->date('tglawal')->nullable();
            $table->date('tglakhir')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->string('trado', 1000)->nullable();
            $table->string('supir', 1000)->nullable();
            $table->string('namagudang', 1000)->nullable();
        });

        $queryhasil =  DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a ")
        )
            ->select(
                'a.jobtrucking as jobtrucking',
                db::raw("isnull(b.nogandengan,'') as nogandengan"),
                db::raw("isnull(b.tgl,'') as tglawal"),
                db::raw("isnull(c.tgl,'') as tglakhir"),
                db::raw("((datediff(day,  b.tgl,getdate())+1)-" .
                    $this->getjumlahharilibur(db::raw("b.tgl"), DB::raw("getdate()"))
                    . ") as jumlahhari"),
                'b.trado',
                'b.supir',
                'b.namagudang'

            )
            ->leftjoin(DB::raw($tempawaltrip . " as b "), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw($tempakhirtrip . " as c "), 'a.jobtrucking', 'c.jobtrucking')
            ->whereRaw("len(ltrim(rtrim(a.jobtrucking)))>4")
            ->whereRaw("year(isnull(c.tgl,'1900/1/1'))=1900")
            ->whereRaw("year(isnull(b.Tgl,'1900/1/1'))<>1900")
            ->whereRaw("b.tgl>=" . $ptglmulai);

        DB::table($temphasil)->insertUsing([
            'jobtrucking',
            'nogandengan',
            'tglawal',
            'tglakhir',
            'jumlahhari',
            'trado',
            'supir',
            'namagudang',
        ], $queryhasil);

        $queryhasil =  DB::table($tempjobtrucking)->from(
            DB::raw($tempjobtrucking . " a ")
        )
            ->select(
                'a.jobtrucking as jobtrucking',
                db::raw("isnull(b.nogandengan,'') as nogandengan"),
                db::raw("isnull(b.tgl,'') as tglawal"),
                db::raw("isnull(c.tgl,'') as tglakhir"),
                db::raw("((datediff(day,b.tgl,c.tgl)+1)-" .
                    $this->getjumlahharilibur(db::raw("b.tgl"), DB::raw("c.tgl"))
                    . ") as jumlahhari"),
                'b.trado',
                'b.supir',
                'b.namagudang'

            )
            ->leftjoin(DB::raw($tempawaltrip . " as b "), 'a.jobtrucking', 'b.jobtrucking')
            ->leftjoin(DB::raw($tempakhirtrip . " as c "), 'a.jobtrucking', 'c.jobtrucking')
            ->whereRaw("len(ltrim(rtrim(a.jobtrucking)))>4")
            ->whereRaw("year(isnull(c.tgl,'1900/1/1'))<>1900")
            ->whereRaw("year(isnull(b.Tgl,'1900/1/1'))<>1900")
            ->whereRaw("b.tgl>=" . $ptglmulai)
            ->whereRaw(
                "((DATEDIFF(day,  B.Ftgl,C.Ftgl)+1)-"
                    . $this->getjumlahharilibur(db::raw("b.tgl"), DB::raw("c.tgl")) . ">6"
            );

        DB::table($temphasil)->insertUsing([
            'jobtrucking',
            'nogandengan',
            'tglawal',
            'tglakhir',
            'jumlahhari',
            'trado',
            'supir',
            'namagudang',
        ], $queryhasil);

        $listjobtrucking = '##listjobtrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($listjobtrucking, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->date('tgl')->nullable();
            $table->string('kota', 1000)->nullable();
        });

        $listjobtruckingrekap = '##listjobtruckingrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($listjobtruckingrekap, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
        });

        $querylistjobtruckingrekap =  DB::table($temphasil)->from(
            DB::raw($temphasil . " a ")
        )
            ->select(
                'a.jobtrucking as jobtrucking',
            )
            ->join(DB::raw("orderantrucking as b with (readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
            ->join(DB::raw("orderantrucking as c with(readuncommitted)"), function ($join) {
                $join->on('a.jobtrucking', '=', 'c.jobtruckingasal');
                $join->on(DB::raw("isnull(c.jobtruckingasal,'')"), '<>', '');
            })
            ->whereRaw("isnull(b.statusapprovalnonchargegandengan,4)=4")
            ->whereRaw("isnull(c.nobukti,'')=''")
            ->orderBy('a.jumlahhari', 'desc');

        DB::table($listjobtruckingrekap)->insertUsing([
            'jobtrucking',
        ], $querylistjobtruckingrekap);


        $querylistjobtrucking =  DB::table('suratpengantar')->from(
            DB::raw("suratpengantar a with (readuncommitted)")
        )
            ->select(
                'a.jobtrucking as jobtrucking',
                'a.tgl as tgl',
                'c.kodekota as kota',
            )
            ->join(DB::raw($listjobtruckingrekap . " as b "), 'a.jobtrucking', 'b.jobtrucking')
            ->join(DB::raw("kota as c with (readuncommitted)"), 'a.sampai_id', 'c.id')
            ->join(DB::raw("orderantrucking as d with (readuncommitted)"), 'a.jobtrucking', 'd.nobukti')

            ->whereRaw("c.kodekota not in(
                     'BELAWAN', 'KIM (KANDANG)','KANDANG'     
                     )")
            ->whereRaw("isnull(d.statusjoblangsir,0)=0");

        DB::table($listjobtrucking)->insertUsing([
            'jobtrucking',
            'tgl',
            'kota',
        ], $querylistjobtrucking);

        $listjobtruckinglist = '##listjobtruckinglist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($listjobtruckinglist, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->date('tgl')->nullable();
            $table->string('kota', 1000)->nullable();
        });

        $querylistjobtruckinglist = DB::table($listjobtrucking)->from(
            DB::raw($listjobtrucking . " a ")
        )
            ->select(
                'a.jobtrucking as jobtrucking',
                db::raw("max(a.tgl) as tgl"),
                db::raw("max(a.kota) as kota"),
            )
            ->groupby('a.jobtrucking');

        DB::table($listjobtruckinglist)->insertUsing([
            'jobtrucking',
            'tgl',
            'kota',
        ], $querylistjobtruckinglist);

        $query = DB::table($temphasil)->from(
            DB::raw($temphasil . " a ")
        )
            ->select(
                'a.jobtrucking as jobtrucking',
            )
            ->join(DB::raw("orderantrucking as b with (readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
            ->leftjoin(DB::raw("orderantrucking as c with(readuncommitted)"), function ($join) {
                $join->on('a.jobtrucking', '=', 'c.jobtruckingasal');
                $join->on(DB::raw("isnull(c.jobtruckingasal,'')"), '<>', '');
            })
            ->leftjoin(DB::raw($listjobtruckinglist . " as d "), 'a.jobtrucking', 'd.jobtrucking')
            ->leftjoin(DB::raw("invoicechargegandengandetail as e "), 'a.jobtrucking', 'e.jobtrucking')
            ->leftjoin(DB::raw("pelunasanpiutangdetail as f "), 'e.nobukti', 'f.invoice_nobukti')
            ->whereRaw("isnull(b.statusapprovalnonchargegandengan,4)=4")
            ->whereRaw("isnull(c.nobukti,'')=''")
            ->whereRaw("isnull(f.nobukti,'')=''")
            ->orderBy('jumlahhari', 'desc');

        $data = $query->get();
        return $data;
    }
    public function getjumlahharilibur($ptgl1, $ptgl2)
    {
        $pjumlah = 0;
        $atgl1 = $ptgl1;
        $atgl2 = $ptgl2;

        while ($atgl1 <= $atgl2) {
            $datepart = DB::select(DB::raw("select datepart(dw," . $ptgl1 . ") as dpart"))->first();
            if ($datepart->dpart == 1) {
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

            $atgl1 = $atgl1 + 1;
        }
        return $pjumlah;
    }

    public function getOrderanTrip($tglproses, $agen)
    {
        //     create table #Tempdatalist(
        //         FJobTrucking varchar(100),
        //         FNoGandengan varchar(500),
        //         FTglAwal datetime,
        //         FTglAkhir datetime,
        //         FJumlahHari integer,
        //         FJenisOrderan varchar(100),
        //         FEmkl varchar(100),
        //         FUkuranContainer varchar(100),
        //         FJobEmkl1 varchar(100),
        //         FJobEmkl2  varchar(100),
        //         FNoCont1 varchar(100),
        //         FNoCont2 varchar(100),
        //         FKGdg varchar(100),
        //         FKSupir varchar(100),
        //         FNamaGudang varchar(1000),
        //         FNoInvoice varchar(100))

        //         insert into #Tempdatalist(
        //         FJobTrucking,FNoGandengan,FTglAwal,FTglAkhir,FJumlahHari,FJenisOrderan,FEmkl,FUkuranContainer,FJobEmkl1,FJobEmkl2 ,
        //         FNoCont1,FNoCont2,FKGdg,FKSupir,FNamaGudang,FNoInvoice)
        //         exec usp_reminderchargegandengan


        //        SELECT CAST(1 AS BIT) AS FPilih,H.FJobTrucking AS FNTransOrder, H.FTglAwal AS FTglOrder,(isnull(H.FJumlahHAri,0)-6) as FQty,
        //        300000 as FHrgSat,((isnull(H.FJumlahHAri,0)-6)*300000) FTotal,0 as FPDisc,0 as FNDisc,
        //          0 as FBiaya,((isnull(H.FJumlahHAri,0)-6)*300000)  as FNominal,
        //          'Charge Gandengan '+ltrim(rtrim(str((isnull(H.FJumlahHAri,0))))-6)+ ' Hari, Dari Tgl '+
        //          (case when day(H.FTglawal)>=10 then '' else '0' end)+ltrim(rtrim(str(day(H.FTglAwal)))) +'-'+
        //          (case when month(H.FTglawal)>=10 then '' else '0' end)+ltrim(rtrim(str(month(H.FTglAwal)))) +'-'+
        //          ltrim(rtrim(str(year(H.FTglAwal)))) +' Sampai '+
        //          (case when day(H.FTglakhir)>=10 then '' else '0' end)+ltrim(rtrim(str(day(H.FTglakhir)))) +'-'+
        //          (case when month(H.FTglakhir)>=10 then '' else '0' end)+ltrim(rtrim(str(month(H.FTglakhir)))) +'-'+
        //          ltrim(rtrim(str(year(H.FTglakhir))))+' Di Gudang '+ltrim(rtrim(H.FNamaGudang))
        //          as FKet,
        //          '' as FUserID,getdate() as FTglInput,H.FEMKL,H.FEMKL,H.FEMKL,H.FKGdg 
        //  FROM #Tempdatalist H 
        //  WHERE H.FJobTrucking NOT IN (SELECT FNTransOrder FROM [TrInvoiceCuciTrado_R] WHERE FNTrans<>@pNoBukti) 
        //          AND H.FEMKL=@pEMKL  and year(isnull(H.FTglAkhir,'1900/1/1'))<>1900 and H.FJumlahHari>6
        //          ORDER BY H.FJobTrucking


        //          $tempdatalist = '##tempdatalist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        //          Schema::create($tempdatalist, function ($table) {
        //              $table->string('jobtrucking', 1000)->nullable();
        //              $table->string('nogandengan', 1000)->nullable();
        //              $table->date('tglawal')->nullable();
        //              $table->date('tglakhir')->nullable();
        //              $table->integer('jumlahhari')->nullable();
        //              $table->string('jenisorderan', 1000)->nullable();
        //              $table->string('emkl', 1000)->nullable();
        //              $table->string('ukurancontainer', 1000)->nullable();
        //              $table->string('jobemkl1', 1000)->nullable();
        //              $table->string('jobemkl2', 1000)->nullable();
        //              $table->string('nocont1', 1000)->nullable();
        //              $table->string('nocont2', 1000)->nullable();
        //              $table->string('trado', 1000)->nullable();
        //              $table->string('supir', 1000)->nullable();
        //              $table->string('namagudang', 1000)->nullable();
        //              $table->string('noinvoice', 1000)->nullable();
        //             });             



        //     $data = [
        //         [
        //             "id" => 1,
        //             "jobtrucking" => "III/ V /00001",
        //             "tgltrip" => "2022-09-12",
        //             "jumlahhari" => "11",
        //             "nominal_detail" => "21000000",
        //             "nopolisi" => "B 9508 PH",
        //             "keterangan" => "keterangan id 1",
        //         ], [
        //             "id" => 2,
        //             "jobtrucking" => "III/ V /00002",
        //             "tgltrip" => "2022-09-12",
        //             "jumlahhari" => "12",
        //             "nominal_detail" => "22000000",
        //             "nopolisi" => "B 9120 QZ",
        //             "keterangan" => "keterangan id 2",
        //         ], [
        //             "id" => 3,
        //             "jobtrucking" => "III/ V /00003",
        //             "tgltrip" => "2022-09-12",
        //             "jumlahhari" => "13",
        //             "nominal_detail" => "23000000",
        //             "nopolisi" => "BK 8007 XA",
        //             "keterangan" => "keterangan id 3",
        //         ]
        //     ];
        $data = $this->reminderchargegandengan();

        return $data;
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'container_id', 'agen_id', 'jenisorder_id', 'pelanggan_id', 'tarif_id', 'nominal', 'nojobemkl', 'nocont', 'noseal', 'nojobemkl2', 'nocont2', 'noseal2', 'statuslangsir', 'statusperalihan', 'modifiedby', 'created_at', 'updated_at'], $models);


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
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsir') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statusperalihan') {
                            $query = $query->where('param2.text', '=', "$filters[data]");
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
                            $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsir') {
                            $query = $query->orWhere('parameter.text', '', "$filters[data]");
                        } elseif ($filters['field'] == 'statusperalihan') {
                            $query = $query->orWhere('param2.text', '', "$filters[data]");
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
                            $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

        $orderanTrucking->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $orderanTrucking->container_id = $data['container_id'];
        $orderanTrucking->agen_id = $data['agen_id'];
        $orderanTrucking->jenisorder_id = $data['jenisorder_id'];
        $orderanTrucking->pelanggan_id = $data['pelanggan_id'];
        $orderanTrucking->tarif_id = $data['tarifrincian_id'];
        $orderanTrucking->nojobemkl = $data['nojobemkl'] ?? '';
        $orderanTrucking->nocont = $data['nocont'];
        $orderanTrucking->noseal = $data['noseal'];
        $orderanTrucking->nojobemkl2 = $data['nojobemkl2'] ?? '';
        $orderanTrucking->nocont2 = $data['nocont2'] ?? '';
        $orderanTrucking->noseal2 = $data['noseal2'] ?? '';
        $orderanTrucking->statuslangsir = $data['statuslangsir'];
        $orderanTrucking->statusperalihan = $data['statusperalihan'];
        $orderanTrucking->modifiedby = auth('api')->user()->name;
        $orderanTrucking->statusformat = $format->id;

        $tarifrincian = TarifRincian::find($data['tarifrincian_id']);
        $orderanTrucking->nominal = $tarifrincian->nominal;
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
        $orderanTrucking->container_id = $data['container_id'];
        $orderanTrucking->agen_id = $data['agen_id'];
        $orderanTrucking->jenisorder_id = $data['jenisorder_id'];
        $orderanTrucking->pelanggan_id = $data['pelanggan_id'];
        $orderanTrucking->tarif_id = $data['tarifrincian_id'];
        $orderanTrucking->nojobemkl = $data['nojobemkl'] ?? '';
        $orderanTrucking->nocont = $data['nocont'];
        $orderanTrucking->noseal = $data['noseal'];
        $orderanTrucking->nojobemkl2 = $data['nojobemkl2'] ?? '';
        $orderanTrucking->nocont2 = $data['nocont2'] ?? '';
        $orderanTrucking->noseal2 = $data['noseal2'] ?? '';
        $orderanTrucking->statuslangsir = $data['statuslangsir'];
        $orderanTrucking->statusperalihan = $data['statusperalihan'];
        $orderanTrucking->modifiedby = auth('api')->user()->name;

        $tarifrincian = TarifRincian::from(DB::raw("tarifrincian"))->where('tarif_id', $data['tarifrincian_id'])->where('container_id', $data['container_id'])->first();
        $orderanTrucking->nominal = $tarifrincian->nominal;

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
            ->select('id', 'nominalperalihan', 'qtyton')
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
}
