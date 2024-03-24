<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\PenerimaanHeaderController;
use App\Models\Error;
use App\Models\Parameter;
use App\Models\PencairanGiroPengeluaranHeader;
use App\Models\PenerimaanHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiPencairanGiro implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $kodeError;
    public $nobukti;
    public $keterangan;
    public $kode;
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

        $detail = json_decode(request()->detail, true);
        $nomor = '';
        $exist = 0;
        $tutup = 0;
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
        for ($i = 0; $i < count($detail['tglbuktigiro']); $i++) {
            $tgltutup = date('Y-m-d', strtotime($tgltutup));
            if ($tgltutup >= date('Y-m-d', strtotime($detail['tglbuktigiro'][$i]))) {
                $tutup++;
                if ($nomor == '') {
                    $nomor = $detail['nobukti'][$i];
                } else {
                    $nomor = $nomor . ', ' . $detail['nobukti'][$i];
                }
            }
        }
        if ($tutup > 0) {
            $this->keterangan = 'No Bukti <b>' . $nomor . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            return false;
        }

        for ($i = 0; $i < count($detail['nobuktiCair']); $i++) {
            if ($detail['nobuktiCair'][$i] != '') {
                $cek = (new PencairanGiroPengeluaranHeader())->cekValidasi($detail['nobuktiCair'][$i]);
                if ($cek['kondisi']) {
                    $this->kodeError = $cek['kodeerror'];
                    $ket = $cek['keterangan'];
                    $exist++;
                    if ($nomor == '') {
                        $nomor = $detail['nobuktiCair'][$i];
                    } else {
                        $nomor = $nomor . ', ' . $detail['nobuktiCair'][$i];
                    }
                }
            }
        }
        $this->nobukti = $nomor;
        if ($exist > 0) {
            $this->keterangan = 'NO BUKTI <b>' . $nomor . '</b>' . $ket;
            return false;
        }

        if (request()->status == 592) {
            $sapp = 0;
            $scc = 0;
            $noapp = '';
            $nocet = '';
            $keteranganerrorapp = $error->cekKeteranganError('SAP') ?? '';
            $keteranganerrorcet = $error->cekKeteranganError('SDC') ?? '';
            for ($i = 0; $i < count($detail['nobuktiCair']); $i++) {
                $penerimaan = DB::table("penerimaanheader")->where('nobukti', $detail['nobuktiCair'][$i])->first();
                if ($penerimaan != '') {

                    $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
                    $status = $penerimaan->statusapproval;
                    $statusdatacetak = $penerimaan->statuscetak;
                    $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

                    if ($status == $statusApproval->id) {
                        $sapp++;
                        if ($noapp == '') {
                            $noapp = $detail['nobuktiCair'][$i];
                        } else {
                            $noapp = $noapp . ', ' . $detail['nobuktiCair'][$i];
                        }
                    } else if ($statusdatacetak == $statusCetak->id) {
                        $scc++;

                        if ($nocet == '') {
                            $nocet = $detail['nobuktiCair'][$i];
                        } else {
                            $nocet = $nocet . ', ' . $detail['nobuktiCair'][$i];
                        }
                    }
                }
            }
            if ($sapp > 0) {
                $this->keterangan = 'NO BUKTI <b>' . $noapp . '</b>' . $keteranganerrorapp;
                return false;
            }
            if ($scc > 0) {
                $this->keterangan = 'NO BUKTI <b>' . $nocet . '</b>' . $keteranganerrorcet;

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
        return $this->keterangan;
    }
}
