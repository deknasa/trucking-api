<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateAllowedAbsen;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ErrorController;

class GetMandorAbsensiSupirRequest extends FormRequest
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
        $formattedDate = date('Y-m-d', strtotime(request()->tglbukaabsensi));
        // Cek apakah ada data dengan tanggal yang sama dalam database
        $existingRecord = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->where('tglbukti', $formattedDate)
            ->whereRaw("cast(format(created_at,'yyyy/MM/dd') as date)<>cast(format(updated_at,'yyyy/MM/dd') as date)")
            ->first();

        if (isset($existingRecord)) {
            $rules = [
                "tglbukaabsensi" => [
                    new DateAllowedAbsen(false),
                ]

            ];
        } else {
            $existing = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
                ->select(
                    db::raw("cast(format(created_at,'yyyy/MM/dd') as date) as created_at")
                )
                ->where('tglbukti', $formattedDate)
                ->first();

            if (isset($existing)) {
                if ($existing->created_at != $formattedDate) {
                    $rules = [
                        "tglbukaabsensi" => [
                            new DateAllowedAbsen(false),
                        ]

                    ];
                } else {
                    $rules = [
                        "tglbukaabsensi" => [
                            new DateAllowedAbsen(true),
                        ]

                    ];
                }
            } else {
                $rules = [
                    "tglbukaabsensi" => [
                        new DateAllowedAbsen(true),
                    ]

                ];
            }
        }



        return $rules;
    }
}
