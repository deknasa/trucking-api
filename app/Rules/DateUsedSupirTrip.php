<?php

namespace App\Rules;

use App\Models\Supir;
use App\Models\AbsensiSupirDetail;
use App\Models\ApprovalSupirTanpa;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class DateUsedSupirTrip implements Rule
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
        $supir = Supir::from(DB::raw("supir with (readuncommitted)"))
        ->where('noktp',request()->noktp)
        ->where('namasupir',request()->namasupir)
        ->first();

        $tglabsensi = date('Y-m-d',strtotime($value))." 23:55:59";
        // cek tgl batas diniput pada tanggal yang sudah lewat
        
        $value = strtotime($value);
        $today = strtotime("today");

        $cekApproval = (new ApprovalSupirTanpa())->cekApproval($supir);
        if( $cekApproval['gambar'] || $cekApproval['keterangan'] ){
            if($value < $today ){
                $saldosuratpengantar = DB::table('saldosuratpengantar')
                ->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('supir_id', $supir->id)
                ->where('tglbukti','>',$tglabsensi)
                ->get();

                $absensi = DB::table('absensisupirapprovalheader')
                ->from(DB::raw("absensisupirapprovalheader as header with (readuncommitted)"))
                ->where('header.tglbukti','>',$tglabsensi)
                ->where('detail.supir_id', $supir->id)
                ->leftJoin(DB::raw("absensisupirapprovaldetail as detail with (readuncommitted)"), 'detail.absensisupirapproval_id', 'header.id')
                ->get();

                if(count($absensi)||count($saldosuratpengantar)){
                    return false;
                }else{
                    (new AbsensiSupirDetail())->updateFromApprovalTanpa($tglabsensi, $supir->id);
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
        return 'The validation error message.';
    }
}
