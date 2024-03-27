<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ProsesUangJalanSupirHeader;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAbsensiSupirHeader;
use App\Rules\ExistSupir;
use App\Rules\ExistTrado;
use App\Rules\validasiDestroyProsesUangJalanSupir;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProsesUangJalanSupirHeaderRequest extends FormRequest
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
        $prosesUang = new ProsesUangJalanSupirHeader();
        $getDataProsesUang = $prosesUang->findAll(request()->id);

        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', Rule::in($getDataProsesUang->supir_id), new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', Rule::in($getDataProsesUang->supir_id), new ExistSupir()]
            ];
        }

        $trado_id = $this->trado_id;
        $rulesTrado_id = [];
        if ($trado_id != null) {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', Rule::in($getDataProsesUang->trado_id), new ExistTrado()]
            ];
        } else if ($trado_id == null && $this->trado != '') {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', Rule::in($getDataProsesUang->trado_id), new ExistTrado()]
            ];
        }
        $rules = [
            'id' => new validasiDestroyProsesUangJalanSupir(),
            'nobukti' => [Rule::in($getDataProsesUang->nobukti)],
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'absensisupir' => ['required', Rule::in($getDataProsesUang->absensisupir), new ExistAbsensiSupirHeader()],
            'supir' => 'required',
            'trado'=> 'required',
        ];
        
        $relatedRequests = [
            UpdateProsesUangJalanSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesSupir_id,
                $rulesTrado_id
            );
        }

        $rules = array_merge(
            $rules,
            $rulesSupir_id,
            $rulesTrado_id
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
