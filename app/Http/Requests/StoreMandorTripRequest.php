<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AbsensiSupirDetail;
use App\Models\Parameter;
use App\Models\TarifRincian;
use App\Rules\cekUpahRitasiDariInputTrip;
use App\Rules\cekUpahRitasiInputTrip;
use App\Rules\cekUpahRitasiKeInputTrip;
use App\Rules\cekUpahSupirInputTrip;
use App\Rules\DateApprovalQuota;
use App\Rules\ExistAbsensiSupirDetail;
use App\Rules\ExistAgen;
use App\Rules\ExistContainer;
use App\Rules\ExistGandengan;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistKota;
use App\Rules\ExistKotaDariSuratPengantar;
use App\Rules\ExistKotaSampaiSuratPengantar;
use App\Rules\ExistPelanggan;
use App\Rules\ExistStatusContainer;
use App\Rules\ExistSupir;
use App\Rules\ExistTarifRincianSuratPengantar;
use App\Rules\ExistTrado;
use App\Rules\ExistUpahSupirRincianSuratPengantar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // START VALIDASI RITASI
        $ritasiRule = [];
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
                'jenisritasi.*' => ['required', 'numeric', Rule::in($status)],
                'ritasidari.*' => ['required'],
                'ritasike.*' => ['required']
            ];
        }
        $ruleCekUpahRitasi = [];
        if ($jenisRitasi && $ritasiDari && $ritasiKe && request()->container_id != 0) {
            $ruleCekUpahRitasi = [
                'ritasidari.*' => new cekUpahRitasiDariInputTrip(),
                'ritasike.*' => new cekUpahRitasiKeInputTrip()
            ];
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

        $tarifrincian_id = $this->tarifrincian_id;
        $rulesTarif_id = [];
        if ($tarifrincian_id != null) {
            $rulesTarif_id = [
                'tarifrincian_id' => ['required', 'numeric', 'min:1', new ExistTarifRincianSuratPengantar()]
            ];
        } else if ($tarifrincian_id == null && request()->upah != '') {
            $rulesTarif_id = [
                'tarifrincian_id' => ['required', 'numeric', 'min:1', new ExistTarifRincianSuratPengantar()]
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

        $dari_id = $this->dari_id;
        $rulesDari_id = [];
        if ($dari_id != null) {
            $rulesDari_id = [
                'dari_id' => ['required', 'numeric', 'min:1', new ExistKotaDariSuratPengantar(), new ExistKota()]
            ];
        } else if ($dari_id == null && $this->dari != '') {
            $rulesDari_id = [
                'dari_id' => ['required', 'numeric', 'min:1', new ExistKotaDariSuratPengantar(), new ExistKota()]
            ];
        }

        $sampai_id = $this->sampai_id;
        $rulesSampai_id = [];
        if ($sampai_id != null) {
            $rulesSampai_id = [
                'sampai_id' => ['required', 'numeric', 'min:1', new ExistKotaDariSuratPengantar(), new ExistKota()]
            ];
        } else if ($sampai_id == null && $this->sampai != '') {
            $rulesSampai_id = [
                'sampai_id' => ['required', 'numeric', 'min:1', new ExistKotaDariSuratPengantar(), new ExistKota()]
            ];
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

        $rulesTrado_id = [];
        if ($this->trado != '') {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', new ExistTrado()],
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()],
                'absensidetail_id' => ['required', 'numeric', 'min:1', new ExistAbsensiSupirDetail()],
            ];
        }

        $rulesUpahSupir = [];
        if(request()->dari_id != '' && request()->sampai_id != '' && request()->container_id != '' && request()->statuscontainer_id != ''){
            $rulesUpahSupir = [
                'dari' => new cekUpahSupirInputTrip()
            ];
        }
        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateApprovalQuota()
            ],

            "agen" => "required",
            "tarifrincian" => "required",
            "container" => "required",
            "dari" => "required",
            "gandengan" => "required",
            "gudang" => "required",
            "jenisorder" => "required",
            "pelanggan" => "required",
            "sampai" => "required",
            "statuscontainer" => "required",
            "statusgudangsama" => "required",
            "statuslongtrip" => "required",
            "trado" => "required",
        ];

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
            $rulesUpahSupir,
            $rulesTarif_id,
            $rulesUpah_id,
            $ruleCekUpahRitasi
        );

        return $rules;
    }
    public function attributes()
    {
        return [
            "agen_id" => "agen",
            "agen" => "agen",
            "container_id" => "container",
            "container" => "container",
            "dari_id" => "dari",
            "dari" => "dari",
            "gandengan_id" => "gandengan",
            "gandengan" => "gandengan",
            "jenisorder_id" => "jenisorder",
            "jenisorder" => "jenisorder",
            "pelanggan_id" => "pelanggan",
            "pelanggan" => "pelanggan",
            "sampai_id" => "sampai",
            "sampai" => "sampai",
            "statuscontainer_id" => "statuscontainer",
            "statuscontainer" => "statuscontainer",
            "trado_id" => "trado",
            "trado" => "trado",
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,

        ];
    }
}
