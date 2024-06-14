<?php

namespace App\Rules;

use App\Models\AbsensiSupirDetail;
use App\Models\AbsensiSupirHeader;
use App\Models\MandorAbsensiSupir;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class AbsensiSupirRICUangJalanRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    protected $data;
    public $kodeerror;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = $this->data;
        $AbsensiSupirHeader = AbsensiSupirHeader::where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))->first();
        if ($AbsensiSupirHeader) {
            $statustambahantrado = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id','text')->where('grp', 'TAMBAHAN TRADO ABSENSI')->where('subgrp', 'TAMBAHAN TRADO ABSENSI')->where('text', 'TIDAK')->first();

            if ($data['statustambahantrado']=="YA") {
                $statustambahantrado = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('id','text')->where('grp', 'TAMBAHAN TRADO ABSENSI')->where('subgrp', 'TAMBAHAN TRADO ABSENSI')->where('text', 'YA')->first();
            }
            if ((new MandorAbsensiSupir)->isTradoMilikSupir()) {
                $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $AbsensiSupirHeader->id)->where('trado_id', $data['trado_id'])->where('statustambahantrado', $statustambahantrado->id)->where('supirold_id', $data['supirold_id'])->lockForUpdate()->first();
            } else {
                $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $AbsensiSupirHeader->id)->where('trado_id', $data['trado_id'])->where('statustambahantrado', $statustambahantrado->id)->lockForUpdate()->first();
            }

            if ($absensiSupirDetail) {
                $validasi = (new MandorAbsensiSupir)->validasiRICUangJalan($data,$absensiSupirDetail);
                $this->kodeeror = $validasi[1];
                return $validasi[0];
            }
        }else {
            $this->kodeeror ="";
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
        $message ="";
        if ($this->kodeeror =="01") {
            $message .= "Sudah Ada Uang Jalan ";
        }
        if ($this->kodeeror =="10") {
            $message .= "Sudah Ada RIC ";
        }
        if ($this->kodeeror =="11") {
            $message .= "Sudah Ada Uang Jalan dan Sudah Ada RIC ";
        }
        if ($this->kodeeror ="") {
            $message ="";
        }

        return $message;
    }
}
