<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InputTrip extends MyModel
{
    use HasFactory;


    public function processStore(array $data)
    {
        $jobtrucking = $data['jobtrucking'] ?? '';
        $upahsupirRincian = DB::table('UpahSupirRincian')->from(
            DB::Raw("UpahSupirRincian with (readuncommitted)")
        )
            ->where('upahsupir_id', $data['upah_id'])
            ->where('container_id', $data['container_id'])
            ->where('statuscontainer_id', $data['statuscontainer_id'])
            ->first();
        $tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $statuslangsir = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS LANGSIR')
            ->where('a.subgrp', '=', 'STATUS LANGSIR')
            ->where('a.text', '=', 'BUKAN LANGSIR')
            ->first();

        $statusperalihan = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS PERALIHAN')
            ->where('a.subgrp', '=', 'STATUS PERALIHAN')
            ->where('a.text', '=', 'BUKAN PERALIHAN')
            ->first();

        $statusbatalmuat = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS BATAL MUAT')
            ->where('a.subgrp', '=', 'STATUS BATAL MUAT')
            ->where('a.text', '=', 'BUKAN BATAL MUAT')
            ->first();

        $tarifrincian = TarifRincian::find($data['tarifrincian_id']);

        if ($jobtrucking == '') {
            $orderan = [
                'tglbukti' => $tglbukti,
                'container_id' => $data['container_id'],
                'agen_id' => $data['agen_id'],
                'jenisorder_id' => $data['jenisorder_id'],
                'pelanggan_id' => $data['pelanggan_id'],
                'tarifrincian_id' => $data['tarifrincian_id'],
                'nojobemkl' => $data['nojobemkl'] ?? '',
                'nocont' => $data['nocont'] ?? '',
                'noseal' => $data['noseal'] ?? '',
                'nojobemkl2' => $data['nojobemkl2'] ?? '',
                'nocont2' => $data['nocont2'] ?? '',
                'noseal2' => $data['noseal2'] ?? '',
                'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                'statusperalihan' => $statusperalihan->id,
                'inputtripmandor' =>  '1',
            ];
            $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
            $nobuktiorderantrucking = $orderanTrucking->nobukti;
        } else {
            $nobuktiorderantrucking = $jobtrucking;
        }

        $bukaTrip = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))
            ->first();

        $approvalId = '';
        if ($bukaTrip != null) {
            $approvalId = $bukaTrip->id;
        }
        $getZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
        $upahZona = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $data['upah_id'])->first();

        $data['zonadari_id'] = '';
        $data['zonasampai_id'] = '';

        if ($data['statusupahzona'] == $getZona->id) {
            $data['zonadari_id'] = $upahZona->zonadari_id;
            $data['zonasampai_id'] = $upahZona->zonasampai_id;
        }
        $dataSP = [

            'jobtrucking' => $nobuktiorderantrucking,
            'tglbukti' => $tglbukti,
            'pelanggan_id' => $data['pelanggan_id'],
            'upah_id' => $data['upah_id'],
            'dari_id' => $data['dari_id'],
            'sampai_id' => $data['sampai_id'],
            'container_id' => $data['container_id'],
            'statuscontainer_id' => $data['statuscontainer_id'],
            'penyesuaian' => $data['penyesuaian'],
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
            'gandengan_id' => $data['gandengan_id'],
            'gandenganasal_id' => $data['gandenganasal_id'],
            'statuslongtrip' => $data['statuslongtrip'],
            'statusgandengan' => $data['statusgandengan'],
            'statusupahzona' => $data['statusupahzona'],
            'omset' => $tarifrincian->nominal ?? 0,
            'gajisupir' => $upahsupirRincian->nominalsupir,
            'gajikenek' => $upahsupirRincian->nominalkenek,
            'agen_id' => $data['agen_id'],
            'zonadari_id' => $data['zonadari_id'],
            'zonasampai_id' => $data['zonasampai_id'],
            'jenisorder_id' => $data['jenisorder_id'],
            'statusperalihan' => $statusperalihan->id,
            'totalomset' => $tarifrincian->nominal ?? 0,
            'tglsp' => $tglbukti,
            'statusbatalmuat' => $statusbatalmuat->id,
            'statusgudangsama' => $data['statusgudangsama'],
            'gudang' => $data['gudang'],
            'lokasibongkarmuat' => $data['lokasibongkarmuat'],
            'tarif_id' => $data['tarifrincian_id'],
            'inputtripmandor' => '1',
            'nominal' => '',
            'approvalbukatanggal_id' => $approvalId
        ];
        $suratPengantar = (new SuratPengantar())->processStore($dataSP);

        $jenisRitasi = false;
        foreach ($data['jenisritasi_id'] as $value) {
            if ($value != null || $value != 0) {
                $jenisRitasi = true;
                break;
            }
        }

        if ($jenisRitasi) {
            for ($i = 0; $i < count($data['jenisritasi_id']); $i++) {
                $ritasi = [
                    'tglbukti' => $tglbukti,
                    'statusritasi_id' => $data['jenisritasi_id'][$i],
                    'suratpengantar_nobukti' => $suratPengantar->nobukti,
                    'supir_id' => $data['supir_id'],
                    'trado_id' => $data['trado_id'],
                    'dari_id' => $data['ritasidari_id'][$i],
                    'sampai_id' => $data['ritasike_id'][$i],
                ];
                (new Ritasi())->processStore($ritasi);
            }
        }

        return $suratPengantar;
    }

    public function getKotaRitasi($dataRitasiId)
    {
        $ritasiPulang = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS RITASI')->where('text', 'PULANG RANGKA')->first();
        $ritasiTurun = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS RITASI')->where('text', 'TURUN RANGKA')->first();

        if ($dataRitasiId == $ritasiPulang->id) {
            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('dari_id')->nullable();
                $table->string('dari')->nullable();
                $table->unsignedBigInteger('sampai_id')->nullable();
                $table->string('sampai')->nullable();
            });

            $dari = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where("kodekota", 'BELAWAN RANGKA')->first();
            $sampai = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where("kodekota", 'KIM (KANDANG)')->first();

            DB::table($temp)->insert(
                ["dari_id" => $dari->id, "dari" => $dari->kodekota, "sampai_id" => $sampai->id, "sampai" => $sampai->kodekota]
            );
            $query = DB::table($temp)->from(DB::raw($temp))->first();

            return $query;
        } else if ($dataRitasiId == $ritasiTurun->id) {
            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('dari_id')->nullable();
                $table->string('dari')->nullable();
                $table->unsignedBigInteger('sampai_id')->nullable();
                $table->string('sampai')->nullable();
            });

            $dari = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where("kodekota", 'KIM (KANDANG)')->first();
            $sampai = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where("kodekota", 'BELAWAN RANGKA')->first();

            DB::table($temp)->insert(
                ["dari_id" => $dari->id, "dari" => $dari->kodekota, "sampai_id" => $sampai->id, "sampai" => $sampai->kodekota]
            );
            $query = DB::table($temp)->from(DB::raw($temp))->first();

            return $query;
        } else {
            $query = [];
            return $query;
        }
    }
}
