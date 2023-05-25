<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;

class StoreUpahSupirRequest extends FormRequest
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
            'kotadari' => ['required','unique:upahsupir'],
            'kotasampai' => ['required','unique:upahsupir'],
            // 'zona' => 'required',
            'jarak' => ['required','numeric','min:0','max:'. (new ParameterController)->getparamid('BATAS KM UPAH SUPIR','BATAS KM UPAH SUPIR')->text],
            'statusaktif' => 'required',
            'statusluarkota' => 'required',
            'tglmulaiberlaku' => ['required','date_format:d-m-Y'],
            // 'tglakhirberlaku' => 'required',
        ];
        $relatedRequests = [
            StoreUpahSupirRincianRequest::class
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
            'nominalsupir.*' => 'nominal supir'
        ];
    }


}
