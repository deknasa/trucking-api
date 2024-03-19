<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTripGajiSupir implements Rule
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
        $dataTrip = request()->rincian_nobukti;
        $empty = 0;
        $listTrip = '';
        if ($dataTrip != '') {

            for ($i = 0; $i < count($dataTrip); $i++) {
                $cekTripExist = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('nobukti', $dataTrip[$i])->first();
                if ($cekTripExist == '') {
                    $empty++;
                    if ($listTrip == '') {
                        $listTrip = $dataTrip[$i];
                    } else {
                        $listTrip = $listTrip . ', ' . $dataTrip[$i];
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
        return app(ErrorController::class)->geterror('DTA')->keterangan . ' (' . $this->trip . ')';
    }
}
