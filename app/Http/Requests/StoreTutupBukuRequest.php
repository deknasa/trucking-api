<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;

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
        
        $tglBatasAkhir = (date('Y') + 1) . '-01-01';
        return [
            //
        ];
    }
}
