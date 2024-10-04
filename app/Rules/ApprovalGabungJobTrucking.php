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
    public $bjumlahtidaksama;
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
        $batal = 0;
        $nocont = '';
        $noinvoice = '';
        $nobuktitrippelabuhan = '';
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
                    'a.nobukti',
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
                $nobuktitrippelabuhan = $querypelabuhan->nobukti ?? '';
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

        $queryutama = db::table('suratpengantar')->from(db::raw("suratpengantar  a with (readuncommitted)"))
            ->select(
                'a.jobtrucking',
                'a.nocont',
                'a.nocont2',
                'a.noseal',
                'a.noseal2',
                'a.nojob',
                'a.nojob2',
                'a.pelanggan_id',
                db::raw("isnull(a.penyesuaian,'') as penyesuaian"),
                'a.container_id',
                'a.trado_id',
                'a.gandengan_id',
                'a.agen_id',
                'a.jenisorder_id',
                'a.tarif_id',
                'a.sampai_id',
                'a.statuslongtrip',
                // 'a.statusgerobak'
            )
            ->where('a.nobukti', $nobuktitrippelabuhan)
            ->first();
            // dd($nobuktitrippelabuhan);
        $penyesuaianutama = $queryutama->penyesuaian ?? '';
        $container_idutama = $queryutama->container_id ?? 0;
        $gandengan_idutama = $queryutama->gandengan_id ?? 0;
        $agen_idutama = $queryutama->agen_id ?? 0;
        $jenisorder_idutama = $queryutama->jenisorder_id ?? 0;
        $tarif_idutama = $queryutama->tarif_id ?? 0;
        $statusgerobakutama = $queryutama->statusgerobak ?? 0;
        $gabungutama = $penyesuaianutama . $container_idutama  . $gandengan_idutama . $agen_idutama . $jenisorder_idutama . $tarif_idutama . $statusgerobakutama;

        $bjumlahtidaksama = 0;
        // dd($gabungutama);
        if (isset($queryutama)) {
            for ($i = 0; $i < count(request()->Id); $i++) {
                $nobukticek = request()->Id[$i];
                if ($nobukticek != $nobuktitrippelabuhan) {
                    $querycek = db::table('suratpengantar')->from(db::raw("suratpengantar  a with (readuncommitted)"))
                        ->select(
                            'a.jobtrucking',
                            'a.nocont',
                            'a.nocont2',
                            'a.noseal',
                            'a.noseal2',
                            'a.nojob',
                            'a.nojob2',
                            'a.pelanggan_id',
                            db::raw("isnull(a.penyesuaian,'') as penyesuaian"),
                            'a.container_id',
                            'a.gandengan_id',
                            'a.agen_id',
                            'a.jenisorder_id',
                            'a.tarif_id',
                            'a.sampai_id',
                            'a.statuslongtrip',
                            // 'a.statusgerobak'
                        )
                        ->where('a.nobukti', $nobukticek)
                        ->first();

                    $penyesuaiancek = $querycek->penyesuaian ?? '';
                    $container_idcek = $querycek->container_id ?? 0;
                    $gandengan_idcek = $querycek->gandengan_id ?? 0;
                    $agen_idcek = $querycek->agen_id ?? 0;
                    $jenisorder_idcek = $querycek->jenisorder_id ?? 0;
                    $tarif_idcek = $querycek->tarif_id ?? 0;
                    $statusgerobakcek = $querycek->statusgerobak ?? 0;

                    $gabungcek = $penyesuaiancek . $container_idcek . $gandengan_idcek . $agen_idcek . $jenisorder_idcek . $tarif_idcek . $statusgerobakcek;

              
                    if ($gabungcek != $gabungutama) {
                        // dd($gabungcek,$gabungutama);
                        // dd('uji');
                        $bjumlahtidaksama = $bjumlahtidaksama + 1;
                    }
                }
            }
        }
        // dd($bjumlahtidaksama);
        $this->bjumlahtidaksama = $bjumlahtidaksama ?? 0;

        if ($bjumlahtidaksama != 0) {
            return false;
        }
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
        if ($this->bjumlahtidaksama != 0) {
            $keterangan = 'Trip  antara dari pelabuhan dengan data bukan pelabuhan ada data tidak sama ';
            return $keterangan;
        }
    }
}
