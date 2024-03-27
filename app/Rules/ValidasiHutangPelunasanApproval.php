<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiHutangPelunasanApproval implements Rule
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
        $dataPiutang = request()->hutang_nobukti;
         $noApprove = 0;
        $listTrip = '';
        if ($dataPiutang != '') {

            for ($i = 0; $i < count($dataPiutang); $i++) {
                $cekHutangApproval = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))
                    ->where('nobukti', $dataPiutang[$i])->first();
                if ($cekHutangApproval != '') {
                    if($cekHutangApproval->statusapproval != 3){

                        $noApprove++;
                        if ($listTrip == '') {
                            $listTrip = $dataPiutang[$i];
                        } else {
                            $listTrip = $listTrip . ', ' . $dataPiutang[$i];
                        }
                    }
                }
            }
        }
        $this->trip = $listTrip;
        if ($noApprove > 0) {
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
        return app(ErrorController::class)->geterror('BAP')->keterangan . ' (' . $this->trip . ')';
    }
}
