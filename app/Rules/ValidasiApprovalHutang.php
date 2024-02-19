<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiApprovalHutang implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nobukti;
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
        $empty = 0;
        $nobukti = '';
        for ($i = 0; $i <  count(request()->hutangId); $i++) {
            $hutang = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))->where('id', request()->hutangId[$i])->first();
            $pelunasanHutang = DB::table('pelunasanhutangdetail')
                ->from(
                    DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti',
                    'a.hutang_nobukti'
                )
                ->where('a.hutang_nobukti', '=', $hutang->nobukti)
                ->first();

            if ($pelunasanHutang != '') {
                $empty++;
                if ($nobukti == '') {
                    $nobukti = $hutang->nobukti;
                } else {
                    $nobukti = $nobukti . ', ' . $hutang->nobukti;
                }
            }
        }

        if($empty > 0)
        {
            $this->nobukti = $nobukti;
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
        return app(ErrorController::class)->geterror('PSD')->keterangan . ' (' . $this->nobukti.')';
    }
}
