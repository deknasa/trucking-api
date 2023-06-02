<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;

class StoreRekapPengeluaranHeaderRequest extends FormRequest
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

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'tgltransaksi' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
        ];

        $rulesbank_id = [];
        $bank_id = $this->bank_id;
        if ($bank_id != '' && $this->bank != '') {
            // dd($kondisialatbayar);
            $rulesbank_id = [
                'bank' => [
                    new ExistBank(),
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


          $rule = array_merge(
            $rules,
            $rulesbank_id,
        );

        return $rule;
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgltransaksi.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
