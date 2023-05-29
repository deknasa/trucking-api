<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\JurnalUmumHeader;
use App\Models\KasGantungHeader;
use App\Models\Pelanggan;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPelanggan implements Rule
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
        $gajisupir = new Pelanggan();
        $nobukti = Pelanggan::from(DB::raw("pelanggan"))->where('id', request()->id)->first();
        $cekdata = $gajisupir->cekvalidasihapus($nobukti->id);
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
