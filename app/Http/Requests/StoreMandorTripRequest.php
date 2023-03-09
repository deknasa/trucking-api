<?php

namespace App\Http\Requests;
use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateApprovalQuota;
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
            'tglbukti' => [
                'required',
                new DateApprovalQuota()
            ],
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
    public function attributes()
    {
        return [
            "agen_id"=>"agen",
            "container_id"=>"container",
            "dari_id"=>"dari",
            "gandengan_id"=>"gandengan",
            "jenisorder_id"=>"jenisorder",
            "pelanggan_id"=>"pelanggan",
            "sampai_id"=>"sampai",
            "statuscontainer_id"=>"statuscontainer",
            "trado_id"=>"trado",
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            "agen_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "container_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "dari_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "gandengan_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "jenisorder_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "pelanggan_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "sampai_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "statuscontainer_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "trado_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,

        ];
    }  
}
