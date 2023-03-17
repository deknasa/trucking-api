<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantar extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantar';

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'tglsp' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function suratpengantarBiaya()
    {
        return $this->hasMany(SuratPengantarBiayaTambahan::class, 'suratpengantar_id');
    }
    public function todayValidation($id)
    {
        $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('tglbukti')
            ->where('id', $id)
            ->first();
        $tglbukti = strtotime($query->tglbukti);
        $today = strtotime('today');
        if ($tglbukti === $today) return true;
        return false;
    }

    public function isEditAble($id)
    {
        $tidakBolehEdit = DB::table('suratpengantar')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT TUJUAN')->where('default', 'YA')->first();

        $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('statusedittujuan as statusedit')
            ->where('id', $id)
            ->first();

        if ($query->statusedit != $tidakBolehEdit->id) return true;
        return false;
    }

    public function cekvalidasihapus($nobukti, $jobtrucking)
    {


        $gajiSupir = DB::table('gajisupirdetail')
            ->from(
                DB::raw("gajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.suratpengantar_nobukti'
            )
            ->where('a.suratpengantar_nobukti', '=', $nobukti)
            ->first();


        if (isset($gajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'gaji supir',
            ];


            goto selesai;
        }

        $tempinvdetail = '##tempinvdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvdetail, function ($table) {
            $table->string('suratpengantar_nobukti')->default('');
        });

        $status = InvoiceDetail::from(
            db::Raw("invoicedetail with (readuncommitted)")
        )->select('suratpengantar_nobukti')
            ->where('orderantrucking_nobukti', $jobtrucking)->first();


        if (isset($status)) {
            $sp = explode(',', $status->suratpengantar_nobukti);

            for ($i = 0; $i < count($sp); $i++) {
                DB::table($tempinvdetail)->insert(
                    [
                        "suratpengantar_nobukti" => $sp[$i]
                    ]
                );
            }
        }


        $query = DB::table($tempinvdetail)->from(DB::raw($tempinvdetail))
            ->select(
                'suratpengantar_nobukti',
            )->where('suratpengantar_nobukti', '=', $nobukti)
            ->first();

        if (isset($query)) {
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

        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
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
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )

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
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statuslongtrip')->default(0);
            $table->unsignedBigInteger('statusperalihan')->default(0);
            $table->unsignedBigInteger('statusritasiomset')->default(0);
            $table->unsignedBigInteger('statusgudangsama')->default(0);
            $table->unsignedBigInteger('statusbatalmuat')->default(0);
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LONGTRIP')
            ->where('subgrp', '=', 'STATUS LONGTRIP')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslongtrip = $status->id ?? 0;

        // PERALIHAN
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

        // RITASI OMSET
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS RITASI OMSET')
            ->where('subgrp', '=', 'STATUS RITASI OMSET')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusritasi = $status->id ?? 0;

        // GUDANG SAMA
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS GUDANG SAMA')
            ->where('subgrp', '=', 'STATUS GUDANG SAMA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusgudang = $status->id ?? 0;

        // BATAL MUAT
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS BATAL MUAT')
            ->where('subgrp', '=', 'STATUS BATAL MUAT')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusbatal = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "statuslongtrip" => $iddefaultstatuslongtrip,
                "statusperalihan" => $iddefaultstatusperalihan,
                "statusritasiomset" => $iddefaultstatusritasi,
                "statusgudangsama" => $iddefaultstatusgudang,
                "statusbatalmuat" => $iddefaultstatusbatal,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statuslongtrip',
                'statusperalihan',
                'statusritasiomset',
                'statusgudangsama',
                'statusbatalmuat'
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        // dd('find');
        $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.tglbukti',
                'suratpengantar.jobtrucking',
                'suratpengantar.statuslongtrip',
                'suratpengantar.nosp',
                'suratpengantar.trado_id',
                'trado.kodetrado as trado',
                'suratpengantar.supir_id',
                'supir.namasupir as supir',
                'suratpengantar.dari_id',
                'kotadari.keterangan as dari',
                'suratpengantar.gandengan_id',
                'gandengan.keterangan as gandengan',
                'suratpengantar.container_id',
                'container.keterangan as container',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.statusperalihan',
                'suratpengantar.persentaseperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.nosptagihlain as nosp2',
                'suratpengantar.statusgudangsama',
                'suratpengantar.keterangan',
                'suratpengantar.sampai_id',
                'kotasampai.keterangan as sampai',
                'suratpengantar.statuscontainer_id',
                'statuscontainer.keterangan as statuscontainer',
                'suratpengantar.nocont2',
                'suratpengantar.noseal2',
                'suratpengantar.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'suratpengantar.agen_id',
                'agen.namaagen as agen',
                'suratpengantar.jenisorder_id',
                'jenisorder.keterangan as jenisorder',
                'suratpengantar.tarif_id as tarifrincian_id',
                'tarif.tujuan as tarifrincian',
                'suratpengantar.nominalperalihan',
                'suratpengantar.nojob',
                'suratpengantar.nojob2',
                'suratpengantar.cabang_id',
                'cabang.namacabang as cabang',
                'suratpengantar.qtyton',
                'suratpengantar.gudang',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.komisisupir',
            )
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->leftJoin('cabang', 'suratpengantar.cabang_id', 'cabang.id')
            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')

            ->where('suratpengantar.id', $id)->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp
        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.nobukti,
                $this->table.jobtrucking,
                $this->table.tglbukti,
                pelanggan.namapelanggan as pelanggan_id,
                $this->table.keterangan,
                kotadari.keterangan as dari_id,
                kotasampai.keterangan as sampai_id,
                $this->table.container_id,
                $this->table.nocont,
                $this->table.statuscontainer_id,
                $this->table.trado_id,
                $this->table.supir_id,
                $this->table.nojob,
                $this->table.statuslongtrip,
                $this->table.agen_id,
                $this->table.jenisorder_id,
                $this->table.nosp,
                $this->table.statusritasiomset,
                $this->table.cabang_id,
                $this->table.qtyton,

                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )

        )
            ->join('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')

            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id');
    }

    public function getpelabuhan($id)
    {
        $data = DB::table('parameter')
            ->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text as id'
            )
            ->where('grp', '=', 'PELABUHAN CABANG')
            ->where('subgrp', '=', 'PELABUHAN CABANG')
            ->where('text', '=', $id)
            ->first();

        if (isset($data)) {
            $kondisi = ['status' => '0'];
        } else {
            $kondisi = ['status' => '1'];
        }
        return $kondisi;
    }


    public function getHistory()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
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
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )

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
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->where('suratpengantar.tglbukti', ">", date('Y-m-d', strtotime('- 7 days')))
            ->where('suratpengantar.tglbukti', "<=", date('Y-m-d', strtotime('now')))
            ->where('suratpengantar.supir_id', request()->supir_id);

        $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($query);
        // $this->filter($query);
        // $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function getListTrip()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
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
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )

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
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->where('suratpengantar.tglbukti', date('Y-m-d', strtotime('now')));

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->string('jobtrucking', 50)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('dari_id')->default('0');
            $table->string('sampai_id')->default('0');
            $table->string('container_id')->default('0');
            $table->string('nocont', 50)->default('');
            $table->string('statuscontainer_id')->default('0');
            $table->string('trado_id')->default('0');
            $table->string('supir_id')->default('0');
            $table->string('nojob', 50)->default('');
            $table->integer('statuslongtrip')->length(11)->default('0');
            $table->string('agen_id')->default('0');
            $table->string('jenisorder_id')->default('0');
            $table->string('nosp', 50)->default('');
            $table->integer('statusritasiomset')->length(11)->default('0');
            $table->string('cabang_id')->default('0');
            $table->decimal('qtyton', 15, 2)->default('0');

            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'jobtrucking', 'tglbukti', 'pelanggan_id', 'keterangan', 'dari_id', 'sampai_id', 'container_id', 'nocont', 'statuscontainer_id', 'trado_id', 'supir_id',
            'nojob', 'statuslongtrip', 'agen_id',
            'jenisorder_id', 'nosp', 'statusritasiomset', 'cabang_id', 'qtyton', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }

    public function getOrderanTrucking($id)
    {
        $data = DB::table('orderantrucking')->select('orderantrucking.*', 'container.keterangan as container', 'agen.namaagen as agen', 'jenisorder.keterangan as jenisorder', 'pelanggan.namapelanggan as pelanggan', 'tarif.tujuan as tarif')
            ->join('container', 'orderantrucking.container_id', 'container.id')
            ->join('agen', 'orderantrucking.agen_id', 'agen.id')
            ->join('jenisorder', 'orderantrucking.jenisorder_id', 'jenisorder.id')
            ->join('pelanggan', 'orderantrucking.pelanggan_id', 'pelanggan.id')
            ->join('tarif', 'orderantrucking.tarif_id', 'tarif.id')
            ->where('orderantrucking.id', $id)
            ->first();

        return $data;
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'dari_id') {
                            $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sampai_id') {
                            $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statuscontainer_id') {
                            $query = $query->where('statuscontainer.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jenisorder_id') {
                            $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tarif_id') {
                            $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandortrado_id') {
                            $query = $query->where('mandortrado.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandorsupir_id') {
                            $query = $query->where('mandorsupir.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statuslongtrip') {
                            $query = $query->where('statuslongtrip.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusperalihan') {
                            $query = $query->where('statusperalihan.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusritasiomset') {
                            $query = $query->where('statusritasiomset.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusgudangsama') {
                            $query = $query->where('statusgudangsama.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusbatalmuat') {
                            $query = $query->where('statusbatalmuat.text', '=', "$filters[data]");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'dari_id') {
                            $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'sampai_id') {
                            $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statuscontainer_id') {
                            $query = $query->orWhere('statuscontainer.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'container_id') {
                            $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'trado_id') {
                            $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'agen_id') {
                            $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jenisorder_id') {
                            $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tarif_id') {
                            $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandortrado_id') {
                            $query = $query->orWhere('mandortrado.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandorsupir_id') {
                            $query = $query->orWhere('mandorsupir.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statuslongtrip') {
                            $query = $query->orWhere('statuslongtrip.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusperalihan') {
                            $query = $query->orWhere('statusperalihan.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusritasiomset') {
                            $query = $query->orWhere('statusritasiomset.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusgudangsama') {
                            $query = $query->orWhere('statusgudangsama.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusbatalmuat') {
                            $query = $query->orWhere('statusbatalmuat.text', '=', "$filters[data]");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
