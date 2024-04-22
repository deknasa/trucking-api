<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiStatusContGajiSupir implements Rule
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
        $allowed = true;
        $listTrip = '';
        if ($dataTrip != '') {
            for ($i = 0; $i < count($dataTrip); $i++) {
                $container_id = request()->rincian_container[$i];
                $statuscontainer_id = request()->rincian_statuscontainer[$i];
                $upah_id = request()->rincian_upahid[$i];
                $cekTripExist = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('nobukti', $dataTrip[$i])->first();
                if ($cekTripExist != '') {
                    if ($cekTripExist->container_id != $container_id) {
                        $allowed = false;
                        if (strpos($listTrip, $dataTrip[$i]) === false) {
                            // If it doesn't exist, append the current element
                            if ($listTrip == '') {
                                $listTrip = $dataTrip[$i];
                            } else {
                                $listTrip = $listTrip . ', ' . $dataTrip[$i];
                            }
                        }
                    }
                    if ($cekTripExist->statuscontainer_id != $statuscontainer_id) {
                        $allowed = false;
                        if (strpos($listTrip, $dataTrip[$i]) === false) {
                            // If it doesn't exist, append the current element
                            if ($listTrip == '') {
                                $listTrip = $dataTrip[$i];
                            } else {
                                $listTrip = $listTrip . ', ' . $dataTrip[$i];
                            }
                        }
                    }
                    if ($cekTripExist->upah_id != $upah_id) {
                        $allowed = false;
                        if (strpos($listTrip, $dataTrip[$i]) === false) {
                            // If it doesn't exist, append the current element
                            if ($listTrip == '') {
                                $listTrip = $dataTrip[$i];
                            } else {
                                $listTrip = $listTrip . ', ' . $dataTrip[$i];
                            }
                        }
                    }
                }
            }
        }
        $this->trip = $listTrip;
        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'container / status container / upah '. $this->trip .' '. app(ErrorController::class)->geterror('TSD')->keterangan . ' DATA DI TRIP'.'<br> Silahkan tekan reload';
    }
}
