<?php

namespace App\Http\Requests;

use App\Rules\ExistTrado;
use App\Rules\ValidasiTradoPengajuanTripInap;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengajuanTripInapRequest extends FormRequest
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
            // "absensi_id" => ["required"],
            "tglabsensi" => ["required"],
            "trado" => ["required", new ValidasiTradoPengajuanTripInap()],
        ];
        $rules = array_merge(
            $rules,
            $rulesTrado_id
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            "absensi_id" => "tgl absensi",
            "tglabsensi" => "tgl absensi",
            "trado_id" => "trado_id",
        ];
    }
}
