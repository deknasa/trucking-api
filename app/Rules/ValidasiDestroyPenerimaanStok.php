<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\PenerimaanStok;
use App\Http\Controllers\Api\ErrorController;

class ValidasiDestroyPenerimaanStok implements Rule
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
        $penerimaanStok = new PenerimaanStok();
        $cekdata = $penerimaanStok->cekvalidasihapus(request()->id);
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
