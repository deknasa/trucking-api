<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class AbsensiTradoNontaktif implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($tglbukti,$trado_id)
    {
        $this->tglbukti = date('Y-m-d',strtotime($tglbukti));
        $this->trado_id = $trado_id;
    }
    protected $tglbukti;
    protected $trado_id;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
           

        $trado = DB::table("trado")->from(DB::raw("trado a with (readuncommitted)"))
        ->join(db::raw("approvaltradogambar b with (readuncommitted)"), 'a.kodetrado', 'b.kodetrado')
        ->join(db::raw("approvaltradoketerangan c with (readuncommitted)"), 'a.kodetrado', 'c.kodetrado')
        ->where('a.id', $this->trado_id)
        ->where('a.statusaktif','<>', $statusaktif->id)
        ->first();
        if ($trado) {
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
        return 'Tanggal aktif trado sudah habis';
    }
}
