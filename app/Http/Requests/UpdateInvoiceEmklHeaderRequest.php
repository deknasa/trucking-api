<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use App\Rules\ExistJenisOrder;
use App\Rules\ExistPelanggan;
use App\Rules\ValidasiDetail;
use App\Rules\validasiJenisOrderInvoiceEmkl;
use App\Rules\validasiNoInvoicePajakEmkl;
use App\Rules\validasiTglInvoiceEmkl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceEmklHeaderRequest extends FormRequest
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
        $jenisorder_id = $this->jenisorder_id;
        if ($jenisorder_id ==1) {
            $rules = [
                'statusinvoice' => [
                    'required', 
                ],
                'tglbukti' => [
                    'required', 'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y'),
                ],
                'tgldari' => [
                    'required', 'date_format:d-m-Y', new validasiTglInvoiceEmkl()
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y', new validasiTglInvoiceEmkl()
                ],
                // 'kapal' => [
                //     'required', 
                // ],
                // 'destination' => [
                //     'required', 
                // ],
    
            ];
        } else {
            $rules = [
                'statusinvoice' => [
                    'required', 
                ],
                'statuspajak' => [
                    'required', 
                ],
                // 'nobuktiinvoicepajak' => [
                //     new validasiNoInvoicePajakEmkl()
                // ],
                'tglbukti' => [
                    'required', 'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y'),
                ],
                'tgldari' => [
                    'required', 'date_format:d-m-Y', new validasiTglInvoiceEmkl()
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y', new validasiTglInvoiceEmkl()
                ],
    
            ];
        }
       
        

        $pelanggan_id = $this->pelanggan_id;
        $rulesPelanggan_id = [];
        if ($pelanggan_id != '' && $this->pelanggan != '') {
            $rulesPelanggan_id = [
                'pelanggan' => [
                    new ExistPelanggan(),
                    new ValidasiDetail($jumlahdetail),
                ]
            ];
        } else if ($pelanggan_id != null) {
            if ($pelanggan_id == 0) {

                $rulesPelanggan_id = [
                    'pelanggan_id' => [
                        'required',
                        'numeric',
                        'min:1',
                        new ExistPelanggan(),

                    ]

                ];
            } else {
                if ($this->pelanggan == '') {

                    $rulesPelanggan_id = [
                        'pelanggan' => [
                            'required',
                            new ExistPelanggan(),
                        ]
                    ];
                }
            }
        } else if ($pelanggan_id == null && $this->pelanggan != '') {

            $rulesPelanggan_id = [
                'pelanggan_id' => [
                    'required',
                    'numeric',
                    'min:1',
                    new ExistPelanggan(),
                ]
            ];
        } else {
            $rulesPelanggan_id = [
                'pelanggan' => [
                    'required',
                ]
            ];
        }

   
        $rulesjenisorder_id = [];
        if ($jenisorder_id != '' && $this->jenisorder != '') {
            $rulesjenisorder_id = [
                'jenisorder' => [
                    new ExistJenisOrder(),
                    new validasiJenisOrderInvoiceEmkl()
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
                    'required'
                ]
            ];
        }        
        
        $relatedRequests = [
            StoreInvoiceEmklDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            if ($jenisorder_id ==1) {
                $rules = array_merge(
                    $rules,
                    (new $relatedRequest)->rules(), 
                    $rulesPelanggan_id,
                    $rulesjenisorder_id,
                );
                    
            } else {
                $rules = array_merge(
                    $rules,
                    (new $relatedRequest)->rules(), 
                    $rulesjenisorder_id,
                );
    
            }
        }
        return $rules;
    }
    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'pelanggan_id' => 'shipper',
            'jenisorder' => 'Jenis Order',
            'statusinvoice' => 'status invoice',
            'statuspajak' => 'status pajak',
        ];
        return $attributes;
    }

    
    public function messages()
    {
        return [
            'jenisorder.required_if' => 'jenis order ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'jenisorder_id.required_if' => 'jenis order ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'nominal.*.min' => 'nominal ' . app(ErrorController::class)->geterror('NTM')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.after_or_equal' => ':attribute ' . (new ErrorController)->geterror('HBSD')->keterangan .' '.  date('d-m-Y', strtotime($this->tgldari)) ,

        ];
    }
}
