<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PenerimaanHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPenerimaan implements Rule
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
        $penerimaan = new PenerimaanHeader();
        $nobukti = PenerimaanHeader::from(DB::raw("penerimaanheader"))->where('id', request()->id)->first();
        $cekdata = $penerimaan->cekvalidasiaksi($nobukti->nobukti);
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
