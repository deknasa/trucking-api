<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ValidasiTradoTanpaGambarKeterangan implements Rule
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

        $trado = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
        ->where('kodetrado', $kodetrado)
        ->first();
        $required = [
            "kodetrado" => $trado->kodetrado,
            "tahun" => $trado->tahun,
            "merek" => $trado->merek,
            "norangka" => $trado->norangka,
            "nomesin" => $trado->nomesin,
            "nama" => $trado->nama,
            "nostnk" => $trado->nostnk,
            "alamatstnk" => $trado->alamatstnk,
            "tglpajakstnk" => $trado->tglpajakstnk,
            "tipe" => $trado->tipe,
            "jenis" => $trado->jenis,
            "isisilinder" => $trado->isisilinder,
            "warna" => $trado->warna,
            "jenisbahanbakar" => $trado->jenisbahanbakar,
            "jumlahsumbu" => $trado->jumlahsumbu,
            "jumlahroda" => $trado->jumlahroda,
            "model" => $trado->model,
            "nobpkb" => $trado->nobpkb,
            "jumlahbanserap" => $trado->jumlahbanserap,
        ];
        $key = array_keys($required, null);
        // dd($key);
        $jumlah = count($key);

        $statusApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        if ($jumlah == 0) {
            $statusketerangan = $statusApp->id;
        } else {
            $statusketerangan = request()->keterangan_statusapproval ?? 0;
        }
        // 
        // $keterangan_statusapproval= request()->keterangan_statusapproval ?? 0;
        $keterangan_statusapproval=  $statusketerangan ?? 0;
        if ($keterangan_statusapproval==0) {
            $this->keterror = 'Status Tanpa Keterangan Untuk Trado <b>' . $kodetrado . '</b> ' . $keteranganerror ;
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
