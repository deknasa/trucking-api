<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\UpahSupirRincian;
use Illuminate\Contracts\Validation\Rule;

class ExistNominalUpahSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $error;
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
        $upahRincian = new UpahSupirRincian();
        if (request()->jobtrucking != '') {

            $nominal = $upahRincian->getExistNominalUpahSupir(request()->container_id, request()->statuscontainer_id, request()->upah_id);
            if ($nominal['status'] == false) {
                $this->error = $nominal['error'];
                return false;
            }
        }

        return true;
        // if($nominal->nominalsupir == null || $nominal->nominalsupir == 0){
        //     return false;
        // }else{
        //     return true;
        // }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        return $controller->geterror('USBA')->keterangan . '. (' . $this->error . ')';
    }
}
