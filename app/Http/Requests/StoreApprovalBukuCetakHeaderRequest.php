<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use Illuminate\Validation\Rule;
use App\Rules\ApprovalBukaCetak;
use App\Rules\BukaCetakSatuArah;
use Illuminate\Foundation\Http\FormRequest;
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
            // 'tableId' => ['required','min:1',new ApprovalBukaCetak(),new BukaCetakSatuArah()],
            'tableId' => ['required','min:1',new ApprovalBukaCetak()],
            'periode' => ['required'],
            'table' => ['required', Rule::in($statusCetakUlang)],
        ];
        
        return $rules;
    }

    public function attributes()
    {
        return [
            'periode' => 'Periode',
            'table' => 'Table',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'tableId.required' => 'TRANSAKSI '.app(ErrorController::class)->geterror('WP')->keterangan,
            'periode.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'table.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
