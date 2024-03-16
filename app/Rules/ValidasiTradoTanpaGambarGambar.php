<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;

class ValidasiTradoTanpaGambarGambar implements Rule
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
        $allowed = true;
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('WI') ?? '';
        $kodetrado=request()->kodetrado ?? '';
        $keterangan_statusapproval= request()->gambar_statusapproval ?? 0;
        if ($keterangan_statusapproval==0) {
            $this->keterror = 'Status Tanpa Gambar Untuk Trado <b>' . $kodetrado . '</b> ' . $keteranganerror ;
            $allowed = false;
            return  $allowed;
        }

        
        return  $allowed;
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
