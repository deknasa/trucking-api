<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglApprovalBukaTanggalTrip implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $tgl;
    public function __construct($tgl)
    {
        $this->tgl = $tgl;
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
            if(date('Y-m-d',strtotime($value)) != $this->tgl){
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
