<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\GajisUpirUangJalan;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiUangJalanEBS implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $error;
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

        $allSP = "";
        $requestData = json_decode(request()->dataric, true);
        foreach ($requestData['nobuktiRIC'] as $key => $value) {
            if ($key == 0) {
                $allSP = $allSP . "'$value'";
            } else {
                $allSP = $allSP . ',' . "'$value'";
            }
        }

        $isTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->first()->text ?? 'TIDAK';
        if ($isTangki == 'YA') {
            $gajiSupirUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                ->select(DB::raw("gajisupiruangjalan.kasgantung_nobukti,kasgantungheader.coakaskeluar, sum(gajisupiruangjalan.nominal) as nominal"))
                ->join(DB::raw("kasgantungheader with (readuncommitted)"), 'gajisupiruangjalan.kasgantung_nobukti', 'kasgantungheader.nobukti')
                ->whereRaw("gajisupiruangjalan.gajisupir_nobukti in ($allSP)")
                ->groupBy('gajisupiruangjalan.kasgantung_nobukti', 'kasgantungheader.coakaskeluar')
                ->get();
        } else {

            $gajiSupirUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
                ->select(DB::raw("absensisupirheader.kasgantung_nobukti,kasgantungheader.coakaskeluar, sum(gajisupiruangjalan.nominal) as nominal"))
                ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'gajisupiruangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
                ->join(DB::raw("kasgantungheader with (readuncommitted)"), 'absensisupirheader.kasgantung_nobukti', 'kasgantungheader.nobukti')
                ->whereRaw("gajisupiruangjalan.gajisupir_nobukti in ($allSP)")
                ->groupBy('absensisupirheader.kasgantung_nobukti', 'kasgantungheader.coakaskeluar')
                ->get();
        }
        $nilaiuangjalan = 0;
        $allKGT = "";
        foreach ($gajiSupirUangjalan as $key => $value) {
            if ($key == 0) {
                $allKGT = $allKGT . "'$value->kasgantung_nobukti'";
            } else {
                $allKGT = $allKGT . ',' . "'$value->kasgantung_nobukti'";
            }
        }

        if (request()->id != '') {
            $getPengembalian = DB::table("prosesgajisupirheader")->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
                ->where('id', request()->id)
                ->first();
        }
        // CEK PENGEMBALIAN KAS GANTUNG
        if ($allKGT != '') {

            $query = DB::table("pengembaliankasgantungdetail")->from(DB::raw("pengembaliankasgantungdetail with (readuncommitted)"))
                ->whereRaw("kasgantung_nobukti in ($allKGT)");
            if (request()->id != '') {
                if ($getPengembalian->pengembaliankasgantung_nobukti != '') {
                    $query->whereRaw("nobukti not in ('$getPengembalian->pengembaliankasgantung_nobukti')");
                }
            }
            $data = $query->first();

            if (isset($data)) {

                $error = new Error();
                $keteranganerror = $error->cekKeteranganError('SPI') ?? '';
                $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
                $this->error = 'NO BUKTI <b>' . $data->kasgantung_nobukti . '</b> ' . $keteranganerror . ' di <b>' . $data->nobukti . '</b> ' . $keterangantambahanerror;
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
        return $this->error;
    }
}
