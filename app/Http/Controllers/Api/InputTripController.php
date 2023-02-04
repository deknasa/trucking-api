<?php

namespace App\Http\Controllers\API;

use App\Models\SuratPengantar;
use App\Models\UpahSupir;
use App\Models\Tarifrincian;
use App\Models\UpahSupirRincian;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMandorTripRequest;


class InputTripController extends Controller
{
   /**
     * @ClassName 
     */
    public function store(StoreMandorTripRequest $request)
    {
        
        DB::beginTransaction();
        try {
            $tglbukti= date('Y-m-d', strtotime('now'));
            $format = DB::table('parameter')
            ->where('grp', 'SURAT PENGANTAR')
            ->where('subgrp', 'SURAT PENGANTAR')
            ->first();
            
            $content = new Request();
            $content['group'] = 'SURAT PENGANTAR';
            $content['subgroup'] = 'SURAT PENGANTAR';
            $content['table'] = 'suratpengantar';
            $content['tgl'] = $tglbukti;
    
            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();

            $suratPengantar = new SuratPengantar();
    
            $suratPengantar->tglbukti = $tglbukti;
            $suratPengantar->agen_id = $request->agen_id;
            $suratPengantar->container_id = $request->container_id;
            $suratPengantar->dari_id = $request->dari_id;
            $suratPengantar->gandengan_id = $request->gandengan_id;
            $suratPengantar->gudang = $request->gudang;
            
            $suratPengantar->jenisorder_id = $request->jenisorder_id;
            $suratPengantar->pelanggan_id = $request->pelanggan_id;
            $suratPengantar->sampai_id = $request->sampai_id;
            $suratPengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratPengantar->statusgudangsama = $request->statusgudangsama;
            $suratPengantar->statuslongtrip = $request->statuslongtrip;
            $suratPengantar->trado_id = $request->trado_id;
            $suratPengantar->upah_id = $upahsupir->id;
            $suratPengantar->supir_id = $request->supir_id;
            $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;

            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->statusformat = $format->id;
    
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $suratPengantar->nobukti = $nobukti;
            $suratPengantar->save();
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Berhasil diinput',
                'data' => $suratPengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
       
    }
}
