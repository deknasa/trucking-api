<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueUpahSupirTangkiKotaSampai implements Rule
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

        if (request()->penyesuaian == '') {
            $query = DB::table('upahsupirtangki')
                ->from(
                    DB::raw("upahsupirtangki as a with (readuncommitted)")
                )
                ->select(
                    'a.id'
                )
                ->where('a.kotasampai_id', '=', (request()->kotasampai_id))
                ->where('a.kotadari_id', '=', request()->kotadari_id)
                ->whereRaw("isnull(a.penyesuaian,'')=''")
                ->first();
            if (!isset($query)) {

                $query = DB::table('upahsupirtangki')
                    ->from(
                        DB::raw("upahsupirtangki as a with (readuncommitted)")
                    )
                    ->select(
                        'a.id'
                    )
                    ->where('a.kotadari_id', '=', (request()->kotasampai_id))
                    ->where('a.kotasampai_id', '=', request()->kotadari_id)
                    ->whereRaw("isnull(a.penyesuaian,'')=''")
                    ->first();
            }
        } else {
            $query = DB::table('upahsupirtangki')
                ->from(
                    DB::raw("upahsupirtangki as a with (readuncommitted)")
                )
                ->select(
                    'a.id'
                )
                ->where('a.kotasampai_id', '=', (request()->kotasampai_id))
                ->where('a.kotadari_id', '=', request()->kotadari_id)
                ->where('a.penyesuaian', '=', request()->penyesuaian)
                ->first();

            if (!isset($query)) {
                $query = DB::table('upahsupirtangki')
                    ->from(
                        DB::raw("upahsupirtangki as a with (readuncommitted)")
                    )
                    ->select(
                        'a.id'
                    )
                    ->where('a.kotadari_id', '=', (request()->kotasampai_id))
                    ->where('a.kotasampai_id', '=', request()->kotadari_id)
                    ->where('a.penyesuaian', '=', request()->penyesuaian)
                    ->first();
            }
        }




        if (isset($query)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $controller = new ErrorController;
        return 'KOTA SAMPAI ' . $controller->geterror('SPI')->keterangan;
    }
}
