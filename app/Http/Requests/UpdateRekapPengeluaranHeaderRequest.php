<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\RekapPengeluaranHeaderController;
use App\Models\RekapPengeluaranHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ValidasiUpdateRekapPengeluaranHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateRekapPengeluaranHeaderRequest extends FormRequest
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
        $controller = new RekapPengeluaranHeaderController;
        $rekappengeluaranheader = new RekapPengeluaranHeader();
        $cekdata = $rekappengeluaranheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

        $query=DB::table('rekappengeluaranheader')->from(
            DB::raw('rekappengeluaranheader a with (readuncommitted)')
        )
        ->select(
            'a.tglbukti',
            'a.tgltransaksi',
            'c.kodebank as bank',
        )
        ->leftJoin(DB::raw("bank c with (readuncommitted)"), 'a.bank_id', 'c.id')
        ->where('a.id','=',$this->id)
        ->first();



        $rules = [
            'id' => [ new ValidasiUpdateRekapPengeluaranHeader($cekdata['kondisi'],$cekdtcetak)],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
                Rule::in(date('d-m-Y', strtotime($query->tglbukti))),
            ],
            'tgltransaksi' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
                Rule::in(date('d-m-Y', strtotime($query->tgltransaksi))),
            ],
        ];

        $rulesbank_id = [];
        $bank_id = $this->bank_id;
        if ($bank_id != '' && $this->bank != '') {
            // dd($kondisialatbayar);
            $rulesbank_id = [
                'bank' => [
                    new ExistBank(),
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
