<?php

namespace App\Rules;

use App\Http\Controllers\Api\PindahBukuController;
use Illuminate\Contracts\Validation\Rule;

class validasiDestroyPindahBuku implements Rule
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
        $cekCetak = app(PindahBukuController::class)->cekvalidasi(request()->id);
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