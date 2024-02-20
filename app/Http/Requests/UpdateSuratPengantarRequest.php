<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Models\Parameter;
use App\Models\SuratPengantar;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Rules\ExistContainer;
use App\Rules\ExistOrderanTrucking;
use App\Rules\ExistTrado;
use App\Rules\ExistSupir;
use App\Rules\ExistGandengan;
use App\Rules\ExistDari;
use App\Rules\ExistSampai;
use App\Rules\ExistStatusContainer;
use App\Rules\ExistPelanggan;
use App\Rules\ExistAgen;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistNominalUpahSupir;
use App\Rules\ExistTarifRincian;
use App\Rules\ExistUpahSupirRincianSuratPengantar;
use App\Rules\ValidasiKotaUpahZona;

class UpdateSuratPengantarRequest extends FormRequest
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

        $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'tglbukti',
                'nobukti',
                'statusapprovaleditsuratpengantar'
            )
            ->where('id', $this->id)
            ->first();


        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS LONGTRIP', 'STATUS LONGTRIP');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statuslongtrip[] = $item['id'];
        }
        $data = $parameter->getcombodata('STATUS PERALIHAN', 'STATUS PERALIHAN');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusperalihan[] = $item['id'];
        }
        $data = $parameter->getcombodata('STATUS BATAL MUAT', 'STATUS BATAL MUAT');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusbatalmuat[] = $item['id'];
        }
        $data = $parameter->getcombodata('STATUS GUDANG SAMA', 'STATUS GUDANG SAMA');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $statusgudangsama[] = $item['id'];
        }

        $dataUpahZona = $parameter->getcombodata('STATUS UPAH ZONA', 'STATUS UPAH ZONA');
        $dataUpahZona = json_decode($dataUpahZona, true);
        foreach ($dataUpahZona as $item) {
            $statusUpahZona[] = $item['id'];
        }

        $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
        $getUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
        $getPeralihan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PERALIHAN')->where('text', 'PERALIHAN')->first();


        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
                Rule::in(date('d-m-Y', strtotime($query->tglbukti))),
            ],
            'nobukti' => [
                Rule::in($query->nobukti),
            ],
            // "lokasibongkarmuat" => "required",
            'statuslongtrip' => ['required', Rule::in($statuslongtrip)],
            'statusperalihan' => ['required', Rule::in($statusperalihan)],
            'statusbatalmuat' => ['required', Rule::in($statusbatalmuat)],
            'statusgudangsama' => ['required', Rule::in($statusgudangsama)],
            'nosp' => 'required',
            'upah' => ['required', new ExistNominalUpahSupir()],
            'statusupahzona' => ['required', Rule::in($statusUpahZona)],
        ];

        $rulesStatusPeralihan = [];
        if (request()->statusperalihan == $getPeralihan->id) {
            $rulesStatusPeralihan = [

                'persentaseperalihan' => ['numeric', 'gt:0']
            ];
        }
        $jobtrucking = $this->jobtrucking;
        $rulesjobtrucking = [];
        if ($jobtrucking != '' && $this->jobtrucking != '') {
            $rulesjobtrucking = [
                'jobtrucking' => [
                    new ExistOrderanTrucking(),
                ]
            ];
        } else if ($jobtrucking != null) {
            if ($jobtrucking == 0) {

                $rulesjobtrucking = [
                    'jobtrucking' => [
                        'required',
                        new ExistOrderanTrucking(),

                    ]

                ];
            } else {
                if ($this->jobtrucking == '') {

                    $rulesjobtrucking = [
                        'jobtrucking' => [
                            'required',
                            new ExistOrderanTrucking(),
                        ]
                    ];
                }
            }
        } else if ($jobtrucking == null && $this->jobtrucking != '') {

            $rulesjobtrucking = [
                'jobtrucking' => [
                    'required',
                    new ExistOrderanTrucking(),
                ]
            ];
        } else {
            $rulesjobtrucking = [
                'jobtrucking' => [
                    'required',
                    new ExistOrderanTrucking(),
                ]
            ];
        }


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

        $trado_id = $this->trado_id;
        $rulestrado_id = [];
        if ($trado_id != '' && $this->trado != '') {
            $rulestrado_id = [
                'trado' => [
                    new ExistTrado(),
                ]
            ];
        } else if ($trado_id != null) {
            if ($trado_id == 0) {

                $rulestrado_id = [
                    'trado_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistTrado(),

                    ]

                ];
            } else {
                if ($this->trado == '') {

                    $rulestrado_id = [
                        'trado' => [
                            'required',
                            new ExistTrado(),
                        ]
                    ];
                }
            }
        } else if ($trado_id == null && $this->trado != '') {

            $rulestrado_id = [
                'trado_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistTrado(),
                ]
            ];
        } else {
            $rulestrado_id = [
                'trado' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistTrado(),
                ]
            ];
        }

        $supir_id = $this->supir_id;
        $rulessupir_id = [];
        if ($supir_id != '' && $this->supir != '') {
            $rulessupir_id = [
                'supir' => [
                    new ExistSupir(),
                ]
            ];
        } else if ($supir_id != null) {
            if ($supir_id == 0) {

                $rulessupir_id = [
                    'supir_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistSupir(),

                    ]

                ];
            } else {
                if ($this->supir == '') {

                    $rulessupir_id = [
                        'supir' => [
                            'required',
                            new ExistSupir(),
                        ]
                    ];
                }
            }
        } else if ($supir_id == null && $this->supir != '') {

            $rulessupir_id = [
                'supir_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistSupir(),
                ]
            ];
        } else {
            $rulessupir_id = [
                'supir' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistSupir(),
                ]
            ];
        }

        $gandengan_id = $this->gandengan_id;
        $rulesgandengan_id = [];
        if ($gandengan_id != '' && $this->gandengan != '') {
            $rulesgandengan_id = [
                'gandengan' => [
                    new ExistGandengan(),
                ]
            ];
        } else if ($gandengan_id != null) {
            if ($gandengan_id == 0) {

                $rulesgandengan_id = [
                    'gandengan_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistGandengan(),

                    ]

                ];
            } else {
                if ($this->gandengan == '') {

                    $rulesgandengan_id = [
                        'gandengan' => [
                            'required',
                            new ExistGandengan(),
                        ]
                    ];
                }
            }
        } else if ($gandengan_id == null && $this->gandengan != '') {

            $rulesgandengan_id = [
                'gandengan_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistGandengan(),
                ]
            ];
        } else {
            $rulesgandengan_id = [
                'gandengan' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistGandengan(),
                ]
            ];
        }

        $dari_id = $this->dari_id;
        $rulesdari_id = [];
        if ($dari_id != '' && $this->dari != '') {
            $rulesdari_id = [
                'dari' => [
                    new ExistDari(),
                ]
            ];
        } else if ($dari_id != null) {
            if ($dari_id == 0) {

                $rulesdari_id = [
                    'dari_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistDari(),

                    ]

                ];
            } else {
                if ($this->dari == '') {

                    $rulesdari_id = [
                        'dari' => [
                            'required',
                            new ExistDari(),
                        ]
                    ];
                }
            }
        } else if ($dari_id == null && $this->dari != '') {

            $rulesdari_id = [
                'dari_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistDari(),
                ]
            ];
        } else {
            $rulesdari_id = [
                'dari' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistDari(),
                ]
            ];
        }

        $sampai_id = $this->sampai_id;
        $rulessampai_id = [];
        if ($sampai_id != '' && $this->sampai != '') {
            $rulessampai_id = [
                'sampai' => [
                    new ExistSampai(),
                ]
            ];
        } else if ($sampai_id != null) {
            if ($sampai_id == 0) {

                $rulessampai_id = [
                    'sampai_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistSampai(),

                    ]

                ];
            } else {
                if ($this->sampai == '') {

                    $rulessampai_id = [
                        'sampai' => [
                            'required',
                            new ExistSampai(),
                        ]
                    ];
                }
            }
        } else if ($sampai_id == null && $this->sampai != '') {

            $rulessampai_id = [
                'sampai_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistSampai(),
                ]
            ];
        } else {
            $rulessampai_id = [
                'sampai' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistSampai(),
                ]
            ];
        }

        $statuscontainer_id = $this->statuscontainer_id;
        $rulesstatuscontainer_id = [];
        if ($statuscontainer_id != '' && $this->statuscontainer != '') {
            $rulesstatuscontainer_id = [
                'statuscontainer' => [
                    new ExistStatusContainer(),
                ]
            ];
        } else if ($statuscontainer_id != null) {
            if ($statuscontainer_id == 0) {

                $rulesstatuscontainer_id = [
                    'statuscontainer_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistStatusContainer(),

                    ]

                ];
            } else {
                if ($this->statuscontainer == '') {

                    $rulesstatuscontainer_id = [
                        'statuscontainer' => [
                            'required',
                            new ExistStatusContainer(),
                        ]
                    ];
                }
            }
        } else if ($statuscontainer_id == null && $this->statuscontainer != '') {

            $rulesstatuscontainer_id = [
                'statuscontainer_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistStatusContainer(),
                ]
            ];
        } else {
            $rulesstatuscontainer_id = [
                'statuscontainer' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistStatusContainer(),
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
                        'required_if:statusupahzona,=,' . $getBukanUpahZona->id,
                        'numeric',
                        'min:1',
                        new ExistTarifRincian(),
                        new ValidasiKotaUpahZona($getBukanUpahZona->id)
                    ]

                ];
            } else {
                if ($this->tarifrincian == '') {

                    $rulestarifrincian_id = [
                        'tarifrincian' => [
                            'required_if:statusupahzona,=,' . $getBukanUpahZona->id,
                            new ValidasiKotaUpahZona($getBukanUpahZona->id)
                        ]
                    ];
                }
            }
        } else if ($tarifrincian_id == null && $this->tarifrincian != '') {

            $rulestarifrincian_id = [
                'tarifrincian_id' => [
                    'required_if:statusupahzona,=,' . $getBukanUpahZona->id,
                    'numeric',
                    'min:1',
                    new ExistTarifRincian(),
                    new ValidasiKotaUpahZona($getBukanUpahZona->id)
                ]
            ];
        } else {
            $rulestarifrincian_id = [
                'tarifrincian' => [
                    'required_if:statusupahzona,=,' . $getBukanUpahZona->id,
                    new ValidasiKotaUpahZona($getBukanUpahZona->id)
                ]
            ];
        }

        if ((request()->dari_id == 1 && request()->sampai_id == 103) || (request()->dari_id == 103 && request()->sampai_id == 1) || (request()->statuslongtrip == 65)) {
            $rulestarifrincian_id = [
                'tarifrincian' => [
                    'nullable',
                    new ExistTarifRincian(),
                ]
            ];
        }

        $upah_id = $this->upah_id;
        $rulesUpah_id = [];
        if ($upah_id != null) {
            $rulesUpah_id = [
                'upah_id' => ['required', 'numeric', 'min:1']
            ];
        } else if ($upah_id == null && request()->upah != '') {
            $rulesUpah_id = [
                'upah_id' => ['required', 'numeric', 'min:1']
            ];
        }
        $relatedRequests = [
            StoreSuratPengantarBiayaTambahanRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulescontainer_id,
                $rulesjobtrucking,
                $rulestrado_id,
                $rulessupir_id,
                // $rulesgandengan_id,
                $rulesdari_id,
                $rulessampai_id,
                $rulesstatuscontainer_id,
                $rulespelanggan_id,
                $rulesagen_id,
                $rulesjenisorder_id,
                $rulestarifrincian_id,
                $rulesUpah_id,
                $rulesStatusPeralihan
            );
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'jobtrucking' => 'job trucking',
            'tglbukti' => 'tgl transaksi',
            'statusperalihan' => 'status peralihan',
            'statuscontainer' => 'status full/empty',
            'statuslongtrip' => 'status longtrip',
            'statusgudangsama' => 'status gudangsama',
            'tarifrincian' => 'tujuan tarif',
            'jenisorder' => 'jenis orderan',
            'sampai' => 'tujuan',
            "lokasibongkarmuat" => "lokasi bongkar/muat",
            // 'qtyton' => 'QTY ton',
            'statusbatalmuat' => 'status batal muat',
            'agen' => 'customer'


        ];
    }

    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tarifrincian.required_if' => app(ErrorController::class)->geterror('WI')->keterangan,
            'statusapprovaleditsuratpengantar.required' => app(ErrorController::class)->geterror('BAED')->keterangan,
        ];
    }
}
