<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiMilikSupirTrado implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodetrado;
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
        $supir_id = request()->supir_id;
        if ($supir_id != '') {

            $cekSupir = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->select('supir_id', 'kodetrado')
                ->where('supir_id', $supir_id)
                ->first();
            if ($cekSupir != '') {
                $this->kodetrado = $cekSupir->kodetrado;
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
        return 'supir ini '.app(ErrorController::class)->geterror('SPI')->keterangan.' '. $this->kodetrado;
    }
}
