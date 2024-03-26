<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiApprovalPengajuanTripInap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $data;
    public $keterangan;
    public $keterangantambahanerror;
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
        $empty = 0;
        $exist = 0;
        $data = '';
        
        $error = new Error();
        for ($i = 0; $i < count(request()->Id); $i++) {
            $pengajuan = DB::table('pengajuantripinap')->from(DB::raw("pengajuantripinap with (readuncommitted)"))->where('id', request()->Id[$i])->first();
            if (isset($pengajuan)) {
                $tripInap = DB::table("tripinap")->from(DB::raw("tripinap with (readuncommitted)"))
                    ->select('tripinap.tglabsensi', 'supir.namasupir', 'trado.kodetrado')
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tripinap.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tripinap.supir_id', 'supir.id')
                    ->where('tripinap.tglabsensi', $pengajuan->tglabsensi)
                    ->where('tripinap.trado_id', $pengajuan->trado_id)
                    ->where('tripinap.supir_id', $pengajuan->supir_id)
                    ->first();

                if (isset($tripInap)) {
                    $tgl = date('d-m-Y', strtotime($tripInap->tglabsensi));

                    // 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> No Bukti pelunasan hutang <b>'. $pelunasanhutangheader->nobukti .'</b> <br> '.$keterangantambahanerror,
                    $data .= "Absensi <b>$tgl</b> Trado <b>$tripInap->kodetrado</b> supir <b>$tripInap->namasupir</b><br>";
                    $empty++;
                }
            }else{
                $trado = request()->trado[$i];
                $supir = request()->supir[$i];
                $absen = request()->absen[$i];

                $data .= 'Absensi <b>' . $absen . '</b> trado <b>' . $trado . '</b> supir <b>' . $supir . '</b><br>';
                $exist++;
            }
        }

        if ($empty > 0) {
            
            $this->keterangantambahanerror = (new Error())->cekKeteranganError('PTBL') ?? '';
            $this->keterangan = $error->cekKeteranganError('SATL2') ?? '';
            $this->data = $data. $this->keterangan. ' (trip inap) <br> ' . $this->keterangantambahanerror;
            return false;
        }
        
        if ($exist > 0) {
            
            $this->keterangantambahanerror = (new Error())->cekKeteranganError('PTBL') ?? '';
            $this->keterangan = $error->cekKeteranganError('DTA') ?? '';
            $this->data = $data. $this->keterangan. ' <br> ' . $this->keterangantambahanerror;
            return false;
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
        return $this->data;
    }
}
