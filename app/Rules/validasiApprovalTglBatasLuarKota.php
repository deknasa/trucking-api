<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiApprovalTglBatasLuarKota implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kode;
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
        if ($value != '') {

            $supir = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))->select('tglmasuk', DB::raw("(case when (year(tglbatastidakbolehluarkota) <= 2000) then null else tglbatastidakbolehluarkota end ) as tglbatastidakbolehluarkota"))
                ->where('id', request()->id)->first();
            $tglbatas = date("Y-m-d", strtotime($value));
            if ($tglbatas < $supir->tglmasuk) {
                $this->kode = 1;
                return false;
            }

            if ($supir->tglbatastidakbolehluarkota != '') {
                if ($value != '') {
                    if (date('Y-m-d', strtotime($supir->tglbatastidakbolehluarkota)) < $tglbatas) {

                        $batas = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'BATAS KM LUAR KOTA')->first()->text;


                        $cekSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                            ->where('jarak', '>', $batas)
                            ->where('supir_id', request()->id)
                            ->whereBetween('tglbukti', [date('Y-m-d', strtotime($supir->tglbatastidakbolehluarkota)), $tglbatas])
                            ->first();
                        if($cekSP != ''){
                            $this->kode = 2;
                            return false;
                        }

                    }
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
        if($this->kode == 1){
            return 'tgl batas ' . app(ErrorController::class)->geterror('MAX')->keterangan . ' tgl masuk';
        }
        if($this->kode == 2){
            return app(ErrorController::class)->geterror('ATLK')->keterangan;
        }
    }
}
