<?php

namespace App\Rules;

use App\Models\Trado;
use App\Models\SuratPengantar;
use App\Models\AbsensiSupirDetail;
use App\Models\ApprovalTradoTanpa;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

class DateUsedTradoTrip implements Rule
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
        $trado = Trado::from(DB::raw("trado with (readuncommitted)"))->where('kodetrado',request()->kodetrado)->first();
        $tglabsensi = date('Y-m-d',strtotime($value))." 23:55:59";
        // cek tgl batas diniput pada tanggal yang sudah lewat

        $value = strtotime($value);
        $today = strtotime("today");
        $cekApproval = (new ApprovalTradoTanpa())->cekApproval($trado);
        if( $cekApproval['gambar'] || $cekApproval['keterangan'] ){
            if($value < $today ){
                $saldosuratpengantar = DB::table('saldosuratpengantar')
                ->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('trado_id', $trado->id)
                ->where('tglbukti','>',$tglabsensi)
                ->get();

                $absensi = DB::table('absensisupirapprovalheader')
                ->from(DB::raw("absensisupirapprovalheader as header with (readuncommitted)"))
                ->where('header.tglbukti','>',$tglabsensi)
                ->where('detail.trado_id', $trado->id)
                ->leftJoin(DB::raw("absensisupirapprovaldetail as detail with (readuncommitted)"), 'detail.absensisupirapproval_id', 'header.id')
                ->get();
                if(count($absensi)||count($saldosuratpengantar)){
                    return false;
                }else{
                    (new AbsensiSupirDetail())->deleteFromApprovalTanpa($tglabsensi, $trado->id);
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
