<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DateAllowedOrderanTrucking;
use App\Rules\OrderanTruckingValidasijob2040;
use App\Rules\OrderanTruckingValidasijob2x20;
use App\Rules\OrderanTruckingValidasinocont2x20;
use App\Rules\OrderanTruckingValidasinoseal2x20;
use App\Rules\OrderanTruckingNoSeal;
use Illuminate\Validation\Rule;
use App\Rules\ExistContainer;
use App\Rules\ExistAgen;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistPelanggan;
use App\Rules\ExistTarifRincian;
use App\Models\Parameter;
use App\Rules\ValidasiDestroyOrderanTrucking;
use App\Rules\validationTarifOrderemkl;
use Illuminate\Support\Facades\DB;


class UpdateOrderanTruckingRequest extends FormRequest
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
    public $queryimport;
    public function rules()
    {
        $query = DB::table('orderantrucking')->from(DB::raw("orderantrucking with (readuncommitted)"))
            ->select(
                'tglbukti',
                'nobukti',
                'statusjeniskendaraan'
            )
            ->where('id', $this->id)
            ->first();
        $jenisTangki = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        if ($query->statusjeniskendaraan == $jenisTangki->id) {
            $rules = [
                'id' => [new DateAllowedOrderanTrucking(), new ValidasiDestroyOrderanTrucking()],
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y'),
                    Rule::in(date('d-m-Y', strtotime($query->tglbukti))),
                ],
                'nobukti' => [
                    Rule::in($query->nobukti),
                ],
            ];

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
            $rule = array_merge(
                $rules,
                $rulesagen_id,
                $rulespelanggan_id,
            );
        } else {

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
                // $queryjenisimport = DB::table('jenisorder')->from(DB::raw("jenisorder with (readuncommitted)"))
                //     ->select(
                //         'id',
                //     )
                //     ->where('id', $queryimport->text)
                //     ->first();

                // if (isset($queryjenisimport)) {
                //     $kondisi = false;
                // } else {
                //     $kondisi = true;
                // }
                $kondisi = true;
            } else {
                $kondisi = false;
            }

            $this->queryimport = $queryimport;
            $requiredSeal = Rule::requiredIf(function () {
                $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
                if ($this->jenisorder_id == $this->queryimport->text) {
                    return false;
                } else {
                    if ($cabang != 'MEDAN') {
                        return true;
                    }else{
                        return false;
                    }
                }
            });
            if ($this->container_id == $queryukuran->text) {
                $kondisiukuran = true;
            } else {
                $kondisiukuran = false;
            }
            $requiredGandengan =  Rule::requiredIf(function () {
                $idCabang = (new Parameter())->cekText('ID CABANG', 'ID CABANG');
                if ($idCabang != 2) {
                    return false;
                }

                $cekTrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as sp with (readuncommitted)"))
                    ->select('trado.statusgerobak')
                    ->join(DB::raw("trado with (readuncommitted)"), 'sp.trado_id', 'trado.id')
                    ->where('sp.jobtrucking', $this->nobukti)->first();
                $idgerobak = (new Parameter())->cekId('STATUS GEROBAK', 'STATUS GEROBAK', 'GEROBAK');
                if ($cekTrip->statusgerobak == $idgerobak) {
                    return false;
                } else {
                    return true;
                }
            });

            $rules = [
                'id' => [new DateAllowedOrderanTrucking(), new ValidasiDestroyOrderanTrucking()],
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y'),
                    Rule::in(date('d-m-Y', strtotime($query->tglbukti))),
                ],
                'nobukti' => [
                    Rule::in($query->nobukti),
                ],
                // 'statuslangsir' => ['required', Rule::in($statuslangsir)],
                // 'statusperalihan' => ['required', Rule::in($statusperalihan)],
                'nojobemkl' => [new OrderanTruckingValidasijob2040()],
                'nojobemkl2' => [new OrderanTruckingValidasijob2x20()],
                'gandengan' => $requiredGandengan,
                'nocont' => 'required',
                'noseal' => [$requiredSeal],
                'nocont2' => [new OrderanTruckingValidasinocont2x20()],
                'noseal2' => [new OrderanTruckingValidasinoseal2x20($kondisi, $kondisiukuran)],
            ];


            $container_id = $this->container_id;
            // dd($container_id);

            $rulescontainer_id = [];
            if ($container_id != '' && $this->container != '') {
                $rulescontainer_id = [
                    'container' => [
                        new ExistContainer(),
                        // new validationTarifOrderemkl()
                    ]
                ];
            } else if ($container_id == '' && $this->container == '') {
                $rulescontainer_id = [
                    'container' => [
                        'required',
                        new ExistContainer(),
                        // new validationTarifOrderemkl()
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
                                // new validationTarifOrderemkl()
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
                        // new validationTarifOrderemkl()
                    ]
                ];
            } else {
                $rulescontainer_id = [
                    'container' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistContainer(),
                        // new validationTarifOrderemkl()
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
                // $rulestarifrincian_id,
            );
        }
        return $rule;
    }

    public function attributes()
    {
        return [
            'nojobemkl' => 'no job emkl',
            'nojobemkl2' => 'noj job emkl ke-2',
            'nocont' => 'no container',
            'noseal' => 'no seal',
            'nocont2' => 'no container ke-2',
            'noseal2' => 'no seal ke-2',
            'statuslangsir' => 'status langsir',
            'statusperalihan' => 'status peralihan',
            'jenisorder' => 'jenis order',
            'agen' => 'customer'
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
