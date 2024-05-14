<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

class ValidasiTglTradoTambahan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $supir;
    public $trado;
    public $tglabsen;
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
        $supir_id = request()->supir_id;
        $trado_id = request()->trado_id;
        $query = DB::table("tradotambahanabsensi")->from(DB::raw("tradotambahanabsensi with (readuncommitted)"))
            ->where('supir_id', $supir_id)
            ->where('trado_id', $trado_id)
            ->where('tglabsensi', date('Y-m-d', strtotime($value)));

        if (request()->id != '') {
            $query->where('id', '<>', request()->id);
        }
        $result = $query->first();


        $this->supir = request()->supir;
        $this->trado = request()->trado;
        $this->tglabsen = $value;

        if ($result != '') {
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
        return 'supir  '. $this->supir .' di trado '.$this->trado.' tgl '. $this->tglabsen .' '. app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
