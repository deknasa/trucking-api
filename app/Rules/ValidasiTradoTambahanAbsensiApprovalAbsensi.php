<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidasiTradoTambahanAbsensiApprovalAbsensi implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $supir;
    public $trado;
    public $tglabsen;
    public $keteranganerror;
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
        $tradoTambahanId=request()->tradoTambahanId ?? 0;
        $queryserap=db::table("tradotambahanabsensi")->from(db::raw("tradotambahanabsensi a with (readuncommitted)"))
        ->select(
            'a.tglabsensi',
            db::raw("isnull(a.supir_id,0) as supir_id"),
            'a.trado_id',
            'a.statusjeniskendaraan',
            db::raw("isnull(b.namasupir,'') as namasupir")

        )
        ->leftjoin(db::raw("supir b with (readuncommitted)"),'a.supir_id','b.id')
        ->where('a.id',$tradoTambahanId)
        ->first();

        $nilai=true;
      
        // dump($queryserap);
        if (isset($queryserap)) {
            $queryabsen=db::table("absensisupirheader")->from(db::raw("absensisupirheader a with (readuncommitted)"))
            ->select(
                db::raw("isnull(b.absen_id,0) as absen_id")
            )
            ->join(db::raw("absensisupirdetail b with (readuncommitted)"),'a.nobukti','b.nobukti')
            ->where('a.tglbukti', $queryserap->tglabsensi)
            ->where('b.statusjeniskendaraan', $queryserap->statusjeniskendaraan)
            ->where('b.supirold_id', $queryserap->supir_id)
            ->first();
            // dd($queryabsen);
            if (isset( $queryabsen)) {
                $queryparameter = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id',
                    'a.subgrp as keterangan',
                    'b.keterangan as keterangandata'
                )
                ->join(db::raw("absentrado b with (readuncommitted)"), 'a.text', 'b.id')
                ->whereraw("a.grp='ABSENSI SUPIR SERAP'")
                ->whereraw("a.text='" . $queryabsen->absen_id . "'")
                ->first();

                if (isset($queryparameter)) {
                    $nilai=false;
                    $this->keteranganerror = 'nama supir <b>' . $queryserap->namasupir . '</b> ada di supir serap, tidak boleh status <b>' . $queryparameter->keterangandata ?? '</b>';
                } 
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
        return $this->keteranganerror;
    }
}
