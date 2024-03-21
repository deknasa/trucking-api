<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiNoWarkatPengeluaran implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeerror;
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
        $alatbayar = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('id', request()->alatbayar_id)->first();
        if ($alatbayar != '') {

            if ($alatbayar->namaalatbayar == 'GIRO') {

                if ($value == '') {
                    $this->kodeerror = 'WI';
                    return false;
                }

                $noWarkat = request()->nowarkat;
                $firstValue = strtoupper($noWarkat[0]);

                // Iterate through the array starting from the second element
                for ($i = 0; $i < count($noWarkat); $i++) {
                    if (strtoupper($noWarkat[$i]) !== $firstValue) {
                        $this->kodeerror = 'TBD';
                        return false;
                        break; // If a different value is found, exit the loop
                    }
                }
            }
            if ($alatbayar->namaalatbayar == 'TRANSFER') {

                $noWarkat = request()->nowarkat;
                $firstValue = strtoupper($noWarkat[0]);

                // Iterate through the array starting from the second element
                for ($i = 0; $i < count($noWarkat); $i++) {
                    if (strtoupper($noWarkat[$i]) !== $firstValue) {
                        $this->kodeerror = 'TBD';
                        return false;
                        break; // If a different value is found, exit the loop
                    }
                }
            }
            
            if ($alatbayar->namaalatbayar == 'TUNAI') {
                if($value != ''){
                    $this->kodeerror = 'TBI';
                    return false;
                }
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
        return 'no warkat ' . app(ErrorController::class)->geterror($this->kodeerror)->keterangan;
    }
}
