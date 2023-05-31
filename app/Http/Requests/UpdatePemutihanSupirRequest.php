<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PemutihanSupir;
use App\Rules\DateTutupBuku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePemutihanSupirRequest extends FormRequest
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
        $pemutihanSupir = new PemutihanSupir();
        $getData = $pemutihanSupir->findAll(request()->id);


        return [
            'nobukti' => [Rule::in($getData->nobukti)],
            'tglbukti' => [
                'required','date_format:d-m-Y',
                'date_equals:' . date('d-m-Y', strtotime($getData->tglbukti)),
                new DateTutupBuku()
            ],
            'supir' => 'required', 'numeric', 'min:1',
            'bank' => 'required', 'numeric', 'min:1'
        ];
    }
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
