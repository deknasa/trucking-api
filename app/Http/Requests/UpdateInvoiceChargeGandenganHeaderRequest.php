<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\InvoiceChargeGandenganHeader;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAgen;
use App\Rules\ValidasiDestroyInvoiceChargeGandengan;
use App\Rules\ValidasiDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceChargeGandenganHeaderRequest extends FormRequest
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
        $invoiceCharge = new InvoiceChargeGandenganHeader();
        $getData = $invoiceCharge->find(request()->id);
        $jumlahdetail = $this->jumlahdetail ?? 0;
        
        $agen_id = $this->agen_id;
        $rulesAgen_id = [];
        if ($agen_id != null) {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen(), Rule::in($getData->agen_id)]
            ];
        } else if ($agen_id == null && $this->agen != '') {
            $rulesAgen_id = [
                'agen_id' => ['required', 'numeric', 'min:1', new ExistAgen(), Rule::in($getData->agen_id)]
            ];
        }

        $rules = [
            'id' => new ValidasiDestroyInvoiceChargeGandengan(),
            'tglbukti' => [
                'required', 'date_format:d-m-Y','before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'agen' => ['required',
            new ValidasiDetail($jumlahdetail)],
            'tglproses' => 'required|date_format:d-m-Y'
        ];

        $rules = array_merge(
            $rules,
            $rulesAgen_id
        );

        return $rules;
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglproses.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
