<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\AlatBayar;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiNoWarkatPelunasanPiutang implements Rule
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
        $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        if (request()->alatbayar_id != $alatbayarGiro->id){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
