<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ErrorController;

class AbsensiSpuriUniqueSupirDetail implements Rule
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
        $query = DB::table('absensisupirheader')
        ->from(
            DB::raw("absensisupirheader as a with (readuncommitted)")
        )
        ->select(
            'b.trado_id'
        )
        ->join(db::Raw("absensisupirdetail as b with (readuncommitted)"),'a.id','b.absensi_id')
        ->whereRaw("a.tglbukti='". date('Y-m-d', strtotime(request()->tglbukti))."'")
        ->where('b.supir_id','=',$value)
        ->first();

        if (isset($query)) {
            $nilai=false;
        } else {
            $nilai=true;
        }


        return $nilai;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        
        return  'supir '. app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
