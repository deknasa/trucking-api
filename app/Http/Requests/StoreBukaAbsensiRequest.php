<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\UniqueTglBukaAbsensi;
use App\Rules\DateAllowedAbsenMandor;
use Illuminate\Foundation\Http\FormRequest;

class StoreBukaAbsensiRequest extends FormRequest
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
            "tglabsensi"=> [
                
                'required', 'date_format:d-m-Y', 
                'before_or_equal:' . date('d-m-Y'),
                new UniqueTglBukaAbsensi,
                new DateTutupBuku(),
            ],
            "user_id"=>["required"]
        ];
    }
}
