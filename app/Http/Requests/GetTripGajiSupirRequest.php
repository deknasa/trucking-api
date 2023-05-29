<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;

class GetTripGajiSupirRequest extends FormRequest
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
            if ($supir_id == 0) {
                $rulesSupir_id = [
                    'supir_id' => ['required', 'numeric', 'min:1']
                ];
            }
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1']
            ];
        }
        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        $rules =  [
            'supir' => 'required',
            'tgldari' => ['required', 'date_format:d-m-Y', 'before:' . $tglbatasakhir,'after_or_equal:'.$tglbatasawal],
            'tglsampai' => ['required', 'date_format:d-m-Y', 'before:' . $tglbatasakhir, 'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))],
        ];

        $rules = array_merge(
            $rules,
            $rulesSupir_id
        );

        return $rules;
    }
}
