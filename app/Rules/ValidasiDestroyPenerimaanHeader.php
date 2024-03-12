<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\PenerimaanHeaderController;
use App\Models\PenerimaanHeader;
use Illuminate\Support\Facades\DB;

class ValidasiDestroyPenerimaanHeader implements Rule
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
        $penerimaan = new PenerimaanHeader();
        $nobukti = PenerimaanHeader::from(DB::raw("penerimaanheader"))->where('id', request()->id)->first();
        $cekdata = $penerimaan->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi']) {
            $this->kodeerror = $cekdata['kodeerror'];
            $this->keterangan = $cekdata['keterangan'] ;
            return false;
        }

        $cekCetak = app(PenerimaanHeaderController::class)->cekvalidasi(request()->id);
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
