<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Bank;
use App\Models\Penerima;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPenerima implements Rule
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
        $penerima = new Penerima();
        $cekdata = $penerima->cekvalidasihapus(request()->id);
        if($cekdata['kondisi']){
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
