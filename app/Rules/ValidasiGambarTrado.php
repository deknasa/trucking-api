<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class ValidasiGambarTrado implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        
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
        $cekValidasi = DB::table('approvaltradogambar')->from(DB::raw("approvaltradogambar with (readuncommitted)"))
        ->select('kodetrado')
        ->first();

        if(request()->kodetrado === $cekValidasi->kodetrado){
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
        $controller = new ErrorController;
        return ':attribute' . ' ' . $controller->geterror('WI')->keterangan;
    }
}
