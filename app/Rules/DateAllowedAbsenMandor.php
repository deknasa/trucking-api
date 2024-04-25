<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BukaAbsensi;
use App\Models\AbsensiSupirHeader;
use App\Models\MandorAbsensiSupir;

class DateAllowedAbsenMandor implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
       
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
        $date = date('Y-m-d', strtotime($value));
        $today = date('Y-m-d', strtotime("today"));
        $allowed = false ;
        $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();
        $todayValidation = (new AbsensiSupirHeader)->todayValidation($date);
        $isDateAllowed = MandorAbsensiSupir::isDateAllowedMandor($date);

        if($todayValidation){
            $allowed = true;
        }
        if ($isDateAllowed){
            $allowed = true;
        }
        
        return $allowed ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tidak Bisa memilih tanggal tersebut';
    }
}
