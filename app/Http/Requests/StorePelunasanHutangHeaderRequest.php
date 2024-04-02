<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistSupplier;
use App\Rules\ExistBank;
use App\Rules\ExistAlatBayar;
use App\Rules\ValidasiHutangList;
use App\Rules\ValidasiBankList;
use App\Models\AlatBayar;
use App\Rules\ValidasiHutangPelunasan;
use App\Rules\ValidasiHutangPelunasanApproval;
use Illuminate\Support\Facades\DB;

class StorePelunasanHutangHeaderRequest extends FormRequest
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
        $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $rulesNoWarkat = [];
        if (request()->alatbayar_id == $alatbayarGiro->id) {
            $rulesNoWarkat = [
                'nowarkat' => 'required'
            ];
        }

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'tglcair' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'after_or_equal:' . request()->tglbukti,
            ],
        ];
        $relatedRequests = [
            StorePelunasanHutangDetailRequest::class
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
                    // new ValidasiHutangPelunasanApproval(),
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
                ]
            ];
        } 
        


        $rule = array_merge(
            $rules,
            $rulessupplier_id,
            // $rulesbank_id,
            $rulesalatbayar_id,
            $rulesNoWarkat
        );

        return $rule;
    }

    public function attributes()
    {

        $attributes = [
            'tglcair' => 'tgl jatuh tempo',
            'nowarkat' => 'nowarkat'
        ];
        $relatedRequests = [
            StorePelunasanHutangDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }
        return $attributes;

        // $attributes = [
        //     'tglbukti' => 'Tanggal Bukti',
        //     'tglcair' => 'Tanggal Cair',
        //     'jumlahdetail' => 'Jumlah Detail 0',
        //     'hutang_id.*' => 'No Bukti Hutang',
        //     'keterangan.*' => 'Keterangan',
        //     'bayar.*' => 'Keterangan',
        //     'sisa.*' => 'Keterangan',
        // ];

        // return $attributes;
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
