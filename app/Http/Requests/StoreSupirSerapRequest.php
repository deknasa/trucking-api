<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\ExistSupir;
use App\Rules\ExistSupirSerap;
use App\Rules\ExistTrado;
use App\Rules\ValidasiSupirSerap;
use App\Rules\ValidasiTglSupirSerap;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupirSerapRequest extends FormRequest
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
        $rules = [
            'tglabsensi' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                new ValidasiTglSupirSerap()
            ],
            'trado' => 'required',
            'supir' => '',
            'supirserap' => ['required', new ValidasiSupirSerap()],
        ];

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
                'supir_id' => [ 'numeric', 'min:1', new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => [ 'numeric', 'min:1', new ExistSupir()]
            ];
        }

        $supirserap_id = $this->supirserap_id;
        $rulesSerap_id = [];
        if ($supirserap_id != null) {
            $rulesSerap_id = [
                'supirserap_id' => ['required', 'numeric', 'min:1', new ExistSupirSerap()]
            ];
        } else if ($supirserap_id == null && $this->supirserap != '') {
            $rulesSerap_id = [
                'supirserap_id' => ['required', 'numeric', 'min:1', new ExistSupirSerap()]
            ];
        }

        $rules = array_merge(
            $rules,
            $rulesTrado_id,
            $rulesSupir_id,
            $rulesSerap_id
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            'supirserap' => 'supir serap'
        ];
    }
}
