<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AbsensiSupirDetail;
use App\Models\Parameter;
use App\Models\TarifRincian;
use App\Models\UpahSupirRincian;
use App\Rules\cekUpahRitasiDariInputTrip;
use App\Rules\cekUpahRitasiInputTrip;
use App\Rules\cekUpahRitasiKeInputTrip;
use App\Rules\cekUpahSupirInputTrip;
use App\Rules\DateApprovalQuota;
use App\Rules\ExistAbsensiSupirDetail;
use App\Rules\ExistAgen;
use App\Rules\ExistContainer;
use App\Rules\ExistDataRitasi;
use App\Rules\ExistGandengan;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistKota;
use App\Rules\ExistKotaDariSuratPengantar;
use App\Rules\ExistKotaSampaiSuratPengantar;
use App\Rules\ExistNominalUpahSupir;
use App\Rules\ExistPelanggan;
use App\Rules\ExistStatusContainer;
use App\Rules\ExistSupir;
use App\Rules\ExistTarifRincianSuratPengantar;
use App\Rules\ExistTrado;
use App\Rules\VAlidasiReminderOli;
use App\Rules\VAlidasiReminderOliPersneling;
use App\Rules\VAlidasiReminderOliGardan;
use App\Rules\VAlidasiReminderSaringanHawa;
use App\Rules\ExistUpahSupirRincianSuratPengantar;
use App\Rules\JenisRitasiInputTrip;
use App\Rules\ValidasiExistOmsetTarif;
use App\Rules\ValidasiKotaUpahZona;
use App\Rules\ValidasiKotaZonaTrip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use App\Models\ReminderOli;

class StoreMandorTripRequest extends FormRequest
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

        $parameter = new Parameter();
        $dataUpahZona = $parameter->getcombodata('STATUS UPAH ZONA', 'STATUS UPAH ZONA');
        $dataUpahZona = json_decode($dataUpahZona, true);
        foreach ($dataUpahZona as $item) {
            $statusUpahZona[] = $item['id'];
        }

        $getGudangSama = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GUDANG SAMA')->where('text', 'GUDANG SAMA')->first();
        $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
        $getUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
        $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'INPUTTRIP')->first();

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
                    'ritasidari.*' => new cekUpahRitasiDariInputTrip(),
                    'ritasike.*' => new cekUpahRitasiKeInputTrip()
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

            $dari_id = $this->dari_id;
            if ($dari_id != null) {
                $rulesDari_id = [
                    'dari_id' => ['required', 'numeric', 'min:1', new ExistKota(), (request()->statusupahzona == $getUpahZona->id) ? new ValidasiKotaZonaTrip($getUpah->zonadari_id) : Rule::in($validasiUpah->kotadari_id)]
                ];
            } else if ($dari_id == null && $this->dari != '') {
                $rulesDari_id = [
                    'dari_id' => ['required', 'numeric', 'min:1', new ExistKota(), (request()->statusupahzona == $getUpahZona->id) ? new ValidasiKotaZonaTrip($getUpah->zonadari_id) : Rule::in($validasiUpah->kotadari_id)]
                ];
            }

            $sampai_id = $this->sampai_id;
            if ($sampai_id != null) {
                $rulesSampai_id = [
                    'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota(), (request()->statusupahzona == $getUpahZona->id) ? new ValidasiKotaZonaTrip($getUpah->zonasampai_id) : Rule::in($validasiUpah->kotasampai_id)]
                ];
            } else if ($sampai_id == null && $this->sampai != '') {
                $rulesSampai_id = [
                    'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota(), (request()->statusupahzona == $getUpahZona->id) ? new ValidasiKotaZonaTrip($getUpah->zonasampai_id) : Rule::in($validasiUpah->kotasampai_id)]
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

        for ($i = 0; $i < count(request()->namastatus); $i++) {
            $namaStatus = 'Penggantian ' . request()->namastatus[$i];
            $jarak = floatval(request()->jarak[$i]);
            DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak) where status='$namaStatus'"));
        }
        // dd(DB::table($tempreminderoli)->get());
        // olimesin
        // $statusbatas = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
        //     ->select(
        //         'a.id'
        //     )
        //     ->where('a.grp', 'STATUS PERGANTIAN')
        //     ->where('a.subgrp', 'STATUS PERGANTIAN')
        //     ->where('a.text', 'SUDAH MELEWATI BATAS')
        //     ->first()->id ?? 0;

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

        // 
        $rulesTrado_id = [];
        if ($this->trado != '') {
            $rulesTrado_id = [
                'trado_id' => [
                    'required', 'numeric', 'min:1', new ExistTrado(), new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
                ],
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()],
                'absensidetail_id' => ['required', 'numeric', 'min:1', new ExistAbsensiSupirDetail()],
            ];
        }

        $rulesTarif_id = [];
        $rulesGandengan_id = [];
        if ((request()->dari_id == 1 && request()->sampai_id == 103) || (request()->dari_id == 103 && request()->sampai_id == 1) || (request()->statuslongtrip == 65)) {


            $getgerobak = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GEROBAK')->where('subgrp', 'STATUS GEROBAK')->where('text', 'GEROBAK')->first();
            $gettrado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('id', request()->trado_id)->first();
            $gerobakVal = ($gettrado == null) ? 0 : $gettrado->statusgerobak;
            if ($getgerobak->id == $gerobakVal) {
                $rules = [
                    'tglbukti' => [
                        'required', 'date_format:d-m-Y',
                        new DateApprovalQuota()
                    ],
                    "nobukti_tripasal" => 'required_if:statusgudangsama,=,' . $getGudangSama->id,
                    "agen" => "required",
                    "container" => "required",
                    "dari" => ["required"],
                    "gudang" => "required",
                    "jenisorder" => "required",
                    "pelanggan" => "required",
                    "sampai" => ["required"],
                    "statuscontainer" => "required",
                    "statusgudangsama" => "required",
                    "statuslongtrip" => "required",
                    "statuslangsir" => "required",
                    // "lokasibongkarmuat" => "required",
                    "trado" => "required",
                    "upah" => ["required", new ExistNominalUpahSupir()],
                    'statusupahzona' => ['required', Rule::in($statusUpahZona)],
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
                        'required', 'date_format:d-m-Y',
                        new DateApprovalQuota()
                    ],
                    "nobukti_tripasal" => 'required_if:statusgudangsama,=,' . $getGudangSama->id,
                    "agen" => "required",
                    "container" => "required",
                    "dari" => ["required"],
                    "gandengan" => "required",
                    "gudang" => "required",
                    "jenisorder" => "required",
                    "pelanggan" => "required",
                    "sampai" => ["required"],
                    "statuscontainer" => "required",
                    "statusgudangsama" => "required",
                    "statuslongtrip" => "required",
                    "statuslangsir" => "required",
                    // "lokasibongkarmuat" => "required",
                    "trado" => "required",
                    "upah" => ["required", new ExistNominalUpahSupir()],
                    'statusupahzona' => ['required', Rule::in($statusUpahZona)],
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
                        'required', 'date_format:d-m-Y',
                        new DateApprovalQuota()
                    ],
                    "nobukti_tripasal" => 'required_if:statusgudangsama,=,' . $getGudangSama->id,
                    "agen" => "required",
                    "tarifrincian" => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiExistOmsetTarif(), new ValidasiKotaUpahZona($getBukanUpahZona->id)],
                    "container" => "required",
                    "dari" => ["required"],
                    "gudang" => "required",
                    "jenisorder" => "required",
                    "pelanggan" => "required",
                    "sampai" => ["required"],
                    "statuscontainer" => "required",
                    "statusgudangsama" => "required",
                    "statuslongtrip" => "required",
                    "statuslangsir" => "required",
                    // "lokasibongkarmuat" => "required",
                    "trado" => "required",
                    "upah" => ["required", new ExistNominalUpahSupir()],
                    'statusupahzona' => ['required', Rule::in($statusUpahZona)],
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
                        'required', 'date_format:d-m-Y',
                        new DateApprovalQuota()
                    ],
                    "nobukti_tripasal" => 'required_if:statusgudangsama,=,' . $getGudangSama->id,
                    "agen" => "required",
                    "tarifrincian" => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiExistOmsetTarif(), new ValidasiKotaUpahZona($getBukanUpahZona->id)],
                    "container" => "required",
                    "dari" => ["required"],
                    "gandengan" => ["required", 'nullable'],
                    "gudang" => "required",
                    "jenisorder" => "required",
                    "pelanggan" => "required",
                    "sampai" => ["required"],
                    "statuscontainer" => "required",
                    "statusgudangsama" => "required",
                    "statuslongtrip" => "required",
                    "statuslangsir" => "required",
                    // "lokasibongkarmuat" => "required",
                    "trado" => "required",
                    "upah" => ["required", new ExistNominalUpahSupir()],
                    'statusupahzona' => ['required', Rule::in($statusUpahZona)],
                ];
            }
        }


        $getListTampilan = json_decode($getListTampilan->memo);
        if ($getListTampilan->INPUT != '') {
            $getListTampilan = (explode(",", $getListTampilan->INPUT));
            foreach ($getListTampilan as $value) {
                if (array_key_exists(trim(strtolower($value)), $rules) == true) {
                    unset($rules[trim(strtolower($value))]);
                }
            }
        }

        $rulesJobTrucking = [];
        if ((request()->statuslongtrip == 66) && (request()->statuslangsir == 80)) {
            // dd('disini');
            $rulesJobTrucking = [
                'jobtrucking' => ['required_unless:dari_id,1']
            ];
        }
        $rules = array_merge(
            $rules,
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
