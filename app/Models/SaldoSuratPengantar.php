<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class SaldoSuratPengantar extends MyModel
{
    use HasFactory;
    protected $table = 'saldosuratpengantar';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    
    public function findAll($id)
    {
        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
        $komisi_gajisupir = $params->text;

        $isKomisiReadonly = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR')->where('subgrp', 'KOMISI')->first();

        $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
        $get = DB::table("saldosuratpengantar")->from(DB::raw("saldosuratpengantar with (readuncommitted)"))->select('statusupahzona')->where('id', $id)->first();

        $getGaji = DB::table('saldosuratpengantar')->from(DB::raw("saldosuratpengantar with (readuncommitted)"));
        if ($komisi_gajisupir == 'YA') {
            if (trim($isKomisiReadonly->text) == 'YA') {
                $getGaji->select(DB::raw("saldosuratpengantar.id, isnull(upahsupirrincian.nominalsupir,0) - isnull(upahsupirrincian.nominalkenek,0) as nominalsupir, upahsupirrincian.nominalkenek, upahsupirrincian.nominalkomisi, upahsupirrincian.nominaltol, upahsupirrincian.liter"));
            } else {
                $getGaji->select(DB::raw("saldosuratpengantar.id, isnull(upahsupirrincian.nominalsupir,0) - isnull(saldosuratpengantar.gajikenek,0) as nominalsupir, saldosuratpengantar.gajikenek as nominalkenek, saldosuratpengantar.komisisupir as nominalkomisi, upahsupirrincian.nominaltol, upahsupirrincian.liter"));
            }
        } else {
            $getGaji->select('saldosuratpengantar.id', 'upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi', 'upahsupirrincian.nominaltol', 'upahsupirrincian.liter');
        }
        $getGaji->leftJoin(DB::raw("upahsupirrincian with (readuncommitted)"), 'saldosuratpengantar.upah_id', 'upahsupirrincian.upahsupir_id')
            ->where('saldosuratpengantar.id', $id)
            ->whereRaw("upahsupirrincian.container_id = saldosuratpengantar.container_id")
            ->whereRaw("upahsupirrincian.statuscontainer_id = saldosuratpengantar.statuscontainer_id");

        $tempGaji = '##tempGaji' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempGaji, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->decimal('nominalsupir', 15, 2)->nullable();
            $table->decimal('nominalkenek', 15, 2)->nullable();
            $table->decimal('nominalkomisi', 15, 2)->nullable();
            $table->decimal('nominaltol', 15, 2)->nullable();
            $table->decimal('liter', 15, 2)->nullable();
        });

        DB::table($tempGaji)->insertUsing([
            'id',
            'nominalsupir',
            'nominalkenek',
            'nominalkomisi',
            'nominaltol',
            'liter'
        ], $getGaji);

        if ($get->statusupahzona == $getBukanUpahZona->id) {

            $data = saldoSuratPengantar::from(DB::raw("saldosuratpengantar with (readuncommitted)"))
                ->select(
                    'saldosuratpengantar.id',
                    'saldosuratpengantar.nobukti',
                    'saldosuratpengantar.tglbukti',
                    'saldosuratpengantar.jobtrucking',
                    'saldosuratpengantar.statuslongtrip',
                    'saldosuratpengantar.nosp',
                    'saldosuratpengantar.trado_id',
                    'trado.kodetrado as trado',
                    'trado.nominalplusborongan',
                    'saldosuratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'saldosuratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'saldosuratpengantar.gandengan_id',
                    'gandengan.kodegandengan as gandengan',
                    'saldosuratpengantar.container_id',
                    'container.kodecontainer as container',
                    'saldosuratpengantar.nocont',
                    'saldosuratpengantar.noseal',
                    'saldosuratpengantar.statusperalihan',
                    DB::raw("(case when saldosuratpengantar.persentaseperalihan IS NULL then 0 else saldosuratpengantar.persentaseperalihan end) as persentaseperalihan"),
                    'saldosuratpengantar.omset',
                    'saldosuratpengantar.statusritasiomset',
                    'saldosuratpengantar.nosptagihlain as nosp2',
                    'saldosuratpengantar.statusgudangsama',
                    'saldosuratpengantar.keterangan',
                    'saldosuratpengantar.penyesuaian',
                    'saldosuratpengantar.sampai_id',
                    'kotasampai.kodekota as sampai',
                    'saldosuratpengantar.statuscontainer_id',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    'saldosuratpengantar.nocont2',
                    'saldosuratpengantar.noseal2',
                    'saldosuratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'saldosuratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'saldosuratpengantar.jenisorder_id',
                    'jenisorder.kodejenisorder as jenisorder',
                    'saldosuratpengantar.tarif_id as tarifrincian_id',
                    'tarif.tujuan as tarifrincian',
                    DB::raw("(case when saldosuratpengantar.nominalperalihan IS NULL then 0 else saldosuratpengantar.nominalperalihan end) as nominalperalihan"),
                    'saldosuratpengantar.nojob',
                    'saldosuratpengantar.nojob2',
                    'saldosuratpengantar.cabang_id',
                    'cabang.namacabang as cabang',
                    'saldosuratpengantar.qtyton',
                    'saldosuratpengantar.gudang',
                    'saldosuratpengantar.statusbatalmuat',
                    'saldosuratpengantar.statusupahzona',
                    'saldosuratpengantar.statusgandengan',
                    $tempGaji . '.nominalsupir as gajisupir',
                    $tempGaji . '.nominalkenek as gajikenek',
                    $tempGaji . '.nominalkomisi as komisisupir',
                    'saldosuratpengantar.upah_id',
                    'saldosuratpengantar.statusapprovalbiayatitipanemkl',
                    'kotaupah.kodekota as upah'
                )
                ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'saldosuratpengantar.dari_id')
                ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'saldosuratpengantar.sampai_id')
                ->leftJoin('agen', 'saldosuratpengantar.agen_id', 'agen.id')
                ->leftJoin('container', 'saldosuratpengantar.container_id', 'container.id')
                ->leftJoin('statuscontainer', 'saldosuratpengantar.statuscontainer_id', 'statuscontainer.id')
                ->leftJoin('trado', 'saldosuratpengantar.trado_id', 'trado.id')
                ->leftJoin('supir', 'saldosuratpengantar.supir_id', 'supir.id')
                ->leftJoin('jenisorder', 'saldosuratpengantar.jenisorder_id', 'jenisorder.id')
                ->leftJoin('tarif', 'saldosuratpengantar.tarif_id', 'tarif.id')
                ->leftJoin('upahsupir', 'saldosuratpengantar.upah_id', 'upahsupir.id')
                ->leftJoin('kota as kotaupah', 'kotaupah.id', '=', 'upahsupir.kotasampai_id')
                ->leftJoin('cabang', 'saldosuratpengantar.cabang_id', 'cabang.id')
                ->leftJoin('pelanggan', 'saldosuratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin('gandengan', 'saldosuratpengantar.gandengan_id', 'gandengan.id')
                ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "saldosuratpengantar.id")

                ->where('saldosuratpengantar.id', $id)->first();
        } else {

            $data = saldoSuratPengantar::from(DB::raw("saldosuratpengantar with (readuncommitted)"))
                ->select(
                    'saldosuratpengantar.id',
                    'saldosuratpengantar.nobukti',
                    'saldosuratpengantar.tglbukti',
                    'saldosuratpengantar.jobtrucking',
                    'saldosuratpengantar.statuslongtrip',
                    'saldosuratpengantar.nosp',
                    'saldosuratpengantar.trado_id',
                    'trado.kodetrado as trado',
                    'trado.nominalplusborongan',
                    'saldosuratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'saldosuratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'saldosuratpengantar.gandengan_id',
                    'gandengan.kodegandengan as gandengan',
                    'saldosuratpengantar.container_id',
                    'container.kodecontainer as container',
                    'saldosuratpengantar.nocont',
                    'saldosuratpengantar.noseal',
                    'saldosuratpengantar.statusperalihan',
                    'saldosuratpengantar.persentaseperalihan',
                    'saldosuratpengantar.statusritasiomset',
                    'saldosuratpengantar.nosptagihlain as nosp2',
                    'saldosuratpengantar.statusgudangsama',
                    'saldosuratpengantar.keterangan',
                    'saldosuratpengantar.penyesuaian',
                    'saldosuratpengantar.sampai_id',
                    'kotasampai.kodekota as sampai',
                    'saldosuratpengantar.statuscontainer_id',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    'saldosuratpengantar.nocont2',
                    'saldosuratpengantar.noseal2',
                    'saldosuratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'saldosuratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'saldosuratpengantar.jenisorder_id',
                    'jenisorder.kodejenisorder as jenisorder',
                    'saldosuratpengantar.tarif_id as tarifrincian_id',
                    'tarif.tujuan as tarifrincian',
                    'saldosuratpengantar.nominalperalihan',
                    'saldosuratpengantar.nojob',
                    'saldosuratpengantar.nojob2',
                    'saldosuratpengantar.cabang_id',
                    'cabang.namacabang as cabang',
                    'saldosuratpengantar.qtyton',
                    'saldosuratpengantar.gudang',
                    'saldosuratpengantar.statusbatalmuat',
                    'saldosuratpengantar.statusupahzona',
                    'saldosuratpengantar.statusgandengan',
                    $tempGaji . '.nominalsupir as gajisupir',
                    $tempGaji . '.nominalkenek as gajikenek',
                    $tempGaji . '.nominalkomisi as komisisupir',
                    'saldosuratpengantar.upah_id',
                    'saldosuratpengantar.statusapprovalbiayatitipanemkl',
                    'zonaupah.zona as upah'
                )
                ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'saldosuratpengantar.dari_id')
                ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'saldosuratpengantar.sampai_id')
                ->leftJoin('agen', 'saldosuratpengantar.agen_id', 'agen.id')
                ->leftJoin('container', 'saldosuratpengantar.container_id', 'container.id')
                ->leftJoin('statuscontainer', 'saldosuratpengantar.statuscontainer_id', 'statuscontainer.id')
                ->leftJoin('trado', 'saldosuratpengantar.trado_id', 'trado.id')
                ->leftJoin('supir', 'saldosuratpengantar.supir_id', 'supir.id')
                ->leftJoin('jenisorder', 'saldosuratpengantar.jenisorder_id', 'jenisorder.id')
                ->leftJoin('tarif', 'saldosuratpengantar.tarif_id', 'tarif.id')
                ->leftJoin('upahsupir', 'saldosuratpengantar.upah_id', 'upahsupir.id')
                ->leftJoin('zona as zonaupah', 'zonaupah.id', '=', 'upahsupir.zonasampai_id')
                ->leftJoin('cabang', 'saldosuratpengantar.cabang_id', 'cabang.id')
                ->leftJoin('pelanggan', 'saldosuratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin('gandengan', 'saldosuratpengantar.gandengan_id', 'gandengan.id')
                ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "saldosuratpengantar.id")

                ->where('saldosuratpengantar.id', $id)->first();
        }
        // dd('find');
        return $data;
    }
}
