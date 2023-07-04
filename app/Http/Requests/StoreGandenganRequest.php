<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ParameterController;



class StoreGandenganRequest extends FormRequest
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
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }


        $rules = [
            'kodegandengan' => ['required', 'unique:gandengan'],
            'trado_id' => ['required', 'unique:gandengan'],
            'jumlahroda' => ['required'],
            'jumlahbanserap' => ['required'],
            'statusaktif' => ['required', Rule::in($status)]
        ];
        return $rules;

    }

    public function attributes()
    {
        return [
            'kodegandengan' => 'kode gandengan',
            'trado_id' => 'no polisi',
            'jumlahroda' => 'jumlah roda',
            'jumlahbanserap' => 'jumlah ban serap',
            'statusaktif' => 'status',
        ];
    }

}
