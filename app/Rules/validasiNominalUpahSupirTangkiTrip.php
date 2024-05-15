<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiNominalUpahSupirTangkiTrip implements Rule
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
        $query = DB::table("upahsupirtangkirincian")
            ->select(DB::raw("isnull(nominalsupir,0) as nominalsupir"))
            ->where('upahsupirtangki_id', request()->upah_id)
            ->where('triptangki_id', request()->triptangki_id)
            ->first();
        if (isset($query)) {
            if ($query->nominalsupir == 0) {
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
        return  app(ErrorController::class)->geterror('NUSTBA')->keterangan . ' (' . request()->triptangki . ')';
    }
}
