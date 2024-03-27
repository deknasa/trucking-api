<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistSupplier;
use Illuminate\Support\Facades\DB;
use App\Models\Hutangheader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyHutangHeader ;
use App\Http\Controllers\Api\HutangHeaderController;

class UpdateHutangHeaderRequest extends FormRequest
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

         
    

        $query = DB::table('hutangheader')->from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(
                'tglbukti',
                'nobukti'
            )
            ->where('id', $this->id)
            ->first();
        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'id' => [ new ValidasiDestroyHutangHeader()],
            'nobukti' => [
                Rule::in($query->nobukti),
            ],
            'supplier' => [
                'required',
                new ExistSupplier(),
            ]
        ];
        $relatedRequests = [
            UpdateHutangDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

  $supplier_id = $this->supplier_id;
        $rulessupplier_id = [];
        if ($supplier_id != null) {
            if ($supplier_id == 0) {
                $rulessupplier_id = [
                    'supplier_id' => ['required', 
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
                        ]                    ];
                }
            }
        } else if ($supplier_id == null && $this->supplier != '') {
            $rulessupplier_id = [
                'supplier_id' => ['required', 
                'numeric', 
                'min:1',
                new ExistSupplier(),
                ]
            ];
        } else {
            $rulessupplier_id = [
                'supplier' => ['required', 
                'numeric', 
                'min:1',
                new ExistSupplier(),
                ]
            ]; 
        }

        $rule = array_merge(
            $rules,
            $rulessupplier_id
        );

        return $rule;
      
    }
    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tanggal Bukti',
            'tgljatuhtempo.*' => 'Tanggal Jatuh Tempo',
            'total_detail.*' => 'Total',
            'keterangan_detail.*' => 'Keterangan'
        ];

        return $attributes;
    }
    public function messages()
    {
        return [
            'total_detail.*.gt' => 'Total Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
