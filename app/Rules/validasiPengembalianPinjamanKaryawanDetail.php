<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;

class validasiPengembalianPinjamanKaryawanDetail implements Rule
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
        $karyawanheader_id = request()->karyawanheader_id ?? 0;
        if ($karyawanheader_id != 0) {
            $karyawan_ids = request()->karyawan_id;
            $hasDifferentValue = false;

            if (isset($karyawan_ids)) {

                foreach ($karyawan_ids as $karyawan_id) {
                    if ($karyawan_id != $karyawanheader_id) {
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
        return 'DATA KARYAWAN PJK ' . app(ErrorController::class)->geterror('TSD')->keterangan . ' KARYAWAN TERPILIH';
        return 'The validation error message.';
    }
}
