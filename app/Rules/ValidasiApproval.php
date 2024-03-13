<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;


class ValidasiApproval implements Rule
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
        // dd('testuji');
        $allowed=true;
        $table='pengeluaranheader';
        $databukti = request()->bukti;
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $nobukti1 = '';
        $a = 0;
        $parameter = new Parameter();

        foreach ($databukti as $dataBukti) {
            $getcetak = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti', 'nobukti')->where('nobukti', $dataBukti)
                ->first();
            if (!isset($getcetak)) {
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

   
        $data1='';
        $a=0;
        $b=0;
        foreach ($databukti as $dataBukti) {
            $getstatus = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('tglbukti', 'nobukti')->where('nobukti', $dataBukti)
            ->first();

            if ($a==0) {
                $data1=$getstatus->statusapproval ?? '';  
            } else {
                if ($data1 !=$getstatus->statusapproval) {
                    if ($b == 0) {
                        $nobukti1 = $nobukti1 . $dataBukti;
                    } else {
                        $nobukti1 = $nobukti1 . ', ' . $dataBukti;
                    }
                    $b = $b + 1;
                }
            }

        }

        if ($b >= 1) {
            $allowed = false;
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('ASB') ?? '';
  
            $this->keterror = 'No Bukti <b>' . $nobukti1 . '</b> <br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            // dd($this->keterror);
            // return $allowed;
            goto lanjut;
        }  

        // dd('test');
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
