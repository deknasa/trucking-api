<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePelangganRequest extends FormRequest
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
            $status[] = $item['id'];
        }
        $statusaktif = $this->statusaktif;
        $rulesStatusAktif = [];
        if ($statusaktif != null) {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        } else if ($statusaktif == null && $this->statusaktifnama != '') {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        }

        $rules = [
            'kodepelanggan' => ['required', 'unique:pelanggan'],
            'namapelanggan' => 'required',
            'namakontak' => 'required',
            'telp' => 'required|min:12|max:13',
            'alamat' => 'required',
            'kota' => 'required',
            'statusaktifnama' => ['required'],
        ];
        $rules = array_merge(
            $rules,
            $rulesStatusAktif,
        );
        return $rules;
    }

    public function attributes()
    {
        return [
            'kodepelanggan' => 'kode pelanggan',
            'namapelanggan' => 'nama pelanggan',
            'namakontak' => 'nama kontak',
            'kodepos' => 'kode pos',
            'telp' => 'no telpon',
            'alamat' => 'alamat',
            'kota' => 'kota',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status aktif',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodepelanggan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namapelanggan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namakontak.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'telp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'alamat.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kota.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'telp.min' => 'min. 12 karakter',
            'telp.max' => 'max. 13 karakter',
            'kodepos.min' => 'min. 5 karakter',
            'kodepos.max' => 'max. 5 karakter',
        ];
    }
}
