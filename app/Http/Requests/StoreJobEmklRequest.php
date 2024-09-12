<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreJobEmklRequest extends FormRequest
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
            "tglbukti"=>"required",
            "shipper_id"=>"required",
            "shipper"=>"required",
            "tujuan_id"=>"required",
            "tujuan"=>"required",
            "container_id"=>"required",
            "container"=>"required",
            "jenisorder_id"=>"required",
            "jenisorder"=>"required",
            "marketing_id"=>"required",
            "marketing"=>"required",
            "kapal"=>"required",
            "destination"=>"required",
            "nocont"=>"required",
            "noseal"=>"required",
        ];
    }

    public function attributes()
    {
        return [
            "tglbukti"=>"tgl bukti",
            "shipper_id"=>"shipper",
            "shipper"=>"shipper",
            "tujuan_id"=>"tujuan",
            "tujuan"=>"tujuan",
            "container_id"=>"container",
            "container"=>"container",
            "jenisorder_id"=>"jenisorder",
            "jenisorder"=>"jenis order",
            "kapal"=>"kapal",
            "destination"=>"destination",
            "nocont"=>"no cont",
            "noseal"=>"no seal",
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            "tglbukti"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "shipper_id"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "shipper"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "tujuan_id"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "tujuan"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "container_id"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "container"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "jenisorder_id"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "jenisorder"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "kapal"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "destination"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "nocont"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            "noseal"=>':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
