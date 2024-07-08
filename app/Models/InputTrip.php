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

        $jenisTangki = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        $data['upahtangki_id'] = 0;
        $data['tariftangki_id'] = 0;

        if ($data['statusjeniskendaraan'] == $jenisTangki->id) {
            $data['upahtangki_id'] = $data['upah_id'];
            $data['upah_id'] = '';
            $data['tariftangki_id'] = $data['tarifrincian_id'];
            $data['tarifrincian_id'] = '';
        }

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

        if($data['statuslongtrip'] == 66 && $data['nobukti_tripasal'] == ''){
            $tarifrincian = TarifRincian::where('tarif_id', $data['tarifrincian_id'])->where('container_id', $data['container_id'])->first();
        }
        
        $parameter = new Parameter();
        $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
        if ($data['dari_id'] == $idkandang) {
            $tarifrincian = TarifRincian::where('tarif_id', $data['tarifrincian_id'])->where('container_id',$data['container_id'])->first();
        }

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
                'gandengan_id' => $data['gandengan_id'],
                'statusperalihan' => $statusperalihan->id,
                'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                'tglbataseditorderantrucking' => $tglBatasEdit,
                'inputtripmandor' =>  '1',
            ];
            $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
            $nobuktiorderantrucking = $orderanTrucking->nobukti;
        } else {
            if ($data['statusgudangsama'] == 204) {

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
                    'gandengan_id' => $data['gandengan_id'],
                    'statusperalihan' => $statusperalihan->id,
                    'tglbataseditorderantrucking' => $tglBatasEdit,
                    'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                    'inputtripmandor' =>  '1',
                ];
                $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                $nobuktiorderantrucking = $orderanTrucking->nobukti;
            } else if ($data['statuslongtrip'] != 66) {

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
                    'gandengan_id' => $data['gandengan_id'],
                    'statusperalihan' => $statusperalihan->id,
                    'tglbataseditorderantrucking' => $tglBatasEdit,
                    'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                    'inputtripmandor' =>  '1',
                ];
                $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                $nobuktiorderantrucking = $orderanTrucking->nobukti;
            } else {

                $nobuktiorderantrucking = $jobtrucking;
            }
        }


        $date = date('Y-m-d', strtotime($data['tglbukti']));
        $user_id = auth('api')->user()->id;

        // GET APPROVAL INPUTTRIP
        $tempApp = '##tempApp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempApp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('jumlahtrip')->nullable();
            $table->unsignedBigInteger('statusapproval')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->datetime('tglbatas')->nullable();
        });

        $querybukaabsen = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->select('id', 'tglbukti', 'jumlahtrip', 'statusapproval', 'user_id', 'tglbatas')
            ->where('tglbukti', $date);
        DB::table($tempApp)->insertUsing([
            'id',
            'tglbukti',
            'jumlahtrip',
            'statusapproval',
            'user_id',
            'tglbatas',
        ],  $querybukaabsen);

        // GET MANDOR DETAIL
        $tempMandor = '##tempMandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempMandor, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        $querymandor = DB::table("mandordetail")->from(DB::raw("mandordetail with (readuncommitted)"))
            ->select('mandor_id')->where('user_id', $user_id);
        DB::table($tempMandor)->insertUsing([
            'mandor_id',
        ],  $querymandor);


        // BUAT TEMPORARY SP GROUP BY TEMPO ID
        $tempSP = '##tempSP' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempSP, function ($table) {
            $table->id();
            $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
            $table->unsignedBigInteger('jumlahtrip')->nullable();
        });

        $querySP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('approvalbukatanggal_id', DB::raw("count(nobukti) as jumlahtrip"))
            ->where('tglbukti', $date)
            ->whereRaw("isnull(approvalbukatanggal_id,0) != 0")
            ->groupBy('approvalbukatanggal_id');

        DB::table($tempSP)->insertUsing([
            'approvalbukatanggal_id',
            'jumlahtrip'
        ],  $querySP);


        // GET APPROVAL BERDASARKAN MANDOR

        $getAll = DB::table("mandordetail")->from(DB::raw("mandordetail as a"))
            ->select('a.mandor_id', 'c.id', 'c.user_id', 'c.statusapproval', 'c.tglbatas', 'c.jumlahtrip', 'e.namamandor')
            ->leftJoin(DB::raw("$tempMandor as b with (readuncommitted)"), 'a.mandor_id', 'b.mandor_id')
            ->leftJoin(DB::raw("$tempApp as c with (readuncommitted)"), 'a.user_id', 'c.user_id')
            ->leftJoin(DB::raw("$tempSP as d with (readuncommitted)"), 'c.id', 'd.approvalbukatanggal_id')
            ->leftjoin(db::raw("mandor e "), 'a.mandor_id', 'e.id')
            ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
            ->whereRaw('COALESCE(c.user_id, 0) <> 0')
            ->whereRaw('isnull(d.jumlahtrip,0) < c.jumlahtrip')
            ->orderBy('c.tglbatas', 'desc')
            ->first();

        $approvalId = '';
        if ($getAll != null) {
            $approvalId = $getAll->id;
        }
        $getZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
        $upahZona = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $data['upah_id'])->first();

        $data['zonadari_id'] = '';
        $data['zonasampai_id'] = '';
        $nominalspr = 0;
        $nominalkenek = 0;

        if ($data['statusjeniskendaraan'] != $jenisTangki->id) {
            if ($data['statusupahzona'] == $getZona->id) {
                $data['zonadari_id'] = $upahZona->zonadari_id;
                $data['zonasampai_id'] = $upahZona->zonasampai_id;
            }
            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
            $komisi_gajisupir = $params->text;
            // if ($komisi_gajisupir == 'YA') {
            //     $nominalSupir = $upahsupirRincian->nominalsupir - $upahsupirRincian->nominalkenek;
            // } else {
            $nominalSupir = $upahsupirRincian->nominalsupir ?? 0;
            //


            // status kandang

            $parameter = new Parameter();
            $idstatuskandang = $parameter->cekId('STATUS KANDANG', 'STATUS KANDANG', 'KANDANG') ?? 0;
            $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
            $idpelabuhan = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? 0;

            $upahsupirkandnag = db::table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                ->select(
                    'b.id',
                    'a.kotadari_id',
                    'a.kotasampai_id',
                    'b.upahsupir_id',
                    'b.container_id',
                    'b.statuscontainer_id',
                    'b.nominalsupir',
                    'b.nominalkenek',
                    'b.nominalkomisi',
                    'b.nominaltol',
                    'b.liter',
                    'b.tas_id',
                    'b.info',
                    'b.modifiedby',
                )
                ->join(db::raw("upahsupirrincian b with (readuncommitted)"), 'a.id', 'b.upahsupir_id')
                ->where('a.kotadari_id', $idpelabuhan)
                ->where('a.kotasampai_id', $idkandang)
                ->where('b.container_id', $data['container_id'])
                ->where('b.statuscontainer_id', $data['statuscontainer_id'])
                ->whereraw("isnull(a.penyesuaian,'')=''");

            $tempupahsupirkandang = '##tempupahsupirkandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupahsupirkandang, function ($table) {
                $table->bigInteger('id')->nullable();
                $table->unsignedBigInteger('kotadari_id')->nullable();
                $table->unsignedBigInteger('kotasampai_id')->nullable();
                $table->unsignedBigInteger('upahsupir_id')->nullable();
                $table->unsignedBigInteger('container_id')->nullable();
                $table->unsignedBigInteger('statuscontainer_id')->nullable();
                $table->double('nominalsupir', 15, 2)->nullable();
                $table->double('nominalkenek', 15, 2)->nullable();
                $table->double('nominalkomisi', 15, 2)->nullable();
                $table->double('nominaltol', 15, 2)->nullable();
                $table->double('liter', 15, 2)->nullable();
                $table->unsignedBigInteger('tas_id')->nullable();
                $table->longText('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
            });

            DB::table($tempupahsupirkandang)->insertUsing([
                'id',
                'kotadari_id',
                'kotasampai_id',
                'upahsupir_id',
                'container_id',
                'statuscontainer_id',
                'nominalsupir',
                'nominalkenek',
                'nominalkomisi',
                'nominaltol',
                'liter',
                'tas_id',
                'info',
                'modifiedby',
            ],  $upahsupirkandnag);

            $querynominal = db::table($tempupahsupirkandang)->from(db::raw($tempupahsupirkandang . " a"))
                ->select(
                    'a.nominalsupir',
                    'a.nominalkenek',
                    'a.nominalkomisi',
                )->first();

            if (isset($querynominal)) {
                $nominalsupirkandang = $querynominal->nominalsupir ?? 0;
                $nominalkenekkandang = $querynominal->nominalkenek ?? 0;
                $nominalkomisikandang = $querynominal->nominalkomisi ?? 0;
            } else {
                $nominalsupirkandang = 0;
                $nominalkenekkandang = 0;
                $nominalkomisikandang = 0;
            }


            if ($data['statuskandang_id'] == $idstatuskandang) {
                $nominalspr = $nominalSupir - $nominalsupirkandang;
                $nominalkenek = $upahsupirRincian->nominalkenek - $nominalkenekkandang;
            } else {
                $nominalspr = $nominalSupir;
                $nominalkenek = $upahsupirRincian->nominalkenek;
            }
        } else {
            // $triptangki = 1;
            // $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            //     ->select('triptangki_id')
            //     ->where('supir_id', $data['supir_id'])
            //     ->where('trado_id', $data['trado_id'])
            //     ->where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))
            //     ->where('statusjeniskendaraan', $jenisTangki->id)
            //     ->orderBy('id', 'desc')
            //     ->count();
            // if ($getTripTangki == 0) {
            //     $getTangki = DB::table("triptangki")->from(DB::raw("triptangki with (readuncommitted)"))
            //         ->where('kodetangki', $triptangki)
            //         ->first();
            //     $data['triptangki_id'] = $getTangki->id;
            // }
            $upahsupir = DB::table("upahsupirtangkirincian")->where('upahsupirtangki_id', $data['upahtangki_id'])->where('triptangki_id', $data['triptangki_id'])->first()->nominalsupir ?? 0;
            $nominalspr = $upahsupir;
        }

        // dd('here');
        // 
        $dataSP = [

            'jobtrucking' => $nobuktiorderantrucking,
            'tglbukti' => $tglbukti,
            'pelanggan_id' => $data['pelanggan_id'],
            'upah_id' => $data['upah_id'],
            'upahtangki_id' => $data['upahtangki_id'],
            'dari_id' => $data['dari_id'],
            'sampai_id' => $data['sampai_id'],
            'container_id' => $data['container_id'],
            'statuscontainer_id' => $data['statuscontainer_id'],
            'statuskandang' => $data['statuskandang_id'],
            'penyesuaian' => $data['penyesuaian'],
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
            'gandengan_id' => $data['gandengan_id'],
            'gandenganasal_id' => $data['gandenganasal_id'],
            'statuslongtrip' => $data['statuslongtrip'],
            'statusgandengan' => $data['statusgandengan'],
            'statusupahzona' => $data['statusupahzona'],
            'omset' => $tarifrincian->nominal ?? 0,
            'gajisupir' => $nominalspr,
            'gajikenek' => $nominalkenek,
            // 'gajisupir' => $nominalSupir,
            // 'gajikenek' => $upahsupirRincian->nominalkenek,
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
            'statusjeniskendaraan' => $data['statusjeniskendaraan'],
            'gudang' => $data['gudang'],
            'lokasibongkarmuat' => $data['lokasibongkarmuat'],
            'tarif_id' => $data['tarifrincian_id'],
            'tariftangki_id' => $data['tariftangki_id'],
            'triptangki_id' => $data['triptangki_id'],
            'inputtripmandor' => '1',
            'nominal' => '',
            'tglbataseditsuratpengantar' => $tglBatasEdit,
            'approvalbukatanggal_id' => $approvalId,
            'nobukti_tripasal' => $data['nobukti_tripasal'],
            'statuspenyesuaian' => $data['statuspenyesuaian']
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

    public function getInfo($trado_id, $upah_id, $statuscontainer, $id)
    {
        if ($upah_id != '' && $trado_id != '') {
            $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
                ->select('a.id')
                ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.text', '=', 'TANGKI')
                ->first();

            if (request()->statusjeniskendaraan == $jenisTangki->id) {
                $getUpah = DB::table("upahsupirtangki")->from(DB::raw("upahsupirtangki with (readuncommitted)"))->where('id', $upah_id)->first();
                $jarak = (float) str_replace(',', '', $getUpah->jarak);
            } else {
                $getUpah = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $upah_id)->first();
                if ($statuscontainer == 3) {
                    $jarak = (float) str_replace(',', '', $getUpah->jarakfullempty);
                } else {
                    $jarak = (float) str_replace(',', '', $getUpah->jarak);
                }
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
            if ($id != '') {
                $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->select(
                        'tglbukti',
                        'nobukti',
                        'statusapprovaleditsuratpengantar',
                        'trado_id',
                        'jarak'
                    )
                    ->where('id', $id)
                    ->first();
                if ($trado_id == $query->trado_id) {

                    $query = DB::table($temtabel)->from(DB::raw("$temtabel as a with (readuncommitted)"))
                        ->select(
                            DB::raw("REPLACE(a.status, 'PENGGANTIAN', '') as status"),
                            DB::raw("CONCAT(CAST((a.kmperjalanan - $query->jarak) AS DECIMAL(10, 2)),'(+$jarak)') as kmperjalanan"),
                            DB::raw(" CAST(ROUND((a.kmperjalanan + $jarak - $query->jarak), 2, 1) AS DECIMAL(10, 2)) as kmtotal"),
                            DB::raw("a.statusbatas"),
                            DB::raw("CAST(ROUND(($jarak), 2, 1) AS DECIMAL(10, 2)) as jarak")
                        )->get();
                } else {

                    $query = DB::table($temtabel)->from(DB::raw("$temtabel as a with (readuncommitted)"))
                        ->select(
                            DB::raw("REPLACE(a.status, 'PENGGANTIAN', '') as status"),
                            DB::raw("CONCAT(CAST(a.kmperjalanan AS DECIMAL(10, 2)),'(+$jarak)') as kmperjalanan"),
                            DB::raw(" CAST(ROUND((a.kmperjalanan + $jarak), 2, 1) AS DECIMAL(10, 2)) as kmtotal"),
                            DB::raw("a.statusbatas"),
                            DB::raw("CAST(ROUND(($jarak), 2, 1) AS DECIMAL(10, 2)) as jarak")
                        )->get();
                }
            } else {

                $query = DB::table($temtabel)->from(DB::raw("$temtabel as a with (readuncommitted)"))
                    ->select(
                        DB::raw("REPLACE(a.status, 'PENGGANTIAN', '') as status"),
                        DB::raw("CONCAT(CAST(a.kmperjalanan AS DECIMAL(10, 2)),'(+$jarak)') as kmperjalanan"),
                        DB::raw(" CAST(ROUND((a.kmperjalanan + $jarak), 2, 1) AS DECIMAL(10, 2)) as kmtotal"),
                        DB::raw("a.statusbatas"),
                        DB::raw("CAST(ROUND(($jarak), 2, 1) AS DECIMAL(10, 2)) as jarak")
                    )->get();
            }
            return $query;
        }
    }

    public function getInfoTangki()
    {
        $trado_id = request()->trado_id;
        $supir_id = request()->supir_id;
        $tglbukti = request()->tglbukti;
        $statusjeniskendaraan = request()->statusjeniskendaraan;

        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        if ($statusjeniskendaraan == $jenisTangki->id) {
            $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('triptangki_id')
                ->where('supir_id', $supir_id)
                ->where('trado_id', $trado_id)
                ->where('tglbukti', date('Y-m-d', strtotime($tglbukti)))
                ->where('statusjeniskendaraan', $jenisTangki->id)
                ->orderBy('id', 'desc')
                ->count();
            if ($getTripTangki > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
