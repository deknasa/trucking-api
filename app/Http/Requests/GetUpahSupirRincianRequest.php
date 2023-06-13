<?php

namespace App\Http\Requests;

use App\Rules\ExistContainer;
use App\Rules\ExistStatusContainer;
use Illuminate\Foundation\Http\FormRequest;

class GetUpahSupirRincianRequest extends FormRequest
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
            'container_id' => ['required', 'numeric', 'min:1', new ExistContainer()],
            'statuscontainer_id' => ['required', 'numeric', 'min:1', new ExistStatusContainer()],
        ];
    }
}
