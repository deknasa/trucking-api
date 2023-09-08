<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ParameterController;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;

class StoreHutangExtraDetailRequest extends FormRequest
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
        $rules=[
            'tgljatuhtempo.*' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),

            ],
            'total_detail.*' => [
                'required','numeric','gt:0','max:'. (new ParameterController)->getparamid('MAXIMAL HUTANG','MAXIMAL HUTANG')->text
            ],
            'keterangan_detail.*' => 'required',

        ];
        return $rules;
    }

    public function attributes() {
        return [
            'tgljatuhtempo.*' => 'tgl jatuh tempo',
            'total_detail.*' => 'total',
            'keterangan_detail.*' => 'keterangan',
            
        ];
    }
}
