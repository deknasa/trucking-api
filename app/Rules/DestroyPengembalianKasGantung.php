<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PengembalianKasGantungHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPengembalianKasGantung implements Rule
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
        $pengembalian = new PengembalianKasGantungHeader();
        $nobukti = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader"))->where('id', request()->id)->first();
        $cekdata = $pengembalian->cekvalidasiaksi($nobukti->nobukti);
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
