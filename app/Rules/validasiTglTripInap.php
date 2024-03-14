<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglTripInap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeerror;
    public $keterangantambahan;
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
        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS PENGAJUAN TRIP INAP')->where('subgrp', 'BATAS PENGAJUAN TRIP INAP')->first()->text;

        $batas = date('Y-m-d', strtotime("-$getBatasInput days"));
        $tgl = date('Y-m-d', strtotime($value));
        $trado_id = request()->trado_id ?? 0;
        $supir_id = request()->supir_id ?? 0;
        if ($tgl < $batas) {
            $cekApprove = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                ->where('tglabsensi', $tgl)
                ->where('trado_id', $trado_id)
                ->where('supir_id', $supir_id)
                ->first();
                
            if (isset($cekApprove)) {
                if ($cekApprove->statusapproval == 4) {
                    if ($cekApprove->statusapprovallewatbataspengajuan == 3) {
                        if (date('Y-m-d H:i:s', strtotime($cekApprove->tglbataslewatbataspengajuan)) < date('Y-m-d H:i:s')) {
                            $this->kodeerror = 'LB';
                            $this->keterangantambahan = 'input';
                            return false;
                        }
                    } else {

                        $this->kodeerror = 'BAP';
                        return false;
                    }
                }
            } else {
                $this->kodeerror = 'DTA';
                $this->keterangantambahan = '(PENGAJUAN TRIP INAP)';
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
        return app(ErrorController::class)->geterror($this->kodeerror)->keterangan . ' ' . $this->keterangantambahan;
    }
}
