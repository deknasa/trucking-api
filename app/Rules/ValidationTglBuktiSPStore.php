<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\ApprovalBukaTanggalSuratPengantar;
use App\Models\SuratPengantarApprovalInputTrip;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidationTglBuktiSPStore implements Rule
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
        $check = (new SuratPengantarApprovalInputTrip())->storeTglValidation($value);
        $user_id = request()->user_id ?? 0;
        if ($check != null) {
            $queryTrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
                ->select('b.tglbukti', DB::raw("isnull(count(a.nobukti), 0) as jumlahtrip"))
                ->join(DB::raw("suratpengantarapprovalinputtrip as b with (readuncommitted)"), 'a.approvalbukatanggal_id', 'b.id')
                ->where("b.tglbukti", date('Y-m-d', strtotime($value)))
                ->where('b.user_id', $user_id)
                ->groupBy('b.tglbukti')
                ->first();
            $jumlahtrip = 0;
            if ($queryTrip != '') {
                $jumlahtrip = $queryTrip->jumlahtrip;
            }

            $cekStatus =  DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
                ->where('a.tglbukti', date('Y-m-d', strtotime($value)))
                ->where('a.user_id', $user_id)
                ->orderBy('a.id', 'desc')
                ->first();
                
            if ($cekStatus != '') {
                $now = date('Y-m-d H:i:s');
                if ($now > date('Y-m-d H:i:s', strtotime($cekStatus->tglbatas))) {
                    return true;
                } else {

                    $queryQuota = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
                        ->select('a.tglbukti', DB::raw("sum(a.jumlahtrip) as quotatrip"))
                        ->where("a.tglbukti", date('Y-m-d', strtotime($value)))
                        ->where('a.user_id', $user_id)
                        ->groupBy('a.tglbukti')
                        ->first();
                        
                    if ($queryQuota != '') {
                        if ($queryQuota->quotatrip < $jumlahtrip) {
                            return false;
                        }
                    } else {
                        return true;
                    }
                }
            }else{
                return true;
            }
        } else {
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
        return app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
