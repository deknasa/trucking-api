<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTripDipakaiKeKandang implements Rule
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
        $nobukti = request()->nobukti;
        $parameter = new Parameter();
        $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
        $cekQuery = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
        ->where('nobukti_tripasal', $nobukti)
        ->where('dari_id', $idkandang)
        ->first();

        if(isset($cekQuery)){
            $cekUpahAwal = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('upah_id')->where('nobukti', $nobukti)->first();
            $upah_id = request()->upah_id;
            
            if($cekUpahAwal->upah_id != $upah_id){
                return false;
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
        return app(ErrorController::class)->geterror('STK')->keterangan;
    }
}
