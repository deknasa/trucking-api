<?php

namespace App\Http\Requests;

use App\Rules\HistoryTradoMilikMandorValidation;
use Illuminate\Foundation\Http\FormRequest;

class HistoryTradoMilikMandorRequest extends FormRequest
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
        return [];
        // return [
        //     'mandorbaru' => ['required', new HistoryTradoMilikMandorValidation]
        // ];
    }
    public function attributes()
    {
        return [
            'mandorbaru' => 'mandor baru'
        ];
    }
}
