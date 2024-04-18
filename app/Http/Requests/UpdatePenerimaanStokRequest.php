<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\PenerimaanStok;
use Illuminate\Validation\Rule;

class UpdatePenerimaanStokRequest extends FormRequest
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
        if (request()->from == 'tas') {
            return [];
        }
        $parameter = new Parameter();
        $dataFormat = $parameter->getComboByGroup('PENERIMAN STOK');
        $dataFormat = json_decode($dataFormat, true);
        foreach ($dataFormat as $item) {
            $format[] = $item['id'];
        }

        $hitungStok = $parameter->getcombodata('STATUS HITUNG STOK', 'STATUS HITUNG STOK');
        $hitungStok = json_decode($hitungStok, true);
        foreach ($hitungStok as $item) {
            $statushitungStok[] = $item['id'];
        }

        $coa = $this->coa;
        $rulesCoa = [];
        if ($coa != null) {
            if ($coa == 0) {
                $rulesCoa = [
                    'coa' => ['required', 'string', 'min:1']
                ];
            } else {
                if ($this->coa == '') {
                    $rulesCoa = [
                        'coa' => ['required']
                    ];
                }
            }
        } else if ($coa == null && $this->keterangancoa != '') {
            $rulesCoa = [
                'coa' => ['required', 'string', 'min:1']
            ];
        }
        $penerimaanStok = new PenerimaanStok();
        $getDataPenerimaanStok = $penerimaanStok->find(request()->id);

        $rules = [
            'kodepenerimaan' => ['required',Rule::in($getDataPenerimaanStok->kodepenerimaan),Rule::unique('penerimaanstok')->whereNotIn('id', [$this->id])],
            'keterangancoa' => 'required',
            "format" => "required",
            'statushitungstok' => ['required', Rule::in($statushitungStok)],
        ];

        $rule = array_merge(
            $rules,
            $rulesCoa
        );
        
        return $rule;
    }

    public function attributes()
    {
        return [
            'kodepenerimaan' => 'kode penerimaan',
            'format' => 'format bukti',
            'statushitungstok' => 'status hitung stok',
            'keterangancoa' => 'coa',
        ];
        
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodepenerimaan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'format.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statushitungstok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangancoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }  
}
