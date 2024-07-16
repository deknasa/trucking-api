<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StoreAlatBayarRequest extends FormRequest
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
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }
        $statusaktif = $this->statusaktif;
        $rulesStatusAktif = [];
        if ($statusaktif != null) {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($statusAktif)]
            ];
        } else if ($statusaktif == null && $this->statusaktifnama != '') {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($statusAktif)]
            ];
        }

        $langsungCair = $parameter->getcombodata('STATUS LANGSUNG CAIR', 'STATUS LANGSUNG CAIR');
        $langsungCair = json_decode($langsungCair, true);
        foreach ($langsungCair as $item) {
            $statusLangsungCair[] = $item['id'];
        }
        $statuslangsungcair = $this->statuslangsungcair;
        $rulesLangsungCair = [];
        if ($statuslangsungcair != null) {
            $statuslangsungcair = [
                'statuslangsungcair' => ['required', Rule::in($statuslangsungcair)]
            ];
        } else if ($statuslangsungcair == null && $this->statuslangsungcairnama != '') {
            $statuslangsungcair = [
                'statuslangsungcair' => ['required', Rule::in($statuslangsungcair)]
            ];
        }

        $default = $parameter->getcombodata('STATUS DEFAULT', 'STATUS DEFAULT');
        $default = json_decode($default, true);
        foreach ($default as $item) {
            $statusDefault[] = $item['id'];
        }
        $statusdefault = $this->statusdefault;
        $rulesStatusDefault = [];
        if ($statusdefault != null) {
            $rulesStatusDefault = [
                'statusdefault' => ['required', Rule::in($statusDefault)]
            ];
        } else if ($statusdefault == null && $this->statusdefaultnama != '') {
            $rulesStatusDefault = [
                'statusdefault' => ['required', Rule::in($statusDefault)]
            ];
        }

        $coa = $this->coa;
        $rulesCoa = [];
        if ($coa != null) {
            if ($coa == 0) {
                $rulesCoa = [
                    'coa' => ['required', 'string', 'min:1']
                ];
            } else {
                if ($this->coa == '') {
                    $rulesCoa = [
                        'coa' => ['required']
                    ];
                }
            }
        } else if ($coa == null && $this->keterangancoa != '') {
            $rulesCoa = [
                'coa' => ['required', 'string', 'min:1']
            ];
        }

        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesBank_id = [
                    'bank_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->bank == '') {
                    $rulesBank_id = [
                        'bank' => ['required']
                    ];
                }
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $rules = [
            'kodealatbayar' => ['required', 'unique:alatbayar'],
            'namaalatbayar' => ['required', 'unique:alatbayar'],
            'statuslangsungcairnama' => ['required'],
            'statusdefaultnama' => ['required'],
            'statusaktifnama' => ['required'],
            'bank' => 'required',
            // 'keterangancoa' => 'required',
        ];

        $rule = array_merge(
            $rules,
            // $rulesCoa,
            $rulesBank_id,
            $rulesStatusAktif,
            $rulesStatusDefault,
            $rulesLangsungCair
        );

        return $rule;
    }

    public function attributes()
    {
        return [
            'kodealatbayar' => 'kode alat bayar',
            'namaalatbayar' => 'nama alat bayar',
            'statuslangsungcairnama' => 'status langsung cair',
            'statusdefaultnama' => 'status default',
            'statusaktifnama' => 'status aktif',
            'keterangan' => 'keterangan',
            'bank' => 'nama bank',
            'keterangancoa' => 'coa',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodealatbayar.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namaalatbayar.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statuslangsungcairnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusdefaultnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktifnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'bank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'keterangancoa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
