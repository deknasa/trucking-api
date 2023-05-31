<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use App\Rules\ApprovalBukaCetak;
use App\Http\Controllers\Api\ErrorController;

class StoreApprovalBukuCetakHeaderRequest extends FormRequest
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
        $dataCetak = $parameter->getcombodata('STATUSCETAK', 'STATUSCETAK');
        $dataCetak = json_decode($dataCetak, true);
        foreach ($dataCetak as $item) {
            $statusCetak[] = $item['id'];
        }

        $dataCetakUlang = $parameter->getcombodata('CETAKULANG', 'CETAKULANG');
        $dataCetakUlang = json_decode($dataCetakUlang, true);
        foreach ($dataCetakUlang as $item) {
            $statusCetakUlang[] = $item['text'];
        }

        
        $rules = [
            'periode' => ['required',new ApprovalBukaCetak()],
            'cetak' => ['required', Rule::in($statusCetak)],
            'table' => ['required', Rule::in($statusCetakUlang)],
        ];
        
        return $rules;
    }

    public function attributes()
    {
        return [
            'periode' => 'Periode',
            'cetak' => 'Proses Data',
            'table' => 'Table',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'periode.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'cetak.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'table.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
