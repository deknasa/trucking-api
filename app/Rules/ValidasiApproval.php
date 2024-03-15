<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


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
        $allowed = true;
        $table = 'pengeluaranheader';
        $databukti = request()->bukti;
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $tempbukti = '##tempbukti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbukti, function ($table) {
            $table->string('nobukti', 100)->nullable();
        });


        $nobukti1 = '';
        $a = 0;
        $parameter = new Parameter();

        foreach ($databukti as $dataBukti) {
            DB::table($tempbukti)->insert(
                [
                    'nobukti' => $dataBukti,
                ]
            );

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


        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup=$parameter->cekText('TUTUP BUKU','TUTUP BUKU') ?? '1900-01-01';
        $tgltutup=date('Y-m-d', strtotime($tgltutup));   
        $querytutup=db::table("pengeluaranheader")->from(db::raw("pengeluaranheader a with (readuncommitted)"))        
        ->select(
            db::raw("isnull(STRING_AGG(a.nobukti, ', '),'') as nobukti")   
            // 'a.nobukti'
        )
        ->join(db::raw($tempbukti." b"),'a.nobukti','b.nobukti')
        ->whereraw("a.tglbukti<='". $tgltutup ."'")
        ->first();
        $nobukti= $querytutup->nobukti ?? '';
        // dd($querytutup);
        if ($nobukti!='') {
            $nobukti= $querytutup->nobukti ?? '';
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $this->keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( '.date('d-m-Y', strtotime($tgltutup)).' ) <br> '.$keterangantambahanerror;
            $this->errorid=2;
            $allowed = false;
            return $allowed;
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


        $data1 = '';
        $a = 0;
        $b = 0;
        $ketstatus = '';
        foreach ($databukti as $dataBukti) {
            $getstatus = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('statusapproval', 'nobukti')->where('nobukti', $dataBukti)
                ->first();

            if ($a == 0) {
                $data1 = $getstatus->statusapproval ?? '';
            } else {
                if ($data1 != $getstatus->statusapproval) {
                    $ketstatus = $parameter->cekdataText($getstatus->statusapproval) ?? '';
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
