<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\JurnalUmumHeader;
use App\Models\KasGantungHeader;
use App\Models\PengeluaranTruckingHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPengeluaranTruckingHeader implements Rule
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
        $gajisupir = new PengeluaranTruckingHeader();
        $nobukti = JurnalUmumHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', request()->id)->first();
        $cekdata = $gajisupir->cekvalidasiaksi($nobukti->nobukti);
        if($cekdata['kondisi']){
            $this->message = 'TDT';
            return false;
        }

        $printValidation = $gajisupir->printValidation(request()->id);
        if ($printValidation) {
            $this->message = 'SDC';
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
