<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;


class UpdateErrorRequest extends FormRequest
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
    $errorId = $this->error->id;

    return [
        'kodeerror' => 'required|unique:error,kodeerror,' . $errorId,
        'keterangan' => 'unique:error,keterangan,' . $errorId,
    ];
}


    public function attributes()
    {
        return [
            'kodeerror' => 'kode error',
            'keterangan' => 'keterangan',
        ];
    }
}
