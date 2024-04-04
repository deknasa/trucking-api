<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parameter;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Models\Error;

class ApprovalKirimBerkas implements Rule
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
    public $keterror;
    public $nobukti;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $table = request()->table;
        $databukti = request()->bukti;
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $nobukti1 = '';
        $a = 0;
        $parameter = new Parameter();

        if ($table == 'PEMUTIHANSUPIR') {
            $table = 'PEMUTIHANSUPIRHEADER';
        }
      
        foreach ($databukti as $dataBukti) {
            $getkirimberkas = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti', 'nobukti')->where('nobukti', $dataBukti)
                ->first();
            if (!isset($getkirimberkas)) {
                if ($a == 0) {
                    $nobukti1 = $nobukti1 . $dataBukti;
                } else {
                    $nobukti1 = $nobukti1 . ', ' . $dataBukti;
                }
                $a = $a + 1;
                // dump($nobukti1);
            }
        }

        if ($a >= 1) {
            $allowed = false;
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
  
            $this->keterror = 'No Bukti <b>' . $nobukti1 . '</b> <br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            // dd($this->keterror);
            // return $allowed;
            goto lanjut;
        }


        $allowed = false;
        $tutupBuku = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();
        $tutupBukuDate = date('Y-m-d', strtotime($tutupBuku->text));

        foreach ($value as $val) {
            $getTgl = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti', 'nobukti')->where('id', $val)->first();
            $date = date('Y-m-d', strtotime($getTgl->tglbukti));

            if ($date > $tutupBukuDate) {
                $allowed = true;
            }
        }
       
        $nobukti1 = '';
        $a = 0;
        foreach ($databukti as $dataBukti) {
            $getkirimberkas = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti', 'nobukti')->where('nobukti', $dataBukti)
                ->where('statuskirimberkas', $parameter->cekId('STATUSKIRIMBERKAS', 'STATUSKIRIMBERKAS', 'BELUM KIRIM BERKAS'))
                ->first();
            if (isset($getkirimberkas)) {
                if ($a == 0) {
                    $nobukti1 = $nobukti1 . $dataBukti;
                } else {
                    $nobukti1 = $nobukti1 . ', ' . $dataBukti;
                }
                $a = $a + 1;
                // dump($nobukti1);
            }
        }
        // dd($nobukti1);
        // $this->nobukti = $nobukti1;
        if ($a >= 1) {
            $allowed = false;
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('BC') ?? '';
  
            $this->keterror = 'No Bukti <b>' . $nobukti1 . '</b> <br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            // dd($this->keterror);
            // return $allowed;
            goto lanjut;
        }


        // 
        if ($allowed == false) {

            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $this->keterror = 'No Bukti <b>' . $getTgl->nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tutupBukuDate)) . ' ) <br> ' . $keterangantambahanerror;
            goto lanjut;
        }

        // 

        if ($allowed = true) {
            $table = request()->table;
            if ($table == 'PEMUTIHANSUPIR') {
                $table = 'PEMUTIHANSUPIRHEADER';
            }
            $allowed = false;
            $statusBelumKirimBerkas = Parameter::where('grp', '=', 'STATUSKIRIMBERKAS')->where('text', '=', 'KIRIM BERKAS')->first();

            foreach ($value as $val) {
                $item = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('statuskirimberkas')->where('id', $val)->where('statuskirimberkas', $statusBelumKirimBerkas->id)->first();
                if ($item) {
                    $allowed = true;
                }
            }
        }

        if ($allowed == false) {
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('BC') ?? '';
            $this->keterror = 'No Bukti <b>' . $this->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            goto lanjut;
        } else {

            $keterror = '';
        }
        lanjut:
        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->keterror;
    }
}
