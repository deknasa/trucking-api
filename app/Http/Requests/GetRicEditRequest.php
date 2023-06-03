<?php

namespace App\Http\Requests;

use App\Models\ProsesGajiSupirHeader;
use Illuminate\Foundation\Http\FormRequest;

class GetRicEditRequest extends FormRequest
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
        $prosesGaji = new ProsesGajiSupirHeader();
        $getDataProsesGaji = $prosesGaji->findAll($this->id);

        $tglbataseedit = date('Y-m-01', strtotime($getDataProsesGaji->tgldari));
        $awalPeriode = date('Y-m-01');
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        
        return [
            'tgldari' => ['required', 'date_format:d-m-Y', 'before_or_equal:' . $tglbatasakhir, 'after_or_equal:' . $tglbataseedit],
            'tglsampai' => ['required', 'date_format:d-m-Y', 'before_or_equal:' . date('Y-m-d'), 'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))],

        ];
    }
}
