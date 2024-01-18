<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTglSupirSerap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $supirserap;
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
        $supirserap_id = request()->supirserap_id;
        $trado_id = request()->trado_id;
        $query = DB::table("supirserap")->from(DB::raw("supirserap with (readuncommitted)"))
            ->where('supirserap_id', $supirserap_id)
            ->where('trado_id', $trado_id)
            ->where('tglabsensi', date('Y-m-d', strtotime($value)));

        if (request()->id != '') {
            $query->where('id', '<>', request()->id);
        }
        $result = $query->first();


        $this->supirserap = request()->supirserap;
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
        return 'supir serap '. $this->supirserap .' di trado '.$this->trado.' tgl '. $this->tglabsen .' '. app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
