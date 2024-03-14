<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;

class ValidasiApprovalHutang implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nobukti;
    public $keterror;
    public $errorid;
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
        $this->errorid=1;
        $allowed = true;
        $table = 'hutangheader';
        $databukti = request()->bukti;
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $nobukti1 = '';
        $a = 0;
        $parameter = new Parameter();
        // dd($databukti);
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
            $this->errorid=2;
            return $allowed;
            // goto lanjut;
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
            $this->errorid=2;
            // dd($this->keterror);
            return $allowed;
        
        }
        
        $this->errorid=1;
        $empty = 0;
        $nobukti = '';
        for ($i = 0; $i <  count(request()->hutangId); $i++) {
            $hutang = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))->where('id', request()->hutangId[$i])->first();
            $pelunasanHutang = DB::table('pelunasanhutangdetail')
                ->from(
                    DB::raw("pelunasanhutangdetail as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti',
                    'a.hutang_nobukti'
                )
                ->where('a.hutang_nobukti', '=', $hutang->nobukti)
                ->first();

            if ($pelunasanHutang != '') {
                $empty++;
                if ($nobukti == '') {
                    $nobukti = $hutang->nobukti;
                } else {
                    $nobukti = $nobukti . ', ' . $hutang->nobukti;
                }
            }
        }

        if($empty > 0)
        {
            $this->nobukti = $nobukti;
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
        if ($this->errorid==2) {
            return $this->keterror;
        } else {
            return app(ErrorController::class)->geterror('PSD')->keterangan . ' (' . $this->nobukti.')';
        }
        
        
    }
}
