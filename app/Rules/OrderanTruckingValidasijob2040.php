<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class OrderanTruckingValidasijob2040 implements Rule
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

        $statustas =  DB::table('parameter')->from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS TAS')
            ->where('subgrp', '=', 'STATUS TAS')
            ->where('text', '=', 'TAS')
            ->first();

        $query = DB::table('agen')
            ->from(
                DB::raw("agen as a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
            ->where('a.statustas', '=', $statustas->id)
            ->where('a.id', '=', request()->agen_id)
            ->first();

        $nojobemkl = request()->nojobemkl ?? '';

        if ((isset($query)) and $nojobemkl == '') {
            $getOrderan = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('id', request()->id)->first();
            $currentDate = date('Y-m-d H:i:s');
            if ($getOrderan->statusapprovaltanpajob == 3 && $currentDate <  date('Y-m-d H:i:s', strtotime($getOrderan->tglbatastanpajoborderantrucking))) {
                $nilai = true;
            } else {
                $nilai = false;
            }
        } else {
            $nilai = true;
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
        $controller = new ErrorController;
        return ':attribute' . ' ' . $controller->geterror('WI')->keterangan;
    }
}
