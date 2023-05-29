<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantar;
use App\Models\SuratPengantarBiayaTambahan;
use App\Models\Pelanggan;
use App\Models\UpahSupir;
use App\Models\UpahSupirRincian;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Models\Trado;
use App\Models\Supir;
use App\Models\Agen;
use App\Models\JenisOrder;
use App\Models\Tarif;
use App\Models\TarifRincian;
use App\Models\Kota;
use App\Models\Parameter;
use App\Http\Requests\StoreSuratPengantarRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\OrderanTrucking;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantarController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(GetIndexRangeRequest $request)
    {
        $suratPengantar = new SuratPengantar();

        return response([
            'data' => $suratPengantar->get(),
            'attributes' => [
                'totalRows' => $suratPengantar->totalRows,
                'totalPages' => $suratPengantar->totalPages
            ]
        ]);
    }

    public function default()
    {
        $suratPengantar = new SuratPengantar();
        return response([
            'status' => true,
            'data' => $suratPengantar->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreSuratPengantarRequest $request)
    {
        DB::beginTransaction();



        try {
            $format = DB::table('parameter')
                ->where('grp', 'SURAT PENGANTAR')
                ->where('subgrp', 'SURAT PENGANTAR')
                ->first();

            $content = new Request();
            $content['group'] = 'SURAT PENGANTAR';
            $content['subgroup'] = 'SURAT PENGANTAR';
            $content['table'] = 'suratpengantar';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $orderanTrucking = OrderanTrucking::where('nobukti', $request->jobtrucking)->first();
            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();

            $tarif = Tarif::find($orderanTrucking->tarif_id);
            $tarifrincian = TarifRincian::from(DB::raw("tarifrincian with (readuncommitted)"))->where('tarif_id', $orderanTrucking->tarif_id)->where('container_id', $orderanTrucking->container_id)->first();
            $trado = Trado::find($request->trado_id);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();
            $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();
            $suratpengantar = new SuratPengantar();

            $suratpengantar->jobtrucking = $request->jobtrucking;
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan ?? '';
            $suratpengantar->nourutorder = $request->nourutorder ?? 1;
            $suratpengantar->upah_id = $upahsupir->id;
            $suratpengantar->dari_id = $request->dari_id;
            $suratpengantar->sampai_id = $request->sampai_id;
            $suratpengantar->container_id = $orderanTrucking->container_id;
            $suratpengantar->nocont = $orderanTrucking->nocont;
            $suratpengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
            $suratpengantar->noseal = $orderanTrucking->noseal;
            $suratpengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            $suratpengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratpengantar->trado_id = $request->trado_id;
            $suratpengantar->supir_id = $request->supir_id;
            $suratpengantar->gandengan_id = $request->gandengan_id ?? 0;
            $suratpengantar->nojob = $orderanTrucking->nojobemkl;
            $suratpengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip;
            $suratpengantar->omset = $tarifrincian->nominal;
            $suratpengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratpengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratpengantar->agen_id = $orderanTrucking->agen_id;
            $suratpengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan;
            $suratpengantar->tarif_id = $orderanTrucking->tarif_id;
            $suratpengantar->nominalperalihan = $request->nominalperalihan ?? 0;
            $persentaseperalihan = 0;
            if ($request->nominalperalihan != 0) {
                $persentaseperalihan = $request->nominalperalihan / $tarifrincian->nominal;
            }

            $suratpengantar->persentaseperalihan = $persentaseperalihan;
            $suratpengantar->discount = $persentaseperalihan;
            $suratpengantar->totalomset = $tarifrincian->nominal - ($tarifrincian->nominal * ($persentaseperalihan / 100));

            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratpengantar->tolsupir = $upahsupirRincian->nominaltol;
            $suratpengantar->jarak = $upahsupir->jarak;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? '';
            $suratpengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $tarifrincian->nominal * $request->qtyton;
            $suratpengantar->mandorsupir_id = $trado->mandor_id;
            $suratpengantar->mandortrado_id = $trado->mandor_id;
            $suratpengantar->statusgudangsama = $request->statusgudangsama;
            $suratpengantar->statusbatalmuat = $request->statusbatalmuat;
            $suratpengantar->gudang = $request->gudang;
            $suratpengantar->modifiedby = auth('api')->user()->name;
            $suratpengantar->statusformat = $format->id;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $suratpengantar->nobukti = $nobukti;

            $suratpengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;

            if ($suratpengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'ENTRY SURAT PENGANTAR',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                if ($request->nominal[0] != 0) {
                    for ($i = 0; $i < count($request->nominal); $i++) {
                        $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();

                        $suratpengantarbiayatambahan->suratpengantar_id = $suratpengantar->id;
                        $suratpengantarbiayatambahan->keteranganbiaya = $request->keterangan_detail[$i];
                        $suratpengantarbiayatambahan->nominal = $request->nominal[$i];
                        $suratpengantarbiayatambahan->nominaltagih = $request->nominalTagih[$i];
                        $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;
                        $suratpengantarbiayatambahan->save();

                        $suratpengantar->biayatambahan_id = $suratpengantarbiayatambahan->id;
                        $suratpengantar->save();
                    }
                }



                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($suratpengantar, $suratpengantar->getTable());
            $suratpengantar->position = $selected->position;
            $suratpengantar->page = ceil($suratpengantar->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $suratpengantar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {

        $data = SuratPengantar::findAll($id);
        $detail = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSuratPengantarRequest $request, SuratPengantar $suratpengantar)
    {

        DB::beginTransaction();
        try {
            $prosesLain = $request->proseslain ?? 0;
            $orderanTrucking = OrderanTrucking::where('nobukti', $request->jobtrucking)->first();

            $tarif = Tarif::find($orderanTrucking->tarif_id);
            $tarif = TarifRincian::where('tarif_id', $orderanTrucking->tarif_id)->where('container_id', $orderanTrucking->container_id)->first();
            
            $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();

            if ($prosesLain == 0) {

                $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();

    
                // return response($tarif,422);
                $trado = Trado::find($request->trado_id);
                $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();

                $suratpengantar->jobtrucking = $request->jobtrucking;
                $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $suratpengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
                $suratpengantar->keterangan = $request->keterangan ?? '';
                $suratpengantar->nourutorder = $request->nourutorder ?? 1;
                $suratpengantar->upah_id = $upahsupir->id;
                $suratpengantar->dari_id = $request->dari_id;
                $suratpengantar->sampai_id = $request->sampai_id;
                $suratpengantar->container_id = $orderanTrucking->container_id;
                $suratpengantar->nocont = $orderanTrucking->nocont;
                $suratpengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
                $suratpengantar->statuscontainer_id = $request->statuscontainer_id;
                $suratpengantar->trado_id = $request->trado_id;
                $suratpengantar->supir_id = $request->supir_id;
                $suratpengantar->gandengan_id = $request->gandengan_id ?? 0;
                $suratpengantar->nojob = $orderanTrucking->nojobemkl;
                $suratpengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
                $suratpengantar->noseal = $orderanTrucking->noseal;
                $suratpengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
                $suratpengantar->statuslongtrip = $request->statuslongtrip;
                $suratpengantar->omset = $tarif->nominal;
                $suratpengantar->gajisupir = $upahsupirRincian->nominalsupir;
                $suratpengantar->gajikenek = $upahsupirRincian->nominalkenek;
                $suratpengantar->agen_id = $orderanTrucking->agen_id;
                $suratpengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
                $suratpengantar->statusperalihan = $request->statusperalihan;
                $suratpengantar->tarif_id = $orderanTrucking->tarif_id;
                $suratpengantar->nominalperalihan = $request->nominalperalihan ?? 0;
                $persentaseperalihan = 0;
                if ($request->nominalperalihan != 0) {
                    $persentaseperalihan = $request->nominalperalihan / $tarif->nominal;
                }

                $suratpengantar->persentaseperalihan = $persentaseperalihan;
                $suratpengantar->discount = $persentaseperalihan;
                $suratpengantar->totalomset = $tarif->nominal - ($tarif->nominal * ($persentaseperalihan / 100));
                $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
                $suratpengantar->nosp = $request->nosp;
                $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglbukti));
                $suratpengantar->komisisupir = $upahsupirRincian->nominalkomisi;
                $suratpengantar->tolsupir = $upahsupirRincian->nominaltol;
                $suratpengantar->jarak = $upahsupir->jarak;
                $suratpengantar->nosptagihlain = $request->nosptagihlain ?? '';
                $suratpengantar->liter = $upahsupirRincian->liter ?? 0;
                $suratpengantar->qtyton = $request->qtyton ?? 0;
                $suratpengantar->totalton = $tarif->nominal * $request->qtyton;
                $suratpengantar->mandorsupir_id = $trado->mandor_id;
                $suratpengantar->mandortrado_id = $trado->mandor_id;
                $suratpengantar->statusgudangsama = $request->statusgudangsama;
                $suratpengantar->statusbatalmuat = $request->statusbatalmuat;
                $suratpengantar->gudang = $request->gudang;
                $suratpengantar->modifiedby = auth('api')->user()->name;
                $suratpengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
                
                $suratpengantar->save();
                if ($request->nominal) {
                    if ($request->nominal[0] != 0) {
                        SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratpengantar->id)->lockForUpdate()->delete();
                        for ($i = 0; $i < count($request->nominal); $i++) {
                            $suratpengantarbiayatambahan = new SuratPengantarBiayaTambahan();
        
                            $suratpengantarbiayatambahan->suratpengantar_id = $suratpengantar->id;
                            $suratpengantarbiayatambahan->keteranganbiaya = $request->keterangan_detail[$i];
                            $suratpengantarbiayatambahan->nominal = $request->nominal[$i];
                            $suratpengantarbiayatambahan->nominaltagih = $request->nominalTagih[$i];
                            $suratpengantarbiayatambahan->modifiedby = auth('api')->user()->name;;
                            $suratpengantarbiayatambahan->save();
        
                            $suratpengantar->biayatambahan_id = $suratpengantarbiayatambahan->id;
                            $suratpengantar->save();
                        }
                    }
                }
                
            } else {

                $suratpengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
                $suratpengantar->container_id = $orderanTrucking->container_id;
                $suratpengantar->nojob = $orderanTrucking->nojobemkl;
                $suratpengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
                $suratpengantar->nocont = $request->nocont ?? '';
                $suratpengantar->nocont2 = $request->nocont2 ?? '';
                $suratpengantar->noseal = $request->noseal ?? '';
                $suratpengantar->noseal2 = $request->noseal2 ?? '';
                $suratpengantar->omset = $tarif->nominal;
                $suratpengantar->agen_id = $orderanTrucking->agen_id;
                $suratpengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
                $suratpengantar->tarif_id = $orderanTrucking->tarif_id;
                $suratpengantar->nominalperalihan = $request->nominalperalihan ?? 0;
                $persentaseperalihan = 0;
                if ($request->nominalperalihan != 0) {
                    $persentaseperalihan = $request->nominalperalihan / $tarif->nominal;
                }

                $suratpengantar->persentaseperalihan = $persentaseperalihan;
                $suratpengantar->discount = $persentaseperalihan;
                $suratpengantar->totalomset = $tarif->nominal - ($tarif->nominal * ($persentaseperalihan / 100));
                $suratpengantar->totalton = $tarif->nominal * $request->qtyton;

                $suratpengantar->save();
            }


            $logTrail = [
                'namatabel' => strtoupper($suratpengantar->getTable()),
                'postingdari' => $request->postingdari ?? 'EDIT SURAT PENGANTAR',
                'idtrans' => $suratpengantar->id,
                'nobuktitrans' => $suratpengantar->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $suratpengantar->toArray(),
                'modifiedby' => $suratpengantar->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($suratpengantar, $suratpengantar->getTable());
            $suratpengantar->position = $selected->position;
            $suratpengantar->page = ceil($suratpengantar->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $suratpengantar
            ]);
        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;
        }
    }

    public function getpelabuhan($id)
    {

        $suratpengantar = new SuratPengantar();
        return response([
            "data" => $suratpengantar->getpelabuhan($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $suratPengantar = new SuratPengantar();
        $suratPengantar = $suratPengantar->lockAndDestroy($id);
        $del = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->delete();


        if ($suratPengantar) {
            $logTrail = [
                'namatabel' => strtoupper($suratPengantar->getTable()),
                'postingdari' => 'DELETE SURAT PENGANTAR',
                'idtrans' => $suratPengantar->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $suratPengantar->toArray(),
                'modifiedby' => $suratPengantar->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            $selected = $this->getPosition($suratPengantar, $suratPengantar->getTable(), true);
            $suratPengantar->position = $selected->position;
            $suratPengantar->id = $selected->id;
            $suratPengantar->page = ceil($suratPengantar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $suratPengantar
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('suratpengantar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function cekUpahSupir(Request $request)
    {
        
        $upahSupir =  DB::table('upahsupir')->from(
            DB::raw("upahsupir with (readuncommitted)")
        )
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $request->dari_id)
            ->where('upahsupir.kotasampai_id', $request->sampai_id)
            ->where('upahsupirrincian.container_id', $request->container_id)
            ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
            ->first();
      
        if (!isset($upahSupir)) {
            $upahSupir =  DB::table('upahsupir')->from(
                DB::raw("upahsupir with (readuncommitted)")
            )
                ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
                ->join(DB::raw("upahsupirrincian with (readuncommitted)"), 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
                ->where('upahsupir.kotasampai_id', $request->dari_id)
                ->where('upahsupir.kotadari_id', $request->sampai_id)
                ->where('upahsupirrincian.container_id', $request->container_id)
                ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
                ->first();
        }
        if ($upahSupir != null) {
            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '1',
            ];

            return response($data);
        } else {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'USBA')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }

    public function cekValidasi($id)
    {
     
        $suratPengantar = new SuratPengantar();
        $nobukti = DB::table('SuratPengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
        ->where('id', $id)->first();
        //validasi Hari ini
        $todayValidation = SuratPengantar::todayValidation($nobukti->id);
        $isEditAble = SuratPengantar::isEditAble($nobukti->id);
        $edit =true;
        if(!$todayValidation && !$isEditAble){
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SATL')->get();
            $keterangan = $query['0'];
            $edit = false;
        }



        $cekdata = $suratPengantar->cekvalidasihapus($nobukti->nobukti, $nobukti->jobtrucking);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'edit' => $edit,
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'edit' => $edit,
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function getTarifOmset($id)
    {

        $iddata = $id ?? 0;
        $tarifrincian = new TarifRincian();
        $omset = $tarifrincian->getid($iddata);


        return response([
            "dataTarif" => $omset
        ]);
    }

    public function getOrderanTrucking($id)
    {

        $suratPengantar = new SuratPengantar();
        return response([
            "data" => $suratPengantar->getOrderanTrucking($id)
        ]);
    }
    /**
     * @ClassName 
     */
    public function approvalBatalMuat($id)
    {
        DB::beginTransaction();
        try{
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            $statusBatalMuat = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS BATAL MUAT')->where('text', '=', 'BATAL MUAT')->first();
            $statusBukanBatalMuat = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS BATAL MUAT')->where('text', '=', 'BUKAN BATAL MUAT')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($suratPengantar->statusbatalmuat == $statusBatalMuat->id) {
                $suratPengantar->statusbatalmuat = $statusBukanBatalMuat->id;
                $aksi = $statusBukanBatalMuat->text;
            } else {
                $suratPengantar->statusbatalmuat = $statusBatalMuat->id;
                $aksi = $statusBatalMuat->text;
            }

            if ($suratPengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' => 'APPROVED BATAL MUAT',
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->id,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantar->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);

        }catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function approvalEditTujuan($id)
    {
        DB::beginTransaction();
        try{
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            $statusEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'EDIT TUJUAN')->first();
            $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($suratPengantar->statusedittujuan == $statusEditTujuan->id) {
                $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
                $aksi = $statusTidakBolehEditTujuan->text;
            } else {
                $suratPengantar->statusedittujuan = $statusEditTujuan->id;
                $aksi = $statusEditTujuan->text;
            }

            if ($suratPengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' => 'APPROVED EDIT TUJUAN',
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->id,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantar->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];
    
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);

        }catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getGaji($dari, $sampai, $container, $statuscontainer)
    {
        $data = DB::table('upahsupir')
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join('upahsupirrincian', 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $dari)
            ->where('upahsupir.kotasampai_id', $sampai)
            ->where('upahsupirrincian.container_id', $container)
            ->where('upahsupirrincian.statuscontainer_id', $statuscontainer)

            // dd($data->toSql());
            ->first();
        if ($data != null) {
            return response([
                'data' => $data
            ]);
        } else {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'USBA')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }
}
