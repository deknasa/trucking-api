<?php

namespace App\Rules;

use App\Models\Menu;
use Illuminate\Contracts\Validation\Rule;

class validasiNonController implements Rule
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
        $menu = new Menu();
        $cekdata = $menu->validasiNonController($value);
        if($cekdata->aco_id == 0){
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
        return 'non controller tidak bisa diedit';
    }
}
