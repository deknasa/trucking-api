<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalSupirResignRequest extends FormRequest
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

        $requiredKeterangan = Rule::requiredIf(function ()  {
            if ($this->input('action') == "approve") {
                return true;
            }
            return false;
        });

        return [
            "keteranganberhentisupir"=>$requiredKeterangan
        ];
    }

    public function attributes()
    {
        $attributes = [
            'keteranganberhentisupir' => 'keterangan',
        ];

        return $attributes;
    }
}
