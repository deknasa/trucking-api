<?php

namespace App\Http\Requests;

use App\Rules\ExistKelompok;
use App\Rules\ValidasiDetail;
use Illuminate\Foundation\Http\FormRequest;

class StoreStokPusatRequest extends FormRequest
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
        $jumlahdetail = $this->jumlahRow ?? 0;
        $kelompok_id = $this->kelompok_id;
        $rulesKelompok_id = [];
        if ($kelompok_id != null) {
            $rulesKelompok_id = [
                'kelompok_id' => ['required', 'numeric', 'min:1', new ExistKelompok()]
            ];
        } else if ($kelompok_id == null && $this->agen != '') {
            $rulesKelompok_id = [
                'kelompok_id' => ['required', 'numeric', 'min:1', new ExistKelompok()]
            ];
        }
        $rules = [
            'namastok' => ['required','unique:stokpusat'],
            'kelompok' => [
                'required',
                new ValidasiDetail($jumlahdetail)
            ]
        ];

        $rules = array_merge(
            $rules,
            $rulesKelompok_id
        );

        return $rules;
    }
}
