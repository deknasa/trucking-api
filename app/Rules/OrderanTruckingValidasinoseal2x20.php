<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\DB;

class OrderanTruckingValidasinoseal2x20 implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param,$paramukuran)
    {
        $this->kondisi = $param;
        $this->ukuran = $paramukuran;
    }
    public $kondisi;
    public $ukuran;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $statustas =  DB::table('parameter')->from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS TAS')
            ->where('subgrp', '=', 'STATUS TAS')
            ->where('text', '=', 'TAS')
            ->first();

  
            $container2x20 =  DB::table('parameter')->from(
                db::Raw("parameter with (readuncommitted)")
            )
                ->select(
                    'text'
                )
                ->where('grp', '=', 'UKURANCONTAINER2X20')
                ->where('subgrp', '=', 'UKURANCONTAINER2X20')
                ->first();            

       
        $noseal = request()->noseal2 ?? '';
        if ($noseal == '' and  request()->container_id==$container2x20->text  )  {
            if ($this->kondisi == true){
                $nilai = true;
            }else{
                $nilai = false;
            }
        } else {

            $nilai = true;
            if ($this->kondisi == true && $this->ukuran==true) {
                // dd('1');
                $nilai = false;
            } else {
                // dd('3');
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
        $controller = new ErrorController;
        return ':attribute' . ' ' . $controller->geterror('WI')->keterangan;
    }
}
