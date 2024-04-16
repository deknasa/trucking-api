<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistAgen;
use App\Rules\ValidasiDestroyInvoiceExtraHeader;
use App\Rules\ValidasiDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateInvoiceExtraHeaderRequest extends FormRequest
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

        $query = DB::table('invoiceExtraheader')->from(
            DB::raw('invoiceextraheader a with (readuncommitted)')
        )
            ->select(
                'a.tglbukti',
                'b.kodeagen as agen',
            )
            ->leftJoin(DB::raw("agen b with (readuncommitted)"), 'a.agen_id', 'b.id')
            ->where('a.id', '=', $this->id)
            ->first();

        $rules = [
            'id' => [ new ValidasiDestroyInvoiceExtraHeader()],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),

            ], 
            'tgljatuhtempo' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . date('d-m-Y', strtotime($query->tglbukti)),
            ],

        ];
        $relatedRequests = [
            StoreInvoiceExtraDetailRequest::class
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
                    // Rule::in($query->agen),
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


        $rule = array_merge(
            $rules,
            $rulesagen_id
        );

        return $rule;
    }

    public function attributes()
    {
        $attributes = [
            'agen' => 'customer',
            'nominal_detail.*' => 'Harga',
            'keterangan_detail.*' => 'Keterangan',
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'nominal_detail.*.gt' => 'Harga Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
