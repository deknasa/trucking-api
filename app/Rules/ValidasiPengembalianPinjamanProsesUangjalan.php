<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiPengembalianPinjamanProsesUangjalan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        $supir_id = request()->supir_id;
        $pjts = request()->pengeluarantruckingheader_nobukti;
        $hasDifferentValue = false;

        if (isset($pjts)) {

            foreach ($pjts as $pjt) {
                $query = DB::table("pengeluarantruckingdetail")->from(db::raw("pengeluarantruckingdetail with (readuncommitted)"))
                    ->where('nobukti', $pjt)
                    ->first();
                if (isset($query)) {
                    if ($query->supir_id != $supir_id) {
                        $hasDifferentValue = true;
                        return false;
                    }
                }
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
        return 'DATA SUPIR PJT ' . app(ErrorController::class)->geterror('TSD')->keterangan . ' SUPIR TERPILIH';
    }
}
