<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\SuratPengantar;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroySuratPengantar implements Rule
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
        $suratPengantar = new SuratPengantar();
        $nobukti = SuratPengantar::from(DB::raw("suratpengantar"))->where('id', request()->id)->first();
        $cekdata = $suratPengantar->cekvalidasihapus($nobukti->nobukti, request()->jobtrucking);
        if($cekdata['kondisi']){
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
