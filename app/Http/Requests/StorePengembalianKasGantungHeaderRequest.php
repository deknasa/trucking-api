<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\PreventInputType;

class StorePengembalianKasGantungHeaderRequest extends FormRequest
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
        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }
        
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
            ],

            "bank" => "required",
            "tgldari" => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
            ],
            "tglsampai" => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
            ],
        ];
        $relatedRequests = [
            StorePengembalianKasGantungDetailRequest::class
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
            'keterangandetail.*' => 'keterangan',
            'coadetail.*' => 'kode perkiraan',
            'nominal.*' => 'Nominal',
        ];
    }
    public function messages()
    {
        return [
            'kasgantungdetail_id.required' => 'KASGANTUNG '.app(ErrorController::class)->geterror('WP')->keterangan,
            'sisa.*.min' => 'SISA '.app(ErrorController::class)->geterror('NTM')->keterangan,
            'nominal.*.numeric' => 'nominal harus '.app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'nominal.*.gt' => ':attribute ' .  app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
