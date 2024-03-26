<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class AbsensiSupirNontaktif implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($tglbukti,$supir_id)
    {
        $this->tglbukti = date('Y-m-d',strtotime($tglbukti));
        $this->supir_id = $supir_id;
    }
    protected $tglbukti;
    protected $supir_id;

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
           

        $supir = DB::table("supir")->from(DB::raw("supir a with (readuncommitted)"))
        ->join(DB::raw("approvalsupirgambar b with (readuncommitted)"), 'a.noktp', 'b.noktp')
        ->join(DB::raw("approvalsupirketerangan c with (readuncommitted)"), 'a.noktp', 'c.noktp')
        ->where('a.id', $this->supir_id)
        ->where('b.tglbatas','<=', $this->tglbukti)
        ->where('a.statusaktif','<>', $statusaktif->id)
        ->first();
        if ($supir) {
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
        return 'SUPIR DARI TRADO INI TIDAK AKTIF';
    }
}
