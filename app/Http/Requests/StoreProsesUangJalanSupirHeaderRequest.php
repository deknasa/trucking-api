<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAbsensiSupirHeader;
use App\Rules\ExistSupir;
use App\Rules\ExistTrado;
use App\Rules\ValidasiPengembalianPinjamanProsesUangjalan;

class StoreProsesUangJalanSupirHeaderRequest extends FormRequest
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

        $rules = [
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
            ],
            'absensisupir' => ['required', new ExistAbsensiSupirHeader()],
            'supir' => ['required', new ValidasiPengembalianPinjamanProsesUangjalan()],
            'trado'=> 'required',
        ];
        $relatedRequests = [
            StoreProsesUangJalanSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesSupir_id,
                $rulesTrado_id
            );
        }

        return $rules;
    }
    public function attributes()
    {
        $attributes = [
            'tgltransfer.*' => 'tgl transfer',
            'keterangantransfer.*' => 'keterangan transfer',
            'nilaitransfer.*' => 'nilai transfer',
            'banktransfer.*' => 'bank transfer',
            'tgladjust' => 'tgl adjust transfer',
            'tgldeposit' => 'tgl deposito transfer',
            'keteranganadjust' => 'keterangan adjust transfer',
            'nilaiadjust' => 'nilai adjust transfer',
            'bankadjust' => 'bank adjust transfer',
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [
            'nilaitransfer.*.gt' => 'nilai transfer Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'nilaiadjust.gt' => 'nilai adjust transfer Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
