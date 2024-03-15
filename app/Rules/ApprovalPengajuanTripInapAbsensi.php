<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ApprovalPengajuanTripInapAbsensi implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterangan;
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
        $id = request()->id;
        $exist = 0;
        $ket = '';
        for ($i = 0; $i < count($id); $i++) {

            $getAbsensi = DB::table("absensisupirheader")->from(DB::raw("absensisupirheader with (readuncommitted)"))->where('id', $id[$i])->first();
            if (isset($getAbsensi)) {
                $cekPengajuan = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                    ->where('tglabsensi', $getAbsensi->tglbukti)
                    ->first();
                if ($cekPengajuan != '') {
                    $ket .= 'absensi <b>' . $getAbsensi->nobukti . '</b> di tanggal <b>' . date('d-m-Y', strtotime($getAbsensi->tglbukti)).'</b> <br>';
                    $exist++;
                }
            }
        }

        if ($exist > 0) {
            $this->keterangan = $ket;
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
        return $this->keterangan . app(ErrorController::class)->geterror('SPI')->keterangan . '<br> di pengajuan trip inap';
    }
}
