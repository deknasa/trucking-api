<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use App\Models\AbsensiSupirHeader;
use Illuminate\Support\Facades\DB;

class GetAbsensiMandorRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $errorid;
    public $nobuktipengeluaran;
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
        $this->errorid=1;
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
            if ($now < $limit) {
                // cek absensisupir approval
                $nobukti= $absensiSupirHeader->nobukti;
                $queryapp=db::table("absensisupirapprovalheader")->from(db::raw("absensisupirapprovalheader a with (readuncommitted)"))
                ->select(
                    'a.pengeluaran_nobukti'
                )
                ->where('a.absensisupir_nobukti',$nobukti)
                ->whereraw("isnull(a.pengeluaran_nobukti,'')<>''")
                ->first();
                if (isset($queryapp)) {
                    $this->nobuktipengeluaran= $queryapp->pengeluaran_nobukti;
                    $this->errorid=2;
                    return false;
                } else {
                    return true;
                }

                
            }
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
        if ($this->errorid==1) {
            return app(ErrorController::class)->geterror('TBT')->keterangan;
        } else {
            return app(ErrorController::class)->geterror('SDP')->keterangan. ' '.$this->nobuktipengeluaran;
        }
        
    }
}
