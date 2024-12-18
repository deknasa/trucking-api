<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PengembalianKasGantungHeader;
use Illuminate\Contracts\Validation\Rule;

class CekMaxBayarPengembalianKasGantungEdit implements Rule
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
        $attribute = substr($attribute,8);
        $nobukti = request()->kasgantung_nobukti[$attribute];

        $kasgantung = new PengembalianKasGantungHeader();
        $getKasgantung = $kasgantung->getSisaEditPengembalianKasGantung(request()->id,$nobukti);

        $totalAwal = $getKasgantung->sisa + $value;
        //dd($totalAwal, $getKasgantung->sisa, $value);
        if((float)$value > (float)$totalAwal){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('NTLB')->keterangan.' nominal kasgantung';
    }
}
