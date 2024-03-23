<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiNobuktiPencairan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nomor;
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
        $detail = json_decode(request()->detail, true);
        $notExist = 0;
        $exist = 0;
        $nomor = '';
        if (request()->status == 591) {

            if (count($detail['nobukti']) > 0) {
                for ($i = 0; $i < count($detail['nobukti']); $i++) {
                    $query = DB::table("pencairangiropengeluaranheader")->from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))
                        ->where('pengeluaran_nobukti', $detail['nobukti'][$i])
                        ->first();

                    if ($query != '') {
                        if ($nomor == '') {
                            $nomor = $detail['nobukti'][$i];
                        } else {
                            $nomor = $nomor . ', ' . $detail['nobukti'][$i];
                        }
                        $exist++;
                    }else{
                        $notExist++;
                    }
                }
            }

            if ($exist > 0 && $notExist > 0) {
                $this->nomor = $nomor;
                return false;
            }
        }
        if (request()->status == 592) {
            
            $a = 0;
            $b = 0;
            if (count($detail['nobukti']) > 0) {
                for ($i = 0; $i < count($detail['nobukti']); $i++) {
                    $query = DB::table("penerimaanheader")->from(DB::raw("penerimaanheader with (readuncommitted)"))
                        ->where('nobukti', $detail['nobuktiCair'][$i])
                        ->first();
                    if ($query != '') {
                        if ($nomor == '') {
                            $nomor = $detail['nobukti'][$i];
                        } else {
                            $nomor = $nomor . ', ' . $detail['nobukti'][$i];
                        }
                        $a++;
                    }else{
                        $b++;
                    }
                }
            }

            if ($a > 0 && $b > 0) {
                $this->nomor = $nomor;
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
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        return 'sebagian data ' . app(ErrorController::class)->geterror('SCG')->keterangan . ' ' . $keterangantambahanerror;
    }
}
