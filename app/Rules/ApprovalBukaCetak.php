<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Parameter;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Models\Error;

class ApprovalBukaCetak implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterror;
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
        $table = request()->table;
        if($table == 'PEMUTIHANSUPIR'){
            $table = 'PEMUTIHANSUPIRHEADER';
        }
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $allowed = false;
        $tutupBuku = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();
        $tutupBukuDate = date('Y-m-d', strtotime($tutupBuku->text));

        foreach ($value as $val) {
            $getTgl = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti','nobukti')->where('id', $val)->first();
            $date = date('Y-m-d', strtotime($getTgl->tglbukti));

            if ($date > $tutupBukuDate) {
                $allowed = true;
            }
        }
        
        // 
        if ($allowed==false) {
        
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $this->keterror = 'No Bukti <b>' . $getTgl->nobukti . '</b><br>' . $keteranganerror . '<br> ( '.date('d-m-Y', strtotime($tutupBukuDate)).' ) <br> '.$keterangantambahanerror;
            goto lanjut;
        } 
       
        // 

        if ($allowed=true) {
            $table = request()->table;
            if($table == 'PEMUTIHANSUPIR'){
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

        if ($allowed==false) {
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $this->keterror ='No Bukti <b>'. $getTgl->nobukti . '</b><br>' .$keteranganerror.' <br> '.$keterangantambahanerror;            
            goto lanjut;
        } else {

            $keterror='';
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
