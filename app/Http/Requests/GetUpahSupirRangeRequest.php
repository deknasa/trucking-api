<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class GetUpahSupirRangeRequest extends FormRequest
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
        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        $rules =  [
            'dari' => [
                'required',
                'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.$tglbatasawal,
            ],
            'sampai' => [
                'required',
                'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
            ],
            
        ];

        return $rules;
    }

    public function attributes()
    {
        return [
            'dari' => 'tanggal dari',
            'sampai' => 'tanggal sampai',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'sampai.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. $this->tgldari,
        ];
    }    

}
