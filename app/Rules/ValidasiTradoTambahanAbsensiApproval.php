<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use App\Models\TradoTambahanAbsensi;
use Illuminate\Contracts\Validation\Rule;

class ValidasiTradoTambahanAbsensiApproval implements Rule
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
    public $tbl_dari;
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
        $tradoAbsensi = TradoTambahanAbsensi::find($value);
        $tradoAbsensi = DB::table("tradotambahanabsensi")->from(DB::raw("tradotambahanabsensi with (readuncommitted)"))
        ->select(DB::raw("tradotambahanabsensi.trado_id, tradotambahanabsensi.tglabsensi, tradotambahanabsensi.supir_id,tradotambahanabsensi.statusjeniskendaraan, trado.kodetrado, supir.namasupir"))
        ->leftJoin(DB::raw("trado with (readuncommitted)"), 'tradotambahanabsensi.trado_id', 'trado.id')
        ->leftJoin(DB::raw("supir with (readuncommitted)"), 'tradotambahanabsensi.supir_id', 'supir.id')
        ->where('tradotambahanabsensi.id', $value)
        ->first();

        $this->supir = $tradoAbsensi->namasupir;
        $this->trado = $tradoAbsensi->kodetrado;
        $this->tglabsen = $tradoAbsensi->tglabsensi;
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail as detail with (readuncommitted)"))
            ->select('header.nobukti','detail.uangjalan')
            ->whereRaw("detail.trado_id = $tradoAbsensi->trado_id and header.tglbukti = '$tradoAbsensi->tglabsensi' and detail.statusjeniskendaraan = $tradoAbsensi->statusjeniskendaraan and (detail.supir_id = $tradoAbsensi->supir_id or detail.supirold_id = $tradoAbsensi->supir_id)")
            ->leftJoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'header.id', 'detail.absensi_id')
            ->first();
        if(is_null($query)){
            return true;
        }
        $absensiApproval = DB::table('absensisupirapprovalheader')->from(DB::raw("absensisupirapprovalheader as detail with (readuncommitted)"))->where('absensisupir_nobukti',$query->nobukti)->first();
        if ($absensiApproval) {
            $this->nobukti = $absensiApproval->pengeluaran_nobukti;
            $this->tbl_dari = "PENGELUARAN";
            return false;
        }

        if (intval($query->uangjalan)) {
            $this->nobukti = $query->nobukti;
            $this->tbl_dari = "ABSENSI";
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
        return 'The validation error message.';
    }
}
