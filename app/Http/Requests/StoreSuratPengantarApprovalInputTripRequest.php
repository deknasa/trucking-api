<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\DateTutupBuku;
use App\Rules\ValidationTglBuktiSPStore;
use Illuminate\Validation\Rule;

class StoreSuratPengantarApprovalInputTripRequest extends FormRequest
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

        $rules = [
            'tglbukti' => ['required', 'date_format:d-m-Y', 'before:'. date('d-m-Y'), new ValidationTglBuktiSPStore(), new DateTutupBuku()],
            'jumlahtrip' => ['required', 'numeric', 'min:1'],
            'statusapproval' => ['required', Rule::in($status)],
            'user' => ['required']
        ];
        return $rules;
    }
}
