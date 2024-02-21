<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiAgenTripGudangSama implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $dataTripAsal;
    public function __construct($tripAsal)
    {
        $this->dataTripAsal = $tripAsal;
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
        if(count($this->dataTripAsal) > 0){
            $agen_id = request()->agen_id ?? 0;
            if($agen_id != 0){
                if($agen_id != $this->dataTripAsal['agen_id']){
                    return false;
                }
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
        return app(ErrorController::class)->geterror('TBD')->keterangan. ' dengan customer trip asal';
    }
}
