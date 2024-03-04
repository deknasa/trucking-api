<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTambahanInvoice implements Rule
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

        $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR BIAYA TAMBAHAN')->first();
        if ($cekStatus->text == 'YA') {
            foreach ($requestData['jobtrucking'] as $value) {

                $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $value)->get();
                for ($i = 0; $i < count($query); $i++) {
                    $trip = $query[$i];
                    $cekTambahan = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))->where('suratpengantar_id', $trip->id)->where('statusapproval', 4)->first();
                    if ($cekTambahan != '') {
                        $allowed = false;

                        if (strpos($listTrip, $trip->nobukti) === false) {
                            // If it doesn't exist, append the current element
                            if ($listTrip == '') {
                                $listTrip = "$trip->nobukti ($value)";
                            } else {
                                $listTrip = $listTrip . ', ' .  " $trip->nobukti ($value)";
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
