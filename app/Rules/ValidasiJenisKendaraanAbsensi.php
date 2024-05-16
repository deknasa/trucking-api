<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

class ValidasiJenisKendaraanAbsensi implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $trado;
    public $supir;
    public $tglabsen;
    public $statusjeniskendaraan;
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
        $trado_id = request()->trado_id;
        $supir_id = request()->supir_id;
        $statusjeniskendaraan = request()->statusjeniskendaraan;
        $tglabsensi = date('Y-m-d',strtotime(request()->tglabsensi));

        $this->trado = request()->trado;
        $this->supir = request()->supir;
        $this->tglabsen = request()->tglabsensi;
        $this->statusjeniskendaraan = $value;

        $absensidetail = DB::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
        ->select('a.*')
        ->where("a.trado_id",$trado_id)
        ->where("a.supir_id",$supir_id)
        ->where("a.statusjeniskendaraan",$statusjeniskendaraan)
        ->where("b.tglbukti",$tglabsensi)
        ->leftJoin("absensisupirheader as b", 'a.absensi_id', 'b.id')
        ->first();
        if ($absensidetail) {
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
        return 'jenis kendaraan dengan supir '. $this->supir .' di trado <b>'.$this->trado.'</b> pada tgl '. $this->tglabsen .' '. app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
