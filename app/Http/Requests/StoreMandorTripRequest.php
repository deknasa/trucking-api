<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMandorTripRequest extends FormRequest
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
            "agen_id" => "required",
            "container_id" => "required",
            "dari_id" => "required",
            "gandengan_id" => "required",
            "gudang" => "required",
            "jenisorder_id" => "required",
            "pelanggan_id" => "required",
            "sampai_id" => "required",
            "statuscontainer_id" => "required",
            "statusgudangsama" => "required",
            "statuslongtrip" => "required",
            "trado_id" => "required",
        ];
    }
}
