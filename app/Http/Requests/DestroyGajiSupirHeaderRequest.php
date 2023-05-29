<?php

namespace App\Http\Requests;

use App\Models\GajiSupirHeader;
use App\Rules\DateTutupBuku;
use App\Rules\DestroyGajiSupirNobukti;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class DestroyGajiSupirHeaderRequest extends FormRequest
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
            'nobukti' => new DestroyGajiSupirNobukti(),
            'tglbukti' => new DateTutupBuku()
        ];
    }
}
