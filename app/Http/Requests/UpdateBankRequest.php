<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class UpdateBankRequest extends FormRequest
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
        
        $rules = [
            'kodebank' => ['required',Rule::unique('bank')->whereNotIn('id', [$this->id])],
            'namabank' => ['required',Rule::unique('bank')->whereNotIn('id', [$this->id])],
            'coa' => ['required',Rule::unique('bank')->whereNotIn('id', [$this->id])],
            'tipe' => 'required',
            'statusaktif' => ['required', Rule::in($status),'numeric', 'min:1'],
            'formatpenerimaan' => 'required','numeric', 'min:1',
            'formatpengeluaran' => 'required',,'numeric', 'min:1',
        ];
        return $rules;
    }

    public function attributes()
    {
        return [
            'kodebank' => 'kode bank',
            'namabank' => 'nama bank',
            'statusaktif' => 'status aktif',
            'coa' => 'kode perkiraan',
            'tipe' => 'tipe',
            'formatpenerimaan' => 'format penerimaan',
            'formatpengeluaran' => 'format pengeluaran',
        ];
    }

    // public function messages()
    // {
    //     $controller = new ErrorController;

    //     return [
    //         'kodebank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //         'namabank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //         'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //         'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //         'tipe.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //         'formatpenerimaan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //         'formatpengeluaran.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
    //     ];
    // }
}
