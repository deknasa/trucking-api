<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\PengeluaranTrucking;
use Illuminate\Validation\Rule;


class UpdatePengeluaranTruckingRequest extends FormRequest
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
        if (request()->from == 'tas') {
            return [];
        }
        $parameter = new Parameter();
        $dataFormat = $parameter->getComboByGroup('PENGELUARAN TRUCKING');
        $dataFormat = json_decode($dataFormat, true);
        foreach ($dataFormat as $item) {
            $format[] = $item['id'];
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
        $pengeluaranTrucking = new PengeluaranTrucking();
        $getDataPengeluaranTrucking = $pengeluaranTrucking->findAll(request()->id);


        $rules =  [
            'kodepengeluaran' => ['required',Rule::in($getDataPengeluaranTrucking->kodepengeluaran),Rule::unique('pengeluarantrucking')->whereNotIn('id', [$this->id])],
            'format' => ['required', Rule::in($format)],
            'coadebetKeterangan' => 'required',
            'coakreditKeterangan' => 'required',
            'coapostingdebetKeterangan' => 'required',
            'coapostingkreditKeterangan' => 'required',
        ];

        $rule = array_merge(
            $rules,
            $rulesCoaDebet,
            $rulesCoaKredit,
            $rulesCoaPostingDebet,
            $rulesCoaPostingKredit
        );

        return $rule;
    }

    public function attributes()
    {
        return [
            'kodepengeluaran' => 'kode pengeluaran',
            'keterangan' => 'keterangan',
            'format' => 'format bukti',
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
            'kodepengeluaran.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'format.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coadebetKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coakreditKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coapostingdebetKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coapostingkreditKeterangan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
