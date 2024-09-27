<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ErrorController;
use App\Models\LaporanKartuPanjar;
use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;



class ValidasiNotaDebetPelunasan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nominalpanjar;
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

        $tgl = request()->tglbukti ?? '01-01-1900' ;
        $tglnow = date('Y-m-d', strtotime($tgl)) ;
        $nobukti=request()->nobukti ?? '' ;
        $agen=request()->agen_id ?? 0 ;
        $pelanggan=request()->pelanggan_id ?? 0 ;
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
        $agen_id = ($cabang == 'BITUNG-EMKL') ? $pelanggan : $agen;
        $notadebet_nobukti=request()->notadebet_nobukti ?? '' ;
        $statuspelunasan=request()->statuspelunasan ?? 0 ;
        $parameter = new Parameter();

        $idstatuspelunasan=$parameter->cekId('PELUNASAN','PELUNASAN','NOTA DEBET') ?? 0;

        if ($idstatuspelunasan==$statuspelunasan) {

            $querypelunasan=db::table("notadebetfifo")->from(db::raw("notadebetfifo a with (readuncommitted)"))
            ->select(
                db::raw("sum(a.nominal) as nominal"),
                db::raw("max(a.notadebet_nobukti) as notadebet_nobukti")
            )
            ->where('a.pelunasanpiutang_nobukti',$nobukti)
            ->first();

            if (isset($querypelunasan)) {
                $nominalpelunasan=$querypelunasan->nominal ?? 0 ;
                $notadebet_nobuktipelunasan=$querypelunasan->notadebet_nobukti ?? '' ;
            } else {
                $nominalpelunasan=0 ;
                $notadebet_nobuktipelunasan='';

            }

            $temppanjar = '##temppanjar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppanjar, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->double('nominal')->nullable();                
            });

            DB::table($temppanjar)->insertUsing([
                'nobukti',
                'nominal',
            ], (new LaporanKartuPanjar())->getSisapanjarbukti($tglnow, $tglnow, 0, 0, 1,$agen_id,$tglnow,$notadebet_nobukti));
            $querysisapanjar=db::table($temppanjar)->from(db::raw($temppanjar . " a"))
            ->select (
                'a.nominal'
            )
            ->where('a.nobukti',$notadebet_nobukti)
            ->first() ;
            if (isset($querysisapanjar)) {
                $nominalsisapanjar=$querysisapanjar->nominal ?? 0;
            } else {
                $nominalsisapanjar=0;
            }
            if ($notadebet_nobukti==$notadebet_nobuktipelunasan) {

                $nominalnew=$nominalsisapanjar+$nominalpelunasan;

                // DB::table($temppanjar)->where('nobukti', $notadebet_nobukti)->update([
                //     'nominal' => $nominalnew,
                // ]);
            } else {
                $nominalnew=$nominalsisapanjar;
            }

            $nominallunas=0;
            $data = [
                'piutang_id' => request()->piutang_id,
                'bayar' => request()->bayar,
            ];
            for ($i = 0; $i < count($data['piutang_id']); $i++) {
                $nominallunas = $nominallunas + $data['bayar'][$i];
            }

     
            $this->nominalpanjar=$nominalnew;
            if ($nominalnew<$nominallunas) {
                return false;
            } else {
                return true; 
            }


            
        } else {
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
        return app(ErrorController::class)->geterror('NPKP')->keterangan.' '.number_format($this->nominalpanjar);
    }
}
