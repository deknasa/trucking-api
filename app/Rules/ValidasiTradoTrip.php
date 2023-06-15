<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AbsensiSupirHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTradoTrip implements Rule
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
        $tglbukti = date('Y-m-d', strtotime('now'));
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $tglbukti)->first();
        $data = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))->whereRaw("id in (select trado_id from absensisupirdetail where absensi_id=$absensiSupirHeader->id)")->where('id', request()->trado_id)->first();
        if($data != null){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
