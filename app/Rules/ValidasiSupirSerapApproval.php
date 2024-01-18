<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\SupirSerap;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiSupirSerapApproval implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $supir;
    public $trado;
    public $tglabsen;
    public $nobukti;

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
        $supirSerap = SupirSerap::find($value);
        $supirSerap = DB::table("supirserap")->from(DB::raw("supirserap with (readuncommitted)"))
        ->select(DB::raw("supirserap.trado_id, supirserap.tglabsensi, supirserap.supirserap_id, trado.kodetrado, supir.namasupir"))
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'supirserap.trado_id', 'trado.id')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'supirserap.supirserap_id', 'supir.id')
        ->where('supirserap.id', $value)
        ->first();

        $this->supir = $supirSerap->namasupir;
        $this->trado = $supirSerap->kodetrado;
        $this->tglabsen = $supirSerap->tglabsensi;
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail as detail with (readuncommitted)"))
            ->select('header.nobukti')
            ->whereRaw("detail.trado_id = $supirSerap->trado_id and header.tglbukti = '$supirSerap->tglabsensi' and (detail.supir_id = $supirSerap->supirserap_id or detail.supirold_id = $supirSerap->supirserap_id)")
            ->leftJoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'header.id', 'detail.absensi_id')
            ->first();

        if ($query != '') {
            $this->nobukti = $query->nobukti;
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
        return 'supir serap '. $this->supir .' di trado '.$this->trado.' tgl '. date('d-m-Y',strtotime($this->tglabsen)) .' SUDAH DIINPUT DI ABSENSI '.$this->nobukti;
    }
}