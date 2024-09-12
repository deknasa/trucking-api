<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreMarketingRequest extends FormRequest
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
            "kodemarketing"=>["required",'unique:marketing'],
            "keterangan"=>"required",
            "statusaktif"=>"required",
        ];
    }

    public function attributes()
    {
        return [
            "kodemarketing"=>"kode marketing",
            "keterangan"=>"keterangan",
            "statusaktif"=>"status aktif",
        ];
    }
    
    public function messages()
    {
        $controller = new ErrorController;

        return [
            "kodemarketing"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "keterangan"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "statusaktif"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
