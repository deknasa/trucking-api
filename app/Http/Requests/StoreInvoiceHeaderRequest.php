<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAgen;
use App\Rules\ExistJenisOrder;
use App\Rules\ValidasiDetail;



class StoreInvoiceHeaderRequest extends FormRequest
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

        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $rules = [
            'tglterima' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'tgljatuhtempo' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . date('d-m-Y')
            ],
            'tgldari' => [
                'required',
                'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
            ],
            'tglsampai' => [
                'required',
                'date_format:d-m-Y',
                'before:' . $tglbatasakhir, 'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))

            ],
        ];

        // $relatedRequests = [
        //     StoreInvoiceHeaderRequest::class
        // ];

        // foreach ($relatedRequests as $relatedRequest) {
        //     $rules = array_merge(
        //         $rules,
        //         (new $relatedRequest)->rules()
        //     );
        // }

        $agen_id = $this->agen_id;
        $rulesagen_id = [];
        if ($agen_id != '' && $this->agen != '') {
            $rulesagen_id = [
                'agen' => [
                    new ExistAgen(),
                    new ValidasiDetail($jumlahdetail),
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
                    new ValidasiDetail($jumlahdetail),
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
        
        $rule = array_merge(
            $rules,
            $rulesagen_id,
            $rulesjenisorder_id,
        );

        return $rule;
    }

    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'tglterima' => 'Tanggal Terima',
            'jenisorder' => 'Jenis Order',
        ];

        // return $attributes;

        // $attributes = [];
        $relatedRequests = [
            StoreHutangBayarDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }
        return $attributes;
    }

    public function messages()
    {
        return [
            'sp_id.required' => 'SP ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'nominalretribusi.*.min' => 'nominal retribusi ' . app(ErrorController::class)->geterror('NTM')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglterima.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.after_or_equal' => ':attribute ' . (new ErrorController)->geterror('HBSD')->keterangan .' '.  date('d-m-Y', strtotime($this->tgldari)) ,

        ];
    }
}
