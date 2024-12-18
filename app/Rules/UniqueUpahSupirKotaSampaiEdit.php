<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueUpahSupirKotaSampaiEdit implements Rule
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
        $id = request()->id;
        
        if (request()->penyesuaian == '') {
            $query = DB::table('upahsupir')
                ->from(
                    DB::raw("upahsupir as a with (readuncommitted)")
                )
                ->select(
                    'a.id'
                )
                ->where('a.kotasampai_id', '=', (request()->kotasampai_id))
                ->where('a.kotadari_id', '=', request()->kotadari_id)
                ->whereRaw("isnull(a.penyesuaian,'')=''")
                ->where('a.id', '<>', $id)
                ->first();

            if (!isset($query)) {

                $query = DB::table('upahsupir')
                    ->from(
                        DB::raw("upahsupir as a with (readuncommitted)")
                    )
                    ->select(
                        'a.id'
                    )
                    ->where('a.kotadari_id', '=', (request()->kotasampai_id))
                    ->where('a.kotasampai_id', '=', request()->kotadari_id)
                    ->whereRaw("isnull(a.penyesuaian,'')=''")
                    ->where('a.id', '<>', $id)
                    ->first();
            }
        } else {
            $query = DB::table('upahsupir')
                ->from(
                    DB::raw("upahsupir as a with (readuncommitted)")
                )
                ->select(
                    'a.id'
                )
                ->where('a.kotasampai_id', '=', (request()->kotasampai_id))
                ->where('a.kotadari_id', '=', request()->kotadari_id)
                ->where('a.penyesuaian', '=', request()->penyesuaian)
                ->where('a.id', '<>', $id)
                ->first();

            if (!isset($query)) {
                $query = DB::table('upahsupir')
                    ->from(
                        DB::raw("upahsupir as a with (readuncommitted)")
                    )
                    ->select(
                        'a.id'
                    )
                    ->where('a.kotadari_id', '=', (request()->kotasampai_id))
                    ->where('a.kotasampai_id', '=', request()->kotadari_id)
                    ->where('a.penyesuaian', '=', request()->penyesuaian)
                    ->where('a.id', '<>', $id)
                    ->first();
            }
        }




        if (isset($query)) {
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
        return 'KOTA SAMPAI ' . $controller->geterror('SPI')->keterangan;
    }
}
