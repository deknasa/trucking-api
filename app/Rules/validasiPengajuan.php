<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiPengajuan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        $tglabsensi = date('Y-m-d', strtotime(request()->tglabsensi));
        $trado_id = request()->trado_id ?? 0;
        $supir_id = request()->supir_id ?? 0;

        if (request()->tglabsensi != '' && $trado_id != 0 && $supir_id != 0) {
            $cekPengajuan = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                ->where('tglabsensi', $tglabsensi)
                ->where('trado_id', $trado_id)
                ->where('supir_id', $supir_id)
                ->first();
            if (isset($cekPengajuan)) {
                if ($cekPengajuan->statusapproval == 4) {
                    $this->keterangan = 'PENGAJUAN TRIP INAP ';
                    return false;
                } else {

                    $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS TRIP INAP')->where('subgrp', 'BATAS TRIP INAP')->first()->text;

                    $batas = date('Y-m-d', strtotime("-$getBatasInput days"));
                    $created_at =  date('Y-m-d', strtotime($cekPengajuan->created_at));
                    // dd($batas > $created_at,$batas ,$created_at);
                    if($batas > $created_at){
                        if($cekPengajuan->statusapprovallewatbataspengajuan == 4){
                            $this->keterangan = 'PENGAJUAN LEWAT BATAS TRIP INAP ';
                            return false;
                        }
                    }
                }
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
        return $this->keterangan . app(ErrorController::class)->geterror('BAP')->keterangan;
    }
}
