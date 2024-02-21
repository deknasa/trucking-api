<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class ValidasiContainerTripGudangSama implements Rule
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
            $container_id = request()->container_id ?? 0;
            if($container_id != 0){
                if($container_id != $this->dataTripAsal['container_id']){
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
        return app(ErrorController::class)->geterror('TBD')->keterangan. ' dengan container trip asal';
    }
}
