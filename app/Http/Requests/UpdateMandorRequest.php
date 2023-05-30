<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class UpdateMandorRequest extends FormRequest
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

        return [
            'namamandor' => ['required',Rule::unique('mandor')->whereNotIn('id', [$this->id])],
            'statusaktif' => ['required', Rule::in($status)],
        ];
    }

    public function attributes()
    {
        return [
            'namamandor' => 'nama mandor',
            'statusaktif' => 'statusaktif',
        ];
        
    }
}