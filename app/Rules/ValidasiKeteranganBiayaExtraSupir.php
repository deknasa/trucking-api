<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiKeteranganBiayaExtraSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $ket;
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
        $attribute = substr($attribute,16);
        $nominal = (request()->nominal[$attribute] == 0) ? 0 : request()->nominal[$attribute];
        $nominalTagih = (request()->nominaltagih[$attribute] == 0) ? 0 : request()->nominaltagih[$attribute];
        if(trim($value) == '') {
            if($nominal != 0){
                $this->ket = 'keterangan biaya tambahan';
                return false;
            }
            if($nominalTagih != 0){
                $this->ket = 'keterangan biaya tambahan';
                return false;
            }
        }else{
            if($nominal == 0 && $nominalTagih == 0){
                $this->ket = 'nominal / nominal tagih';
                return false;
            } 
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
        return $this->ket.' '.app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
