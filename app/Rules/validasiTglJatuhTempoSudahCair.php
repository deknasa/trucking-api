<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglJatuhTempoSudahCair implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nomor;
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
        $exist = 0;
        $nomor = '';
        for ($i = 0; $i < count($detail['nobukti']); $i++) {
            $cekPenerimaan = DB::table("penerimaanheader")->from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->where('penerimaangiro_nobukti', $detail['nobukti'][$i])
                ->first();
            if ($cekPenerimaan != '') {
                if ($nomor == '') {
                    $nomor = $detail['nobukti'][$i];
                } else {
                    $nomor = $nomor . ', ' . $detail['nobukti'][$i];
                }
                $exist++;
            }
        }

        if($exist > 0){
            $this->nomor = $nomor;
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
        return 'NO BUKTI <b>'.$this->nomor .'</b><br>'. app(ErrorController::class)->geterror('SCG')->keterangan;
    }
}
