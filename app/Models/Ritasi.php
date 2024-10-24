<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Ritasi extends MyModel
{
    use HasFactory;

    protected $table = 'ritasi';

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

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)
            ->select(
                'ritasi.id',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'parameter.text as statusritasi',
                'ritasi.suratpengantar_nobukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                'ritasi.jarak',
                'ritasi.upah',
                'ritasi.extra',
                'ritasi.gaji',
                'dari.keterangan as dari_id',
                'sampai.keterangan as sampai_id',
                'ritasi.modifiedby',
                'ritasi.created_at',
                'ritasi.updated_at',
                db::raw("cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadersuratpengantar"),
                db::raw("cast(cast(format((cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadersuratpengantar"),
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'ritasi.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', '=', 'sampai.id');

        if (request()->tgldari) {
            $query->whereBetween('ritasi.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusritasi')->nullable();
        });

        $statusritasi = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS RITASI')
            ->where('subgrp', '=', 'STATUS RITASI')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusritasi" => $statusritasi->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusritasi'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function find($id)
    {
        $query = DB::table('ritasi')->select(
            'ritasi.id',
            'ritasi.nobukti',
            'ritasi.tglbukti',
            'ritasi.dataritasi_id as statusritasi_id',
            'parameter.text as statusritasi',
            'ritasi.suratpengantar_nobukti',
            'ritasi.dari_id',
            'dari.kodekota as dari',
            'ritasi.sampai_id',
            'sampai.kodekota as sampai',
            'ritasi.trado_id',
            'trado.kodetrado as trado',
            'ritasi.supir_id',
            'supir.namasupir as supir'
        )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', '=', 'sampai.id')
            ->leftJoin(DB::raw("dataritasi with (readuncommitted)"), 'ritasi.dataritasi_id', 'dataritasi.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', 'dataritasi.statusritasi')
            ->where('ritasi.id', $id);

        $data = $query->first();
        return $data;
    }
    // public function selectColumns($query)
    // {
    //     return $query->select(
    //         DB::raw(
    //             "$this->table.id,
    //         $this->table.nobukti,
    //         $this->table.tglbukti,
    //         'parameter.text as statusritasi',
    //         'suratpengantar.nobukti as suratpengantar_nobukti',
    //         'supir.namasupir as supir_id',
    //         'trado.kodetrado as trado_id',
    //         $this->table.jarak,
    //         $this->table.gaji,
    //         'dari.keterangan as dari_id',
    //         'sampai.keterangan as sampai_id',
    //         $this->table.modifiedby,
    //         $this->table.created_at,
    //         $this->table.updated_at"
    //         )
    //     )
    //         ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
    //         ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
    //         ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
    //         ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
    //         ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
    //         ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id');
    // }

    // public function createTemp(string $modelTable)
    // {
    //     $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    //     Schema::create($temp, function ($table) {
    //         $table->bigInteger('id')->nullable();
    //         $table->string('nobukti', 1000)->nullable();
    //         $table->date('tglbukti')->nullable();
    //         $table->string('statusritasi', 1000)->nullable();
    //         $table->string('suratpengantar_nobukti', 1000)->nullable();
    //         $table->string('supir_id', 1000)->nullable();
    //         $table->string('trado_id', 1000)->nullable();
    //         $table->string('jarak', 1000)->nullable();
    //         $table->string('gaji', 1000)->nullable();
    //         $table->string('dari_id', 1000)->nullable();
    //         $table->string('sampai_id', 1000)->nullable();
    //         $table->string('modifiedby', 50)->nullable();
    //         $table->dateTime('created_at')->nullable();
    //         $table->dateTime('updated_at')->nullable();
    //         $table->increments('position');
    //     });
    //     if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
    //         request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
    //         request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
    //     }
    //     $this->setRequestParameters();
    //     $query = DB::table($modelTable);
    //     $query = $this->selectColumns($query);
    //     if (request()->tgldari) {
    //         $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
    //     }
    //     $this->sort($query);
    //     $models = $this->filter($query);
    //     $models = $query
    //         ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

    //     DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'statusritasi', 'suratpengantar_nobukti', 'supir_id', 'trado_id', 'jarak', 'gaji', 'dari_id', 'sampai_id', 'modifiedby', 'created_at', 'updated_at'], $models);


    //     return  $temp;
    // }

    public function selectColumns()
    {

        $getSudahbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'SUDAH BUKA')->first() ?? 0;
        $getBelumbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'BELUM BUKA')->first() ?? 0;

        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        $supirheader = request()->supirheader ?? 0;

        $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        $userid = auth('api')->user()->id;
        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.user_id', $userid);
        DB::table($tempmandordetail)->insertUsing([
            'mandor_id',
        ],  $querymandor);


        $tempspric = '##tempspric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempspric, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('ebsnobukti', 50)->nullable();
            $table->string('suratpengantar_nobukti', 50)->nullable();
        });
        $queryric = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail a with (readuncommitted)"))
            ->select(
                db::raw("max(a.nobukti) as nobukti"),
                db::raw("max(d.nobukti) as ebsnobukti"),
                'a.suratpengantar_nobukti'
            )
            ->join(db::raw("suratpengantar as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
            ->leftjoin(db::raw("prosesgajisupirdetail d with (readuncommitted)"), 'a.nobukti', 'd.gajisupir_nobukti')
            ->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])
            ->groupBy('a.suratpengantar_nobukti');

        if ($supirheader != 0) {
            $queryric->where('b.supir_id', $supirheader);
        }
        DB::table($tempspric)->insertUsing([
            'nobukti',
            'ebsnobukti',
            'suratpengantar_nobukti',
        ], $queryric);

        $tempsuratpengantarrinci = '##tempsuratpengantarrinci' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsuratpengantarrinci, function ($table) {
            $table->integer('id')->nullable();
            $table->integer('idoriginal')->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('ritasi_nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->string('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('keteranganritasi')->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('jarak', 15, 2)->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->string('agen_id')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('container_id')->nullable();
            $table->string('nocont')->nullable();
            $table->string('noseal')->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('gudang')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('gandengan_id')->nullable();
            $table->longText('statuslongtrip')->nullable();
            $table->longText('statusperalihan')->nullable();
            $table->longText('statusritasiomset')->nullable();
            $table->longText('statusapprovalmandor')->nullable();
            $table->longText('statusapprovalmandortext')->nullable();
            $table->dateTime('tglapprovalmandor')->nullable();
            $table->string('userapprovalmandor')->nullable();
            $table->string('tarif_id')->nullable();
            $table->string('mandortrado_id')->nullable();
            $table->string('mandorsupir_id')->nullable();
            $table->longText('statusgudangsama')->nullable();
            $table->longText('statusbatalmuat')->nullable();
            $table->string('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('flag')->nullable();
            $table->string('gajisupir_nobukti', 500)->nullable();
            $table->string('prosesgajisupir_nobukti', 500)->nullable();
            $table->unsignedBigInteger('statusgajisupir')->nullable();
        });
        $query = DB::table('suratpengantar')->select(
            'suratpengantar.id',
            'suratpengantar.id as idoriginal',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.kodekota as dari_id',
            'kotasampai.kodekota as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
            'suratpengantar.penyesuaian',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'container.keterangan as container_id',
            'suratpengantar.nocont',
            'suratpengantar.noseal',
            'statuscontainer.keterangan as statuscontainer_id',
            'suratpengantar.gudang',
            'trado.kodetrado as trado_id',
            'supir.namasupir as supir_id',
            'gandengan.keterangan as gandengan_id',
            'statuslongtrip.memo as statuslongtrip',
            'statusperalihan.memo as statusperalihan',
            'statusritasiomset.memo as statusritasiomset',
            'statusapprovalmandor.memo as statusapprovalmandor',
            'statusapprovalmandor.text as statusapprovalmandortext',
            DB::raw('(case when (year(suratpengantar.tglapprovalmandor) <= 2000) then null else suratpengantar.tglapprovalmandor end ) as tglapprovalmandor'),
            'suratpengantar.userapprovalmandor',
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at',
            DB::raw("1 as flag"),
            db::raw("isnull(gajisupir.nobukti,'') as gajisupir_nobukti, isnull(gajisupir.ebsnobukti,'') as prosesgajisupir_nobukti, (case when isnull(gajisupir.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir")

        )

            ->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])
            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('parameter as statusapprovalmandor', 'suratpengantar.statusapprovalmandor', 'statusapprovalmandor.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("$tempspric as gajisupir with (readuncommitted)"), 'suratpengantar.nobukti', 'gajisupir.suratpengantar_nobukti');
        // ->orderBy('suratpengantar.tglbukti', 'desc');

        if (!$isAdmin) {
            if ($isMandor) {
                $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
            }
        }
        if ($supirheader != 0) {
            $query->where('suratpengantar.supir_id', $supirheader);
        }

        DB::table($tempsuratpengantarrinci)->insertUsing([
            'id',
            'idoriginal',
            'jobtrucking',
            'nobukti',
            'tglbukti',
            'nosp',
            'tglsp',
            'nojob',
            'pelanggan_id',
            'keterangan',
            'dari_id',
            'sampai_id',
            'gajisupir',
            'jarak',
            'penyesuaian',
            'agen_id',
            'jenisorder_id',
            'container_id',
            'nocont',
            'noseal',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statusperalihan',
            'statusritasiomset',
            'statusapprovalmandor',
            'statusapprovalmandortext',
            'tglapprovalmandor',
            'userapprovalmandor',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statusgudangsama',
            'statusbatalmuat',
            'modifiedby',
            'created_at',
            'updated_at',
            'flag',
            'gajisupir_nobukti',
            'prosesgajisupir_nobukti',
            'statusgajisupir'
        ], $query);
        $tempspric = '##tempspric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempspric, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('ebsnobukti', 50)->nullable();
            $table->string('ritasi_nobukti', 50)->nullable();
        });
        $queryric = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail a with (readuncommitted)"))
            ->select(
                db::raw("max(a.nobukti) as nobukti"),
                db::raw("max(d.nobukti) as ebsnobukti"),
                'a.ritasi_nobukti'
            )
            ->join(db::raw("ritasi as b with (readuncommitted)"), 'a.ritasi_nobukti', 'b.nobukti')
            ->leftjoin(db::raw("prosesgajisupirdetail as d with (readuncommitted)"), 'a.nobukti', 'd.gajisupir_nobukti')
            ->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])
            ->groupBy('a.ritasi_nobukti');

        if ($supirheader != 0) {
            $queryric->where('b.supir_id', $supirheader);
        }
        DB::table($tempspric)->insertUsing([
            'nobukti',
            'ebsnobukti',
            'ritasi_nobukti',
        ], $queryric);

        $query = DB::table('suratpengantar')->select(
            'suratpengantar.id',
            'ritasi.id as idoriginal',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'ritasi.nobukti as ritasi_nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.kodekota as dari_id',
            'kotasampai.kodekota as sampai_id',
            'statusritasi.text as keteranganritasi',
            'ritasi.gaji as gajisupir',
            'ritasi.jarak',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'container.keterangan as container_id',
            'suratpengantar.nocont',
            'suratpengantar.noseal',
            'statuscontainer.keterangan as statuscontainer_id',
            'suratpengantar.gudang',
            'trado.kodetrado as trado_id',
            'supir.namasupir as supir_id',
            'gandengan.keterangan as gandengan_id',
            'statuslongtrip.memo as statuslongtrip',
            'statusperalihan.memo as statusperalihan',
            'statusritasiomset.memo as statusritasiomset',
            'statusapprovalmandor.memo as statusapprovalmandor',
            'statusapprovalmandor.text as statusapprovalmandortext',
            DB::raw('(case when (year(ritasi.tglapprovalmandor) <= 2000) then null else ritasi.tglapprovalmandor end ) as tglapprovalmandor'),
            'ritasi.userapprovalmandor',
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at',
            DB::raw("2 as flag"),
            db::raw("isnull(gajisupir.nobukti,'') as gajisupir_nobukti, isnull(gajisupir.ebsnobukti,'') as prosesgajisupir_nobukti, 
                    (case when isnull(gajisupir.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir")

        )

            ->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])
            ->join('ritasi', 'suratpengantar.nobukti', 'ritasi.suratpengantar_nobukti')
            ->join('parameter as statusritasi', 'ritasi.statusritasi', 'statusritasi.id')
            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'ritasi.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'ritasi.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('parameter as statusapprovalmandor', 'ritasi.statusapprovalmandor', 'statusapprovalmandor.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("$tempspric as gajisupir with (readuncommitted)"), 'ritasi.nobukti', 'gajisupir.ritasi_nobukti');

        // ->orderBy('suratpengantar.tglbukti', 'desc');
        if (!$isAdmin) {
            if ($isMandor) {
                $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
            }
        }
        if ($supirheader != 0) {
            $query->where('suratpengantar.supir_id', $supirheader);
        }


        DB::table($tempsuratpengantarrinci)->insertUsing([
            'id',
            'idoriginal',
            'jobtrucking',
            'nobukti',
            'ritasi_nobukti',
            'tglbukti',
            'nosp',
            'tglsp',
            'nojob',
            'pelanggan_id',
            'keterangan',
            'dari_id',
            'sampai_id',
            'keteranganritasi',
            'gajisupir',
            'jarak',
            'agen_id',
            'jenisorder_id',
            'container_id',
            'nocont',
            'noseal',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statusperalihan',
            'statusritasiomset',
            'statusapprovalmandor',
            'statusapprovalmandortext',
            'tglapprovalmandor',
            'userapprovalmandor',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statusgudangsama',
            'statusbatalmuat',
            'modifiedby',
            'created_at',
            'updated_at',
            'flag',
            'gajisupir_nobukti',
            'prosesgajisupir_nobukti',
            'statusgajisupir'
        ], $query);

        $queryfinal = DB::table($tempsuratpengantarrinci)->from(DB::raw("$tempsuratpengantarrinci as suratpengantar with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By suratpengantar.tglbukti, suratpengantar.nobukti, suratpengantar.flag,suratpengantar.ritasi_nobukti) as id"),
                'suratpengantar.idoriginal',
                'suratpengantar.jobtrucking',
                'suratpengantar.nobukti',
                'suratpengantar.ritasi_nobukti',
                'suratpengantar.tglbukti',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.nojob',
                'suratpengantar.pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.dari_id',
                'suratpengantar.sampai_id',
                'suratpengantar.keteranganritasi',
                'suratpengantar.gajisupir',
                'suratpengantar.jarak',
                'suratpengantar.penyesuaian',
                'suratpengantar.agen_id',
                'suratpengantar.jenisorder_id',
                'suratpengantar.container_id',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.gudang',
                'suratpengantar.trado_id',
                'suratpengantar.supir_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statusperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.statusapprovalmandor',
                'suratpengantar.statusapprovalmandortext',
                'suratpengantar.tglapprovalmandor',
                'suratpengantar.userapprovalmandor',
                'suratpengantar.tarif_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                'suratpengantar.flag',
                'suratpengantar.gajisupir_nobukti',
                'suratpengantar.prosesgajisupir_nobukti',
                'statusgajisupir.memo as statusgajisupir'
            )
            ->leftJoin('parameter as statusgajisupir', 'suratpengantar.statusgajisupir', 'statusgajisupir.id');

        return $queryfinal;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->integer('idoriginal')->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('ritasi_nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->string('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('keteranganritasi')->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('jarak', 15, 2)->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->string('agen_id')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('container_id')->nullable();
            $table->string('nocont')->nullable();
            $table->string('noseal')->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('gudang')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('gandengan_id')->nullable();
            $table->longText('statuslongtrip')->nullable();
            $table->longText('statusperalihan')->nullable();
            $table->longText('statusritasiomset')->nullable();
            $table->longText('statusapprovalmandor')->nullable();
            $table->longText('statusapprovalmandortext')->nullable();
            $table->dateTime('tglapprovalmandor')->nullable();
            $table->string('userapprovalmandor')->nullable();
            $table->string('tarif_id')->nullable();
            $table->string('mandortrado_id')->nullable();
            $table->string('mandorsupir_id')->nullable();
            $table->longText('statusgudangsama')->nullable();
            $table->longText('statusbatalmuat')->nullable();
            $table->string('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('flag')->nullable();
            $table->string('gajisupir_nobukti', 500)->nullable();
            $table->string('prosesgajisupir_nobukti', 500)->nullable();
            $table->longText('statusgajisupir')->nullable();
            $table->increments('position');
        });
        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }
        $this->setRequestParameters();
        $query = $this->selectColumns();
        if ($this->params['sortIndex'] == 'nobukti') {
            $query->orderBy('suratpengantar.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy('suratpengantar.flag', $this->params['sortOrder'])
                ->orderBy('suratpengantar.ritasi_nobukti', $this->params['sortOrder']);
        } else {
            $query->orderBy('suratpengantar.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'idoriginal',
            'jobtrucking',
            'nobukti',
            'ritasi_nobukti',
            'tglbukti',
            'nosp',
            'tglsp',
            'nojob',
            'pelanggan_id',
            'keterangan',
            'dari_id',
            'sampai_id',
            'keteranganritasi',
            'gajisupir',
            'jarak',
            'penyesuaian',
            'agen_id',
            'jenisorder_id',
            'container_id',
            'nocont',
            'noseal',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statusperalihan',
            'statusritasiomset',
            'statusapprovalmandor',
            'statusapprovalmandortext',
            'tglapprovalmandor',
            'userapprovalmandor',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statusgudangsama',
            'statusbatalmuat',
            'modifiedby',
            'created_at',
            'updated_at',
            'flag',
            'gajisupir_nobukti',
            'prosesgajisupir_nobukti',
            'statusgajisupir'
        ], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'dari_id') {
            return $query->orderBy('dari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai_id') {
            return $query->orderBy('sampai.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statusritasi') {
            return $query->orderBy('parameter.text', $this->params['sortOrder']);
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

                        if ($filters['field'] != '') {
                            // if ($filters['field'] == 'statusritasi') {
                            //     $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'supir_id') {
                            //     $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'trado_id') {
                            //     $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'dari_id') {
                            //     $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'sampai_id') {
                            //     $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jarak' || $filters['field'] == 'gaji' || $filters['field'] == 'upah' || $filters['field'] == 'extra') {
                            //     $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tglbukti') {
                            //     $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            //     $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            // } else {
                            //     // $query = $query->where($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                            //     $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            // }
                            if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak' || $filters['field'] == 'omset' || $filters['field'] == 'nominalperalihan' || $filters['field'] == 'totalomset') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'biayaextra' || $filters['field'] == 'biayatagih') {
                                $query = $query->whereRaw("format(suratpengantarbiayatambahan." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp' || $filters['field'] == 'tglapprovalbiayaextra' || $filters['field'] == 'tglapprovaleditsuratpengantar') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbatasapprovalbiayaextra' || $filters['field'] == 'tglbataseditsuratpengantar') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'statusapprovalmandor') {
                                $query = $query->where('suratpengantar.statusapprovalmandortext', '=', "$filters[data]");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("suratpengantar.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] != '') {
                                // if ($filters['field'] == 'statusritasi') {
                                //     $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'supir_id') {
                                //     $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'trado_id') {
                                //     $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'dari_id') {
                                //     $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                                // } elseif ($filters['field'] == 'sampai_id') {
                                //     $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'jarak' || $filters['field'] == 'gaji' || $filters['field'] == 'upah' || $filters['field'] == 'extra') {
                                //     $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'tglbukti') {
                                //     $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                //     $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                // } else {
                                //     // $query = $query->orWhere($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                                //     $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                // }
                                if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak' || $filters['field'] == 'omset' || $filters['field'] == 'nominalperalihan' || $filters['field'] == 'totalomset') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'biayaextra' || $filters['field'] == 'biayatagih') {
                                    $query = $query->orWhereRaw("format(suratpengantarbiayatambahan." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp' || $filters['field'] == 'tglapprovalbiayaextra' || $filters['field'] == 'tglapprovaleditsuratpengantar') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbatasapprovalbiayaextra' || $filters['field'] == 'tglbataseditsuratpengantar') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'statusapprovalmandor') {
                                    $query = $query->orWhere('suratpengantar.statusapprovalmandortext', '=', "$filters[data]");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw("suratpengantar.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function cekvalidasiaksi($nobukti)
    {
        $error = new Error();
        $statusCetak = (new Parameter())->cekId('STATUSCETAK', 'STATUSCETAK', 'CETAK');

        $gajiSupir = DB::table('gajisupirdetail')
            ->from(
                DB::raw("gajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.ritasi_nobukti',
                'a.nobukti',
                'b.statuscetak',
                db::raw("isnull(c.nobukti,'') as nobuktiebs")
            )
            ->join(DB::raw("gajisupirheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(DB::raw("prosesgajisupirdetail as c with (readuncommitted)"), 'b.nobukti', 'c.gajisupir_nobukti')
            ->where('a.ritasi_nobukti', '=', $nobukti)
            ->first();

        if (isset($gajiSupir)) {
            if ($gajiSupir->statuscetak == $statusCetak) {
                $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'No Bukti gaji supir <b>' . $gajiSupir->nobukti . '</b> ' . $keteranganerror,
                    'kodeerror' => 'SDC',
                ];

                goto selesai;
            } else {
                if ($gajiSupir->nobuktiebs != '') {
                    $keteranganerror = $error->cekKeteranganError('SPOST') ?? '';
                    $data = [
                        'kondisi' => true,
                        'keterangan' => 'No Bukti gaji supir <b>' . $gajiSupir->nobukti . '</b> ' . $keteranganerror . '<br> No Bukti Posting <b>' . $gajiSupir->nobuktiebs . '</b>',
                        'kodeerror' => 'SPOST',
                    ];

                    goto selesai;
                }
            }
        }


        $querytrip = DB::table("ritasi")->from(DB::raw("ritasi with (readuncommitted)"))
            ->where('nobukti', $nobukti)
            ->where('statusapprovalmandor', 3)
            ->first();
        if ($querytrip != '') {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $querytrip->nobukti . '</b> ' . $keteranganerror . ' mandor',
                'kodeerror' => 'SAP',
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

    public function cekUpahRitasi($dari, $sampai)
    {
        $query = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))
            ->select(DB::raw("upahritasi.nominalsupir"))
            ->whereRaw("((upahritasi.kotadari_id=" . $dari . " and upahritasi.kotasampai_id=" . $sampai . ") or (upahritasi.kotasampai_id=" . $dari . " and upahritasi.kotadari_id=" . $sampai . "))")
            // ->whereRaw('upahritasi.kotasampai_id', $sampai)
            ->whereRaw("upahritasi.nominalsupir <> 0")
            ->where("upahritasi.statusaktif", '1')
            ->first();
        return $query;
    }

    public function processStore(array $data): Ritasi
    {
        $urutke = 0;

        $querytrip = DB::table('ritasi')->from(
            db::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a.suratpengantar_urutke'
            )
            ->where('a.suratpengantar_nobukti', '=', $data['suratpengantar_nobukti'])
            ->whereRaw("isnull(a.suratpengantar_nobukti,'')<>''")
            ->orderby('a.suratpengantar_urutke', 'desc')
            ->first();

        if (isset($querytrip)) {
            $urutke = $querytrip->suratpengantar_urutke + 1;
        } else {
            $urutke = 1;
        }

        // dd($urutke);
        $group = 'RITASI';
        $subGroup = 'RITASI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();
        $upahRitasi = DB::table('upahritasi')
            ->whereRaw("(upahritasi.kotadari_id=" . $data['dari_id'] . " and upahritasi.kotasampai_id=" . $data['sampai_id'] . ") or (upahritasi.kotasampai_id=" . $data['dari_id'] . " and upahritasi.kotadari_id=" . $data['sampai_id'] . ")")->first();
        $extra = DB::table("dataritasi")->from(DB::raw("dataritasi with (readuncommitted)"))->where('id', $data['statusritasi_id'])->first();
        $extraNominal = $extra->nominal ?? 0;
        $ritasi = new Ritasi();
        $ritasi->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $ritasi->statusritasi = $extra->statusritasi;
        $ritasi->dataritasi_id = $data['statusritasi_id'];
        $ritasi->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $ritasi->suratpengantar_urutke = $urutke;
        $ritasi->supir_id = $data['supir_id'];
        $ritasi->trado_id = $data['trado_id'];
        $ritasi->dari_id = $data['dari_id'];
        $ritasi->sampai_id = $data['sampai_id'];
        $ritasi->statusapprovalmandor = 4;
        $ritasi->jarak = $upahRitasi->jarak ?? 0;
        $ritasi->upah = $upahRitasi->nominalsupir ?? 0;
        $ritasi->extra = $extra->nominal ?? 0;
        $ritasi->gaji = $upahRitasi->nominalsupir + $extraNominal;
        $ritasi->statusformat = $format->id;
        $ritasi->modifiedby = auth('api')->user()->name;
        $ritasi->info = html_entity_decode(request()->info);
        $ritasi->nobukti = (new RunningNumberService)->get($group, $subGroup, $ritasi->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$ritasi->save()) {
            throw new \Exception("Error storing ritasi.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ritasi->getTable()),
            'postingdari' => 'ENTRY RITASI',
            'idtrans' => $ritasi->id,
            'nobuktitrans' => $ritasi->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $ritasi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);


        return $ritasi;
    }

    public function processUpdate(Ritasi $ritasi, array $data): Ritasi
    {



        $upahRitasi = DB::table('upahritasi')
            ->whereRaw("(upahritasi.kotadari_id=" . $data['dari_id'] . " and upahritasi.kotasampai_id=" . $data['sampai_id'] . ") or (upahritasi.kotasampai_id=" . $data['dari_id'] . " and upahritasi.kotadari_id=" . $data['sampai_id'] . ")")->first();
        $extra = DB::table("dataritasi")->from(DB::raw("dataritasi with (readuncommitted)"))->where('id', $data['statusritasi_id'])->first();
        $extraNominal = $extra->nominal ?? 0;
        $ritasi->statusritasi = $extra->statusritasi;
        $ritasi->dataritasi_id = $data['statusritasi_id'];
        $ritasi->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $ritasi->supir_id = $data['supir_id'];
        $ritasi->trado_id = $data['trado_id'];
        $ritasi->jarak = $upahRitasi->jarak ?? 0;
        $ritasi->upah = $upahRitasi->nominalsupir ?? 0;
        $ritasi->extra = $extra->nominal ?? 0;
        $ritasi->gaji = $upahRitasi->nominalsupir + $extraNominal;
        $ritasi->dari_id = $data['dari_id'];
        $ritasi->sampai_id = $data['sampai_id'];
        $ritasi->modifiedby = auth('api')->user()->name;
        $ritasi->info = html_entity_decode(request()->info);

        $notrip = $data['suratpengantar_nobukti'] ?? '';

        $queryritasi = DB::table("ritasi")->from(
            db::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.suratpengantar_nobukti', $notrip)
            ->orderBy('a.nobukti', 'asc')
            ->get();
        $urutke = 0;
        $datadetail = json_decode($queryritasi, true);
        foreach ($datadetail as $item) {
            $urutke = $urutke + 1;
            $ritasiUpdate  = Ritasi::lockForUpdate()->where("nobukti", $item['nobukti'])
                ->firstorFail();
            $ritasiUpdate->suratpengantar_urutke = $urutke;
            $ritasiUpdate->save();
        }

        if (!$ritasi->save()) {
            throw new \Exception("Error updating ritasi.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ritasi->getTable()),
            'postingdari' => 'EDIT RITASI',
            'idtrans' => $ritasi->id,
            'nobuktitrans' => $ritasi->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $ritasi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $cekRic = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->where('ritasi_nobukti', $ritasi->nobukti)
            ->first();

        if ($cekRic != '') {
            $data = [
                'id_detail' => $cekRic->id,
                'nobukti' => $cekRic->nobukti,
                'suratpengantar_nobukti' => $cekRic->suratpengantar_nobukti,
                'ritasi_nobukti' => $ritasi->nobukti,
                'gajisupir' => $cekRic->gajisupir,
                'komisisupir' => $cekRic->komisisupir,
                'gajiritasi' => $ritasi->gaji,
            ];
            (new GajiSupirHeader())->processUpdateTrip($data, 'edit');
        }

        return $ritasi;
    }

    public function processDestroy($id): Ritasi
    {

        $notripquery = db::table("ritasi")->from(
            db::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a.suratpengantar_nobukti'
            )
            ->where('a.id', $id)
            ->first();

        if (isset($notripquery)) {
            $notrip = $notripquery->suratpengantar_nobukti;
        } else {
            $notrip = '';
        }

        $ritasi = new Ritasi();
        $ritasi = $ritasi->lockAndDestroy($id);
        if ($notrip != '') {

            $queryritasi = DB::table("ritasi")->from(
                db::raw("ritasi a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.suratpengantar_nobukti', $notrip)
                ->orderBy('a.nobukti', 'asc')
                ->get();
            $urutke = 0;
            $datadetail = json_decode($queryritasi, true);
            foreach ($datadetail as $item) {
                $urutke = $urutke + 1;
                $ritasiUpdate  = Ritasi::lockForUpdate()->where("nobukti", $item['nobukti'])
                    ->firstorFail();
                $ritasiUpdate->suratpengantar_urutke = $urutke;
                $ritasiUpdate->save();
            }
        }
        $cekRic = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->where('ritasi_nobukti', $ritasi->nobukti)
            ->first();

        if ($cekRic != '') {
            $data = [
                'id_detail' => $cekRic->id,
                'nobukti' => $cekRic->nobukti,
                'suratpengantar_nobukti' => $cekRic->suratpengantar_nobukti,
                'ritasi_nobukti' => $ritasi->nobukti,
                'gajisupir' => $cekRic->gajisupir,
                'komisisupir' => $cekRic->komisisupir,
                'gajiritasi' => $ritasi->gaji,
            ];
            (new GajiSupirHeader())->processUpdateTrip($data, 'delete');
        }


        (new LogTrail())->processStore([
            'namatabel' => $ritasi->getTable(),
            'postingdari' => 'DELETE RITASI',
            'idtrans' => $ritasi->id,
            'nobuktitrans' => $ritasi->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $ritasi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $ritasi;
    }

    public function getExport()
    {
        $this->setRequestParameters();
        $getParameter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text as judul',
                DB::raw("'Laporan Ritasi' as judulLaporan")
            )->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();

        $query = DB::table($this->table)
            ->select(
                'ritasi.id',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'parameter.text as statusritasi',
                'suratpengantar.nobukti as suratpengantar_nobukti',
                'd.nobukti as prosesgajisupir_nobukti',
                'b.nobukti as gajisupir_nobukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                'ritasi.jarak',
                'ritasi.gaji',
                'dari.keterangan as dari_id',
                'sampai.keterangan as sampai_id',
                DB::raw("'" . request()->tgldari . "' as tgldari"),
                DB::raw("'" . request()->tglsampai . "' as tglsampai"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
            ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'ritasi.nobukti', 'b.ritasi_nobukti')
            ->leftJoin(DB::raw("prosesgajisupirdetail as d with (readuncommitted)"), 'd.gajisupir_nobukti', 'b.nobukti')
            ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id');

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        $allData = [
            'data' => $data,
            'parameter' => $getParameter
        ];
        return $allData;
    }

    public function ExistTradoSupirRitasi($nobukti)
    {
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('trado_id', 'supir_id')
            ->where('nobukti', $nobukti)
            ->first();
        return $query;
    }
}
