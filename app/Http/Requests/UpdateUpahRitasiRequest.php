<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;

class UpdateUpahRitasiRequest extends FormRequest
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

        $kotadari_id = $this->kotadari_id ?? 0;
        $kotasampai_id = $this->kotasampai_id ?? 0;

        if ($kotadari_id == 0 and $this->kotadari <> '') {
            $ruleskotadari_id =  [
                'kotadari_id' => ['required'],
            ];
        } else  if ($kotadari_id == 0) {
            $ruleskotadari_id =  [
                'kotadari' => ['required',],
            ];
        } else if ($kotadari_id <> 0) {
            $ruleskotadari_id =  [
                'kotadari_id' => [
                    'numeric', 'min:1', Rule::unique('upahritasi')
                        ->whereNotIn('id', [$this->id])
                        ->where('kotadari_id', [$this->kotadari_id])
                        ->where('kotasampai_id', [$this->kotasampai_id])
                ],
            ];
        }


        if ($kotasampai_id == 0 and $this->kotasampai_id <> '') {
            $ruleskotasampai_id =  [
                'kotasampai_id' => ['required'],
            ];
        } else  if ($kotasampai_id == 0) {
            $ruleskotasampai_id =  [
                'kotasampai' => ['required',],
            ];
        } else if ($kotasampai_id <> 0) {
            $ruleskotasampai_id =  [
                'kotaampai_id' => [
                    'numeric', 'min:1', Rule::unique('upahritasi')
                        ->whereNotIn('id', [$this->id])
                        ->where('kotadari_id', [$this->kotadari_id])
                        ->where('kotasampai_id', [$this->kotasampai_id])
                ],
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





        return $rules;
    }


    public function attributes()
    {
        return [
            'kotadari' => 'dari',
            'kotasampai' => 'tujuan',
            'kotadari_id' => 'dari',
            'kotasampai_id' => 'tujuan',
            'statusaktif' => 'status aktif',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'container.*' => 'container',
            'nominalsupir.*' => 'nominal supir',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kotadari_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kotasampai_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kotadari_id.unique' => 'DARI DAN TUJUAN ' . $controller->geterror('SPI')->keterangan,
            'kotasampai_id.unique' => 'DARI DAN TUJUAN ' . $controller->geterror('SPI')->keterangan,
        ];
    }
}
