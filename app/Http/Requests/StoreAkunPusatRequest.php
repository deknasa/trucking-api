<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreAkunPusatRequest extends FormRequest
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
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusAktif[] = $item['id'];
        }

        $parameter = new Parameter();
        $dataCoa = $parameter->getcombodata('STATUS COA', 'STATUS COA');
        $dataCoa = json_decode($dataCoa, true);
        foreach ($dataCoa as $item) {
            $statusCoa[] = $item['id'];
        }

        $parameter = new Parameter();
        $dataAccount = $parameter->getcombodata('STATUS ACCOUNT PAYABLE', 'STATUS ACCOUNT PAYABLE');
        $dataAccount = json_decode($dataAccount, true);
        foreach ($dataAccount as $item) {
            $statusAccount[] = $item['id'];
        }

        $parameter = new Parameter();
        $dataNeraca = $parameter->getcombodata('STATUS NERACA', 'STATUS NERACA');
        $dataNeraca = json_decode($dataNeraca, true);
        foreach ($dataNeraca as $item) {
            $statusNeraca[] = $item['id'];
        }

        $parameter = new Parameter();
        $dataLabaRugi = $parameter->getcombodata('STATUS LABA RUGI', 'STATUS LABA RUGI');
        $dataLabaRugi = json_decode($dataLabaRugi, true);
        foreach ($dataLabaRugi as $item) {
            $statusLabaRugi[] = $item['id'];
        }


        $rules = [
            'coa' => ['required','unique:akunpusat','numeric'],
            'keterangancoa' => ['required','unique:akunpusat'],
            'type' => ['required'],
            'level' => ['required'],
            'parent' => ['required'],
            'statuscoa' => ['required', Rule::in($statusCoa)],
            'statusaccountpayable' => ['required', Rule::in($statusAccount)],
            'statusneraca' => ['required', Rule::in($statusNeraca)],
            'statuslabarugi' => ['required', Rule::in($statusLabaRugi)],
            'coamain' => ['required'],
            'statusaktif' => ['required', Rule::in($statusAktif)],
        ];

        return $rules;
    }

    public function attributes()
    {
        return [
            'coa' => 'kode coa',
            'keterangancoa' => 'keterangan coa',
            'type' => 'type',
            'level' => 'level',
            'parent' => 'parent',
            'statuscoa' => 'status coa',
            'statusaccountpayable' => 'status account payable',
            'statusneraca' => 'status neraca',
            'statuslabarugi' => 'status laba rugi',
            'coamain' => 'kode perkiraan utama',
            'statusaktif' => 'status aktif',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangancoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'type.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'level.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'parent.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuscoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaccountpayable.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusneraca.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuslabarugi.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coamain.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}    

