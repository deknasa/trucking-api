<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PenerimaanGiroHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPenerimaanGiro implements Rule
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
        $penerimaanGiro = new PenerimaanGiroHeader();
        $nobukti = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader"))->where('id', request()->id)->first();
        $cekdata = $penerimaanGiro->cekvalidasiaksi($nobukti->nobukti);
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
