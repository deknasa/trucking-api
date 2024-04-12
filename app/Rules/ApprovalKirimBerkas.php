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
            $getcetak = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti', 'nobukti')->where('nobukti', $dataBukti)
                ->where('statuscetak', $parameter->cekId('STATUSCETAK', 'STATUSCETAK', 'BELUM CETAK'))
                ->first();
            if (isset($getcetak)) {
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
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();

            foreach ($value as $val) {
                $item = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('statuscetak')->where('id', $val)->where('statuscetak', $statusBelumCetak->id)->first();
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

        // STATUSKIRIMBERKAS	STATUSKIRIMBERKAS		BELUM KIRIM BERKAS
        $defaultidbelumkirimberkas = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
        ->select(
            'a.id'
        )
        ->where('a.grp', 'STATUSKIRIMBERKAS')
        ->where('a.subgrp', 'STATUSKIRIMBERKAS')
        ->where('a.text', 'BELUM KIRIM BERKAS')
        ->first()->id ?? '';


        $data1 = '';
        $a = 0;
        $b = 0;
        $ketstatus = '';
        foreach ($databukti as $dataBukti) {
            $getstatus = DB::table($table)->from(DB::raw($table ." a with (readuncommitted)"))->select(db::raw("isnull(a.statuskirimberkas,".$defaultidbelumkirimberkas.") as statuskirimberkas"), 'a.nobukti')->where('a.nobukti', $dataBukti)
                ->first();

            if ($a == 0) {
                $data1 = $getstatus->statuskirimberkas ?? '';
            } else {
                if ($data1 != $getstatus->statuskirimberkas) {
                    $ketstatus = $parameter->cekdataText($getstatus->statuskirimberkas) ?? '';
                    if ($b == 0) {
                        $nobukti1 = $nobukti1 . $dataBukti;
                    } else {
                        $nobukti1 = $nobukti1 . ', ' . $dataBukti;
                    }
                    $b = $b + 1;
                }
            }
            $a = $a + 1;
        }


        if ($b >= 1) {
            $allowed = false;
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('ASB') ?? '';

            $this->keterror = 'No Bukti <b>' . $nobukti1 . '</b> Status ' . $ketstatus . ' <br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            // dd($this->keterror);
            // return $allowed;
            goto lanjut;
        }

        // 
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
