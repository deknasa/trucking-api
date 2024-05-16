<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTripTangkiEditTrip implements Rule
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
        $supir_id = request()->supir_id;
        $trado_id = request()->trado_id;
        $triptangki_id = request()->triptangki_id;
        $tglbukti = date('Y-m-d', strtotime(request()->tglbukti));
        $statusjeniskendaraan = request()->statusjeniskendaraan;
        $id = request()->id;

        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('trado_id', $trado_id)
            ->where('supir_id', $supir_id)
            ->where('tglbukti', $tglbukti)
            ->where('statusjeniskendaraan', $statusjeniskendaraan)
            ->where('triptangki_id', $triptangki_id)
            ->where('id', '<>', $id)
            ->first();
        if (isset($query)) {
            $this->trip = $query->nobukti;
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
        return request()->triptangki . ' ' . app(ErrorController::class)->geterror('SPI')->keterangan . ' (' . $this->trip . ')';
    }
}
