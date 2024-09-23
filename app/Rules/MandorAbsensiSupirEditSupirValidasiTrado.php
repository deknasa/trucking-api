<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MandorAbsensiSupirEditSupirValidasiTrado implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($trado,$supir, $tglbukti = null)
    {
        $this->trado_id = $trado;
        $this->supir_id = $supir;
        $this->tglbukti = ($tglbukti == null) ? date('Y-m-d') : date('Y-m-d', strtotime($tglbukti));
    }


    protected $trado_id;
    protected $supir_id;
    protected $tglbukti;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();
        if ($tradoMilikSupir->text == 'YA') {
            $nilai = true;
        } else {
            $query = DB::table('absensisupirheader')
                ->from(
                    DB::raw("absensisupirheader as a with (readuncommitted)")
                )
                ->select(
                    'b.trado_id'
                )
                ->join(db::Raw("absensisupirdetail as b with (readuncommitted)"), 'a.id', 'b.absensi_id')
                ->join(db::Raw("trado as c with (readuncommitted)"), 'b.trado_id', 'c.id')
                ->whereRaw("isnull(c.tglberlakumilikmandor,'1900/1/1')<='" . date('Y-m-d') . "'")
                ->whereRaw("a.tglbukti='" . $this->tglbukti . "'")
                ->where('b.supir_id', '=', $this->supir_id)
                ->where('b.trado_id', '<>', $this->trado_id)
                ->first();



            if (isset($query)) {
                $nilai = false;
            } else {
                $nilai = true;
            }
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
        $query = DB::table('absensisupirheader')
            ->from(
                DB::raw("absensisupirheader as a with (readuncommitted)")
            )
            ->select(
                'c.kodetrado'
            )
            ->join(db::Raw("absensisupirdetail as b with (readuncommitted)"), 'a.id', 'b.absensi_id')
            ->join(db::Raw("trado as c with (readuncommitted)"), 'b.trado_id', 'c.id')
            ->whereRaw("isnull(c.tglberlakumilikmandor,'1900/1/1')<='" . date('Y-m-d') . "'")
            ->whereRaw("a.tglbukti='" . date('Y-m-d') . "'")
            ->where('b.supir_id', '=', $this->supir_id )
            ->where('b.trado_id', '<>', $this->trado_id)
            ->first();

        return ':attribute Sudah Pernah Di Input Di Trado ' . $query->kodetrado;
    }
}
