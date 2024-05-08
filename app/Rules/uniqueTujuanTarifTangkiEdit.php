<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class uniqueTujuanTarifTangkiEdit implements Rule
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
        $penyesuaian = request()->penyesuaian ?? '';
        $query = DB::table('tariftangki')
            ->from(
                DB::raw("tariftangki as a with (readuncommitted)")
            )
            ->select(
                'a.id'
            )
            ->where('a.tujuan', '=', request()->tujuan)
            ->where('a.penyesuaian', '=', $penyesuaian)
            ->where('a.id', '<>', request()->id)
            ->first();


         
        if (isset($query))  {
            $nilai = false;
        } else {
            $nilai = true;
        }

        return $nilai;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        return ':attribute ' . $controller->geterror('SPI')->keterangan;
    }
}
