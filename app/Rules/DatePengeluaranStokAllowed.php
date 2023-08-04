<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\PengeluaranStokHeader;

class DatePengeluaranStokAllowed implements Rule
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
        $date = date('Y-m-d', strtotime($value));
        $allowed = true ;
        $todayValidation = PengeluaranStokheader::todayValidation($date);
        if (!$todayValidation) {
            $isBukaTanggalValidation = PengeluaranStokHeader::isBukaTanggalValidation($date,request()->pengeluaranstok_id);
            return $isBukaTanggalValidation;
        }

        return  $allowed;
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
