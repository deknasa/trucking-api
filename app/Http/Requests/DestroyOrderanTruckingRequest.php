<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OrderanTrucking;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyOrderanTrucking ;
use App\Http\Controllers\Api\OrderanTruckingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DestroyOrderanTruckingRequest extends FormRequest
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
            'id' => [ new ValidasiDestroyOrderanTrucking()],
        ];
    }
}
