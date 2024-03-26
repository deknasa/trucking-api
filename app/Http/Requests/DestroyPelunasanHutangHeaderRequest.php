<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\PelunasanHutangHeaderController;
use App\Models\PelunasanHutangHeader;
use App\Rules\ValidasiDestroyHutangBayarHeader;
use Illuminate\Foundation\Http\FormRequest;

class DestroyPelunasanHutangHeaderRequest extends FormRequest
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
            'id' => [ new ValidasiDestroyHutangBayarHeader()],
        ];
    }
}
