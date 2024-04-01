<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiKeteranganPengeluaranTrucking implements Rule
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
            if ($fetchFormat->kodepengeluaran == 'PJT') {
                $attribute = substr($attribute, 11);
                $supir = request()->supir[$attribute];
                $ketDefault = "PINJAMAN SUPIR " . $supir;
                $data = app(Controller::class)->like_match('%' . strtoupper($ketDefault) . '%', strtoupper($value));
                $this->keterangan = 'KETERANGAN PINJAMAN ' . app(ErrorController::class)->geterror('HMK')->keterangan . ' : <br>' . $ketDefault;
                return $data;
            }

            if ($fetchFormat->kodepengeluaran == 'PJK') {
                $attribute = substr($attribute, 11);
                $karyawan = request()->karyawan[$attribute];
                $ketDefault = "PINJAMAN KARYAWAN " . $karyawan;
                $data = app(Controller::class)->like_match('%' . strtoupper($ketDefault) . '%', strtoupper($value));
                $this->keterangan = 'KETERANGAN PINJAMAN ' . app(ErrorController::class)->geterror('HMK')->keterangan . ' : <br>' . $ketDefault;
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
