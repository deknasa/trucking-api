<?php

namespace App\Rules;

use App\Http\Controllers\Api\TripInapController;
use Illuminate\Contracts\Validation\Rule;

class validasiDestroyTripInap implements Rule
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
        
        $cekCetak = app(TripInapController::class)->cekValidasi(request()->id);
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
