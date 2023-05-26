<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Rules\NullToNumeric;

class StoreUpahRitasiRequest extends FormRequest
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
        // dd(request()->kotadari_id);

        $kotadari_id = $this->kotadari_id ?? 0;
        $kotasampai_id = $this->kotasampai_id ?? 0;

        if ($kotadari_id == 0 or $this->kotadari<>'') {
            $ruleskotadari_id =  [
                'kotadari' => ['required',],
            ];
        }
        else  if ($kotadari_id == 0) {
            $ruleskotadari_id =  [
                'kotadari' => ['required',],
            ];
        } else if ($kotadari_id <> 0){
            $ruleskotadari_id =  [
               'kotadari_id' => ['numeric','min:1','unique:upahritasi'],
            ];
        }
        if ($kotasampai_id == 0) {
            $ruleskotasampai_id =  [
                'kotasampai' => ['required',],
            ];
        } else if ($kotasampai_id <> 0){
            $ruleskotasampai_id =  [
               'kotasampai_id' => ['numeric','min:1','unique:upahritasi'],
            ];
        }
        $rules =  [
           
            'jarak' => ['required', 'numeric', 'min:0', 'max:' . (new ParameterController)->getparamid('BATAS NILAI JARAK', 'BATAS NILAI JARAK')->text],
            'statusaktif' => 'required',
            'tglmulaiberlaku' => ['required', 'date_format:d-m-Y'],
        ];

        $relatedRequests = [
            StoreUpahRitasiRincianRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $ruleskotadari_id,
                $ruleskotasampai_id
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
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'container.*' => 'container',
            'nominalsupir.*' => 'nominal supir',
        ];
    }
}
