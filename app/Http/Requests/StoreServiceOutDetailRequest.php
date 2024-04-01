<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceOutDetailRequest extends FormRequest
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
            'servicein_nobukti' => 'required|array|distinct',
            'servicein_nobukti.*' => 'required|distinct',
            'keterangan_detail' => 'required|array|distinct',
            'keterangan_detail.*' => 'required|distinct'
        ];
    }

    public function attributes() {
        return [
            'servicein_nobukti.*' => 'servicein nobukti',
            'keterangan_detail.*' => 'Keterangan',
        ];
    }
}
