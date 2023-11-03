<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\ExistAkuntansi;
use App\Rules\ExistTypeAkuntansi;
use App\Rules\ValidasiParentAkunPusat;
use Illuminate\Validation\Rule;

class UpdateAkunPusatRequest extends FormRequest
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
        $dataParent = $parameter->getcombodata('STATUS PARENT', 'STATUS PARENT');
        $dataParent = json_decode($dataParent, true);
        foreach ($dataParent as $item) {
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

        $type_id = $this->type_id;
        $rulesType_id = [];
        if ($type_id != null) {
            $rulesType_id = [
                'type_id' => ['required', 'numeric', 'min:1', new ExistTypeAkuntansi()],
            ];
        } else if ($type_id == null && $this->type != '') {
            $rulesType_id = [
                'type_id' => ['required', 'numeric', 'min:1', new ExistTypeAkuntansi()],
            ];
        }

        $akuntansi_id = $this->akuntansi_id;
        $rulesAkuntansi_id = [];
        if ($akuntansi_id != null) {
            $rulesAkuntansi_id = [
                'akuntansi_id' => ['required', 'numeric', 'min:1', new ExistAkuntansi()],
            ];
        } else if ($akuntansi_id == null && $this->akuntansi != '') {
            $rulesAkuntansi_id = [
                'akuntansi_id' => ['required', 'numeric', 'min:1', new ExistAkuntansi()],
            ];
        }
        $rules = [
            'coa' => ['required', Rule::unique('akunpusat')->whereNotIn('id', [$this->id])],
            'keterangancoa' => ['required', Rule::unique('akunpusat')->whereNotIn('id', [$this->id])],
            'type' => ['required'],
            'akuntansi' => ['required'],
            'statuscoa' => ['required', Rule::in($statusCoa)],
            'statusparent' => ['required', Rule::in($statusAccount)],
            'statusneraca' => ['required', Rule::in($statusNeraca)],
            'statuslabarugi' => ['required', Rule::in($statusLabaRugi)],
            'coamain' => ['required'],
            'statusaktif' => ['required', Rule::in($statusAktif)],            
            'parent' => [new ValidasiParentAkunPusat]
        ];
        $rules = array_merge(
            $rules,
            $rulesType_id,
            $rulesAkuntansi_id
        );
        return $rules;
    }

    public function attributes()
    {
        return [
            'coa' => 'kode cabang',
            'keterangancoa' => 'keterangan coa',
            'type' => 'type',
            'statuscoa' => 'status coa',
            'statusparent' => 'status parent',
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
            'statuscoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusparent.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusneraca.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuslabarugi.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coamain.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
