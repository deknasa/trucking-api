<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreMenuRequest extends FormRequest
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
        return [
            'menuname' => 'required',
            'menuseq' => 'numeric|nullable',
            'controller' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'menuname' => 'nama menu',
            'menuseq' => 'Pengurutan',
            'menuparent' => 'menuparent',
            'menuicon' => 'icon menu',
            'aco_id' => 'aco_id',
            'link' => 'link',
            'menuexe' => 'menuexe',
            'menukode' => 'menukode',
            'controller' => 'controller',
        ];
    }
}
