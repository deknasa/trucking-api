<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\TarifRincian;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validationTarifOrderemkl implements Rule
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

        $suratPengantar = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', request()->nobukti)->first();
        $jenisorderanmuatan = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN MUATAN')
            ->where('a.subgrp', 'JENIS ORDERAN MUATAN')
            ->first()->id;

        $jenisorderanbongkaran = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN BONGKARAN')
            ->where('a.subgrp', 'JENIS ORDERAN BONGKARAN')
            ->first()->id;

        $jenisorderanimport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN IMPORT')
            ->where('a.subgrp', 'JENIS ORDERAN IMPORT')
            ->first()->id;

        $jenisorderanexport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
            ->where('a.grp', 'JENIS ORDERAN EXPORT')
            ->where('a.subgrp', 'JENIS ORDERAN EXPORT')
            ->first()->id;

        $getTarif = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
            ->select(db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . request()->jenisorder_id  . " then isnull(tarifmuatan.id,0)  
                when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . request()->jenisorder_id  . "then isnull(tarifbongkaran.id,0)  
                when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . request()->jenisorder_id  . " then isnull(tarifimport.id,0)  
                when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . request()->jenisorder_id  . " then isnull(tarifexport.id,0)  
                else  isnull(tarif.id,0) end) as tarif_id"))

            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
            ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
            ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
            ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')
            ->where('upahsupir.id', $suratPengantar->upah_id)
            ->first();

        $tarif = TarifRincian::where('tarif_id', $getTarif->tarif_id)->where('container_id', request()->container_id)->first();
        if($tarif == ''){
            return false;
        }else{
            if($tarif->nominal == 0){
                return false;
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
        return app(ErrorController::class)->geterror('TBA')->keterangan;
    }
}
