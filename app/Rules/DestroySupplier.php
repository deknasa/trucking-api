<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Agen;
use App\Models\Bank;
use App\Models\Supplier;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroySupplier implements Rule
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
        $bank = new Supplier();
        $cekdata = $bank->cekvalidasihapus($bank->id);
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
