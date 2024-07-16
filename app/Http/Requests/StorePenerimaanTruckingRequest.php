<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class StorePenerimaanTruckingRequest extends FormRequest
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
        // dd($this->format);
        if (request()->from == 'tas') {
            return [];
        }
        $parameter = new Parameter();
        $dataFormat = $parameter->getComboByGroup('PENERIMAAN TRUCKING');
        $dataFormat = json_decode($dataFormat, true);
        foreach ($dataFormat as $item) {
            $format[] = $item['id'];
        }
        $statusformat = $this->input("format");
        $rulesFormat = [];
        if ($format != null) {
            $rulesFormat = [
                'format' => ['required', Rule::in($statusformat)]
            ];
        } else if ($format == null && $this->formatnama != '') {
            $rulesFormat = [
                'format' => ['required', Rule::in($statusformat)]
            ];
        }

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

        $coadebet = $this->coadebet;
        $rulesCoaDebet = [];
        if ($coadebet != null) {
            if ($coadebet == 0) {
                $rulesCoaDebet = [
                    'coadebet' => ['required', 'string', 'min:1']
                ];
            } else {
                if ($this->coadebet == '') {
                    $rulesCoaDebet = [
                        'coadebet' => ['required']
                    ];
                }
            }
        } else if ($coadebet == null && $this->coadebetKeterangan != '') {
            $rulesCoaDebet = [
                'coadebet' => ['required', 'string', 'min:1']
            ];
        }

        $coakredit = $this->coakredit;
        $rulesCoaKredit = [];
        if ($coakredit != null) {
            if ($coakredit == 0) {
                $rulesCoaKredit = [
                    'coakredit' => ['required', 'string', 'min:1']
                ];
            } else {
                if ($this->coakredit == '') {
                    $rulesCoaKredit = [
                        'coakredit' => ['required']
                    ];
                }
            }
        } else if ($coakredit == null && $this->coakreditKeterangan != '') {
            $rulesCoaKredit = [
                'coakredit' => ['required', 'string', 'min:1']
            ];
        }

        $coapostingdebet = $this->coapostingdebet;
        $rulesCoaPostingDebet = [];
        if ($coapostingdebet != null) {
            if ($coapostingdebet == 0) {
                $rulesCoaPostingDebet = [
                    'coapostingdebet' => ['required', 'string', 'min:1']
                ];
            } else {
                if ($this->coapostingdebet == '') {
                    $rulesCoaPostingDebet = [
                        'coapostingdebet' => ['required']
                    ];
                }
            }
        } else if ($coapostingdebet == null && $this->coapostingdebetKeterangan != '') {
            $rulesCoaPostingDebet = [
                'coapostingdebet' => ['required', 'string', 'min:1']
            ];
        }

        $coapostingkredit = $this->coapostingkredit;
        $rulesCoaPostingKredit = [];
        if ($coapostingkredit != null) {
            if ($coapostingkredit == 0) {
                $rulesCoaPostingKredit = [
                    'coapostingkredit' => ['required', 'string', 'min:1']
                ];
            } else {
                if ($this->coapostingkredit == '') {
                    $rulesCoaPostingKredit = [
                        'coapostingkredit' => ['required']
                    ];
                }
            }
        } else if ($coapostingkredit == null && $this->coapostingkreditKeterangan != '') {
            $rulesCoaPostingKredit = [
                'coapostingkredit' => ['required', 'string', 'min:1']
            ];
        }

        $rules =  [
            'kodepenerimaan' => ['required', 'unique:penerimaantrucking'],
            'formatnama' => ['required'],
            'coadebetKeterangan' => 'required',
            'coakreditKeterangan' => 'required',
            'coapostingdebetKeterangan' => 'required',
            'coapostingkreditKeterangan' => 'required',
            'statusaktifnama' => ['required'],
        ];

        $rule = array_merge(
            $rules,
            $rulesCoaDebet,
            $rulesCoaKredit,
            $rulesCoaPostingDebet,
            $rulesCoaPostingKredit,
            $rulesStatusAktif,
            $rulesFormat
        );

        return $rule;
    }

    public function attributes()
    {
        return [
            'kodepenerimaan' => 'kode penerimaan',
            'keterangan' => 'keterangan',
            'formatnama' => 'format bukti',
            'statusaktifnama' => 'status aktif',
            'coadebetKeterangan' => 'coa debet',
            'coakreditKeterangan' => 'coa kredit',
            'coapostingdebetKeterangan' => 'coa posting debet',
            'coapostingkreditKeterangan' => 'coa posting kredit',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'kodepenerimaan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'formatnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktifnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coadebetKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coakreditKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coapostingdebetKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coapostingkreditKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
