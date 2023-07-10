<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\SuratPengantarApprovalInputTrip;
use App\Models\SuratPengantar;
use Illuminate\Support\Facades\DB;

class DateApprovalQuota implements Rule
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
        $date = date('Y-m-d', strtotime($value));
        $today = date('Y-m-d', strtotime("today"));
        $allowed = false ;
        
        $bukaAbsensi = SuratPengantarApprovalInputTrip::where('tglbukti', '=', $date)
        ->sum('jumlahtrip');
        if($date == $today){
            $allowed = true;
        }
        if ($bukaAbsensi){
            $suratPengantar = SuratPengantar::where('tglbukti', '=', $date)->count();
            $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();
            $cekApproval = SuratPengantarApprovalInputTrip::where('statusapproval', '=', $nonApproval->id)->where('tglbukti', '=', $date)->first();
            if($cekApproval){
                return false;
            }
            if($bukaAbsensi < ($suratPengantar+1)){
                return false;
            }
            $allowed = true;
        }
        
        return $allowed ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tanggal Sudah Tidak Berlaku';
    }
}
