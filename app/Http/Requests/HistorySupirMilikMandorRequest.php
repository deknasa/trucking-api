<?php

namespace App\Http\Requests;

use App\Rules\HistorySupirMilikMandorValidation;
use Illuminate\Foundation\Http\FormRequest;

class HistorySupirMilikMandorRequest extends FormRequest
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
        return [
            // 'mandorbaru' => ['required', new HistorySupirMilikMandorValidation]
        ];
    }

    public function attributes()
    {
        return [
            'mandorbaru' => 'mandor baru'
        ];
    }
}
