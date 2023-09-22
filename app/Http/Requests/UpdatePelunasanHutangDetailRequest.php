<?php

namespace App\Http\Requests;

use App\Rules\ExistHutangNoBukti;
use App\Rules\HutangBayarLimit;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePelunasanHutangDetailRequest extends FormRequest
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

        // $rules=[
        //     'hutang_nobukti.*' => [
        //         new ExistHutangNoBukti(),
        //         'required',
        //     ],

        //  ];
        //  return $rules;

        // return [
        $rules = [
            'hutang_nobukti.*' => [
                new ExistHutangNoBukti(),
                'required',
            ],
            // 'hutang_id' => 'required',
            'keterangan.*' => 'required',
            'bayar.*' => ['required','numeric','gt:0',new HutangBayarLimit()],
            'sisa.*' => 'required|numeric|min:0',

            'keterangan' => 'required|array',
            'bayar' => 'required|array',
            'sisa' => 'required|array',

        ];
        return $rules;
    }

    public function attributes()
    {
        return [
            'keterangan.*' => 'keterangan detail',
            'bayar.*' => 'bayar'
        ];
    }
}
