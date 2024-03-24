<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ValidasiTradoTanpaGambarGambar implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $keterror;
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
        $allowed = true;
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('WI') ?? '';
        $kodetrado=request()->kodetrado ?? '';

        // 
        $photobpkb = true;
        $photostnk = true;
        $phototrado = true;

        $trado = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
        ->where('kodetrado', $kodetrado)
        ->first();

        if (!is_null(json_decode($trado->photobpkb))) {
            foreach (json_decode($trado->photobpkb) as $value) {
                if ($value != '') {
                    if (!Storage::exists("trado/bpkb/$value")) {
                        $photobpkb = false;
                        goto selesai1;
                    }
                } else {
                    $photobpkb = false;
                    goto selesai1;
                }
            }
        } else {
            $photobpkb = false;
        }

        selesai1:
        if (!is_null(json_decode($trado->photostnk))) {
            foreach (json_decode($trado->photostnk) as $value) {
                if ($value != '') {
                    if (!Storage::exists("trado/stnk/$value")) {
                        $photostnk = false;
                        goto selesai2;
                    }
                } else {
                    $photostnk = false;
                    goto selesai2;
                }
            }
        } else {
            $photostnk = false;
        }


        selesai2:
        if (!is_null(json_decode($trado->phototrado))) {
            foreach (json_decode($trado->phototrado) as $value) {
                if ($value != '') {
                    if (!Storage::exists("trado/trado/$value")) {
                        $phototrado = false;
                        goto selesai3;
                    }
                } else {
                    $phototrado = false;
                    goto selesai3;
                }
            }
        } else {
            $phototrado = false;
        }

        selesai3:

        $statusApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        if ($photobpkb == true && $photostnk == true  && $phototrado == true) {
            $statusgambar = $statusApp->id;
        } else {
            $statusgambar = request()->gambar_statusapproval ?? 0;
        }

        // 
        
        $keterangan_statusapproval= $statusgambar ?? 0;
        // $keterangan_statusapproval= request()->gambar_statusapproval ?? 0;
        if ($keterangan_statusapproval==0) {
            $this->keterror = 'Status Tanpa Gambar Untuk Trado <b>' . $kodetrado . '</b> ' . $keteranganerror ;
            $allowed = false;
            return  $allowed;
        }

        
        return  $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->keterror;
    }
}
