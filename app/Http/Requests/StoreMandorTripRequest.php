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
                'required','date_format:d-m-Y',
                new DateApprovalQuota()
            ],
            
            "agen_id" => "required",
            "agen" => "required",
            "tarifrincian" => "required",
            "container_id" => "required",
            "container" => "required",
            "dari_id" => "required",
            "dari" => "required",
            "gandengan_id" => "required",
            "gandengan" => "required",
            "gudang" => "required",
            "jenisorder_id" => "required",
            "jenisorder" => "required",
            "pelanggan_id" => "required",
            "pelanggan" => "required",
            "sampai_id" => "required",
            "sampai" => "required",
            "statuscontainer_id" => "required",
            "statuscontainer" => "required",
            "statusgudangsama" => "required",
            "statuslongtrip" => "required",
            "trado_id" => "required",
            "trado" => "required",
        ];


    }
    public function attributes()
    {
        return [
            "agen_id"=>"agen",
            "agen"=>"agen",
            "container_id"=>"container",
            "container"=>"container",
            "dari_id"=>"dari",
            "dari"=>"dari",
            "gandengan_id"=>"gandengan",
            "gandengan"=>"gandengan",
            "jenisorder_id"=>"jenisorder",
            "jenisorder"=>"jenisorder",
            "pelanggan_id"=>"pelanggan",
            "pelanggan"=>"pelanggan",
            "sampai_id"=>"sampai",
            "sampai"=>"sampai",
            "statuscontainer_id"=>"statuscontainer",
            "statuscontainer"=>"statuscontainer",
            "trado_id"=>"trado",
            "trado"=>"trado",
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            "agen_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "agen.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "container_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "container.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "dari_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "dari.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "gandengan_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "gandengan.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "jenisorder_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "jenisorder.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "pelanggan_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "pelanggan.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "sampai_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "sampai.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "statuscontainer_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "statuscontainer.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "trado_id.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            "trado.required"=>":attribute".' '.$controller->geterror('WI')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,

        ];
    }  
}
