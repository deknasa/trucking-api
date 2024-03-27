<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\PelunasanHutangHeaderController;
use App\Models\AlatBayar;
use App\Models\PelunasanHutangHeader;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAlatBayar;
use App\Rules\ExistBank;
use App\Rules\ExistSupplier;
use App\Rules\ValidasiBankList;
use App\Rules\ValidasiDestroyHutangBayarHeader;
use App\Rules\ValidasiHutangList;
use App\Rules\ValidasiHutangPelunasan;
use App\Rules\ValidasiHutangPelunasanApproval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdatePelunasanHutangHeaderRequest extends FormRequest
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
    

        $jumlahdetail = $this->jumlahdetail ?? 0;

        $query=DB::table('pelunasanhutangheader')->from(
            DB::raw('pelunasanhutangheader a with (readuncommitted)')
        )
        ->select(
            'a.tglbukti',
            'b.namasupplier as supplier',
            'c.namabank as bank',
            'd.kodealatbayar as alatbayar',
        )
        ->leftJoin(DB::raw("supplier b with (readuncommitted)"), 'a.supplier_id', 'b.id')
        ->leftJoin(DB::raw("bank c with (readuncommitted)"), 'a.bank_id', 'c.id')
        ->leftJoin(DB::raw("alatbayar d with (readuncommitted)"), 'a.alatbayar_id', 'd.id')
        ->where('a.id','=',$this->id)
        ->first();


        $rules = [
            'id' => [ new ValidasiDestroyHutangBayarHeader()],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'tglcair' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
        ];
        $relatedRequests = [
            UpdatePelunasanHutangDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

        $supplier_id = $this->supplier_id;
        $rulessupplier_id = [];
        if ($supplier_id != '' && $this->supplier != '') {
            $rulessupplier_id = [
                'supplier' => [
                    new ExistSupplier(),
                    new ValidasiHutangList($jumlahdetail),
                    Rule::in($query->supplier),
                    new ValidasiHutangPelunasanApproval(),
                    new ValidasiHutangPelunasan()

                ]
            ];
        } else if ($supplier_id != null) {
            if ($supplier_id == 0) {

                $rulessupplier_id = [
                    'supplier_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistSupplier(),

                    ]

                ];
            } else {
                if ($this->supplier == '') {

                    $rulessupplier_id = [
                        'supplier' => [
                            'required',
                            new ExistSupplier(),
                        ]
                    ];
                }
            }
        } else if ($supplier_id == null && $this->supplier != '') {

            $rulessupplier_id = [
                'supplier_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistSupplier(),
                ]
            ];
        } else {
            $rulessupplier_id = [
                'supplier' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistSupplier(),
                ]
            ];
        }

        $bank_id = $this->bank_id;
        $alatBayar = new AlatBayar();
        if ($bank_id != null && $bank_id != 0) {
            $getAlatBayar = $alatBayar->validateBankWithAlatbayar(request()->bank_id);
            $getAlatBayar = json_decode($getAlatBayar, true);
            $kondisialatbayar = true;
            // dd($getAlatBayar);
            foreach ($getAlatBayar as $item) {
                if ($this->alatbayar_id == $item['id']) {
                    $kondisialatbayar = false;
                }
            }
        }
        // dd($kondisialatbayar);
        $rulesbank_id = [];
        if ($bank_id != '' && $this->bank != '') {
            // dd($kondisialatbayar);
            $rulesbank_id = [
                'bank' => [
                    new ExistBank(),
                    new ValidasiBankList($kondisialatbayar),
                    Rule::in($query->bank),

                ]
            ];
        } else if ($bank_id != null) {
            if ($bank_id == 0) {
                $rulesbank_id = [
                    'bank_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistBank(),

                    ]

                ];
            } else {
                if ($this->bank == '') {
                    $rulesbank_id = [
                        'bank' => [
                            'required',
                            new ExistBank(),
                        ]
                    ];
                }
            }
        } else if ($bank_id == null && $this->bank != '') {
            $rulesbank_id = [
                'bank_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistBank(),
                ]
            ];
        } else {
            $rulesbank_id = [
                'bank' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistBank(),
                ]
            ];
        }


        $alatbayar_id = $this->alatbayar_id;
        $rulesalatbayar_id = [];
        if ($alatbayar_id != '' && $this->alatbayar != '') {
            // dd($kondisialatbayar);
            $rulesalatbayar_id = [
                'alatbayar' => [
                    new ExistAlatBayar(),
                    Rule::in($query->alatbayar),

                ]
            ];
        } else if ($alatbayar_id != null) {
            if ($alatbayar_id == 0) {
                $rulesalatbayar_id = [
                    'alatbayar_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistAlatBayar(),
                    ]

                ];
            } else {
                if ($this->alatbayar == '') {
                    $rulesalatbayar_id = [
                        'alatbayar' => [
                            'required',
                            new ExistAlatBayar(),
                        ]
                    ];
                }
            }
        } else if ($alatbayar_id == null && $this->alatbayar != '') {
            $rulesalatbayar_id = [
                'alatbayar_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistAlatBayar(),
                ]
            ];
        } else {
            $rulesalatbayar_id = [
                'alatbayar' => [
                    'required',
                    new ExistAlatBayar(),
                ]
            ];
        }


        $rule = array_merge(
            $rules,
            $rulessupplier_id,
            $rulesbank_id,
            $rulesalatbayar_id,
        );

        return $rule;
    }

    public function attributes()
    {

        $attributes = [];
        $relatedRequests = [
            UpdatePelunasanHutangDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }
        return $attributes;

        // return [
        //     'hutang_id' => 'Pilih Hutang',
        //     'keterangan.*' => 'keterangan detail',
        //     'bayar.*' => 'bayar',
        // ];
    }

    public function messages()
    {
        return [
            'hutang_id.required' => 'HUTANG ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
            'bayar.*.numeric' => 'nominal harus ' . app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            'bayar.*.gt' =>  app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglcair.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
