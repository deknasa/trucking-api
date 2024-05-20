<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class PotonganBayarPelunasanPiutang implements Rule
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
        $attribute = substr($attribute,9);
        $bayar = (request()->bayar[$attribute] == '') ? 0 : request()->bayar[$attribute];
        $potonganpph = (request()->potonganpph[$attribute] == '') ? 0 : request()->potonganpph[$attribute];
        $total = $value+$bayar+$potonganpph;
        if($total == 0){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return  app(ErrorController::class)->geterror('WI')->keterangan.' bayar/potongan/potongan B. pph';
    }
}
