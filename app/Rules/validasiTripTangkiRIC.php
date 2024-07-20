<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTripTangkiRIC implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trip;
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
        $dataTrip = request()->rincian_nobukti;
        $supir_id = request()->supir_id;
        $empty = 0;
        $listTanggal = '';
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first()->id ?? 0;
        if (request()->statusjeniskendaraan == $jenisTangki) {

            $statusJenis = 0;
            $lastStatusJenis = 0;
            $start = new DateTime(request()->tgldari);
            $end = new DateTime(request()->tglsampai);

            // Add one day to the end date to make the loop inclusive
            $end->modify('+1 day');

            $interval = new DateInterval('P1D'); // 1 day interval
            $period = new DatePeriod($start, $interval, $end);

            foreach ($period as $date) {
                $tgl = $date->format('Y-m-d');
                $getData = DB::table("triptangki")->from(DB::raw("triptangki as t with (readuncommitted)"))
                    ->select(DB::raw("STRING_AGG(cast(keterangan  as nvarchar(max)), ', ') as keterangan"))
                    ->whereRaw("t.id BETWEEN (SELECT MIN(triptangki_id) FROM suratpengantar where supir_id=$supir_id and tglbukti='$tgl' and statusjeniskendaraan=$jenisTangki) 
                 AND (SELECT MAX(triptangki_id) FROM  suratpengantar where supir_id=$supir_id and tglbukti='$tgl' and statusjeniskendaraan=$jenisTangki)")
                    ->whereRaw(" t.id NOT in (
                         SELECT triptangki_id
                         FROM suratpengantar as tr
                         WHERE t.id = tr.triptangki_id and tr.supir_id=$supir_id and tr.tglbukti='$tgl' and tr.statusjeniskendaraan=$jenisTangki
                     )")
                    ->first();
                if ($getData->keterangan != '') {
                    $empty++;
                    if ($listTanggal == '') {
                        $listTanggal =  $date->format('d-m-Y') . ' (' . $getData->keterangan . ')';
                    } else {
                        $listTanggal = $listTanggal . ', ' .  $date->format('d-m-Y') . ' (' . $getData->keterangan . ')';
                    }
                }
            }
            $this->trip = $listTanggal;
            if ($empty > 0) {
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
        return app(ErrorController::class)->geterror('DTA')->keterangan . '. ' . $this->trip;
    }
}
