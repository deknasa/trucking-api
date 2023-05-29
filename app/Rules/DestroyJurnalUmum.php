<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\JurnalUmumHeader;
use App\Models\KasGantungHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyJurnalUmum implements Rule
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
        $gajisupir = new JurnalUmumHeader();
        $nobukti = JurnalUmumHeader::from(DB::raw("jurnalumumheader"))->where('id', request()->id)->first();
        $cekdata = $gajisupir->cekvalidasiaksi($nobukti->nobukti);
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
        return app(ErrorController::class)->geterror('TDT')->keterangan;
    }
}
