<?php

namespace App\Rules;

use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class BukaCetakSatuArah implements Rule
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
        $table = request()->table;
        if($table == 'PEMUTIHANSUPIR'){
            $table = 'PEMUTIHANSUPIRHEADER';
        }
        $allowed = false;
        $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();

        foreach ($value as $val) {
            $item = DB::table($table)->from(DB::raw("$table with (readuncommitted)"))->select('statuscetak')->where('id', $val)->where('statuscetak', $statusBelumCetak->id)->first();
            if ($item) {
                $allowed = true;
            }
        }
        
        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $msg = 'PROSES TIDAK BISA LANJUT KARENA';
        return "$msg<br> No Bukti belum dicetak ";
    }
}
