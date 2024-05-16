<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAgen;
use App\Rules\ExistJenisOrder;
use App\Rules\ValidasiContSPInvoice;
use App\Rules\ValidasiDestroyInvoiceHeader;
use App\Rules\ValidasiDetail;
use App\Rules\ValidasiHutangList;
use App\Rules\ValidasiTambahanInvoice;
use App\Rules\validasiTripInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateInvoiceHeaderRequest extends FormRequest
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

        $jenisGandengan = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'GANDENGAN')
            ->first();
        $query=DB::table('invoiceheader')->from(
            DB::raw('invoiceheader a with (readuncommitted)')
        )
        ->select(
            'a.tglbukti',
            'b.kodeagen as agen',
            'c.keterangan as jenisorder',
        )
        ->leftJoin(DB::raw("agen b with (readuncommitted)"), 'a.agen_id', 'b.id')
        ->leftJoin(DB::raw("jenisorder c with (readuncommitted)"), 'a.jenisorder_id', 'c.id')
        ->where('a.id','=',$this->id)
        ->first();


        $rules = [
            'id' => new ValidasiDestroyInvoiceHeader(),
            'statuspilihaninvoice' => [
                'required', 
            ],
            'statusjeniskendaraan' => [
                'required', 
            ],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:'.date('d-m-Y'),
                new DateTutupBuku(),
            ],
             'tgljatuhtempo' => [
                'required','date_format:d-m-Y',
                'after_or_equal:'.date('d-m-Y', strtotime($query->tglbukti)),
            ],
            'tgldari' => [
                'required',
                'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                new ValidasiHutangList($jumlahdetail),
            ],
            'tglsampai' => [
                'required',
                'date_format:d-m-Y',
                'before:' . $tglbatasakhir, 'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))

            ],
        ];

        $relatedRequests = [
            UpdateInvoiceDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

        $agen_id = $this->agen_id;
        $rulesagen_id = [];
        if ($agen_id != '' && $this->agen != '') {
            $rulesagen_id = [
                'agen' => [
                    new ExistAgen(),
                    new ValidasiDetail($jumlahdetail),
                    Rule::in($query->agen),
                    new validasiTripInvoice(),
                    new ValidasiContSPInvoice(),
                    new ValidasiTambahanInvoice()

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
                    Rule::in($query->jenisorder),
                ]
            ];
        } else if ($jenisorder_id != null) {
            if ($jenisorder_id == 0) {

                $rulesjenisorder_id = [
                    'jenisorder_id' => [
                        'required_if:statusjeniskendaraan,=,' . $jenisGandengan->id,
                        'numeric',
                        'min:1',
                        new ExistJenisOrder(),

                    ]

                ];
            } else {
                if ($this->jenisorder == '') {

                    $rulesjenisorder_id = [
                        'jenisorder' => [
                            'required_if:statusjeniskendaraan,=,' . $jenisGandengan->id,
                        ]
                    ];
                }
            }
        } else if ($jenisorder_id == null && $this->jenisorder != '') {

            $rulesjenisorder_id = [
                'jenisorder_id' => [
                    'required_if:statusjeniskendaraan,=,' . $jenisGandengan->id,
                    'numeric',
                    'min:1',
                    new ExistJenisOrder(),
                ]
            ];
        } else {
            $rulesjenisorder_id = [
                'jenisorder' => [
                    'required_if:statusjeniskendaraan,=,' . $jenisGandengan->id,
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
            'agen' => 'Customer'
        ];

        return $attributes;

        // $attributes = [];
        // $relatedRequests = [
        //     UpdateInvoiceDetailRequest::class
        // ];

        // foreach ($relatedRequests as $relatedRequest) {
        //     $attributes = array_merge(
        //         $attributes,
        //         (new $relatedRequest)->attributes()
        //     );
        // }
        // return $attributes;
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
