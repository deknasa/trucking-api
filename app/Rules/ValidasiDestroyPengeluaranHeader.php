<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\PengeluaranHeaderController;
use App\Models\PengeluaranHeader;
use Illuminate\Support\Facades\DB;

class ValidasiDestroyPengeluaranHeader implements Rule
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
        $pengeluaran = new PengeluaranHeader();
        $nobukti = PengeluaranHeader::from(DB::raw("pengeluaranheader"))->where('id', request()->id)->first();
        $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi']) {
            $this->kodeerror = $cekdata['kodeerror'];
            $this->keterangan = ' (' . $cekdata['keterangan'] . ')';
            return false;
        }

        $cekCetak = app(PengeluaranHeaderController::class)->cekvalidasi(request()->id);
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
        return app(ErrorController::class)->geterror($this->kodeerror)->keterangan . $this->keterangan;
    }
}
