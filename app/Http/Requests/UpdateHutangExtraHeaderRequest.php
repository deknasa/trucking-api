<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\DestroyHutangExtra;
use App\Rules\ExistSupplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateHutangExtraHeaderRequest extends FormRequest
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
        $query = DB::table('hutangextraheader')->from(DB::raw("hutangextraheader with (readuncommitted)"))
        ->select(
            'tglbukti',
            'nobukti'
        )
        ->where('id', $this->id)
        ->first();

        $rules = [
            'id' => new DestroyHutangExtra(),
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y'),
            ],
            'nobukti' => [
                Rule::in($query->nobukti),
            ],
            'supplier' => [
                'required',
                new ExistSupplier(),
            ]
        ];
        $relatedRequests = [
            UpdateHutangExtraDetailRequest::class
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

        $rule = array_merge(
            $rules,
            $rulessupplier_id
        );

        return $rule;
    }
}
