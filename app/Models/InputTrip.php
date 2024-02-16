<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
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

        $tarifrincian = TarifRincian::where('tarif_id', $data['tarifrincian_id'])->where('container_id', $data['container_id'])->first();

        $kondisi = true;
        $tanggal = date('Y-m-d', strtotime($data['tglbukti'] . '+1 days'));
        start:
        while ($kondisi == true) {

            $cekHarilibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))->where('tgl', $tanggal)->first();
            if ($cekHarilibur != '') {
                $tanggal = date('Y-m-d', strtotime($tanggal . '+1 days'));
                goto start;
            }

            if (date('D', strtotime($tanggal)) == 'Sun') {
                $tanggal = date('Y-m-d', strtotime($tanggal . '+1 days'));
            } else {
                $kondisi = false;
            }
        }
        end:

        $tglBatasEdit = date('Y-m-d', strtotime($tanggal)) . ' ' . '12:00:00';

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
                'tglbataseditorderantrucking' => $tglBatasEdit,
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
        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
        $komisi_gajisupir = $params->text;
        // if ($komisi_gajisupir == 'YA') {
        //     $nominalSupir = $upahsupirRincian->nominalsupir - $upahsupirRincian->nominalkenek;
        // } else {
        $nominalSupir = $upahsupirRincian->nominalsupir;
        // }
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
            'gajisupir' => $nominalSupir,
            'gajikenek' => $upahsupirRincian->nominalkenek,
            'agen_id' => $data['agen_id'],
            'zonadari_id' => $data['zonadari_id'],
            'zonasampai_id' => $data['zonasampai_id'],
            'jenisorder_id' => $data['jenisorder_id'],
            'jenisorderemkl_id' => $data['jenisorder_id'],
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
            'tglbataseditsuratpengantar' => $tglBatasEdit,
            'approvalbukatanggal_id' => $approvalId,
            'nobukti_tripasal' => $data['nobukti_tripasal']
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

    public function getInfo($trado_id, $upah_id, $statuscontainer)
    {
        if ($upah_id != '') {

            $getUpah = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $upah_id)->first();
            if ($statuscontainer == 3) {
                $jarak = number_format((float) $getUpah->jarakfullempty, 2);
            } else {
                $jarak = number_format((float) $getUpah->jarak, 2);
            }

            $getTrado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('id', $trado_id)->first();
            $temtabel = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->longText('nopol')->nullable();
                $table->integer('trado_id')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('status', 100)->nullable();
                $table->double('km', 15, 2)->nullable();
                $table->double('kmperjalanan', 15, 2)->nullable();
                $table->integer('statusbatas')->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'nopol',
                'trado_id',
                'tanggal',
                'status',
                'km',
                'kmperjalanan',
                'statusbatas'
            ], (new ReminderOli())->getdata2($trado_id));
            $query = DB::table($temtabel)->from(DB::raw("$temtabel as a with (readuncommitted)"))
                ->select(
                    DB::raw("REPLACE(a.status, 'PENGGANTIAN', '') as status"),
                    DB::raw("CONCAT(CAST(a.kmperjalanan AS DECIMAL(10, 2)),'(+$jarak)') as kmperjalanan"),
                    DB::raw(" CAST(ROUND((a.kmperjalanan + $jarak), 2, 1) AS DECIMAL(10, 2)) as kmtotal"),
                    DB::raw("a.statusbatas"),
                    DB::raw("CAST(ROUND(($jarak), 2, 1) AS DECIMAL(10, 2)) as jarak")
                )->get();
            return $query;
        }
    }
}
