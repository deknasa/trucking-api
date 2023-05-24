<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\OrderanTruckingValidasijob2040 ;
use App\Rules\OrderanTruckingValidasijob2x20 ;
use App\Rules\OrderanTruckingValidasinocont2x20 ;
use App\Rules\OrderanTruckingValidasinoseal2x20 ;
use Illuminate\Validation\Rule;

class StoreOrderanTruckingRequest extends FormRequest
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
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'container' => 'required',
            'agen' => 'required',
            'jenisorder' => 'required',
            'pelanggan' => 'required',
            'tarifrincian' => 'required',
            'statuslangsir' => 'required',
            'statusperalihan' => 'required',
            'nojobemkl' => [new OrderanTruckingValidasijob2040()],
            'nojobemkl2' => [new OrderanTruckingValidasijob2x20()],
            'nocont' => 'required',
            'noseal' => 'required',
            'nocont2' => [new OrderanTruckingValidasinocont2x20()],
            'noseal2' => [new OrderanTruckingValidasinoseal2x20()],
        ];
    }

    
    public function attributes()
    {
        return [
            'nojobemkl' => 'no job emkl',
            'nojobemkl' => 'noj job emkl ke-2',
            'nocont' => 'no container',
            'noseal' => 'no seal',
            'nocont2' => 'no container ke-2',
            'noseal2' => 'no seal ke-2',
        ];
    }
    
    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'nocont.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'noseal.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,

        ];
    }
}
