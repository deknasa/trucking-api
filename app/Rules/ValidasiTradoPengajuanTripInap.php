<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTradoPengajuanTripInap implements Rule
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
        $now = date('Y-m-d', strtotime(request()->tglabsensi));
        $cektripinap = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
            ->where('trado_id', request()->trado_id)
            ->where('supir_id', request()->supir_id)
            ->whereRaw("tglabsensi = '$now'");
        if (request()->id != '') {
            $cektripinap->where('id', '<>', request()->id);
        }
        
        $result = $cektripinap->first();
        if ($result != '') {
            return false;
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
        return 'trado ' . app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
