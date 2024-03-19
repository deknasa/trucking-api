<?php

namespace App\Http\Requests;

use App\Rules\HistoryTradoMilikSupirValidation;
use App\Rules\UniqueHistoryTradoMilikSupirValidation;
use Illuminate\Foundation\Http\FormRequest;

class HistoryTradoMilikSupirRequest extends FormRequest
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
            'supirbaru' => [new HistoryTradoMilikSupirValidation(), new UniqueHistoryTradoMilikSupirValidation()]
        ];
    }
    public function attributes()
    {
        return [
            'supirbaru' => 'supir baru'
        ];
    }
}
