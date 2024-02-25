<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\SuratPengantarApprovalInputTrip;
use Illuminate\Contracts\Validation\Rule;

class ValidasiSuratPengantarApprovalInputTrip implements Rule
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
        $check = (new SuratPengantarApprovalInputTrip())->cekvalidasiaksi(request()->id, 'DELETE');
        if($check['kondisi'] == true){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
