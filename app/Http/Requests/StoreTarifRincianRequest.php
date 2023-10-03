<?php

namespace App\Http\Requests;

use App\Models\TarifRincian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTarifRincianRequest extends FormRequest
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
        $rulesDetailId = [];
        // if (request()->parent_id) {
        //     $tarifRincian = new TarifRincian();
        //     $getTarifRincian = $tarifRincian->getAll(request()->parent_id);
        //     $dataRincian = json_decode($getTarifRincian, true);
            
        //     foreach ($dataRincian as $item) {
        //         $rincianId[] = $item['id'];
        //     }
        //     $rulesDetailId = [
        //         'detail_id.*' => ['required', 'numeric', 'min:1',Rule::in($rincianId)]
        //     ];
        // }
        $rules = [
            'container' => 'required|array',
            'container.*' => 'required',
            'nominal.*' => 'required|numeric|min:0'
        ];
        $rules = array_merge(
            $rules,
            $rulesDetailId,
        );
        return $rules;
    }
}
