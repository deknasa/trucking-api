<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiContSPGajiSupir implements Rule
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
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        if ($dataTrip != '') {
            for ($i = 0; $i < count($dataTrip); $i++) {
                $cekTripExist = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('nobukti', $dataTrip[$i])->first();
                if ($cekTripExist != '') {
                    if ($cekTripExist->nosp == '') {
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
                    if ($cekTripExist->statusjeniskendaraan != $jenisTangki->id) {
                        if ($cekTripExist->nocont == '') {
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

                        if ($cekTripExist->container_id == 3) {
                            if ($cekTripExist->nocont2 == '') {
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
                } else {

                    $cekTripExist = DB::table("saldosuratpengantar")->from(DB::raw("saldosuratpengantar with (readuncommitted)"))
                        ->where('nobukti', $dataTrip[$i])->first();
                    if ($cekTripExist != '') {

                        if ($cekTripExist->nosp == '') {
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

                        if ($cekTripExist->statusjeniskendaraan != $jenisTangki->id) {
                            if ($cekTripExist->nocont == '') {
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

                            if ($cekTripExist->container_id == 3) {
                                if ($cekTripExist->nocont2 == '') {
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
