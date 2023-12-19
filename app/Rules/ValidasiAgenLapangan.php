<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiAgenLapangan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        $id = request()->id ?? '';
        $container_id = request()->container_id;
        $this->container = request()->container;
        $this->agen = request()->agen;
        $agen_id = request()->agen_id;
        if ($id == '') {
            $cekQuery = DB::table("lapangan")->from(DB::raw("lapangan with (readuncommitted)"))
                ->where('agen_id', $agen_id)
                ->where('container_id', $container_id)
                ->first();

            if ($cekQuery != '') {
                return false;
            }
        } else {
            $cekQuery = DB::table("lapangan")->from(DB::raw("lapangan with (readuncommitted)"))
                ->where('agen_id', $agen_id)
                ->where('container_id', $container_id)
                ->where('id', '<>', $id)
                ->first();

            if ($cekQuery != '') {
                return false;
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
        return $this->agen.' '.$this->container.' '.app(ErrorController::class)->geterror('SPI')->keterangan;
    }
}
