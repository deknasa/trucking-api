<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PemutihanSupir;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistSupir;
use App\Rules\ValidasiDestroyPemutihanSupir;
use App\Rules\ValidasiHutangList;
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

        $jumlahdetail = $this->jumlahdetail ?? 0;
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

        $bank_id = $this->bank_id;
        $ruleBank_id = [];
        if (request()->jumlahposting > 0) {

            if ($bank_id != null) {
                $ruleBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
                ];
            } else if ($bank_id == null && $this->bank != '') {
                $ruleBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
                ];
            }
        }
        
        $requiredBank = Rule::requiredIf(function () {
            $jumlahposting = request()->jumlahposting;
            if ($jumlahposting > 0) {
                return true;
            }
            return false;
        });
        $rules = [
            'id' => new ValidasiDestroyPemutihanSupir(),
            'nobukti' => [Rule::in($getData->nobukti)],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'supir' => [
                'required', Rule::in($getData->supir), new ValidasiHutangList($jumlahdetail)
            ],
            'bank' => $requiredBank,
        ];
        $rules = array_merge(
            $rules,
            $ruleBank_id,
            $rulesSupir_id
        );

        return $rules;
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
