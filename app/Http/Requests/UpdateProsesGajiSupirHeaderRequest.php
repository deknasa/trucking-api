<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesGajiSupirHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DestroyProsesGajiSupir;
use App\Rules\ExistBank;
use Illuminate\Validation\Rule;

class UpdateProsesGajiSupirHeaderRequest extends FormRequest
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
        $getDataProsesGaji = $prosesGaji->findAll(request()->id);

        $tglbatasawal = date('Y-m-01');
        $tglbataseedit = date('Y-m-01', strtotime($getDataProsesGaji->tgldari));
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        // First day of the month.
        $awalPeriode = date('Y-m-01', strtotime(request()->tgldari));
        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank(), Rule::in($getDataProsesGaji->bank_id)]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank(), Rule::in($getDataProsesGaji->bank_id)]
            ];
        }
        $rules = [
            'id' => new DestroyProsesGajiSupir(),
            'nobukti' => [Rule::in($getDataProsesGaji->nobukti)],
            'bank' => [
                'required',
            ],
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                // 'before_or_equal:' .$tglbatasakhir,
                // 'after_or_equal:'.$tglbataseedit,
                'before_or_equal:' . date('Y-m-d'),
                new DateTutupBuku()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                'after_or_equal:' . $this->tgldari
            ],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
        ];
        $relatedRequests = [
            UpdateProsesGajiSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesBank_id
            );
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'periode' => 'Periode',
            'tgldari' => 'Tanggal Dari',
            'tglsampai' => 'Tanggal Sampai',
            'tglbukti' => 'Tanggal Bukti'
        ];
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'periode.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
