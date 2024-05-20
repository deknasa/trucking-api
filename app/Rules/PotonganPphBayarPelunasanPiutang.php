<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class PotonganPphBayarPelunasanPiutang implements Rule
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
        $attribute = substr($attribute,12);
        $bayar = (request()->bayar[$attribute] == '') ? 0 : request()->bayar[$attribute];
        $potongan = (request()->potongan[$attribute] == '') ? 0 : request()->potongan[$attribute];
        $total = $value+$bayar+$potongan;
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
