<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglPengajuanTripInap implements Rule
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
        if ($tgl < $batas) {
            $cekApprovalPengajuan = DB::table("absensisupirheader")->from(DB::raw("absensisupirheader with (readuncommitted)"))
                ->where('tglbukti',  $tgl)->first();
            if (isset($cekApprovalPengajuan)) {
                if ($cekApprovalPengajuan->statusapprovalpengajuantripinap == 4) {
                    $this->kodeerror = 'BAP';
                    return false;
                }
                if ($cekApprovalPengajuan->statusapprovalpengajuantripinap == 3) {
                    if (date('Y-m-d H:i:s', strtotime($cekApprovalPengajuan->tglbataspengajuantripinap)) < date('Y-m-d H:i:s')) {
                        $this->kodeerror = 'LB';
                        $this->keterangantambahan = 'input';
                        return false;
                    }
                }
            } else {
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
