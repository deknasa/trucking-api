<?php

namespace App\Http\Requests;

use App\Rules\AbsensiRicUsed;
use Illuminate\Support\Facades\DB;
use App\Rules\GetAbsensiMandorRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class GetMandorAbsensiSupirRequest extends FormRequest
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
            "tglbukaabsensi" => [new GetAbsensiMandorRule()]
        ];
    }

}
