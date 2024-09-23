<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiJenisOrderInvoiceEmkl implements Rule
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
        $statusNonPajak = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->where('grp', 'STATUS PAJAK')->where('text', 'NON PAJAK')->first();
        $isMuatan = DB::table('jenisorder')->from(DB::raw("jenisorder with (readuncommitted)"))
        ->where('kodejenisorder','MUAT')->first();
        $isBongkaran = DB::table('jenisorder')->from(DB::raw("jenisorder with (readuncommitted)"))
        ->where('kodejenisorder','BKR')->first();
        $jenisorder_id=request()->jenisorder_id ?? 0;
        if ($jenisorder_id==1) {
            if(request()->statuspajak == $statusNonPajak->id && request()->jenisorder_id == $isMuatan->id){
                return false;
            } 
            if(request()->statuspajak != $statusNonPajak->id && request()->jenisorder_id != $isMuatan->id){
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
        return 'The validation error message.';
    }
}
