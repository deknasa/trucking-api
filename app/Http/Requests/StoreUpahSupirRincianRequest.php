<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Rules\validasiKomisiSupirUpahSupir;

class StoreUpahSupirRincianRequest extends FormRequest
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
            'container' => 'required|array',
            'container.*' => 'required',
            'container_id.*' => 'required',
            'statuscontainer' => 'required|array',
            'statuscontainer.*' => 'required',
            'statuscontainer_id.*' => 'required',
            'nominalsupir.*' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS NILAI UPAH','BATAS NILAI UPAH')->text],
            'nominalkenek.*' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS NILAI UPAH KERNEK','BATAS NILAI UPAH KERNEK')->text],
            'nominalkomisi.*' => ['required','numeric', new validasiKomisiSupirUpahSupir(),'max:'. (new ParameterController)->getparamid('BATAS NILAI UPAH KOMISI','BATAS NILAI UPAH KOMISI')->text],
            'nominaltol.*' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS NILAI TOL','BATAS NILAI TOL')->text],
            'liter.*' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS NILAI LITER','BATAS NILAI LITER')->text],
        ];
    }

    public function attributes()
    {
        return [
            'statuscontainer.*' => 'status container',
            'nominalsupir.*' => 'nominal supir',
            'nominalkenek.*' => 'nominal kenek',
            'nominalkomisi.*' => 'nominal komisi',
            'nominaltol.*' => 'nominal tol',
            'liter.*' => 'liter',
        ];
    }
}
