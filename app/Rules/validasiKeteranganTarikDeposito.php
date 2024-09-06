<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiKeteranganTarikDeposito implements Rule
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
        if (request()->pengeluarantrucking_id != '') {
            $idpengeluaran = request()->pengeluarantrucking_id;
            $fetchFormat =  DB::table('pengeluarantrucking')
                ->where('id', $idpengeluaran)
                ->first();
            if ($fetchFormat->kodepengeluaran == 'TDE') {
                $supir = request()->supirheader;
                $ketDefault = "PENARIKAN DEPOSITO SUPIR " . $supir;
                $data = app(Controller::class)->like_match('%' . strtoupper($ketDefault) . '%', strtoupper($value));
                $this->keterangan = 'KETERANGAN DEPOSITO ' . app(ErrorController::class)->geterror('HMK')->keterangan . ' : <br>' . $ketDefault;
                return $data;
            }

            if ($fetchFormat->kodepengeluaran == 'TDEK') {
                $karyawan = request()->karyawanheader;
                $ketDefault = "PENARIKAN DEPOSITO KARYAWAN " . $karyawan;
                $data = app(Controller::class)->like_match('%' . strtoupper($ketDefault) . '%', strtoupper($value));
                $this->keterangan = 'KETERANGAN DEPOSITO ' . app(ErrorController::class)->geterror('HMK')->keterangan . ' : <br>' . $ketDefault;
                return $data;
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
        return $this->keterangan;
    }
}
