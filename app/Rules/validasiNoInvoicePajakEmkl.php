<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiNoInvoicePajakEmkl implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeerror;
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
        $statusNonPajak = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PAJAK')->where('text', 'NON PAJAK')->first();
        if(request()->statuspajak == $statusNonPajak->id && $value != ''){
            $this->kodeerror = 'TBI';
            return false;
        } 
        if(request()->statuspajak != $statusNonPajak->id && $value == ''){
            $this->kodeerror = 'WI';
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
        return app(ErrorController::class)->geterror($this->kodeerror)->keterangan;
    }
}
