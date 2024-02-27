<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueTglBukaAbsensi implements Rule
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
        $query = DB::table('bukaabsensi')
            ->from(
                DB::raw("bukaabsensi as a with (readuncommitted)")
            )
            ->select(
                'a.id',
                'a.mandor_user_id'
            )
             ->where('a.tglabsensi', '=', date('Y-m-d', strtotime(request()->tglabsensi)))
             ->where('a.mandor_user_id', '=', request()->user_id)
            ->first();


         
        if (isset($query))  {
            $nilai = false;
        } else {
            $nilai = true;
        }

        return $nilai;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return request()->user.' ' .app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
