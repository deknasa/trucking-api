<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiPiutangPelunasan implements Rule
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
        $dataPiutang = request()->piutang_nobukti;
        $empty = 0;
        $listTrip = '';
        if ($dataPiutang != '') {

            for ($i = 0; $i < count($dataPiutang); $i++) {
                $cekPiutangExist = DB::table("piutangheader")->from(DB::raw("piutangheader with (readuncommitted)"))
                    ->where('nobukti', $dataPiutang[$i])->first();
                if ($cekPiutangExist == '') {
                    $empty++;
                    if ($listTrip == '') {
                        $listTrip = $dataPiutang[$i];
                    } else {
                        $listTrip = $listTrip . ', ' . $dataPiutang[$i];
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
