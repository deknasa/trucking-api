<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use App\Rules\DateTutupBuku;
use App\Http\Controllers\Api\ErrorController;

class StoreTutupBukuRequest extends FormRequest
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
        $getBatas = $parameter->getTutupBuku();
        $tglbatasawal = $getBatas->text;

        $rules = [
            'tgltutupbuku' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'after:'.$tglbatasawal,
                'before_or_equal:' . date('d-m-Y'),

            ]
        ];

        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'tgltutupbuku' => 'Tanggal Tutup Buku',
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'tgltutupbuku.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
