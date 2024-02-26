<?php

namespace App\Rules;

use App\Models\HariLibur;
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
        $getDay = date('l', strtotime(request()->tglbukti . '+1 days'));
        $getTomorrow = date('Y-m-d', strtotime(request()->tglbukti . '+1 days'));
        $getHariLibur = HariLibur::where('tgl', $getTomorrow)->first();
        $allowed = false;
        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first();
        $getFormat = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'INPUT TRIP')->where('subgrp', 'FORMAT BATAS INPUT')->first();
        $getapproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->first();
        $bukaAbsensi = SuratPengantarApprovalInputTrip::where('tglbukti', '=', $date)
            ->where('statusapproval', $getapproval->id)
            ->orderBy('id', 'desc')
            ->first();
        if ($date == $today) {
            $allowed = true;
        }
        if ($getFormat->text == 'FORMAT 2') {
            if (date('Y-m-d', strtotime(request()->tglbukti . '+1 days')) . ' ' . $getBatasInput->text > date('Y-m-d H:i:s')) {
                $allowed = true;
            }
        }

        if (strtolower($getDay) == 'sunday') {
            $allowed = true;
        }
        if ($getHariLibur != null) {
            $allowed = true;
        }
        if ($bukaAbsensi) {
            $now = date('Y-m-d');
            $suratPengantar = SuratPengantar::where('tglbukti', '=', $date)->whereRaw("approvalbukatanggal_id = $bukaAbsensi->id")->count();
            $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();
            $cekApproval = SuratPengantarApprovalInputTrip::where('statusapproval', '=', $nonApproval->id)->where('tglbukti', '=', $date)->orderBy('id', 'desc')->first();

            $cekStatus =  DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
                ->where('a.tglbukti', $date)
                ->orderBy('a.id', 'desc')
                ->first();

            if ($cekApproval != '') {
                return false;
            } else {

                $now = date('Y-m-d H:i:s');
                if ($now > $cekStatus->tglbatas) {
                    return false;
                }
            }

            if ($bukaAbsensi->jumlahtrip < ($suratPengantar + 1)) {
                return false;
            }
            $allowed = true;
        }
        return $allowed;
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
