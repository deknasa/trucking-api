<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class ValidasiTujuanTarifDariUpahSupir implements Rule
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
        //
        if(request()->upahsupir_id != null || request()->upahsupir_id != 0){
            
            $result = DB::table('upahsupir')
                ->leftJoin('kota', 'upahsupir.kotasampai_id', '=', 'kota.id')
                ->select('kota.keterangan')
                ->where('upahsupir.id', request()->upahsupir_id)
                ->first();

            if($result->keterangan != $value){
                return false;
            }else{
                return true;
            }
            
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
        $controller = new ErrorController;
        return ':attribute ' . $controller->geterror('TVD')->keterangan;
    }
}
