<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistSupir;
use App\Rules\ValidasiHutangList;

class StorePendapatanSupirHeaderRequest extends FormRequest
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
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $jumlahdetail = $this->jumlahdetail ?? 0;
        $bank_id = $this->bank_id;
        $ruleBank_id = [];
        if ($bank_id != null) {
            $ruleBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $ruleBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        }

        $rules = [
            'tglbukti' => [
                'required',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'bank' => 'required',
            'supir' => [
                new ValidasiHutangList($jumlahdetail)
            ],
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                new DateTutupBuku()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                'after_or_equal:' . $this->tgldari
            ],
        ];


        $rules = array_merge(
            $rules,
            $ruleBank_id
        );

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
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
            'tglsampai.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
        ];
    }
}
