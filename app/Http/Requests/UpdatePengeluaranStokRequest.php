<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\PengeluaranStok;
use Illuminate\Validation\Rule;

class UpdatePengeluaranStokRequest extends FormRequest
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
        $pengeluaranStok = $parameter->getComboByGroup('PENGELUARAN STOK');
        $pengeluaranStok = json_decode($pengeluaranStok, true);
        foreach ($pengeluaranStok as $item) {
            $format[] = $item['id'];
        }

        $hitungStok = $parameter->getcombodata('STATUS HITUNG STOK', 'STATUS HITUNG STOK');
        $hitungStok = json_decode($hitungStok, true);
        foreach ($hitungStok as $item) {
            $statusHitungStok[] = $item['id'];
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

        $pengeluaranStok = new PengeluaranStok();
        $getDataPengeluaranStok = $pengeluaranStok->find(request()->id);
        $rules = [
            "kodepengeluaran" => ['required',Rule::in($getDataPengeluaranStok->kodepengeluaran),Rule::unique('pengeluaranstok')->whereNotIn('id', [$this->id])],
            "keterangancoa" => "required",
            "format" => ['required', Rule::in($format)],
            "statushitungstok" => ['required', Rule::in($statusHitungStok)],
        ];

        $rule = array_merge(
            $rules,
            $rulesCoa,
        );
        
        return $rule;
    }

    public function attributes()
    {
        return [
            'kodepengeluaran' => 'kode pengeluaran',
            'format' => 'format bukti',
            'statushitungstok' => 'status hitung stok',
            'keterangancoa' => 'coa',
        ];
        
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodepengeluaran.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'format.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statushitungstok.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangancoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    } 
}
