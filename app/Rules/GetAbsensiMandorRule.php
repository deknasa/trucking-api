<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use App\Models\AbsensiSupirHeader;

class GetAbsensiMandorRule implements Rule
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
        $allow = true;
        $date = date('Y-m-d', strtotime($value));
        $todayValidation = AbsensiSupirHeader::todayValidation($date);
        //check apakah tanggal hari ini jika true  maka tidak masuk if
        if(!$todayValidation){
            $isBukaTanggalValidation = (new AbsensiSupirHeader())->isBukaTanggalValidation($date);
            $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti',$date)->first();
            //check apakah data sudah ada atau tidak, jika tidak masuk kedalam if
            if (!$absensiSupirHeader) {
                //check tanggal sudah dibuka
                return $isBukaTanggalValidation;
            }
            //cek tgl dari absensiSupirHeader
            $tglbatas = $absensiSupirHeader->tglbataseditabsensi ?? 0;
            $limit = strtotime($tglbatas);
            $now = strtotime('now');
            // dd($limit,$now);
            if ($now < $limit) return true;
            return false;
        }
        return  $allow;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('TBT')->keterangan;
    }
}
