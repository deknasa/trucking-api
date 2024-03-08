<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PengembalianKasGantungHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DestroyPengembalianKasGantung;
use App\Rules\ValidasiDetail;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyPengembalianKasGantungHeader;

class UpdatePengembalianKasGantungHeaderRequest extends FormRequest
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

        $jumlahdetail = $this->jumlahdetail ?? 0;
        $pengembalian = new PengembalianKasGantungHeader();
        $getDataPengembalian = $pengembalian->findAll(request()->id);

        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPengembalian->bank_id)]
                ];
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', Rule::in($getDataPengembalian->bank_id)]
            ];
        }

        $tglbataseedit = date('Y-m-01', strtotime($getDataPengembalian->tgldari));
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        $rules = [
            'id' => [ new ValidasiDestroyPengembalianKasGantungHeader()],   
            'nobukti' => [Rule::in($getDataPengembalian)],
            // 'nobukti' => [Rule::in($getDataPengembalian), new DestroyPengembalianKasGantung()],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],

            "bank" => "required",
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' .$tglbatasakhir,
                new ValidasiDetail($jumlahdetail)
            ],
            "tglsampai" => [
                "required", 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                'after_or_equal:' . $this->tgldari
            ],
        ];
        $relatedRequests = [
            UpdatePengembalianKasGantungDetailRequest::class
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
            'tglbukti' => 'tanggal bukti',
            'tgldari' => 'tanggal dari',
            'tglsampai' => 'tanggal sampai',
        ];
        
    }

    public function messages()
    {
        return [
            'kasgantungdetail_id.required' => 'KASGANTUNG ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
            'nominal.*.numeric' => 'nominal harus ' . app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'nominal.*.gt' => ':attribute ' .  app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
