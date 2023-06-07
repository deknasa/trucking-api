<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiDariSimpanKandangUpahSupir implements Rule
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
        $getKandang = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp','STATUS SIMPAN KANDANG')->where('text','SIMPAN KANDANG')->first();
        $getBelawan = DB::table("kota")->from(DB::raw("kota with (readuncommitted)"))->where('kodekota','BELAWAN')->first();
        if(request()->statussimpankandang == $getKandang->id){
            if($value != $getBelawan->kodekota){
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
        return  app(ErrorController::class)->geterror('TVD')->keterangan;
    }
}
