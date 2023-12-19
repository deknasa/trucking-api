<?php

namespace App\Http\Requests;

use App\Rules\ExistAgen;
use App\Rules\ExistContainer;
use App\Rules\ValidasiAgenOtobon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOtobonRequest extends FormRequest
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
        } else if ($container_id == null && $this->agen != '') {
            $rulesContainer_id = [
                'container_id' => ['required', 'numeric', 'min:1', new ExistContainer()]
            ];
        }

        $rules = [
            'agen' => ['required', new ValidasiAgenOtobon()],
            'container' => 'required',
            'nominal' => ['required','numeric','gt:0']
        ];

        $rules = array_merge(
            $rules,
            $rulesContainer_id,
            $rulesAgen_id
        );

        return $rules;
    }
    public function attributes()
    {
        return [
            'agen' => 'customer',
            'agen_id' => 'customer id'
        ];
    }
}
