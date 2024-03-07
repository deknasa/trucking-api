<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BukaAbsensi;
use App\Models\AbsensiSupirHeader;

class DateAllowedAbsen implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
       
    }
    
    private $message;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $date = date('Y-m-d', strtotime($value));
        $today = date('Y-m-d', strtotime("today"));
        $allowed = true ;
        $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();
        $todayValidation = AbsensiSupirHeader::todayValidation($date);
        //check apakah tanggal hari ini jika true  maka tidak masuk if
        if(!$todayValidation){
            $absensiSupirHeader = (new AbsensiSupirHeader())->where('tglbukti',$date)->first();
            $isBukaTanggalValidation = (new AbsensiSupirHeader())->isBukaTanggalValidation($date);

            if (!$absensiSupirHeader) {
                //check tanggal sudah dibuka
                return $isBukaTanggalValidation;
            }
            $tglbatas = $absensiSupirHeader->tglbataseditabsensi ?? 0;
            $limit = strtotime($tglbatas);
            $now = strtotime('now');
            // dd($limit,$now);
            if ($now < $limit) return true;
            return false;
        }
        return  $allowed;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->message) {
            return $this->message;
        }
        return 'Tidak Bisa memilih tanggal tersebut';
    }
}
