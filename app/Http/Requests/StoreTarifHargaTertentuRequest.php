<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ExistContainer;
use App\Rules\ExistTarif;
use Illuminate\Foundation\Http\FormRequest;

class StoreTarifHargaTertentuRequest extends FormRequest
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

        $container_id = $this->container_id;
        // dd($container_id);

        $rulescontainer_id = [];
        if ($container_id != '' && $this->container != '') {
            $rulescontainer_id = [
                'container' => [
                    new ExistContainer(),
                ]
            ];
        } else if ($container_id == '' && $this->container == '') {
            $rulescontainer_id = [
                'container' => [
                    'required',
                    new ExistContainer(),
                ]
            ];
        } else if ($container_id != null) {
            if ($container_id == 0) {

                $rulescontainer_id = [
                    'container_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistContainer(),

                    ]

                ];
            } else {
                if ($this->container == '') {

                    $rulescontainer_id = [
                        'container' => [
                            'required',
                            new ExistContainer(),
                        ]
                    ];
                }
            }
        } else if ($container_id == null && ($this->container != '' || $this->container != 0)) {
            $rulescontainer_id = [
                'container' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistContainer(),
                ]
            ];
        } else {
            $rulescontainer_id = [
                'container' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistContainer(),
                ]
            ];
        }
        // 

        $tarif_id = $this->tarif_id;
        // dd($tarif_id);

        $rulestarif_id = [];
        if ($tarif_id != '' && $this->tarif != '') {
            $rulestarif_id = [
                'tarif_id' => [
                    new ExistTarif(),
                ]
            ];
        } else if ($tarif_id == '' && $this->tarif == '') {
            $rulestarif_id = [
                'tarif_id' => [
                    'required',
                    new ExistTarif(),
                ]
            ];
        } else if ($tarif_id != null) {
            if ($tarif_id == 0) {

                $rulestarif_id = [
                    'tarif_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistTarif(),

                    ]

                ];
            } else {
                if ($this->tarif == '') {

                    $rulestarif_id = [
                        'tarif_id' => [
                            'required',
                            new ExistTarif(),
                        ]
                    ];
                }
            }
        } else if ($tarif_id == null && ($this->tarif != '' || $this->tarif != 0)) {
            $rulestarif_id = [
                'tarif_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistTarif(),
                ]
            ];
        } else {
            $rulestarif_id = [
                'tarif_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistTarif(),
                ]
            ];
        }

        $rules = [
            'tujuanbongkar' => 'required',
            'lokasidooring' => 'required',
            'shipper' => 'required',
            'nominal' => 'required',
        ];


        $rule = array_merge(
            $rules,
            $rulescontainer_id,
            $rulestarif_id,
        );

        return $rule;
    }
    public function attributes()
    {
        return [
            'tujuanbongkar' => 'tujuan bongkar',
            'lokasidooring' => 'lokasi dooring',
            'shipper' => 'shipper',
            'nominal' => 'nominal',
            'tarif' => 'tujuan',
            'cabang' => 'cabang',
            'container' => 'ukuran container',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tujuanbongkar.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'lokasidooring.required' => 'Tujuan ' . $controller->geterror('WI')->keterangan,
            'shipper.required' => 'Tujuan ' . $controller->geterror('WI')->keterangan,
            'nominal.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,

        ];
    }
}
