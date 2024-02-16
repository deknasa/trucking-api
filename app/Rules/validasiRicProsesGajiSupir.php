<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class validasiRicProsesGajiSupir implements Rule
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
        $dataric = json_decode(request()->dataric, true);
        $empty = 0;
        $listTrip = '';

        for ($i = 0; $i < count($dataric['nobuktiRIC']); $i++) 
        {
            $ric = $dataric['nobuktiRIC'][$i];
            $cekRic = DB::table("gajisupirheader")->from(DB::raw("gajisupirheader with (readuncommitted)"))->where('nobukti', $ric)->first();
            if($cekRic == ''){
                $empty++;
                if($listTrip == ''){
                    $listTrip = $ric;
                }else{
                    $listTrip = $listTrip . ', ' . $ric;
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
