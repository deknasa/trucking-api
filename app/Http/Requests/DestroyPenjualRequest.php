<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class DestroyPenjualRequest extends FormRequest
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
        if (request()->id) {
            return [];
        } else {
            $controller = new ErrorController();

            return [
                $controller->geterror('DTA')->keterangan
            ];
        }
        // dd(request()->id);
        // $limit = request()->id;
      
        // return [];
      
    }

}
