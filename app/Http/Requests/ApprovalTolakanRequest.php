<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Rules\ApprovalTolakanRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovalTolakanRequest extends FormRequest
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
        $data = $parameter->getcombodata('STATUS APPROVAL', 'STATUS APPROVAL');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        return [
            'jobtruckingtrans' => new ApprovalTolakanRule(),
            'statustolakan' => ['required', Rule::in($status)],
            'nominalperalihantolakan' => 'numeric|min:0'
        ];
    }

    public function attributes()
    {
        return [
            "statustolakan" => 'status tolakan'
        ];
    }
}
