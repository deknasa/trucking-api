<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\CekUpahRitasi;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistDataRitasi;
use App\Rules\ExistKota;
use App\Rules\ExistSupir;
use App\Rules\ExistSuratPengantarRitasi;
use App\Rules\ExistTrado;

class StoreRitasiRequest extends FormRequest
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
        $statusritasi_id = $this->statusritasi_id;
        $rulesStatusRitasi_id = [];
        if ($statusritasi_id != null) {
            $rulesStatusRitasi_id = [
                'statusritasi_id' => ['required', 'numeric', 'min:1', new ExistDataRitasi()]
            ];
        } else if ($statusritasi_id == null && $this->statusritasi != '') {
            $rulesStatusRitasi_id = [
                'statusritasi_id' => ['required', 'numeric', 'min:1', new ExistDataRitasi()]
            ];
        }
        $dari_id = $this->dari_id;
        $rulesDari_id = [];
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
        $rulesSampai_id = [];
        if ($sampai_id != null) {
            $rulesSampai_id = [
                'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota()]
            ];
        } else if ($sampai_id == null && $this->sampai != '') {
            $rulesSampai_id = [
                'sampai_id' => ['required', 'numeric', 'min:1', new ExistKota()]
            ];
        }
        $trado_id = $this->trado_id;
        $rulesTrado_id = [];
        if ($trado_id != null) {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', new ExistTrado()]
            ];
        } else if ($trado_id == null && $this->trado != '') {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', new ExistTrado()]
            ];
        }
        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        }

        $rules = [
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                'date_equals:'.date('d-m-Y'),
                new DateTutupBuku()
            ],
            'statusritasi' => 'required',
            'suratpengantar_nobukti' => [new ExistSuratPengantarRitasi()],
            'dari' => ['required', new CekUpahRitasi()],
            'sampai' => 'required',
            'trado' => 'required',
            'supir' => 'required',
        ];

        $rules = array_merge(
            $rules,
            $rulesStatusRitasi_id,
            $rulesDari_id,
            $rulesSampai_id,
            $rulesTrado_id,
            $rulesSupir_id
        );

        return $rules;
    }
    public function attributes()
    {
        return [
            'tglbukti' => 'tanggal bukti',
            'statusritasi' => 'status ritasi',
            'suratpengantar_nobukti' => 'No bukti surat pengantar',
        ];
    }
    
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
