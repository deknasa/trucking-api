<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\ExistGudang;
use Illuminate\Foundation\Http\FormRequest;

class StoreOpnameHeaderRequest extends FormRequest
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
        $gudang_id = $this->gudang_id;
        $rulesGudang_id = [];
        if ($gudang_id != null) {
            $rulesGudang_id = [
                'gudang_id' => ['required', 'numeric', 'min:1', new ExistGudang()]
            ];
        } else if ($gudang_id == null && $this->gudang != '') {
            $rulesGudang_id = [
                'gudang_id' => ['required', 'numeric', 'min:1', new ExistGudang()]
            ];
        }
        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
            ],
            'gudang' => 'required',
        ];

        $rules = array_merge(
            $rules,
            $rulesGudang_id
        );
        return $rules;
    }
}
