<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTripInvoice implements Rule
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
        for ($i = 0; $i < count($detail['jobtrucking']); $i++) 
        {
            $jobtrucking = $detail['jobtrucking'][$i];
            
            $cekRic = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $jobtrucking)->first();
            if($cekRic == ''){
                $empty++;
                if($listTrip == ''){
                    $listTrip = $jobtrucking;
                }else{
                    $listTrip = $listTrip . ', ' . $jobtrucking;
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
