<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTripKomisi implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trip;
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
        $detail = json_decode(request()->detail, true);
        $empty = 0;
        $listTrip = '';

        $parameter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
        ->where('grp','PENDAPATAN SUPIR')->where('subgrp', 'TAB KOMISI')->first();
        if($parameter->text == 'FORMAT 1'){
            for ($i = 0; $i < count($detail['nobukti_trip']); $i++) 
            {
                $nobukti_trip = $detail['nobukti_trip'][$i];
                
                $cekRic = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('nobukti', $nobukti_trip)->first();
                if($cekRic == ''){
                    $empty++;
                    if($listTrip == ''){
                        $listTrip = $nobukti_trip;
                    }else{
                        $listTrip = $listTrip . ', ' . $nobukti_trip;
                    }
                }
            }
        }else{
            for ($i = 0; $i < count($detail['nobukti_ric']); $i++) 
            {
                $nobukti_ric = $detail['nobukti_ric'][$i];
                
                $cekRic = DB::table("gajisupirheader")->from(DB::raw("gajisupirheader with (readuncommitted)"))->where('nobukti', $nobukti_ric)->first();
                if($cekRic == ''){
                    $empty++;
                    if($listTrip == ''){
                        $listTrip = $nobukti_ric;
                    }else{
                        $listTrip = $listTrip . ', ' . $nobukti_ric;
                    }
                }
            }
        }

        
        $this->trip = $listTrip;
        if ($empty > 0) {
            return false;
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
        return app(ErrorController::class)->geterror('DTA')->keterangan . ' (' . $this->trip.')';
    }
}
