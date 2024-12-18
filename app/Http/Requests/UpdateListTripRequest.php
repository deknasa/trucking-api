<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\ReminderOli;
use App\Models\UpahSupirRincian;
use App\Rules\cekJobTruckingEditTrip;
use App\Rules\cekUpahRitasiDariInputTrip;
use App\Rules\cekUpahRitasiKeInputTrip;
use App\Rules\cekUpahSupirEditTrip;
use App\Rules\DateApprovalQuota;
use App\Rules\DestroyListTrip;
use App\Rules\ExistAbsensiSupirDetail;
use App\Rules\ExistAgen;
use App\Rules\ExistContainer;
use App\Rules\ExistGandengan;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistKota;
use App\Rules\ExistNominalUpahSupir;
use App\Rules\ExistPelanggan;
use App\Rules\ExistStatusContainer;
use App\Rules\ExistSupir;
use App\Rules\ExistTarifRincianSuratPengantar;
use App\Rules\ExistTrado;
use App\Rules\ExistUpahSupirRincianSuratPengantar;
use App\Rules\JenisRitasiInputTrip;
use App\Rules\ValidasiAgenTripGudangSama;
use App\Rules\validasiBatasLuarKota;
use App\Rules\ValidasiContainerTripGudangSama;
use App\Rules\ValidasiExistOmsetTarif;
use App\Rules\ValidasiJenisOrderGudangsama;
use App\Rules\ValidasiJenisOrderLongtrip;
use App\Rules\ValidasiKotaUpahZona;
use App\Rules\ValidasiKotaZonaTrip;
use App\Rules\ValidasiLongtripGudangsama;
use App\Rules\validasiNominalUpahSupirTangkiTrip;
use App\Rules\ValidasiPelangganTripGudangSama;
use App\Rules\ValidasiReminderOli;
use App\Rules\ValidasiReminderOliGardan;
use App\Rules\ValidasiReminderOliPersneling;
use App\Rules\ValidasiReminderSaringanHawa;
use App\Rules\validasiStatusContainerLongtrip;
use App\Rules\validasiStatusJenisKendaraan;
use App\Rules\ValidasiTradoTripGudangSama;
use App\Rules\validasiTripDipakaiKeKandang;
use App\Rules\ValidasiTripGudangSama;
use App\Rules\validasiTripTangkiEditTrip;
use App\Rules\validasiUpahSupirTangki;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateListTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
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
        if (request()->statusjeniskendaraan == $jenisTangki->id) {
            $agen_id = $this->agen_id;
            $rulesAgen_id = [];
            if ($agen_id != null) {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen()]
                ];
            } else if ($agen_id == null && $this->agen != '') {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen()]
                ];
            }
            $triptangki_id = $this->triptangki_id;
            $rulesTripTangki_id = [];
            if ($triptangki_id != null) {
                $rulesTripTangki_id = [
                    'triptangki_id' => ['required', 'numeric', 'min:1']
                ];
            } else if ($triptangki_id == null && $this->triptangki != '') {
                $rulesTripTangki_id = [
                    'triptangki_id' => ['required', 'numeric', 'min:1']
                ];
            }
            $upah_id = $this->upah_id;
            $rulesUpah_id = [];
            if ($upah_id != null) {
                $rulesUpah_id = [
                    'upah_id' => ['required', 'numeric', 'min:1']
                ];
            } else if ($upah_id == null && request()->upah != '') {
                $rulesUpah_id = [
                    'upah_id' => ['required', 'numeric', 'min:1']
                ];
            }
            $rulesDari_id = [];
            $rulesSampai_id = [];

            if ($upah_id != null) {
                $dari_id = $this->dari_id;
                if ($dari_id != null) {
                    $rulesDari_id = [
                        'dari_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                } else if ($dari_id == null && $this->dari != '') {
                    $rulesDari_id = [
                        'dari_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                }

                $sampai_id = $this->sampai_id;
                if ($sampai_id != null) {
                    $rulesSampai_id = [
                        'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                } else if ($sampai_id == null && $this->sampai != '') {
                    $rulesSampai_id = [
                        'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                }
            }
            $pelanggan_id = $this->pelanggan_id;
            $rulesPelanggan_id = [];
            if ($pelanggan_id != null) {
                $rulesPelanggan_id = [
                    'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
                ];
            } else if ($pelanggan_id == null && $this->pelanggan != '') {
                $rulesPelanggan_id = [
                    'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
                ];
            }


            $trado_id = request()->trado_id ?? 0;
            if (request()->trado_id != '') {
                // 
                $tempreminderoli = '##tempreminderoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempreminderoli, function ($table) {
                    $table->id();
                    $table->longText('nopol')->nullable();
                    $table->integer('trado_id')->nullable();
                    $table->date('tanggal')->nullable();
                    $table->string('status', 100)->nullable();
                    $table->double('km', 15, 2)->nullable();
                    $table->double('kmperjalanan', 15, 2)->nullable();
                    $table->integer('statusbatas')->nullable();
                });

                DB::table($tempreminderoli)->insertUsing([
                    'nopol',
                    'trado_id',
                    'tanggal',
                    'status',
                    'km',
                    'kmperjalanan',
                    'statusbatas',
                ], (new ReminderOli())->getdata2(request()->trado_id));
                $table = DB::table($tempreminderoli)->get();
                $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->select(
                        'tglbukti',
                        'nobukti',
                        'statusapprovaleditsuratpengantar',
                        'trado_id',
                        'jarak',
                    )
                    ->where('id', $this->id)
                    ->first();


                for ($i = 1; $i <= count($table); $i++) {
                    $getJarak = DB::table("upahsupirtangki")->from(DB::raw("upahsupirtangki with (readuncommitted)"))->where('id', request()->upah_id)->first();
                    $jarak = $getJarak->jarak ?? 0;

                    if ($query->trado_id == request()->trado_id) {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak - $query->jarak) where id='$i'"));
                    } else {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak) where id='$i'"));
                    }
                }


                $statusapproval = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.grp', 'STATUS APPROVAL')
                    ->where('a.subgrp', 'STATUS APPROVAL')
                    ->where('a.text', 'APPROVAL')
                    ->first()->id ?? 0;



                // pergantian oli mesin
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Mesin')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolimesin', $statusapproval)
                        ->whereraw("tglbatasreminderolimesin>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolimesin = true;
                            $keteranganvalidasireminderolimesin = "OLI MESIN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolimesin = false;
                            $keteranganvalidasireminderolimesin = "";
                        }
                    } else {
                        $validasireminderolimesin = false;
                        $keteranganvalidasireminderolimesin = "";
                    }
                } else {
                    $validasireminderolimesin = false;
                    $keteranganvalidasireminderolimesin = "";
                }

                // pergantian oli persneling
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Persneling')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolipersneling', $statusapproval)
                        ->whereraw("tglbatasreminderolipersneling>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolipersneling = true;
                            $keteranganvalidasireminderolipersneling = "OLI PERSNELING SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolipersneling = false;
                            $keteranganvalidasireminderolipersneling = "";
                        }
                    } else {
                        $validasireminderolipersneling = false;
                        $keteranganvalidasireminderolipersneling = "";
                    }
                } else {
                    $validasireminderolipersneling = false;
                    $keteranganvalidasireminderolipersneling = "";
                }

                // pergantian oli GARDAN
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Gardan')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderoligardan', $statusapproval)
                        ->whereraw("tglbatasreminderoligardan>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderoligardan = true;
                            $keteranganvalidasireminderoligardan = "OLI GARDAN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderoligardan = false;
                            $keteranganvalidasireminderoligardan = "";
                        }
                    } else {
                        $validasireminderoligardan = false;
                        $keteranganvalidasireminderoligardan = "";
                    }
                } else {
                    $validasireminderoligardan = false;
                    $keteranganvalidasireminderoligardan = "";
                }

                // pergantian SARINGAN HAWA
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Saringan Hawa')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalremindersaringanhawa', $statusapproval)
                        ->whereraw("tglbatasremindersaringanhawa>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasiremindersaringanhawa = true;
                            $keteranganvalidasiremindersaringanhawa = "SARINGAN HAWA SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasiremindersaringanhawa = false;
                            $keteranganvalidasiremindersaringanhawa = "";
                        }
                    } else {
                        $validasiremindersaringanhawa = false;
                        $keteranganvalidasiremindersaringanhawa = "";
                    }
                } else {
                    $validasiremindersaringanhawa = false;
                    $keteranganvalidasiremindersaringanhawa = "";
                }
            }
            // 
            $rulesTrado_id = [];
            if ($this->trado != '') {
                $rulesTrado_id = [
                    'trado_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistTrado(),
                        new validasiBatasLuarKota(),
                        new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin),
                        new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling),
                        new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan),
                        new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
                    ],
                    'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()],
                    'absensidetail_id' => ['required', 'numeric', 'min:1', new ExistAbsensiSupirDetail()],
                ];
            }

            $parameter = new Parameter();
            $dataPenyesuaian = $parameter->getcombodata('STATUS PENYESUAIAN', 'STATUS PENYESUAIAN');
            $dataPenyesuaian = json_decode($dataPenyesuaian, true);
            foreach ($dataPenyesuaian as $item) {
                $statusPenyesuaian[] = $item['id'];
            }
            $rules = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    new DateApprovalQuota()
                ],
                "agen" => ["required"],
                "dari" => ["required"],
                "tarifrincian" => ['required'],
                "statusjeniskendaraan" => ["required", new validasiStatusJenisKendaraan()],
                "pelanggan" => ["required"],
                "sampai" => ["required"],
                "trado" => ["required"],
                "upah" => ["required", new validasiNominalUpahSupirTangkiTrip()],
                "triptangki" => ["required", new validasiTripTangkiEditTrip()],
                'statuspenyesuaian' => ['required', Rule::in($statusPenyesuaian)],
            ];
            $rulesId = [
                'id' => new DestroyListTrip()
            ];
            $rules = array_merge(
                $rules,
                $rulesId,
                $rulesAgen_id,
                $rulesDari_id,
                $rulesSampai_id,
                $rulesPelanggan_id,
                $rulesTrado_id,
                $rulesUpah_id,
                $rulesTripTangki_id
            );
        } else {


            $parameter = new Parameter();
            $dataUpahZona = $parameter->getcombodata('STATUS UPAH ZONA', 'STATUS UPAH ZONA');
            $dataUpahZona = json_decode($dataUpahZona, true);
            foreach ($dataUpahZona as $item) {
                $statusUpahZona[] = $item['id'];
            }
            $dataPenyesuaian = $parameter->getcombodata('STATUS PENYESUAIAN', 'STATUS PENYESUAIAN');
            $dataPenyesuaian = json_decode($dataPenyesuaian, true);
            foreach ($dataPenyesuaian as $item) {
                $statusPenyesuaian[] = $item['id'];
            }

            $trip = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(DB::raw("suratpengantar.jobtrucking, suratpengantar.gudang, suratpengantar.upah_id, suratpengantar.statuscontainer_id, suratpengantar.dari_id, agen.namaagen as agen, container.kodecontainer as container,statuscontainer.kodestatuscontainer as statuscontainer, jenisorder.keterangan as jenisorder, pelanggan.namapelanggan as pelanggan"))
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'suratpengantar.agen_id', 'agen.id')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
                ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
                ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'suratpengantar.jenisorder_id', 'jenisorder.id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->where('suratpengantar.id', request()->id)->first();
            $ruleAgen = '';
            $ruleContainer = '';
            $ruleStatusContainer = '';
            $ruleJenisorder = '';
            $rulePelanggan = '';
            $ruleGudang = '';
            $idUpahSupir = 0;
            $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
            if ($trip->statuscontainer_id != 3) {
                if ($trip->jobtrucking != '') {
                    if ($trip->dari_id != 1) {
                        $cekjobtrucking = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $trip->jobtrucking)->where('id', '<>', request()->id)->first();
                        // if ($cekjobtrucking != '') {
                        //     $idUpahSupir = $trip->upah_id;
                        //     $ruleAgen = Rule::in($trip->agen);
                        //     $ruleContainer = Rule::in($trip->container);
                        //     $ruleStatusContainer = Rule::in($trip->statuscontainer);
                        //     $ruleJenisorder = Rule::in($trip->jenisorder);
                        //     $rulePelanggan = Rule::in($trip->pelanggan);
                        //     $ruleGudang = Rule::in($trip->gudang);
                        // }
                    }
                }
            }
            $getGudangSama = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GUDANG SAMA')->where('text', 'GUDANG SAMA')->first();
            $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
            $getUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
            $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'INPUTTRIP')->first();
            $dataTripAsal = [];
            if (request()->statusgudangsama == $getGudangSama->id) {
                if (request()->nobukti_tripasal != '') {
                    $getDataTripAsal = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))->select('upah_id', 'agen_id', 'pelanggan_id', 'container_id', 'trado_id')->where('nobukti', request()->nobukti_tripasal)->first();
                    $dataTripAsal = json_decode(json_encode($getDataTripAsal), true);
                }
            }

            // START VALIDASI RITASI
            $ritasiRule = [];
            $ruleCekUpahRitasi = [];
            if (request()->ritasidari != null && request()->ritasike != null) {

                $jenisRitasi = false;
                foreach (request()->jenisritasi as $value) {
                    if ($value != null) {
                        $jenisRitasi = true;
                        break;
                    }
                }
                $ritasiDari = false;
                foreach (request()->ritasidari as $value) {
                    if ($value != null) {
                        $ritasiDari = true;
                        break;
                    }
                }
                $ritasiKe = false;
                foreach (request()->ritasike as $value) {
                    if ($value != null) {
                        $ritasiKe = true;
                        break;
                    }
                }
                if ($jenisRitasi || $ritasiDari || $ritasiKe) {
                    $parameter = new Parameter();
                    $data = $parameter->getcombodata('STATUS RITASI', 'STATUS RITASI');
                    $data = json_decode($data, true);
                    foreach ($data as $item) {
                        $status[] = $item['id'];
                    }
                    $ritasiRule = [
                        'jenisritasi.*' => ['required'],
                        'jenisritasi_id.*' => [new JenisRitasiInputTrip()],
                        'ritasidari.*' => ['required'],
                        'ritasike.*' => ['required']
                    ];
                }
                if ($jenisRitasi && $ritasiDari && $ritasiKe && request()->container_id != 0) {
                    $ruleCekUpahRitasi = [
                        'ritasidari.*' => ['required', new cekUpahRitasiDariInputTrip()],
                        'ritasike.*' => ['required', new cekUpahRitasiKeInputTrip()]
                    ];
                }
            }
            // END VALIDASI RITASI

            $agen_id = $this->agen_id;
            $rulesAgen_id = [];
            if ($agen_id != null) {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen()]
                ];
            } else if ($agen_id == null && $this->agen != '') {
                $rulesAgen_id = [
                    'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen()]
                ];
            }

            $container_id = $this->container_id;
            $rulesContainer_id = [];
            if ($container_id != null) {
                $rulesContainer_id = [
                    'container_id' => ['required', 'numeric', 'min:1', new ExistContainer()]
                ];
            } else if ($container_id == null && request()->container != '') {
                $rulesContainer_id = [
                    'container_id' => ['required', 'numeric', 'min:1', new ExistContainer()]
                ];
            }


            $upah_id = $this->upah_id;
            $rulesUpah_id = [];
            if ($upah_id != null) {
                $rulesUpah_id = [
                    'upah_id' => ['required', 'numeric', 'min:1', new ExistUpahSupirRincianSuratPengantar()]
                ];
            } else if ($upah_id == null && request()->upah != '') {
                $rulesUpah_id = [
                    'upah_id' => ['required', 'numeric', 'min:1', new ExistUpahSupirRincianSuratPengantar()]
                ];
            }

            $rulesDari_id = [];
            $rulesSampai_id = [];

            if ($upah_id != null) {
                $validasiUpah = (new UpahSupirRincian())->cekValidasiInputTripUpah(request()->statuscontainer_id, request()->jenisorder_id, request()->upah_id);
                $getUpah = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->select('zonadari_id', 'zonasampai_id')->where('id', request()->upah_id)->first();
                $validasiDari = '';
                if (request()->statuslongtrip == 66 && $validasiUpah != '') {
                    $validasiDari = (request()->statusupahzona == $getUpahZona->id) ? new ValidasiKotaZonaTrip($getUpah->zonadari_id) : Rule::in($validasiUpah->kotadari_id);
                }
                $validasiSampai = '';
                if (request()->statuslongtrip == 66 && $validasiUpah != '') {
                    $validasiSampai = (request()->statusupahzona == $getUpahZona->id) ? new ValidasiKotaZonaTrip($getUpah->zonasampai_id) : Rule::in($validasiUpah->kotasampai_id);
                }
                $dari_id = $this->dari_id;
                if ($dari_id != null) {
                    $rulesDari_id = [
                        'dari_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                } else if ($dari_id == null && $this->dari != '') {
                    $rulesDari_id = [
                        'dari_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                }

                $sampai_id = $this->sampai_id;
                if ($sampai_id != null) {
                    $rulesSampai_id = [
                        'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                } else if ($sampai_id == null && $this->sampai != '') {
                    $rulesSampai_id = [
                        'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota()]
                    ];
                }
            }
            $pelanggan_id = $this->pelanggan_id;
            $rulesPelanggan_id = [];
            if ($pelanggan_id != null) {
                $rulesPelanggan_id = [
                    'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
                ];
            } else if ($pelanggan_id == null && $this->pelanggan != '') {
                $rulesPelanggan_id = [
                    'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
                ];
            }



            $jenisorder_id = $this->jenisorder_id;
            $rulesJenisOrder_id = [];
            if ($jenisorder_id != null) {
                $rulesJenisOrder_id = [
                    'jenisorder_id' => ['required', 'numeric', 'min:1', new ExistJenisOrder()]
                ];
            } else if ($jenisorder_id == null && $this->jenisorder != '') {
                $rulesJenisOrder_id = [
                    'jenisorder_id' => ['required', 'numeric', 'min:1', new ExistJenisOrder()]
                ];
            }

            $statusContainer_id = $this->statuscontainer_id;
            $rulesStatusContainer_id = [];
            if ($statusContainer_id != null) {
                $rulesStatusContainer_id = [
                    'statuscontainer_id' => ['required', 'numeric', 'min:1', new ExistStatusContainer()]
                ];
            } else if ($statusContainer_id == null && $this->statuscontainer != '') {
                $rulesStatusContainer_id = [
                    'statuscontainer_id' => ['required', 'numeric', 'min:1', new ExistStatusContainer()]
                ];
            }

            $trado_id = request()->trado_id ?? 0;
            if (request()->trado_id != '') {
                // 
                $tempreminderoli = '##tempreminderoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempreminderoli, function ($table) {
                    $table->id();
                    $table->longText('nopol')->nullable();
                    $table->integer('trado_id')->nullable();
                    $table->date('tanggal')->nullable();
                    $table->string('status', 100)->nullable();
                    $table->double('km', 15, 2)->nullable();
                    $table->double('kmperjalanan', 15, 2)->nullable();
                    $table->integer('statusbatas')->nullable();
                });

                DB::table($tempreminderoli)->insertUsing([
                    'nopol',
                    'trado_id',
                    'tanggal',
                    'status',
                    'km',
                    'kmperjalanan',
                    'statusbatas',
                ], (new ReminderOli())->getdata2(request()->trado_id));
                $table = DB::table($tempreminderoli)->get();
                $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->select(
                        'tglbukti',
                        'nobukti',
                        'statusapprovaleditsuratpengantar',
                        'trado_id',
                        'jarak'
                    )
                    ->where('id', $this->id)
                    ->first();


                for ($i = 1; $i <= count($table); $i++) {
                    $getJarak = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', request()->upah_id)->first();
                    $jarak = 0;
                    if (request()->statuscontainer_id != '') {
                        if (request()->statuscontainer_id == 3) {
                            $jarak = $getJarak->jarakfullempty ?? 0;
                        } else {
                            $jarak = $getJarak->jarak ?? 0;
                        }
                    }
                    if ($query->trado_id == request()->trado_id) {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak - $query->jarak) where id='$i'"));
                    } else {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak) where id='$i'"));
                    }
                }


                $statusapproval = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.grp', 'STATUS APPROVAL')
                    ->where('a.subgrp', 'STATUS APPROVAL')
                    ->where('a.text', 'APPROVAL')
                    ->first()->id ?? 0;



                // pergantian oli mesin
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Mesin')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolimesin', $statusapproval)
                        ->whereraw("tglbatasreminderolimesin>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolimesin = true;
                            $keteranganvalidasireminderolimesin = "OLI MESIN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolimesin = false;
                            $keteranganvalidasireminderolimesin = "";
                        }
                    } else {
                        $validasireminderolimesin = false;
                        $keteranganvalidasireminderolimesin = "";
                    }
                } else {
                    $validasireminderolimesin = false;
                    $keteranganvalidasireminderolimesin = "";
                }

                // pergantian oli persneling
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Persneling')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolipersneling', $statusapproval)
                        ->whereraw("tglbatasreminderolipersneling>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolipersneling = true;
                            $keteranganvalidasireminderolipersneling = "OLI PERSNELING SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolipersneling = false;
                            $keteranganvalidasireminderolipersneling = "";
                        }
                    } else {
                        $validasireminderolipersneling = false;
                        $keteranganvalidasireminderolipersneling = "";
                    }
                } else {
                    $validasireminderolipersneling = false;
                    $keteranganvalidasireminderolipersneling = "";
                }

                // pergantian oli GARDAN
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Gardan')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderoligardan', $statusapproval)
                        ->whereraw("tglbatasreminderoligardan>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderoligardan = true;
                            $keteranganvalidasireminderoligardan = "OLI GARDAN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderoligardan = false;
                            $keteranganvalidasireminderoligardan = "";
                        }
                    } else {
                        $validasireminderoligardan = false;
                        $keteranganvalidasireminderoligardan = "";
                    }
                } else {
                    $validasireminderoligardan = false;
                    $keteranganvalidasireminderoligardan = "";
                }

                // pergantian SARINGAN HAWA
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Saringan Hawa')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalremindersaringanhawa', $statusapproval)
                        ->whereraw("tglbatasremindersaringanhawa>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasiremindersaringanhawa = true;
                            $keteranganvalidasiremindersaringanhawa = "SARINGAN HAWA SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasiremindersaringanhawa = false;
                            $keteranganvalidasiremindersaringanhawa = "";
                        }
                    } else {
                        $validasiremindersaringanhawa = false;
                        $keteranganvalidasiremindersaringanhawa = "";
                    }
                } else {
                    $validasiremindersaringanhawa = false;
                    $keteranganvalidasiremindersaringanhawa = "";
                }
            }
            // 
            $rulesTrado_id = [];
            if ($this->trado != '') {
                $rulesTrado_id = [
                    'trado_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistTrado(),
                        new validasiBatasLuarKota(),
                        new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin),
                        new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling),
                        new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan),
                        new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
                    ],
                    'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()],
                    'absensidetail_id' => ['required', 'numeric', 'min:1', new ExistAbsensiSupirDetail()],
                ];
            }

            $rulesTarif_id = [];
            $rulesGandengan_id = [];
            $ruleTripAsal = Rule::requiredIf(function () {
                $getGudangSama = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GUDANG SAMA')->where('text', 'GUDANG SAMA')->first();
                $getLongtrip = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LONGTRIP')->where('text', 'LONGTRIP')->first();

                if (request()->statusgudangsama ==  $getGudangSama->id) {
                    if ((request()->statuscontainer_id == 1 && request()->jenisorder_id == 1) || (request()->statuscontainer_id == 1 && request()->jenisorder_id == 4)) {
                        return true;
                    }
                }
                if (request()->statuslongtrip ==  $getLongtrip->id) {
                    return true;
                }
                return false;
            });

            if ((request()->dari_id == 1 && request()->sampai_id == 103) || (request()->dari_id == 103 && request()->sampai_id == 1) || (request()->statuslongtrip == 65)) {


                $getgerobak = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GEROBAK')->where('subgrp', 'STATUS GEROBAK')->where('text', 'GEROBAK')->first();
                $gettrado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('id', request()->trado_id)->first();
                $gerobakVal = ($gettrado == null) ? 0 : $gettrado->statusgerobak;
                if ($getgerobak->id == $gerobakVal) {
                    $rules = [
                        'tglbukti' => [
                            'required',
                            'date_format:d-m-Y',
                            new DateApprovalQuota()
                        ],
                        "nobukti_tripasal" => $ruleTripAsal,
                        "agen" => ["required", $ruleAgen,  new ValidasiAgenTripGudangSama($dataTripAsal)],
                        "container" => ["required", $ruleContainer, new ValidasiContainerTripGudangSama($dataTripAsal)],
                        "dari" => ["required"],
                        "gudang" => ["required", $ruleGudang],
                        "jenisorder" => ["required", $ruleJenisorder, new ValidasiJenisOrderGudangsama()],
                        "pelanggan" => ["required", $rulePelanggan, new ValidasiPelangganTripGudangSama($dataTripAsal)],
                        "sampai" => ["required"],
                        "statusjeniskendaraan" => ["required", new validasiStatusJenisKendaraan()],
                        "statuscontainer" => ["required", $ruleStatusContainer],
                        "statusgudangsama" => ["required", new ValidasiLongtripGudangsama()],
                        "statuslongtrip" => ["required",  new validasiStatusContainerLongtrip()],
                        "statuslangsir" => "required",
                        // "lokasibongkarmuat" => "required",
                        "trado" => ["required", new ValidasiTradoTripGudangSama($dataTripAsal)],
                        "upah" => ["required", new ExistNominalUpahSupir(), new validasiTripDipakaiKeKandang(), new ValidasiTripGudangSama($dataTripAsal)],

                        'statuspenyesuaian' => ['required', Rule::in($statusPenyesuaian)],
                    ];
                } else {
                    $gandengan_id = $this->gandengan_id;
                    $rulesGandengan_id = [];
                    if ($gandengan_id != null) {
                        $rulesGandengan_id = [
                            'gandengan_id' => ['required', 'numeric', 'min:1', new ExistGandengan()]
                        ];
                    } else if ($gandengan_id == null && $this->gandengan != '') {
                        $rulesGandengan_id = [
                            'gandengan_id' => ['required', 'numeric', 'min:1', new ExistGandengan()]
                        ];
                    }
                    $rules = [
                        'tglbukti' => [
                            'required',
                            'date_format:d-m-Y',
                            new DateApprovalQuota()
                        ],
                        "nobukti_tripasal" => $ruleTripAsal,
                        "agen" => ["required", $ruleAgen,  new ValidasiAgenTripGudangSama($dataTripAsal)],
                        "container" => ["required", $ruleContainer, new ValidasiContainerTripGudangSama($dataTripAsal)],
                        "dari" => ["required"],
                        "gandengan" => "required",
                        "statusjeniskendaraan" => ["required", new validasiStatusJenisKendaraan()],
                        "gudang" => ["required", $ruleGudang],
                        "jenisorder" => ["required", $ruleJenisorder, new ValidasiJenisOrderGudangsama()],
                        "pelanggan" => ["required", $rulePelanggan, new ValidasiPelangganTripGudangSama($dataTripAsal)],
                        "sampai" => ["required"],
                        "statuscontainer" => ["required", $ruleStatusContainer],
                        "statusgudangsama" => ["required", new ValidasiLongtripGudangsama()],
                        "statuslongtrip" => ["required", new validasiStatusContainerLongtrip()],
                        "statuslangsir" => "required",
                        // "lokasibongkarmuat" => "required",
                        "trado" => ["required", new ValidasiTradoTripGudangSama($dataTripAsal)],
                        "upah" => ["required", new ExistNominalUpahSupir(), new validasiTripDipakaiKeKandang(),  new ValidasiTripGudangSama($dataTripAsal)],

                        'statuspenyesuaian' => ['required', Rule::in($statusPenyesuaian)],
                    ];
                }
            } else {
                $tarifrincian_id = $this->tarifrincian_id;
                $rulesTarif_id = [];
                if ($tarifrincian_id != null) {
                    $rulesTarif_id = [
                        'tarifrincian_id' => ['required', 'numeric', 'min:1', new ExistTarifRincianSuratPengantar()]
                    ];
                } else if ($tarifrincian_id == null && request()->tarifrincian != '') {
                    $rulesTarif_id = [
                        'tarifrincian_id' => ['required', 'numeric', 'min:1', new ExistTarifRincianSuratPengantar()]
                    ];
                }

                $getgerobak = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GEROBAK')->where('subgrp', 'STATUS GEROBAK')->where('text', 'GEROBAK')->first();
                $gettrado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('id', request()->trado_id)->first();
                $gerobakVal = ($gettrado == null) ? 0 : $gettrado->statusgerobak;
                if ($getgerobak->id == $gerobakVal) {
                    $rules = [
                        'tglbukti' => [
                            'required',
                            'date_format:d-m-Y',
                            new DateApprovalQuota()
                        ],
                        "nobukti_tripasal" => $ruleTripAsal,
                        "agen" => ["required", $ruleAgen,  new ValidasiAgenTripGudangSama($dataTripAsal)],
                        "tarifrincian" => [new ValidasiExistOmsetTarif()],
                        "container" => ["required", $ruleContainer, new ValidasiContainerTripGudangSama($dataTripAsal)],
                        "dari" => ["required"],
                        "gudang" => ["required", $ruleGudang],
                        "statusjeniskendaraan" => ["required", new validasiStatusJenisKendaraan()],
                        "jenisorder" => ["required", $ruleJenisorder, new ValidasiJenisOrderGudangsama()],
                        "pelanggan" => ["required", $rulePelanggan, new ValidasiPelangganTripGudangSama($dataTripAsal)],
                        "sampai" => ["required"],
                        "statuscontainer" => ["required", $ruleStatusContainer],
                        "statusgudangsama" => ["required", new ValidasiLongtripGudangsama()],
                        "statuslongtrip" => ["required", new validasiStatusContainerLongtrip()],
                        "statuslangsir" => "required",
                        // "lokasibongkarmuat" => "required",
                        "trado" => ["required", new ValidasiTradoTripGudangSama($dataTripAsal)],
                        "upah" => ["required", new ExistNominalUpahSupir(), new validasiTripDipakaiKeKandang(), new ValidasiTripGudangSama($dataTripAsal)],

                        'statuspenyesuaian' => ['required', Rule::in($statusPenyesuaian)],
                    ];
                } else {
                    $gandengan_id = $this->gandengan_id;
                    $rulesGandengan_id = [];
                    if ($gandengan_id != null) {
                        $rulesGandengan_id = [
                            'gandengan_id' => ['required', 'numeric', 'min:1', new ExistGandengan()]
                        ];
                    } else if ($gandengan_id == null && $this->gandengan != '') {
                        $rulesGandengan_id = [
                            'gandengan_id' => ['required', 'numeric', 'min:1', new ExistGandengan()]
                        ];
                    }
                    $rules = [
                        'tglbukti' => [
                            'required',
                            'date_format:d-m-Y',
                            // new DateApprovalQuota()
                        ],
                        "nobukti_tripasal" => $ruleTripAsal,
                        "agen" => ["required", $ruleAgen,  new ValidasiAgenTripGudangSama($dataTripAsal)],
                        "tarifrincian" => [new ValidasiExistOmsetTarif()],
                        "container" => ["required", $ruleContainer, new ValidasiContainerTripGudangSama($dataTripAsal)],
                        "dari" => ["required"],
                        "gandengan" => ["required", 'nullable'],
                        "gudang" => ["required", $ruleGudang],
                        "statusjeniskendaraan" => ["required", new validasiStatusJenisKendaraan()],
                        "jenisorder" => ["required", $ruleJenisorder, new ValidasiJenisOrderGudangsama()],
                        "pelanggan" => ["required", $rulePelanggan, new ValidasiPelangganTripGudangSama($dataTripAsal)],
                        "sampai" => ["required"],
                        "statuscontainer" => ["required", $ruleStatusContainer],
                        "statusgudangsama" => ["required", new ValidasiLongtripGudangsama()],
                        "statuslongtrip" => ["required", new validasiStatusContainerLongtrip()],
                        "statuslangsir" => "required",
                        // "lokasibongkarmuat" => "required",
                        "trado" => ["required", new ValidasiTradoTripGudangSama($dataTripAsal)],
                        "upah" => ["required", new ExistNominalUpahSupir(), new validasiTripDipakaiKeKandang(), new ValidasiTripGudangSama($dataTripAsal)],

                        'statuspenyesuaian' => ['required', Rule::in($statusPenyesuaian)],
                    ];
                }
            }


            $getListTampilan = json_decode($getListTampilan->memo);
            if ($getListTampilan->INPUT != '') {
                $getListTampilan = (explode(",", $getListTampilan->INPUT));
                foreach ($getListTampilan as $value) {
                    if ($value == 'JOBLANGSIR') {
                        $value = 'statuslangsir';
                    }
                    if (array_key_exists(trim(strtolower($value)), $rules) == true) {
                        if (trim(strtolower($value)) == 'gandengan') {
                            unset($rulesGandengan_id['gandengan_id']);
                        }
                        unset($rules[trim(strtolower($value))]);
                    }
                }
            }

            $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
            $jobmanual = $parameter->cekText('JOB TRUCKING MANUAL', 'JOB TRUCKING MANUAL') ?? 'TIDAK';
            $rulesJobTrucking = [];
            if ($jobmanual == 'TIDAK') {
                if ((request()->statuslongtrip == 66) && (request()->statuslangsir == 80) && (request()->statusgudangsama == 205)) {
                    if (request()->dari_id != $idkandang && request()->nobukti_tripasal == '') {
                        $rulesJobTrucking = [
                            'jobtrucking' => ['required_unless:dari_id,1']
                        ];
                    }
                }
            }
            $rulesId = [
                'id' => new DestroyListTrip()
            ];
            $rules = array_merge(
                $rules,
                $rulesId,
                $ritasiRule,
                $rulesAgen_id,
                $rulesContainer_id,
                $rulesDari_id,
                $rulesSampai_id,
                $rulesPelanggan_id,
                $rulesGandengan_id,
                $rulesJenisOrder_id,
                $rulesStatusContainer_id,
                $rulesTrado_id,
                $rulesTarif_id,
                $rulesUpah_id,
                $ruleCekUpahRitasi,
                $rulesJobTrucking
            );
            if (request()->statuslongtrip == 66 && request()->nobukti_tripasal == '') {
                $rules = array_merge(
                    $rules,
                    $rulesTarif_id,
                );
            }
            if (request()->statuslongtrip == 66 && request()->nobukti_tripasal != '') {
                unset($rules['tarifrincian_id']);
            }
        }

        return $rules;
    }
    public function attributes()
    {
        return [
            "agen_id" => "customer",
            "agen" => "customer",
            "container_id" => "container",
            "container" => "container",
            "dari_id" => "dari",
            "dari" => "dari",
            "gandengan_id" => "gandengan",
            "gandengan" => "gandengan",
            "lokasibongkarmuat" => "lokasi bongkar/muat",
            "jenisorder_id" => "jenis order",
            "jenisorder" => "jenis order",
            "pelanggan_id" => "pelanggan",
            "pelanggan" => "pelanggan",
            "sampai_id" => "sampai",
            "sampai" => "sampai",
            "statuscontainer_id" => "status container",
            "statuscontainer" => "status container",
            "statuslangsir" => "status langsir",
            "trado_id" => "trado",
            "trado" => "trado",
            "nobukti_tripasal" => 'trip asal',
            'jenisritasi.*' => "jenis ritasi",
            'ritasidari.*' => 'ritasi dari',
            'ritasike.*' => 'ritasi ke',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tarifrincian.required_if' => 'TARIF ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nobukti_tripasal.required_if' => 'TRIP ASAL ' . app(ErrorController::class)->geterror('WI')->keterangan,

        ];
    }
}
