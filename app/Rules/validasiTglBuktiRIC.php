<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglBuktiRIC implements Rule
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
        $id = request()->id;

        $cekTgl = DB::table("gajisupirheader")->from(DB::raw("gajisupirheader with (readuncommitted)"))->select("tglbukti")->where('id', $id)->first();

        if($cekTgl != ''){
            $tglbaru = date('m-Y',strtotime(request()->tglbukti));
            $tgllama = date('m-Y', strtotime($cekTgl->tglbukti));
            if($tglbaru != $tgllama){
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
        return app(ErrorController::class)->geterror('TBB')->keterangan.' DENGAN DATA SEBELUMNYA';
    }
}
