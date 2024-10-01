<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceEmklDetailRequest extends FormRequest
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
        $dataPenjualan = json_decode(request()->detail, true);

        $i=0;
        // $dataNominal = $dataPenjualan['nominal'];

        foreach ($dataPenjualan as $row => $data) {

            $mainValidator = validator($dataPenjualan, [
                "nominal.*" => ['required','gt:0'],
                
            ]);
            $mainValidator->validate();
            $validatedDetailData = $mainValidator->validated();
            $i++;
        }
        

        return $validatedDetailData;
    }
}
