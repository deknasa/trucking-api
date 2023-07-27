<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\OrderanTruckingValidasijob2040;
use App\Rules\OrderanTruckingValidasijob2x20;
use App\Rules\OrderanTruckingValidasinocont2x20;
use App\Rules\OrderanTruckingValidasinoseal2x20;
use Illuminate\Validation\Rule;
use App\Rules\ExistContainer;
use App\Rules\ExistAgen;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistPelanggan;
use App\Rules\ExistTarifRincian;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Rules\OrderanTruckingNoSeal;


class StoreOrderanTruckingRequest extends FormRequest
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


        $queryimport = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
            )
            ->where('grp', 'JENIS ORDERAN IMPORT')
            ->where('subgrp', 'JENIS ORDERAN IMPORT')
            ->first();

        $queryukuran = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
            )
            ->where('grp', 'UKURANCONTAINER2X20')
            ->where('subgrp', 'UKURANCONTAINER2X20')
            ->first();

        if ($this->jenisorder_id == $queryimport->text) {
            $queryjenisimport = DB::table('jenisorder')->from(DB::raw("jenisorder with (readuncommitted)"))
                ->select(
                    'id',
                )
                ->where('id', $queryimport->text)
                ->first();


            if (isset($queryjenisimport)) {
                $kondisi = false;
            } else {
                $kondisi = true;
            }
        } else {
            $kondisi = true;
        }

   

        if ($this->container_id == $queryukuran->text) {
            $kondisiukuran = true;
        } else {
            $kondisiukuran = false;
        }



// dd($kondisiukuran);

        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS LANGSIR', 'STATUS LANGSIR');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statuslangsir[] = $item['id'];
        }

        $data = $parameter->getcombodata('STATUS PERALIHAN', 'STATUS PERALIHAN');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusperalihan[] = $item['id'];
        }

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            // 'statuslangsir' => ['required', Rule::in($statuslangsir)],
            // 'statusperalihan' => ['required', Rule::in($statusperalihan)],
            'nojobemkl' => [new OrderanTruckingValidasijob2040()],
            'nojobemkl2' => [new OrderanTruckingValidasijob2x20()],
            'nocont' => 'required',
            'noseal' => [new OrderanTruckingNoSeal($kondisi)],
            'nocont2' => [new OrderanTruckingValidasinocont2x20()],
            'noseal2' => [new OrderanTruckingValidasinoseal2x20($kondisi,$kondisiukuran)],
        ];


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

        $agen_id = $this->agen_id;
        $rulesagen_id = [];
        if ($agen_id != '' && $this->agen != '') {
            $rulesagen_id = [
                'agen' => [
                    new ExistAgen(),
                ]
            ];
        } else if ($agen_id != null) {
            if ($agen_id == 0) {

                $rulesagen_id = [
                    'agen_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistAgen(),

                    ]

                ];
            } else {
                if ($this->agen == '') {

                    $rulesagen_id = [
                        'agen' => [
                            'required',
                            new ExistAgen(),
                        ]
                    ];
                }
            }
        } else if ($agen_id == null && $this->agen != '') {

            $rulesagen_id = [
                'agen_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistAgen(),
                ]
            ];
        } else {
            $rulesagen_id = [
                'agen' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistAgen(),
                ]
            ];
        }

        $jenisorder_id = $this->jenisorder_id;
        $rulesjenisorder_id = [];
        if ($jenisorder_id != '' && $this->jenisorder != '') {
            $rulesjenisorder_id = [
                'jenisorder' => [
                    new ExistJenisOrder(),
                ]
            ];
        } else if ($jenisorder_id != null) {
            if ($jenisorder_id == 0) {

                $rulesjenisorder_id = [
                    'jenisorder_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistJenisOrder(),

                    ]

                ];
            } else {
                if ($this->jenisorder == '') {

                    $rulesjenisorder_id = [
                        'jenisorder' => [
                            'required',
                            new ExistJenisOrder(),
                        ]
                    ];
                }
            }
        } else if ($jenisorder_id == null && $this->jenisorder != '') {

            $rulesjenisorder_id = [
                'jenisorder_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistJenisOrder(),
                ]
            ];
        } else {
            $rulesjenisorder_id = [
                'jenisorder' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistJenisOrder(),
                ]
            ];
        }


        $pelanggan_id = $this->pelanggan_id;
        $rulespelanggan_id = [];
        if ($pelanggan_id != '' && $this->pelanggan != '') {
            $rulespelanggan_id = [
                'pelanggan' => [
                    new ExistPelanggan(),
                ]
            ];
        } else if ($pelanggan_id != null) {
            if ($pelanggan_id == 0) {

                $rulespelanggan_id = [
                    'pelanggan_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistPelanggan(),

                    ]

                ];
            } else {
                if ($this->pelanggan == '') {

                    $rulespelanggan_id = [
                        'pelanggan' => [
                            'required',
                            new ExistPelanggan(),
                        ]
                    ];
                }
            }
        } else if ($pelanggan_id == null && $this->pelanggan != '') {

            $rulespelanggan_id = [
                'pelanggan_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistPelanggan(),
                ]
            ];
        } else {
            $rulespelanggan_id = [
                'pelanggan' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistPelanggan(),
                ]
            ];
        }

        $tarifrincian_id = $this->tarifrincian_id;
        $rulestarifrincian_id = [];
        if ($tarifrincian_id != '' && $this->tarifrincian != '') {
            $rulestarifrincian_id = [
                'tarifrincian' => [
                    new ExistTarifRincian(),
                ]
            ];
        } else if ($tarifrincian_id != null) {
            if ($tarifrincian_id == 0) {

                $rulestarifrincian_id = [
                    'tarifrincian_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistTarifRincian(),

                    ]

                ];
            } else {
                if ($this->tarifrincian == '') {

                    $rulestarifrincian_id = [
                        'tarifrincian' => [
                            'required',
                            new ExistTarifRincian(),
                        ]
                    ];
                }
            }
        } else if ($tarifrincian_id == null && $this->tarifrincian != '') {

            $rulestarifrincian_id = [
                'tarifrincian_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistTarifRincian(),
                ]
            ];
        } else {
            $rulestarifrincian_id = [
                'tarifrincian' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistTarifRincian(),
                ]
            ];
        }


        $rule = array_merge(
            $rules,
            $rulescontainer_id,
            $rulesagen_id,
            $rulesjenisorder_id,
            $rulespelanggan_id,
            $rulestarifrincian_id,
        );

        return $rule;
    }






    public function attributes()
    {
        return [
            'nojobemkl' => 'no job emkl',
            'nojobemkl' => 'noj job emkl ke-2',
            'nocont' => 'no container',
            'noseal' => 'no seal',
            'nocont2' => 'no container ke-2',
            'noseal2' => 'no seal ke-2',
            'statuslangsir' => 'status langsir',
            'statusperalihan' => 'status peralihan',
            'jenisorder' => 'jenis order',
            'tarifrincian' => 'tujuan',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'nocont.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'tarifrincian_id.required' => 'Tujuan ' . $controller->geterror('WI')->keterangan,
            'tarifrincian.required' => 'Tujuan ' . $controller->geterror('WI')->keterangan,
            'noseal.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,

        ];
    }
}
