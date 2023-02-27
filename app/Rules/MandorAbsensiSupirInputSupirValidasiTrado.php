<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;


class MandorAbsensiSupirInputSupirValidasiTrado implements Rule
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

        // dd(request()->trado);
        $query = DB::table('absensisupirheader')
        ->from(
            DB::raw("absensisupirheader as a with (readuncommitted)")
        )
        ->select(
            'b.trado_id'
        )
        ->join(db::Raw("absensisupirdetail as b with (readuncommitted)"),'a.id','b.absensi_id')
        ->whereRaw("a.tglbukti='".date('Y-m-d')."'")
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
        $query = DB::table('absensisupirheader')
        ->from(
            DB::raw("absensisupirheader as a with (readuncommitted)")
        )
        ->select(
            'c.keterangan'
        )
        ->join(db::Raw("absensisupirdetail as b with (readuncommitted)"),'a.id','b.absensi_id')
        ->join(db::Raw("trado as c with (readuncommitted)"),'b.trado_id','c.id')
        ->whereRaw("a.tglbukti='".date('Y-m-d')."'")
        ->where('b.supir_id','=',request()->supir_id)
        ->first();

        // dd(request()->supir_id);
        return ':attribute Sudah Pernah Di Input Di Trado '. $query->keterangan;
    }
}
