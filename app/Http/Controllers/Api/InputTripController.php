<?php

namespace App\Http\Controllers\Api;


use App\Models\SuratPengantar;
use App\Models\UpahSupir;
use App\Models\Tarifrincian;
use App\Models\UpahSupirRincian;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMandorTripRequest;
use App\Http\Requests\StoreOrderantruckingRequest;
use App\Http\Requests\StoreRitasiRequest;
use App\Models\InputTrip;
use Illuminate\Http\JsonResponse;

class InputTripController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
    }
    /**
     * @ClassName
     */
    public function store(StoreMandorTripRequest $request)
    {

        DB::beginTransaction();
        try {
            $inputTrip = (new InputTrip())->processStore($request->all());
            $inputTrip->position = $this->getPosition($inputTrip, $inputTrip->getTable())->position;
            $inputTrip->page = ceil($inputTrip->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $inputTrip
            ], 201);

            // $tglbukti = date('Y-m-d', strtotime(now()));
            // $tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            // // return response([$tglbukti],422);
            // $format = DB::table('parameter')
            //     ->where('grp', 'SURAT PENGANTAR')
            //     ->where('subgrp', 'SURAT PENGANTAR')
            //     ->first();

            // $content = new Request();
            // $content['group'] = 'SURAT PENGANTAR';
            // $content['subgroup'] = 'SURAT PENGANTAR';
            // $content['table'] = 'suratpengantar';
            // $content['tgl'] = $tglbukti;

           
            // $upahsupirRincian = DB::table('UpahSupirRincian')->from(
            //     DB::Raw("UpahSupirRincian with (readuncommitted)")
            // )
            //     ->where('upahsupir_id', $request->upah_id)
            //     ->where('container_id', $request->container_id)
            //     ->where('statuscontainer_id', $request->statuscontainer_id)
            //     ->first();

            // $jobtrucking = $request->jobtrucking ?? '';

            // $statuslangsir = DB::table('parameter')->from(
            //     DB::raw("parameter as a with (readuncommitted)")
            // )
            //     ->select(
            //         'a.id'
            //     )
            //     ->where('a.grp', '=', 'STATUS LANGSIR')
            //     ->where('a.subgrp', '=', 'STATUS LANGSIR')
            //     ->where('a.text', '=', 'BUKAN LANGSIR')
            //     ->first();

            // $statusperalihan = DB::table('parameter')->from(
            //     DB::raw("parameter as a with (readuncommitted)")
            // )
            //     ->select(
            //         'a.id'
            //     )
            //     ->where('a.grp', '=', 'STATUS PERALIHAN')
            //     ->where('a.subgrp', '=', 'STATUS PERALIHAN')
            //     ->where('a.text', '=', 'BUKAN PERALIHAN')
            //     ->first();

            // $statusbatalmuat = DB::table('parameter')->from(
            //     DB::raw("parameter as a with (readuncommitted)")
            // )
            //     ->select(
            //         'a.id'
            //     )
            //     ->where('a.grp', '=', 'STATUS BATAL MUAT')
            //     ->where('a.subgrp', '=', 'STATUS BATAL MUAT')
            //     ->where('a.text', '=', 'BUKAN BATAL MUAT')
            //     ->first();

            // $tarifrincian = TarifRincian::find($request->tarifrincian_id);

            // $suratPengantar = new SuratPengantar();

            // $suratPengantar->statusperalihan = $statusperalihan->id;
            // $suratPengantar->statusbatalmuat = $statusbatalmuat->id;

            // $suratPengantar->tglbukti = $tglbukti;
            // $suratPengantar->tglsp = $tglbukti;
            // $suratPengantar->agen_id = $request->agen_id;
            // $suratPengantar->container_id = $request->container_id;
            // $suratPengantar->dari_id = $request->dari_id;
            // $suratPengantar->gandengan_id = $request->gandengan_id;
            // $suratPengantar->gudang = $request->gudang;

            // $suratPengantar->jenisorder_id = $request->jenisorder_id;
            // $suratPengantar->pelanggan_id = $request->pelanggan_id;
            // $suratPengantar->sampai_id = $request->sampai_id;
            // $suratPengantar->statuscontainer_id = $request->statuscontainer_id;
            // $suratPengantar->statusgudangsama = $request->statusgudangsama;
            // $suratPengantar->statuslongtrip = $request->statuslongtrip;
            // $suratPengantar->trado_id = $request->trado_id;
            // $suratPengantar->upah_id = $request->upah_id;
            // $suratPengantar->supir_id = $request->supir_id;
            // $suratPengantar->tarif_id = $request->tarifrincian_id;
            // $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir;
            // $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
            // $suratPengantar->omset = $tarifrincian->nominal;
            // $suratPengantar->totalomset = $tarifrincian->nominal;

            // $suratPengantar->modifiedby = auth('api')->user()->name;
            // $suratPengantar->statusformat = $format->id;

            // $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            // $suratPengantar->nobukti = $nobukti;


            // if ($jobtrucking == '') {
            //     $group = 'ORDERANTRUCKING';
            //     $subgroup = 'ORDERANTRUCKING';
            //     $format = DB::table('parameter')
            //         ->where('grp', $group)
            //         ->where('subgrp', $subgroup)
            //         ->first();

            //     $content = new Request();
            //     $content['group'] = $group;
            //     $content['subgroup'] = $subgroup;
            //     $content['table'] = 'orderantrucking';
            //     $content['tgl'] = $tglbukti;
            //     $nobuktiorderantrucking = app(Controller::class)->getRunningNumber($content)->original['data'];
            // } else {
            //     $nobuktiorderantrucking = $jobtrucking;
            // }
            // $suratPengantar->jobtrucking = $nobuktiorderantrucking;

            // $suratPengantar->save();

            // if ($jobtrucking == '') {
            //     $orderan = [
            //         'tglbukti' => $tglbukti,
            //         'container_id' => $request->container_id,
            //         'agen_id' => $request->agen_id,
            //         'jenisorder_id' => $request->jenisorder_id,
            //         'pelanggan_id' => $request->pelanggan_id,
            //         'tarifrincian_id' => $request->tarifrincian_id,
            //         'nojobemkl' => $request->nojobemkl ?? '',
            //         'nocont' => $request->nocont ?? '',
            //         'noseal' => $request->noseal ?? '',
            //         'nojobemkl2' => $request->nojobemkl2 ?? '',
            //         'nocont2' => $request->nocont2 ?? '',
            //         'noseal2' => $request->noseal2 ?? '',
            //         'statuslangsir' => $statuslangsir->id,
            //         'statusperalihan' => $statusperalihan->id,
            //         'modifiedby' => auth('api')->user()->name,
            //         'statusformat' => $format->id,
            //         'nobukti' =>  $nobuktiorderantrucking,
            //         'inputtripmandor' =>  '1',
            //     ];

            //     // dd($orderan);
            //     $orderanTrucking = new StoreOrderanTruckingRequest($orderan);
            //     app(OrderanTruckingController::class)->store($orderanTrucking);
            // }

            // $jenisRitasi = false;
            // foreach (request()->jenisritasi as $value) {
            //     if ($value != null) {
            //         $jenisRitasi = true;
            //         break;
            //     }
            // }
            // if ($jenisRitasi) {
            //     for ($i = 0; $i < count($request->jenisritasi); $i++) {
            //         $ritasi = [
            //             'tglbukti' => $tglbukti,
            //             'statusritasi' => $request->jenisritasi[$i],
            //             'suratpengantar_nobukti' => $nobukti,
            //             'supir_id' => $request->supir_id,
            //             'trado_id' => $request->trado_id,
            //             'dari_id' => $request->ritasidari_id[$i],
            //             'sampai_id' => $request->ritasike_id[$i]
            //         ];

            //         $storeRitasi = new StoreRitasiRequest($ritasi);
            //         $storeRitasi = app(RitasiController::class)->store($storeRitasi);
            //     }
            // }
            // DB::commit();
            // return response([
            //     'status' => true,
            //     'message' => 'Berhasil diinput',
            //     'data' => $suratPengantar
            // ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
