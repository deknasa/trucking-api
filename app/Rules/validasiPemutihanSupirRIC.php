<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PenerimaanTruckingHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiPemutihanSupirRIC implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $supir_id = request()->supir_id;
        $getSupirLama = DB::table('supir')->from(DB::raw("supir with (readuncommitted)"))
            ->select(DB::raw("isnull(supirlama_id,0) as supirlama_id"))->where('id', $supir_id)->first();
        if (isset($getSupirLama)) {
            if ($getSupirLama->supirlama_id != 0) {
                $cekPemutihan = DB::table("pemutihansupirheader")->from(DB::raw("pemutihansupirheader with (readuncommitted)"))
                    ->where('supir_id', $getSupirLama->supirlama_id)
                    ->first();
                if ($cekPemutihan != '') {
                    return true;
                } else {
                    $cekPinjaman = (new PenerimaanTruckingHeader())->getPinjaman($getSupirLama->supirlama_id, true);
                    if(isset($cekPinjaman)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('SSMT')->keterangan;
    }
}
