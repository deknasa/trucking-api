<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Error;


class MandorAbsensiSupirEditSupirValidasiTradoSupirSerap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($trado, $absen_id, $tglbukti, $supirold)
    {
        $this->trado_id = $trado;
        $this->absen_id = $absen_id;
        $this->tglbukti = $tglbukti;
        $this->supirold_id = $supirold;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    protected $trado_id;
    protected $supir_id;
    protected $absen_id;
    protected $tglbukti;
    protected $error;
    protected $keteranganerror;
    protected $supirold_id;


    public function passes($attribute, $value)
    {
        $error = new Error();
        $querynamasupir = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.namasupir'
            )
            ->where('a.id', $this->supirold_id)
            ->first();

        if (isset($querynamasupir)) {
            $namasupir = $querynamasupir->namasupir ?? '';
        } else {
            $namasupir = '';
        }

        $xtglbukti = date('Y-m-d', strtotime($this->tglbukti));
        $queryserap = db::table("supirserap")->from(db::raw("supirserap a with (readuncommitted)"))
            ->select(
                'a.supirserap_id',
            )
            ->where('a.supirserap_id', $this->supirold_id)
            ->where('a.tglabsensi', $xtglbukti)
            ->first();
  

        if (isset($queryserap)) {
            $queryparameter = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id',
                    'a.subgrp as keterangan',
                    'b.keterangan as keterangandata'
                )
                ->join(db::raw("absentrado b with (readuncommitted)"), 'a.text', 'b.id')
                ->whereraw("a.grp='ABSENSI SUPIR SERAP'")
                ->whereraw("a.text='" . $this->absen_id . "'")
                ->first();
  
            if (isset($queryparameter)) {
                $nilai = false;
                // dd('test');
                $this->keteranganerror = 'nama supir ' . $namasupir . ' ada di supir serap, tidak boleh status ' . $queryparameter->keterangandata ?? '';
                $this->error = 2;
                goto selesai;
            } else {
                $this->error = 2;
                 $this->keteranganerror = 'test1';
                $nilai = true;
            }
        } else {
            $this->error = 2;
            $this->keteranganerror = 'test2';

            $nilai = true;
        }


        //  DB::table($tempstatusnonsupirserap)->insertUsing([
        //      'id',
        //      'keterangan',
        //  ],  $queryparameter);

        // dd($this->error);
        selesai:
        return $nilai;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return  $this->keteranganerror;
    }
}
