<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\GajiSupirHeaderController;
use App\Models\GajiSupirHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyGajiSupirNobukti implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeerror;
    public $keterangan;
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
        $gajisupir = new GajiSupirHeader();
        $nobukti = GajiSupirHeader::from(DB::raw("gajisupirheader"))->where('id', request()->id)->first();
        $cekdata = $gajisupir->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi']) {
            $this->kodeerror = $cekdata['kodeerror'];            
            $this->keterangan = $cekdata['keterangan'] ;
            return false;
        }
        
        $cekCetak = app(GajiSupirHeaderController::class)->cekvalidasi(request()->id);
        $getOriginal = $cekCetak->original;
        if ($getOriginal['error'] == true) {
            $this->kodeerror = $getOriginal['kodeerror'];
            $this->keterangan = $getOriginal['message'];
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
        return $this->keterangan;
    }
}
