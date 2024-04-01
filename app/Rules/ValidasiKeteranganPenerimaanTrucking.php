<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiKeteranganPenerimaanTrucking implements Rule
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
        if (request()->penerimaantrucking_id != '') {
            $idpenerimaan = request()->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();
            if ($fetchFormat->kodepenerimaan == 'DPO') {
                $attribute = substr($attribute, 11);
                $supir = request()->supir[$attribute];
                $ketDefault = "DEPOSITO SUPIR " . $supir;
                $data = app(Controller::class)->like_match('%' . strtoupper($ketDefault) . '%', strtoupper($value));
                $this->keterangan = 'KETERANGAN DEPOSITO ' . app(ErrorController::class)->geterror('HMK')->keterangan . ' : <br>' . $ketDefault;
                return $data;
            }

            if ($fetchFormat->kodepenerimaan == 'DPOK') {
                $attribute = substr($attribute, 11);
                $karyawan = request()->karyawandetail[$attribute];
                $ketDefault = "DEPOSITO KARYAWAN " . $karyawan;
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
