<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use Illuminate\Validation\Rule;
use App\Rules\ApprovalKirimBerkas;
use App\Rules\BukaCetakSatuArah;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreApprovalKirimBerkasRequest extends FormRequest
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
        $dataKirimBerkas = $parameter->getcombodata('KIRIMBERKAS', 'KIRIMBERKAS');
        $dataKirimBerkas = json_decode($dataKirimBerkas, true);
        foreach ($dataKirimBerkas as $item) {
            $statusKirimBerkas[] = $item['id'];
        }

        $dataKirimBerkas = $parameter->getcombodata('CETAKULANG', 'CETAKULANG');
        $dataKirimBerkas = json_decode($dataKirimBerkas, true);
        foreach ($dataKirimBerkas as $item) {
            $statusKirimBerkas[] = $item['text'];
        }

        // dd('test');
        $rules = [
            // 'tableId' => ['required','min:1',new ApprovalBukaCetak(),new BukaCetakSatuArah()],
            'tableId' => ['required','min:1',new ApprovalKirimBerkas()],
            'periode' => ['required'],
            'table' => ['required', Rule::in($statusKirimBerkas)],
        ];
        
        return $rules;
    }

    public function attributes()
    {
        return [
            'tableId' => 'No Bukti',
        ];
    }

     public function messages()
    {
        $controller = new ErrorController;

        return [
            'tableId.required' => ':attribute' . ' Harap Di Pilih'  ,
        ];
    }
}
