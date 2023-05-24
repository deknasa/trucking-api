<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\MinNull;
use App\Rules\NotDecimal;
use App\Rules\NumberMax;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStokRequest extends FormRequest
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
            "namastok"=>'required',
            "kelompok"=>'required',
            "subkelompok"=>'required',
            "kategori"=>'required',
            "statusaktif"=>'required',
            "namaterpusat"=>'required',
            "qtymin"=> [new NotDecimal(), new MinNull()],
            "qtymax"=> [new NotDecimal(), new NumberMax()],
            'gambar' => 'array',
            'gambar.*' => 'image'
        ];
    }

    public function messages()
    {
        return [
            'gambar.*.image' => app(ErrorController::class)->geterror('WG')->keterangan
        ];
    }
}
