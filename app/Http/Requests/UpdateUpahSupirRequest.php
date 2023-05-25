<?php

namespace App\Http\Requests;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUpahSupirRequest extends FormRequest
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
        $rules =  [
            'kotadari' => 'required',
            'kotasampai' => 'required',
            // 'zona' => 'required',
            'jarak' => ['required','numeric','gt:0','min:0','max:'. (new ParameterController)->getparamid('BATAS KM UPAH SUPIR','BATAS KM UPAH SUPIR')->text],
            'statusaktif' => 'required',
            'statusluarkota' => 'required',
            'tglmulaiberlaku' => 'required',
            // 'tglakhirberlaku' => 'required',
        ];
        $relatedRequests = [
            UpdateUpahSupirRincianRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }
        
        return $rules;
    }
    
    public function attributes()
    {
        return [
            'kotadari' => 'kota dari',
            'kotasampai' => 'kota sampai',
            'statusaktif' => 'status aktif',
            'statusluarkota' => 'status luar kota',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'tglakhirberlaku' => 'tanggal akhir berlaku',
            'container.*' => 'container',
            'statuscontainer.*' => 'container',
            'nominalsupir.*' => 'nominal supir',
        ];
    }

    public function messages()
    {
        return [
            'jarak.max' => ':attribute ' . 'maximal jarak '. (new ParameterController)->getparamid('BATAS KM UPAH SUPIR','BATAS KM UPAH SUPIR')->text,
            'jarak.min' => ':attribute ' . (new ErrorController)->geterror('TBMINUS')->keterangan,
            'nominalsupir.*.gt' => ':attribute ' . (new ErrorController)->geterror('WI')->keterangan,
        ];
    }
}
