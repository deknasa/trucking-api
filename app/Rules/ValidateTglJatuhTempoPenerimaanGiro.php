<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Agen;
use Illuminate\Contracts\Validation\Rule;

class ValidateTglJatuhTempoPenerimaanGiro implements Rule
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
        $agen = Agen::find(request()->agen_id);
        if ($agen != null) {
            $top = intval($agen->top);
            $dateNow = date('Y-m-d', strtotime(request()->tglbukti));
            $nextDay = date('d-m-Y', strtotime($dateNow . " +$top day"));
            if (strtotime($value) > strtotime($nextDay)) {
                return false;
            } else {
                return true;
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
        return app(ErrorController::class)->geterror('TOP')->keterangan;
    }
}
