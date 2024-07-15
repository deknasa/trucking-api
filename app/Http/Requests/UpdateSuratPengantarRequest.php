<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Models\Parameter;
use App\Models\ReminderOli;
use App\Models\SuratPengantar;
use App\Rules\DestroySuratPengantar;
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
use App\Rules\ValidasiAgenTripGudangSama;
use App\Rules\validasiBatasLuarKota;
use App\Rules\ValidasiContainerTripGudangSama;
use App\Rules\ValidasiGajiKenekSP;
use App\Rules\ValidasiJenisOrderGudangsama;
use App\Rules\ValidasiJenisOrderLongtrip;
use App\Rules\ValidasiKotaUpahZona;
use App\Rules\ValidasiLongtripGudangsama;
use App\Rules\validasiNominalUpahSupirTangkiTrip;
use App\Rules\ValidasiPelangganTripGudangSama;
use App\Rules\ValidasiReminderOli;
use App\Rules\ValidasiReminderOliGardan;
use App\Rules\ValidasiReminderOliPersneling;
use App\Rules\ValidasiReminderSaringanHawa;
use App\Rules\validasiStatusContainerLongtrip;
use App\Rules\validasiStatusJenisKendaraan;
use App\Rules\ValidasiTradoTripGudangSama;
use App\Rules\ValidasiTripGudangSama;
use App\Rules\validasiTripTangkiEditTrip;
use Illuminate\Support\Facades\Schema;

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
        if (request()->statusjeniskendaraan == $jenisTangki->id) {
            $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(
                'tglbukti',
                'nobukti',
                'statusapprovaleditsuratpengantar',
                'trado_id',
                'jarak'
            )
            ->where('id', $this->id)
            ->first();
            $rules = [
                'id' => new DestroySuratPengantar(),
                'tglbukti' => [
                    'required', 'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y'),
                    Rule::in(date('d-m-Y', strtotime($query->tglbukti))),
                ],
                "agen" => ["required"],
                "dari" => ["required"],
                'nobukti' => [
                    Rule::in($query->nobukti),
                ],
                "tarifrincian" => ['required'],
                "statusjeniskendaraan" => ["required", new validasiStatusJenisKendaraan()],
                'nosp' => 'required',
                'omset' => ['required','numeric','gt:0'],
                'gajisupir' => ['required','numeric','gt:0'],
                'upah' => ['required', new validasiNominalUpahSupirTangkiTrip()],
                'qtyton' => ['required','numeric'],
                "triptangki" => ["required", new validasiTripTangkiEditTrip()],
            ];

            $tempreminderoli = '##tempreminderoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            if (request()->trado_id != '') {

                // 
                Schema::create($tempreminderoli, function ($table) {
                    $table->id();
                    $table->longText('nopol')->nullable();
                    $table->integer('trado_id')->nullable();
                    $table->date('tanggal')->nullable();
                    $table->string('status', 100)->nullable();
                    $table->double('km', 15, 2)->nullable();
                    $table->double('kmperjalanan', 15, 2)->nullable();
                    $table->integer('statusbatas')->nullable();
                });
                DB::table($tempreminderoli)->insertUsing([
                    'nopol',
                    'trado_id',
                    'tanggal',
                    'status',
                    'km',
                    'kmperjalanan',
                    'statusbatas',
                ], (new ReminderOli())->getdata2(request()->trado_id));
                $table = DB::table($tempreminderoli)->get();


                for ($i = 1; $i <= count($table); $i++) {
                    $getJarak = DB::table("upahsupirtangki")->from(DB::raw("upahsupirtangki with (readuncommitted)"))->where('id', request()->upah_id)->first();
                    $jarak = $getJarak->jarak ?? 0;
                  
                    if ($query->trado_id == request()->trado_id) {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak - $query->jarak) where id='$i'"));
                    } else {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak) where id='$i'"));
                    }
                }


                $statusapproval = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.grp', 'STATUS APPROVAL')
                    ->where('a.subgrp', 'STATUS APPROVAL')
                    ->where('a.text', 'APPROVAL')
                    ->first()->id ?? 0;



                // pergantian oli mesin
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Mesin')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolimesin', $statusapproval)
                        ->whereraw("tglbatasreminderolimesin>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolimesin = true;
                            $keteranganvalidasireminderolimesin = "OLI MESIN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolimesin = false;
                            $keteranganvalidasireminderolimesin = "";
                        }
                    } else {
                        $validasireminderolimesin = false;
                        $keteranganvalidasireminderolimesin = "";
                    }
                } else {
                    $validasireminderolimesin = false;
                    $keteranganvalidasireminderolimesin = "";
                }

                // pergantian oli persneling
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Persneling')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolipersneling', $statusapproval)
                        ->whereraw("tglbatasreminderolipersneling>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolipersneling = true;
                            $keteranganvalidasireminderolipersneling = "OLI PERSNELING SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolipersneling = false;
                            $keteranganvalidasireminderolipersneling = "";
                        }
                    } else {
                        $validasireminderolipersneling = false;
                        $keteranganvalidasireminderolipersneling = "";
                    }
                } else {
                    $validasireminderolipersneling = false;
                    $keteranganvalidasireminderolipersneling = "";
                }

                // pergantian oli GARDAN
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Gardan')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderoligardan', $statusapproval)
                        ->whereraw("tglbatasreminderoligardan>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderoligardan = true;
                            $keteranganvalidasireminderoligardan = "OLI GARDAN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderoligardan = false;
                            $keteranganvalidasireminderoligardan = "";
                        }
                    } else {
                        $validasireminderoligardan = false;
                        $keteranganvalidasireminderoligardan = "";
                    }
                } else {
                    $validasireminderoligardan = false;
                    $keteranganvalidasireminderoligardan = "";
                }

                // pergantian SARINGAN HAWA
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Saringan Hawa')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalremindersaringanhawa', $statusapproval)
                        ->whereraw("tglbatasremindersaringanhawa>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasiremindersaringanhawa = true;
                            $keteranganvalidasiremindersaringanhawa = "SARINGAN HAWA SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasiremindersaringanhawa = false;
                            $keteranganvalidasiremindersaringanhawa = "";
                        }
                    } else {
                        $validasiremindersaringanhawa = false;
                        $keteranganvalidasiremindersaringanhawa = "";
                    }
                } else {
                    $validasiremindersaringanhawa = false;
                    $keteranganvalidasiremindersaringanhawa = "";
                }
            }
            $trado_id = $this->trado_id;
            $rulestrado_id = [];
            if ($trado_id != '' && $this->trado != '') {
                $rulestrado_id = [
                    'trado' => [
                        new ExistTrado()
                    ],
                    'trado_id' => [
                        new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa),
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
                                new ExistTrado(), new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
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
                        new ExistTrado(), new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
                    ]
                ];
            }

            
            $supir_id = $this->supir_id;
            $rulessupir_id = [];
            if ($supir_id != '' && $this->supir != '') {
                $rulessupir_id = [
                    'supir' => [
                        new ExistSupir(),
                    ],
                    'supir_id' => [
                        new validasiBatasLuarKota()
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
            $pelanggan_id = $this->pelanggan_id;
            $rulespelanggan_id = [];

            if ($this->jobtrucking != '') {
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
            }

            $agen_id = $this->agen_id;
            $rulesagen_id = [];
            if ($this->jobtrucking != '') {

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
                    $rulestrado_id,
                    $rulessupir_id,
                    // $rulesgandengan_id,
                    $rulesdari_id,
                    $rulessampai_id,
                    $rulespelanggan_id,
                    $rulesagen_id,
                    $rulesUpah_id,
                );
            }
        } else {

            $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'tglbukti',
                    'nobukti',
                    'statusapprovaleditsuratpengantar',
                    'trado_id',
                    'jarak'
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

            $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
            $getUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
            $getPeralihan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PERALIHAN')->where('text', 'PERALIHAN')->first();
            $getGudangSama = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GUDANG SAMA')->where('text', 'GUDANG SAMA')->first();

            $dataTripAsal = [];
            if (request()->statusgudangsama == $getGudangSama->id) {
                if (request()->nobukti_tripasal != '') {
                    $getDataTripAsal = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))->select('upah_id', 'agen_id', 'pelanggan_id', 'container_id', 'trado_id')->where('nobukti', request()->nobukti_tripasal)->first();
                    $dataTripAsal = json_decode(json_encode($getDataTripAsal), true);
                }
            }

            $ruleTripAsal = Rule::requiredIf(function () {
                $getGudangSama = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS GUDANG SAMA')->where('text', 'GUDANG SAMA')->first();
                $getLongtrip = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LONGTRIP')->where('text', 'LONGTRIP')->first();
                if (request()->statusgudangsama ==  $getGudangSama->id) {
                    if ((request()->statuscontainer_id == 1 && request()->jenisorder_id == 1) || (request()->statuscontainer_id == 1 && request()->jenisorder_id == 4)) {
                        return true;
                    }
                }
                if (request()->statuslongtrip ==  $getLongtrip->id) {
                    return true;
                }
                return false;
            });

            $rules = [
                'id' => new DestroySuratPengantar(),
                'tglbukti' => [
                    'required', 'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y'),
                    Rule::in(date('d-m-Y', strtotime($query->tglbukti))),
                ],
                'nobukti' => [
                    Rule::in($query->nobukti),
                ],
                "nobukti_tripasal" => $ruleTripAsal,
                // "lokasibongkarmuat" => "required",
                'statuslongtrip' => ['required', Rule::in($statuslongtrip), new validasiStatusContainerLongtrip()],
                'statusperalihan' => ['required', Rule::in($statusperalihan)],
                'statusbatalmuat' => ['required', Rule::in($statusbatalmuat)],
                'statusgudangsama' => ['required', Rule::in($statusgudangsama), new ValidasiLongtripGudangsama()],
                'nosp' => 'required',
                'upah' => ['required', new ExistNominalUpahSupir(), new ValidasiTripGudangSama($dataTripAsal)],
                'gajisupir' => new ValidasiGajiKenekSP('gajisupir'),
                'gajikenek' => new ValidasiGajiKenekSP('gajikenek')
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
            }
            // else if ($jobtrucking != null) {
            //     if ($jobtrucking == 0) {

            //         $rulesjobtrucking = [
            //             'jobtrucking' => [
            //                 'required',
            //                 new ExistOrderanTrucking(),

            //             ]

            //         ];
            //     } else {
            //         if ($this->jobtrucking == '') {

            //             $rulesjobtrucking = [
            //                 'jobtrucking' => [
            //                     'required',
            //                     new ExistOrderanTrucking(),
            //                 ]
            //             ];
            //         }
            //     }
            // } else if ($jobtrucking == null && $this->jobtrucking != '') {

            //     $rulesjobtrucking = [
            //         'jobtrucking' => [
            //             'required',
            //             new ExistOrderanTrucking(),
            //         ]
            //     ];
            // } else {
            //     $rulesjobtrucking = [
            //         'jobtrucking' => [
            //             'required',
            //             new ExistOrderanTrucking(),
            //         ]
            //     ];
            // }


            $container_id = $this->container_id;
            // dd($container_id);

            $rulescontainer_id = [];

            if ($this->jobtrucking != '') {
                if ($container_id != '' && $this->container != '') {
                    $rulescontainer_id = [
                        'container' => [
                            new ExistContainer(),
                            new ValidasiContainerTripGudangSama($dataTripAsal)
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
            }
            $tempreminderoli = '##tempreminderoli' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            if (request()->trado_id != '') {

                // 
                Schema::create($tempreminderoli, function ($table) {
                    $table->id();
                    $table->longText('nopol')->nullable();
                    $table->integer('trado_id')->nullable();
                    $table->date('tanggal')->nullable();
                    $table->string('status', 100)->nullable();
                    $table->double('km', 15, 2)->nullable();
                    $table->double('kmperjalanan', 15, 2)->nullable();
                    $table->integer('statusbatas')->nullable();
                });
                DB::table($tempreminderoli)->insertUsing([
                    'nopol',
                    'trado_id',
                    'tanggal',
                    'status',
                    'km',
                    'kmperjalanan',
                    'statusbatas',
                ], (new ReminderOli())->getdata2(request()->trado_id));
                $table = DB::table($tempreminderoli)->get();


                for ($i = 1; $i <= count($table); $i++) {
                    $getJarak = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', request()->upah_id)->first();
                    $jarak = 0;
                    if (request()->statuscontainer_id != '') {
                        if (request()->statuscontainer_id == 3) {
                            $jarak = $getJarak->jarakfullempty ?? 0;
                        } else {
                            $jarak = $getJarak->jarak ?? 0;
                        }
                    }
                    if ($query->trado_id == request()->trado_id) {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak - $query->jarak) where id='$i'"));
                    } else {
                        DB::update(DB::raw("UPDATE " . $tempreminderoli . " SET kmperjalanan=(kmperjalanan + $jarak) where id='$i'"));
                    }
                }


                $statusapproval = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )
                    ->where('a.grp', 'STATUS APPROVAL')
                    ->where('a.subgrp', 'STATUS APPROVAL')
                    ->where('a.text', 'APPROVAL')
                    ->first()->id ?? 0;



                // pergantian oli mesin
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Mesin')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolimesin', $statusapproval)
                        ->whereraw("tglbatasreminderolimesin>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolimesin = true;
                            $keteranganvalidasireminderolimesin = "OLI MESIN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolimesin = false;
                            $keteranganvalidasireminderolimesin = "";
                        }
                    } else {
                        $validasireminderolimesin = false;
                        $keteranganvalidasireminderolimesin = "";
                    }
                } else {
                    $validasireminderolimesin = false;
                    $keteranganvalidasireminderolimesin = "";
                }

                // pergantian oli persneling
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Persneling')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderolipersneling', $statusapproval)
                        ->whereraw("tglbatasreminderolipersneling>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderolipersneling = true;
                            $keteranganvalidasireminderolipersneling = "OLI PERSNELING SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderolipersneling = false;
                            $keteranganvalidasireminderolipersneling = "";
                        }
                    } else {
                        $validasireminderolipersneling = false;
                        $keteranganvalidasireminderolipersneling = "";
                    }
                } else {
                    $validasireminderolipersneling = false;
                    $keteranganvalidasireminderolipersneling = "";
                }

                // pergantian oli GARDAN
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Oli Gardan')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalreminderoligardan', $statusapproval)
                        ->whereraw("tglbatasreminderoligardan>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasireminderoligardan = true;
                            $keteranganvalidasireminderoligardan = "OLI GARDAN SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasireminderoligardan = false;
                            $keteranganvalidasireminderoligardan = "";
                        }
                    } else {
                        $validasireminderoligardan = false;
                        $keteranganvalidasireminderoligardan = "";
                    }
                } else {
                    $validasireminderoligardan = false;
                    $keteranganvalidasireminderoligardan = "";
                }

                // pergantian SARINGAN HAWA
                $query = db::table($tempreminderoli)->from(db::raw($tempreminderoli . " a"))
                    ->select(
                        'a.km',
                        'a.kmperjalanan'
                    )
                    ->where('a.status', 'Penggantian Saringan Hawa')
                    ->first();

                if (isset($query)) {
                    $trado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
                        ->where('id', request()->trado_id)
                        ->where('statusapprovalremindersaringanhawa', $statusapproval)
                        ->whereraw("tglbatasremindersaringanhawa>=getdate()")
                        ->first();
                    if (!isset($trado)) {
                        if ($query->kmperjalanan >= $query->km) {
                            $validasiremindersaringanhawa = true;
                            $keteranganvalidasiremindersaringanhawa = "SARINGAN HAWA SUDAH MELEWATI BATAS YANG DITENTUKAN";
                        } else {
                            $validasiremindersaringanhawa = false;
                            $keteranganvalidasiremindersaringanhawa = "";
                        }
                    } else {
                        $validasiremindersaringanhawa = false;
                        $keteranganvalidasiremindersaringanhawa = "";
                    }
                } else {
                    $validasiremindersaringanhawa = false;
                    $keteranganvalidasiremindersaringanhawa = "";
                }
            }
            $trado_id = $this->trado_id;
            $rulestrado_id = [];
            if ($trado_id != '' && $this->trado != '') {
                $rulestrado_id = [
                    'trado' => [
                        new ExistTrado(), new ValidasiTradoTripGudangSama($dataTripAsal)
                    ],
                    'trado_id' => [
                        new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa),
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
                                new ExistTrado(), new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
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
                        new ExistTrado(), new ValidasiReminderOli($validasireminderolimesin, $keteranganvalidasireminderolimesin), new ValidasiReminderOliPersneling($validasireminderolipersneling, $keteranganvalidasireminderolipersneling), new ValidasiReminderOliGardan($validasireminderoligardan, $keteranganvalidasireminderoligardan), new ValidasiReminderSaringanHawa($validasiremindersaringanhawa, $keteranganvalidasiremindersaringanhawa)
                    ]
                ];
            }

            $supir_id = $this->supir_id;
            $rulessupir_id = [];
            if ($supir_id != '' && $this->supir != '') {
                $rulessupir_id = [
                    'supir' => [
                        new ExistSupir(),
                    ],
                    'supir_id' => [
                        new validasiBatasLuarKota()
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

            if ($this->jobtrucking != '') {
                if ($pelanggan_id != '' && $this->pelanggan != '') {
                    $rulespelanggan_id = [
                        'pelanggan' => [
                            new ExistPelanggan(),
                            new ValidasiPelangganTripGudangSama($dataTripAsal)
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
            }

            $agen_id = $this->agen_id;
            $rulesagen_id = [];
            if ($this->jobtrucking != '') {

                if ($agen_id != '' && $this->agen != '') {
                    $rulesagen_id = [
                        'agen' => [
                            new ExistAgen(),
                            new ValidasiAgenTripGudangSama($dataTripAsal)
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
            }

            $jenisorder_id = $this->jenisorder_id;
            $rulesjenisorder_id = [];

            if ($this->jobtrucking != '') {
                if ($jenisorder_id != '' && $this->jenisorder != '') {
                    $rulesjenisorder_id = [
                        'jenisorder' => [
                            new ExistJenisOrder(), new ValidasiJenisOrderGudangsama()
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
                            'numeric',
                            'min:1',
                            new ExistTarifRincian(),
                        ]

                    ];
                } else {
                    if ($this->tarifrincian == '') {

                        $rulestarifrincian_id = [
                            'tarifrincian' => [
                            ]
                        ];
                    }
                }
            } else if ($tarifrincian_id == null && $this->tarifrincian != '') {

                $rulestarifrincian_id = [
                    'tarifrincian_id' => [
                        'numeric',
                        'min:1',
                        new ExistTarifRincian(),
                    ]
                ];
            } else {
                $rulestarifrincian_id = [
                    'tarifrincian' => [
                    ]
                ];
            }

            if ((request()->dari_id == 1 && request()->sampai_id == 103) || (request()->dari_id == 103 && request()->sampai_id == 1) || (request()->statuslongtrip == 65) || (request()->statuslongtrip == 66 && request()->nobukti_tripasal != '')) {
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
            'nobukti_tripasal.required_if' => 'TRIP ASAL ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ];
    }
}
