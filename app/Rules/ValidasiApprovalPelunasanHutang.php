<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use App\Models\PelunasanHutangHeader;
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
    public $kodeerror;
    public $keterangan;
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
        for ($i = 0; $i < count(request()->bayarId); $i++) {

            $pelunasanHutangHeader = new PelunasanHutangHeader();
            $nobukti = PelunasanHutangHeader::from(DB::raw("pelunasanhutangheader"))->where('id', request()->bayarId[$i])->first();
            $cekdata = $pelunasanHutangHeader->cekvalidasiaksi($nobukti->nobukti, $nobukti->pengeluaran_nobukti);
            if ($cekdata['kondisi']) {
                $this->kodeerror = $cekdata['kodeerror'];
                $this->keterangan = $cekdata['keterangan'];
                return false;
            }
            // $cekPosting = DB::table("pelunasanhutangheader")->from(DB::raw("pelunasanhutangheader with (readuncommitted)"))
            // ->where('id', request()->bayarId[$i])->first();
            // if(isset($cekPosting)){
            //     if($cekPosting->pengeluaran_nobukti != ''){
            //         $posting++;
            //         if ($nobukti == '') {
            //             $nobukti = $cekPosting->nobukti;
            //         } else {
            //             $nobukti = $nobukti . ', ' . $cekPosting->nobukti;
            //         }
            //     }
            // }
        }
        // $this->nobukti = $nobukti;
        // if ($posting > 0) {
        //     return false;
        // }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {

        return $this->keterangan;
        // $error = new Error();
        // $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        // return 'NO BUKTI <b>'. $this->nobukti . '</b> <br>'. app(ErrorController::class)->geterror('SPOST')->keterangan . ' <br>' . $keterangantambahanerror;
    }
}
