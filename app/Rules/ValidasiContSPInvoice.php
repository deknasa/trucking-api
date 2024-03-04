<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiContSPInvoice implements Rule
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
        $requestData = json_decode(request()->detail, true);
        $allowed = true;
        $listTrip = '';
        foreach ($requestData['jobtrucking'] as $value) {
            
            $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $value)->get();
            for ($i = 0; $i < count($query); $i++) {
                $cekTripExist = $query[$i];
                if ($cekTripExist->nosp == '') {
                    $allowed = false;
                    if (strpos($listTrip, $value) === false) {
                        // If it doesn't exist, append the current element
                        if ($listTrip == '') {
                            $listTrip = $value;
                        } else {
                            $listTrip = $listTrip . ', ' . $value;
                        }
                    }
                }
                if ($cekTripExist->nocont == '') {
                    $allowed = false;
                    if (strpos($listTrip, $value) === false) {
                        // If it doesn't exist, append the current element
                        if ($listTrip == '') {
                            $listTrip = $value;
                        } else {
                            $listTrip = $listTrip . ', ' . $value;
                        }
                    }
                }

                if ($cekTripExist->container_id == 3) {
                    if ($cekTripExist->nocont2 == '') {
                        $allowed = false;
                        if (strpos($listTrip, $value) === false) {
                            // If it doesn't exist, append the current element
                            if ($listTrip == '') {
                                $listTrip = $value;
                            } else {
                                $listTrip = $listTrip . ', ' . $value;
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
        return app(ErrorController::class)->geterror('DBL')->keterangan . ' (' . $this->trip . ')';
    }
}
