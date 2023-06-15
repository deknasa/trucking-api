<?php

namespace App\Models;

use App\Services\RunningNumberService;
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
            $table->string('suratpengantar_nobukti')->nullable();
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
        if (request()->tgldari) {
            $query->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
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
            $table->unsignedBigInteger('statuslongtrip')->nullable();
            $table->unsignedBigInteger('statusperalihan')->nullable();
            $table->unsignedBigInteger('statusritasiomset')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
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
                'trado.nominalplusborongan',
                'suratpengantar.supir_id',
                'supir.namasupir as supir',
                'suratpengantar.dari_id',
                'kotadari.kodekota as dari',
                'suratpengantar.gandengan_id',
                'gandengan.kodegandengan as gandengan',
                'suratpengantar.container_id',
                'container.kodecontainer as container',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.statusperalihan',
                'suratpengantar.persentaseperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.nosptagihlain as nosp2',
                'suratpengantar.statusgudangsama',
                'suratpengantar.keterangan',
                'suratpengantar.sampai_id',
                'kotasampai.kodekota as sampai',
                'suratpengantar.statuscontainer_id',
                'statuscontainer.kodestatuscontainer as statuscontainer',
                'suratpengantar.nocont2',
                'suratpengantar.noseal2',
                'suratpengantar.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'suratpengantar.agen_id',
                'agen.namaagen as agen',
                'suratpengantar.jenisorder_id',
                'jenisorder.kodejenisorder as jenisorder',
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
                'suratpengantar.upah_id',
                'kotaupah.kodekota as upah'
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
            ->leftJoin('upahsupir', 'suratpengantar.upah_id', 'upahsupir.id')
            ->leftJoin('kota as kotaupah', 'kotaupah.id', '=', 'upahsupir.kotasampai_id')
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
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

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

            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
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
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('container_id')->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->integer('statuslongtrip')->length(11)->nullable();
            $table->string('agen_id')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->integer('statusritasiomset')->length(11)->nullable();
            $table->string('cabang_id')->nullable();
            $table->decimal('qtyton', 15, 2)->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        if (request()->tgldariheader) {
            $query->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
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
        if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'dari_id') {
            return $query->orderBy('kotadari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai_id') {
            return $query->orderBy('kotasampai.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statuscontainer_id') {
            return $query->orderBy('statuscontainer.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tarif_id') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandortrado_id') {
            return $query->orderBy('mandortrado.namamandor', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandorsupir_id') {
            return $query->orderBy('mandorsupir.namamandor', $this->params['sortOrder']);
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
                        } else if ($filters['field'] == 'gandengan_id') {
                            $query = $query->where('gandengan.keterangan', 'LIKE', "%$filters[data]%");
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
                        } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp') {
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
                    $query = $query->where(function ($query) {
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
                            } else if ($filters['field'] == 'gandengan_id') {
                                $query = $query->orWhere('gandengan.keterangan', 'LIKE', "%$filters[data]%");
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
                            } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): SuratPengantar
    {
        $group = 'SURAT PENGANTAR';
        $subGroup = 'SURAT PENGANTAR';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $orderanTrucking = OrderanTrucking::where('nobukti', $data['jobtrucking'])->first();
        $upahsupir = UpahSupir::where('id', $data['upah_id'])->first();

        $tarifrincian = TarifRincian::from(DB::raw("tarifrincian with (readuncommitted)"))->where('tarif_id', $orderanTrucking->tarif_id)->where('container_id', $orderanTrucking->container_id)->first();
        $trado = Trado::find($data['trado_id']);
        $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $data['upah_id'])->where('container_id', $data['container_id'])->where('statuscontainer_id', $data['statuscontainer_id'])->first();
        $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();
        $suratPengantar = new SuratPengantar();

        $suratPengantar->jobtrucking = $data['jobtrucking'];
        $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $suratPengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
        $suratPengantar->keterangan = $data['keterangan'] ?? '';
        $suratPengantar->nourutorder = $data['nourutorder'] ?? 1;
        $suratPengantar->upah_id = $upahsupir->id;
        $suratPengantar->dari_id = $data['dari_id'];
        $suratPengantar->sampai_id = $data['sampai_id'];
        $suratPengantar->container_id = $orderanTrucking->container_id;
        $suratPengantar->nocont = $orderanTrucking->nocont;
        $suratPengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
        $suratPengantar->noseal = $orderanTrucking->noseal;
        $suratPengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
        $suratPengantar->statuscontainer_id = $data['statuscontainer_id'];
        $suratPengantar->trado_id = $data['trado_id'];
        $suratPengantar->supir_id = $data['supir_id'];
        $suratPengantar->gandengan_id = $data['gandengan_id'] ?? 0;
        $suratPengantar->nojob = $orderanTrucking->nojobemkl;
        $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
        $suratPengantar->statuslongtrip = $data['statuslongtrip'];
        $suratPengantar->omset = $tarifrincian->nominal;
        $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir;
        $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
        $suratPengantar->agen_id = $orderanTrucking->agen_id;
        $suratPengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
        $suratPengantar->statusperalihan = $data['statusperalihan'];
        $suratPengantar->tarif_id = $orderanTrucking->tarif_id;
        $suratPengantar->nominalperalihan = $data['nominalperalihan'] ?? 0;
        $persentaseperalihan = 0;
        if (array_key_exists('nominalperalihan', $data)) {

            if ($data['nominalperalihan'] != 0) {
                $persentaseperalihan = $data['nominalperalihan'] / $tarifrincian->nominal;
            }
        }

        $suratPengantar->persentaseperalihan = $persentaseperalihan;
        $suratPengantar->discount = $persentaseperalihan;
        $suratPengantar->totalomset = $tarifrincian->nominal - ($tarifrincian->nominal * ($persentaseperalihan / 100));

        $suratPengantar->biayatambahan_id = $data['biayatambahan_id'] ?? 0;
        $suratPengantar->nosp = $data['nosp'];
        $suratPengantar->tglsp = date('Y-m-d', strtotime($data['tglbukti']));
        $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
        $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
        $suratPengantar->jarak = $upahsupir->jarak;
        $suratPengantar->nosptagihlain = $data['nosptagihlain'] ?? '';
        $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
        $suratPengantar->qtyton = $data['qtyton'] ?? 0;
        $suratPengantar->totalton = $tarifrincian->nominal * $data['qtyton'];
        $suratPengantar->mandorsupir_id = $trado->mandor_id;
        $suratPengantar->mandortrado_id = $trado->mandor_id;
        $suratPengantar->statusgudangsama = $data['statusgudangsama'];
        $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
        $suratPengantar->gudang = $data['gudang'];
        $suratPengantar->modifiedby = auth('api')->user()->name;
        $suratPengantar->statusformat = $format->id;

        $suratPengantar->nobukti = (new RunningNumberService)->get($group, $subGroup, $suratPengantar->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


        $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;

        if (!$suratPengantar->save()) {
            throw new \Exception('Error storing surat pengantar.');
        }

       $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => $suratPengantar->getTable(),
            'postingdari' => 'ENTRY SURAT PENGANTAR',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->id,
            'aksi' => 'ENTRY',
            'datajson' => $suratPengantar->toArray(),
        ]);
        if ($data['nominal'][0] != 0) {
            $suratPengantarBiayaTambahans = [];
            for ($i = 0; $i < count($data['nominal']); $i++) {
                $suratPengantarBiayaTambahan = (new SuratPengantarBiayaTambahan())->processStore($suratPengantar, [
                    'keteranganbiaya' => $data['keterangan_detail'][$i],
                    'nominal' => $data['nominal'][$i],
                    'nominaltagih' => $data['nominalTagih'][$i]
                ]);
                $suratPengantarBiayaTambahans[] = $suratPengantarBiayaTambahan->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($suratPengantarBiayaTambahan->getTable()),
                'postingdari' => 'ENTRY SURAT PENGANTAR BIAYA TAMBAHAN',
                'idtrans' =>  $suratPengantarLogTrail->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $suratPengantarBiayaTambahans,
                'modifiedby' => auth('api')->user()->user,
            ]);
        }

        return $suratPengantar;
    }
    public function processUpdate(SuratPengantar $suratPengantar, array $data): SuratPengantar
    {
        $prosesLain = $data['proseslain'] ?? 0;
        $orderanTrucking = OrderanTrucking::where('nobukti', $data['jobtrucking'])->first();

        $tarif = Tarif::find($orderanTrucking->tarif_id);
        $tarif = TarifRincian::where('tarif_id', $orderanTrucking->tarif_id)->where('container_id', $orderanTrucking->container_id)->first();

        $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();

        if ($prosesLain == 0) {

            $upahsupir = UpahSupir::where('id', $data['upah_id'])->first();


            // return response($tarif,422);
            $trado = Trado::find($data['trado_id']);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $data['container_id'])->where('statuscontainer_id', $data['statuscontainer_id'])->first();

            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratPengantar->keterangan = $data['keterangan'] ?? '';
            $suratPengantar->nourutorder = $data['nourutorder'] ?? 1;
            $suratPengantar->upah_id = $upahsupir->id;
            $suratPengantar->dari_id = $data['dari_id'];
            $suratPengantar->sampai_id = $data['sampai_id'];
            $suratPengantar->container_id = $orderanTrucking->container_id;
            $suratPengantar->nocont = $orderanTrucking->nocont;
            $suratPengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
            $suratPengantar->statuscontainer_id = $data['statuscontainer_id'];
            $suratPengantar->trado_id = $data['trado_id'];
            $suratPengantar->supir_id = $data['supir_id'];
            $suratPengantar->gandengan_id = $data['gandengan_id'] ?? 0;
            $suratPengantar->nojob = $orderanTrucking->nojobemkl;
            $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratPengantar->noseal = $orderanTrucking->noseal;
            $suratPengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            $suratPengantar->statuslongtrip = $data['statuslongtrip'];
            $suratPengantar->omset = $tarif->nominal;
            $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratPengantar->agen_id = $orderanTrucking->agen_id;
            $suratPengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratPengantar->statusperalihan = $data['statusperalihan'];
            $suratPengantar->tarif_id = $orderanTrucking->tarif_id;
            $suratPengantar->nominalperalihan = $data['nominalperalihan'] ?? 0;
            $persentaseperalihan = 0;
            if (array_key_exists('nominalperalihan', $data)) {
                if ($data['nominalperalihan'] != 0) {
                    $persentaseperalihan = $data['nominalperalihan'] / $tarif->nominal;
                }
            }

            $suratPengantar->persentaseperalihan = $persentaseperalihan;
            $suratPengantar->discount = $persentaseperalihan;
            $suratPengantar->totalomset = $tarif->nominal - ($tarif->nominal * ($persentaseperalihan / 100));
            $suratPengantar->biayatambahan_id = $data['biayatambahan_id'] ?? 0;
            $suratPengantar->nosp = $data['nosp'];
            $suratPengantar->tglsp = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
            $suratPengantar->jarak = $upahsupir->jarak;
            $suratPengantar->nosptagihlain = $data['nosptagihlain'] ?? '';
            $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratPengantar->qtyton = $data['qtyton'] ?? 0;
            $suratPengantar->totalton = $tarif->nominal * $data['qtyton'];
            $suratPengantar->mandorsupir_id = $trado->mandor_id;
            $suratPengantar->mandortrado_id = $trado->mandor_id;
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
            if (!$suratPengantar->save()) {
                throw new \Exception('Error edit surat pengantar.');
            }
    
            $suratPengantarLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($suratPengantar->getTable()),
                'postingdari' => 'EDIT SURAT PENGANTAR',
                'idtrans' => $suratPengantar->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $suratPengantar->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);

            if ($data['nominal'][0] != 0) {
                
                SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratPengantar->id)->lockForUpdate()->delete();
                $suratPengantarBiayaTambahans = [];
                for ($i = 0; $i < count($data['nominal']); $i++) {
                    $suratPengantarBiayaTambahan = (new SuratPengantarBiayaTambahan())->processStore($suratPengantar, [
                        'keteranganbiaya' => $data['keterangan_detail'][$i],
                        'nominal' => $data['nominal'][$i],
                        'nominaltagih' => $data['nominalTagih'][$i]
                    ]);
                    $suratPengantarBiayaTambahans[] = $suratPengantarBiayaTambahan->toArray();
                }
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($suratPengantarBiayaTambahan->getTable()),
                    'postingdari' => 'EDIT SURAT PENGANTAR BIAYA TAMBAHAN',
                    'idtrans' =>  $suratPengantarLogTrail->id,
                    'nobuktitrans' => $suratPengantar->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $suratPengantarBiayaTambahans,
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
        } else {

            $suratPengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratPengantar->container_id = $orderanTrucking->container_id;
            $suratPengantar->nojob = $orderanTrucking->nojobemkl;
            $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratPengantar->nocont = $data['nocont'] ?? '';
            $suratPengantar->nocont2 = $data['nocont2'] ?? '';
            $suratPengantar->noseal = $data['noseal'] ?? '';
            $suratPengantar->noseal2 = $data['noseal2'] ?? '';
            $suratPengantar->omset = $tarif->nominal;
            $suratPengantar->agen_id = $orderanTrucking->agen_id;
            $suratPengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratPengantar->tarif_id = $orderanTrucking->tarif_id;
            $suratPengantar->nominalperalihan = $data['nominalperalihan'] ?? 0;
            $persentaseperalihan = 0;
            if (array_key_exists('nominalperalihan', $data)) {
                if ($data['nominalperalihan'] != 0) {
                    $persentaseperalihan = $data['nominalperalihan'] / $tarif->nominal;
                }
            }

            $suratPengantar->persentaseperalihan = $persentaseperalihan;
            $suratPengantar->discount = $persentaseperalihan;
            $suratPengantar->totalomset = $tarif->nominal - ($tarif->nominal * ($persentaseperalihan / 100));
            $suratPengantar->totalton = $tarif->nominal * $data['qtyton'];
            if (!$suratPengantar->save()) {
                throw new \Exception('Error edit surat pengantar.');
            }
            $suratPengantarLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($suratPengantar->getTable()),
                'postingdari' => 'EDIT SURAT PENGANTAR',
                'idtrans' => $suratPengantar->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $suratPengantar->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }


        return $suratPengantar;
    }
    
    public function processDestroy($id): SuratPengantar
    {
        $suratPengantarBiayaTambahan = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();
       
        $suratPengantar = new SuratPengantar();
        $suratPengantar = $suratPengantar->lockAndDestroy($id);

        $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => $suratPengantar->getTable(),
            'postingdari' => 'DELETE SURAT PENGANTAR',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $suratPengantar->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        
        if(count($suratPengantarBiayaTambahan->toArray()) > 0){
            SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->delete();
           $tes = (new LogTrail())->processStore([
                'namatabel' => 'SURATPENGANTARBIAYATAMBAHAN',
                'postingdari' => 'DELETE SURAT PENGANTAR BIAYA TAMBAHAN',
                'idtrans' => $suratPengantarLogTrail['id'],
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $suratPengantarBiayaTambahan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ]);
        }
       

        return $suratPengantar;
    }
}
