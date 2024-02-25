<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiStatusApprovalBukaTanggalTrip implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $status;
    public function __construct($status)
    {
       $this->status = $status;
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
        $id = request()->id;
        $cekQuery = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
        ->where('approvalbukatanggal_id', $id)
        ->first();

        if($cekQuery != ''){
            if($value != $this->status){
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
        return app(ErrorController::class)->geterror('DTSA')->keterangan;
    }
}
