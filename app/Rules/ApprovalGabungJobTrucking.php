<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

use App\Models\SuratPengantar;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;

class ApprovalGabungJobTrucking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trip;
    public $bjumlah;
    public $nocont;
    public $noinvoice;
    public $batal;
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
        $parameter = new Parameter();

        // $pelabuhancabang = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? '0';
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;
        $pelabuhancabang = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
            ->select(
                db::raw("STRING_AGG(id,',') as id"),
            )
            ->where('a.statuspelabuhan', $statuspelabuhan)
            ->first()->id ?? 1;

        $bjumlah = 0;
        $batal=0;
        $nocont = '';
        $noinvoice = '';
        for ($i = 0; $i < count(request()->Id); $i++) {
            $nobukti = request()->Id[$i];
            // dd($nobukti);

            $querybulanpelabuhan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    db::raw("format(a.tglbukti,'MM-yyyy') as bulan")
                )
                ->where('a.nobukti', $nobukti)
                ->whereraw("(a.dari_id in(" . $pelabuhancabang . ") or isnull(a.statuslongtrip,0)=65)")
                ->first();

            $querynonbulanpelabuhan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    db::raw("format(a.tglbukti,'MM-yyyy') as bulan")
                )
                ->where('a.nobukti', $nobukti)
                ->whereraw("(a.dari_id not in(" . $pelabuhancabang . ") )")
                ->first();

            if (isset($querybulanpelabuhan)) {
                $bulanpelabuhan = $querybulanpelabuhan->bulan ?? '';
            }
            if (isset($querynonbulanpelabuhan)) {
                $bulannonpelabuhan = $querybulannonpelabuhan->bulan ?? '';
            }






            $querypelabuhan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.nocont',
                    db::raw("isnull(b.nobukti,'') as noinvoice")
                )
                ->leftjoin(db::raw("invoicedetail b with (readuncommitted)"), 'a.jobtrucking', 'b.orderantrucking_nobukti')
                ->where('a.nobukti', $nobukti)
                ->whereraw("(a.dari_id in(" . $pelabuhancabang . ") or isnull(a.statuslongtrip,0)=65)")
                ->first();

            $querynonpelabuhan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.nocont',
                )
                ->where('a.nobukti', $nobukti)
                ->whereraw("(a.dari_id not in(" . $pelabuhancabang . ") or isnull(a.statuslongtrip,0)=65)")
                ->whereraw("isnull(a.jobtrucking,'')<>''")
                ->first();


            if (isset($querypelabuhan)) {
                $nocont = $querypelabuhan->nocont ?? '';
                $noinvoice = $querypelabuhan->noinvoice ?? '';
                $bjumlah = $bjumlah + 1;
            }
            if (isset($querynonpelabuhan)) {
                $batal = 1;
            }
        }

        $this->bjumlah = $bjumlah ?? 0;
        $this->nocont = $nocont ?? '';
        $this->noinvoice = $noinvoice ?? '';
        $this->batal = $batal;
        // dd($nocont);

        if ($bjumlah == 0) {
            return false;
        }

        if ($bjumlah > 1) {
            return false;
        }
        // dd($nocont,$batal);
        if ($nocont == '' && $batal == 0) {
            // dd('test');
            return false;
        }
        // dd($batal);
        if ($noinvoice != ''  && $bulanpelabuhan == $bulannonpelabuhan) {
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
        // dd($this->bjumlah);
        if ($this->bjumlah == 0) {
            return app(ErrorController::class)->geterror('TTAP')->keterangan;
        }
        if ($this->bjumlah > 1) {
            return app(ErrorController::class)->geterror('TTPL')->keterangan;
        }
        // dd($this->nocont);
        if ($this->nocont == '' && $this->batal == 0) {
            $keterangan = 'No Container Dari Pelabuhan ' . app(ErrorController::class)->geterror('WI')->keterangan;
            return $keterangan;
        }
        if ($this->noinvoice != '') {
            $keterangan = 'Job Trucking Sudah di Pakai di Bukti  <b>' . $this->noinvoice . '</b><br>' . app(ErrorController::class)->geterror('SATL')->keterangan;
            return $keterangan;
        }
    }
}
