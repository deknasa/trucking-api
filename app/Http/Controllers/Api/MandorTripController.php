<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\MandorTrip;
use App\Models\SuratPengantar;
use App\Http\Requests\StoreMandorTripRequest;
use App\Http\Requests\UpdateMandorTripRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use Illuminate\Http\Request;



class MandorTripController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

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
    
    
            $suratPengantar = new SuratPengantar();
    
            $suratPengantar->tglbukti = $tglbukti;
            $suratPengantar->agen_id = $request->agen_id;
            $suratPengantar->container_id = $request->container_id;
            $suratPengantar->dari_id = $request->dari_id;
            $suratPengantar->gandengan_id = $request->gandengan_id;
            $suratPengantar->gudang = $request->gudang;
            
            $suratPengantar->jenisorder_id = $request->jenisorder_id;
            // $suratPengantar->lokasibongkarmuat = $request->lokasibongkarmuat;
            $suratPengantar->pelanggan_id = $request->pelanggan_id;
            $suratPengantar->sampai_id = $request->sampai_id;
            $suratPengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratPengantar->statusgudangsama = $request->statusgudangsama;
            $suratPengantar->statuslongtrip = $request->statuslongtrip;
            // $suratPengantar->tarifrincian_id = $request->tarifrincian_id;
            $suratPengantar->trado_id = $request->trado_id;
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MandorTrip  $mandorTrip
     * @return \Illuminate\Http\Response
     */
    public function show(MandorTrip $mandorTrip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MandorTrip  $mandorTrip
     * @return \Illuminate\Http\Response
     */
    public function edit(MandorTrip $mandorTrip)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMandorTripRequest $request, MandorTrip $mandorTrip)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function destroy(MandorTrip $mandorTrip)
    {
        //
    }
}
