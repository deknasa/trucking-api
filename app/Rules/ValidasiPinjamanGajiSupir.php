<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiPinjamanGajiSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $pjt;
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
        $nobuktiPjt = request()->pinjPribadi_nobukti;
        $allowed = true;
        $listPjt = '';
        for ($i = 0; $i < count($nobuktiPjt); $i++) {
            $query = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->where('nobukti', $nobuktiPjt[$i])
                ->first();

            if (isset($query)) {
                if ($query->supir_id != request()->supir_id) {
                    $allowed = false;
                    if ($listPjt == '') {
                        $listPjt = $nobuktiPjt[$i];
                    } else {
                        $listPjt = $listPjt . ', ' . $nobuktiPjt[$i];
                    }
                }
            }
        }
        $this->pjt = $listPjt;
        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->pjt . ' BUKAN MILIK ' . request()->supir;
    }
}
