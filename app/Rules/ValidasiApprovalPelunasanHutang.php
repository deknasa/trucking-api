<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiApprovalPelunasanHutang implements Rule
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
        $posting = 0;
        $nobukti = '';
        for($i = 0; $i<count(request()->bayarId); $i++)
        {
            $cekPosting = DB::table("pelunasanhutangheader")->from(DB::raw("pelunasanhutangheader with (readuncommitted)"))
            ->where('id', request()->bayarId[$i])->first();
            if(isset($cekPosting)){
                if($cekPosting->pengeluaran_nobukti != ''){
                    $posting++;
                    if ($nobukti == '') {
                        $nobukti = $cekPosting->nobukti;
                    } else {
                        $nobukti = $nobukti . ', ' . $cekPosting->nobukti;
                    }
                }
            }
        }
        $this->nobukti = $nobukti;
        if ($posting > 0) {
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
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        return 'NO BUKTI <b>'. $this->nobukti . '</b> <br>'. app(ErrorController::class)->geterror('SPOST')->keterangan . ' <br>' . $keterangantambahanerror;
    }
}
