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
            'menuseq' => 'required',
            'menuicon' => 'required',
            'modifiedby' => 'required'
            
        ];
    }

    public function attributes()
    {
        return [
            'menuname' => 'nama menu',
            'menuseq' => 'menuseq',
            'menuparent' => 'menuparent',
            'menuicon' => 'icon menu',
            'aco_id' => 'aco_id',
            'link' => 'link',
            'menuexe' => 'menuexe',
            'menukode' => 'menukode',
            'modifiedby' => 'modified by'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'menuname.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'menuseq.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'menuicon.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
            'modifiedby.required' => ':attributes'.' '. $controller->geterror(1)->keterangan,
 

        ];
    }

}
