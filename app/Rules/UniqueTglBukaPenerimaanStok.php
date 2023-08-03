<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueTglBukaPenerimaanStok implements Rule
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
        $query = DB::table('bukapenerimaanstok')
            ->from(
                DB::raw("bukapenerimaanstok as a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
             ->where('a.tglbukti', '=', date('Y-m-d', strtotime(request()->tglbukti)))
             ->where('a.penerimaanstok_id', '=', request()->penerimaanstok_id)
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
        return app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
