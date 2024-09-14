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
    public $keterangan;
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
        $agen_id = request()->agen_id;
        $empty = 0;
        $different = 0;
        $listTrip = '';
        if ($agen_id != '' || $agen_id != 0) {

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
                    } else {
                        if ($cekPiutangExist->agen_id != $agen_id) {
                            $different++;
                        }
                    }
                }
            }
            $this->trip = $listTrip;
            if ($empty > 0) {
                $this->keterangan = app(ErrorController::class)->geterror('DTA')->keterangan . ' (' . $this->trip . ')';
                return false;
            }
            if ($different > 0) {
                $this->keterangan = 'DATA CUSTOMER PIUTANG ' . app(ErrorController::class)->geterror('TSD')->keterangan . ' CUSTOMER TERPILIH';
                return false;
            }
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
        return  $this->keterangan;
    }
}
