<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTujuanKota implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $Kota;
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

        $kota=request()->kota ?? '';
        $tujuan=request()->tujuan ?? '';
        $this->Kota=$kota;
        $data=app(Controller::class)->like_match('%'.$kota.'%',$tujuan);
        return $data;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('WMT')->keterangan . ' ' . $this->Kota;
    }
}
