<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\TarifRincian;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExistTarifRincianSuratPengantar implements Rule
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
        $tarifRincian = new TarifRincian();
        $dataTarif = $tarifRincian->getValidasiTarif(request()->container_id, request()->upah_id);
        if($dataTarif == null){
            return false;
        }else{
            $statusjenis = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'UPAH SUPIR')
                ->where('subgrp', '=', 'TARIF JENIS ORDER')
                ->first();
            
                if ($statusjenis->text == 'YA') {
                    $jenisorder = 'tarif'.strtolower(request()->jenisorder).'_id';
                    if($value != $dataTarif->tarif_id) {
                        if($value != $dataTarif->$jenisorder){
                            return false;
                        }  
                    } else{
                        return true;
                    }
                }else{
                    if($value != $dataTarif->tarif_id) {
                        return false;
                    }else{
                        return true;
                    }
                }
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
        return ':attribute' . ' ' . $controller->geterror('TVD')->keterangan;
    }
}
