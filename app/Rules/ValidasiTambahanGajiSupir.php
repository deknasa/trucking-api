<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTambahanGajiSupir implements Rule
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
        $allowed = true;
        $listTrip = '';

        $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR BIAYA TAMBAHAN')->first();
        if ($cekStatus->text == 'YA') {
            $dataTrip = request()->rincian_nobukti;
            if ($dataTrip != '') {
                for ($i = 0; $i < count(request()->rincian_nobukti); $i++) {
                    $nobukti = request()->rincian_nobukti[$i];

                    $query = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan as a with (readuncommitted)"))
                        ->join(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.suratpengantar_id', 'b.id')
                        ->where('b.nobukti', $nobukti)
                        ->where('a.statusapproval', 4)
                        ->first();

                    if ($query != '') {
                        $allowed = false;
                        if (strpos($listTrip, $nobukti) === false) {
                            // If it doesn't exist, append the current element
                            if ($listTrip == '') {
                                $listTrip = $nobukti;
                            } else {
                                $listTrip = $listTrip . ', ' . $nobukti;
                            }
                        }
                    } else {

                        $queryTambahan = DB::table("saldosuratpengantarbiayatambahan")->from(DB::raw("saldosuratpengantarbiayatambahan as a with (readuncommitted)"))
                            ->join(DB::raw("saldosuratpengantar as b with (readuncommitted)"), 'a.suratpengantar_id', 'b.id')
                            ->where('b.nobukti', $nobukti)
                            ->where('a.statusapproval', 4)
                            ->first();
                        if ($queryTambahan != '') {
                            $allowed = false;
                            if (strpos($listTrip, $nobukti) === false) {
                                // If it doesn't exist, append the current element
                                if ($listTrip == '') {
                                    $listTrip = $nobukti;
                                } else {
                                    $listTrip = $listTrip . ', ' . $nobukti;
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
        return app(ErrorController::class)->geterror('BTBA')->keterangan . ' (' . $this->trip . ')';
    }
}
