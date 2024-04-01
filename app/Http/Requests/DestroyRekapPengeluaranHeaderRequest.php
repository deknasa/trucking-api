<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\RekapPengeluaranheader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyRekapPengeluaranHeader ;
use App\Http\Controllers\Api\RekapPengeluaranHeaderController;

class DestroyRekapPengeluaranHeaderRequest extends FormRequest
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
            'id' => [ new ValidasiDestroyRekapPengeluaranHeader()],
        ];
    }
}
