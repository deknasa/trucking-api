<?php

namespace App\Http\Requests;

use App\Models\AlatBayar;
use App\Rules\DateTutupBuku;
use App\Rules\validasiDestroyPindahBuku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdatePindahBukuRequest extends FormRequest
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
        $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $rulesNoWarkat = [];
        if (request()->alatbayar_id == $alatbayarGiro->id) {
            $rulesNoWarkat = [
                'nowarkat' => 'required'
            ];
        }
        $rules = [
            'id' => new validasiDestroyPindahBuku(),
            'tglbukti' => [
                'required','date_format:d-m-Y',
                // 'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'tgljatuhtempo' => ['required','date_format:d-m-Y','after_or_equal:'.request()->tglbukti],
            'bankdari' => 'required',
            'bankke' => 'required',
            'alatbayar' => 'required',
            'nominal' => ['required', 'numeric', 'gt:0'],
            'keterangan' => 'required',
        ];

        $rules = array_merge(
            $rules,
            $rulesNoWarkat
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            'tgljatuhtempo' => 'tanggal jatuh tempo',
            'nowarkat' => 'no warkat',
            'bankdari' => 'bank dari',
            'bankke' => 'bank ke',
            'alatbayar' => 'alat bayar',
        ];
    }
}
