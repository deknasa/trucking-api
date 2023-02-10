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
    public function index()
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
            $trado = Trado::find($request->trado_id);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();

            $suratpengantar = new SuratPengantar();

            $suratpengantar->jobtrucking = $request->jobtrucking;
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan;
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
            $suratpengantar->gandengan_id = $request->gandengan_id;
            $suratpengantar->nojob = $orderanTrucking->nojobemkl;
            $suratpengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip;
            $suratpengantar->omset = $tarif->nominal;
            $suratpengantar->discount = $request->persentaseperalihan ?? 0;
            $suratpengantar->totalomset = $tarif->nominal - ($tarif->nominal * ($request->discount / 100));
            $suratpengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratpengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratpengantar->agen_id = $orderanTrucking->agen_id;
            $suratpengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan;
            $suratpengantar->tarif_id = $orderanTrucking->tarif_id;
            $suratpengantar->persentaseperalihan = $request->persentaseperalihan ?? 0;
            $nominalperalihan = $request->nominalperalihan ?? 0;
            if ($request->persentaseperalihan != 0) {
                $nominalperalihan = $tarif->nominal * ($request->persentaseperalihan / 100);
            }

            $suratpengantar->nominalperalihan = $nominalperalihan;
            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->statusritasiomset = $request->statusritasiomset;
            $suratpengantar->cabang_id = $request->cabang_id;
            $suratpengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratpengantar->tolsupir = $upahsupirRincian->nominaltol;
            $suratpengantar->jarak = $upahsupir->jarak;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? '';
            $suratpengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $tarif->nominalton * $request->qtyton;
            $suratpengantar->mandorsupir_id = $trado->mandor_id;
            $suratpengantar->mandortrado_id = $trado->mandor_id;
            $suratpengantar->statusgudangsama = $request->statusgudangsama;
            $suratpengantar->statusbatalmuat = $request->statusbatalmuat;
            $suratpengantar->gudang = $request->gudang;
            $suratpengantar->modifiedby = auth('api')->user()->name;
            $suratpengantar->statusformat = $format->id;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $suratpengantar->nobukti = $nobukti;


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
            $orderanTrucking = OrderanTrucking::where('nobukti', $request->jobtrucking)->first();
            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();

            // $tarif = Tarif::find($orderanTrucking->tarif_id);
            $tarif = TarifRincian::where('tarif_id',$orderanTrucking->tarif_id)->where('container_id',$request->container_id)->first();

            // return response($tarif,422);
            $trado = Trado::find($request->trado_id);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();


            $suratpengantar->jobtrucking = $request->jobtrucking;
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan;
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
            $suratpengantar->gandengan_id = $request->gandengan_id;
            $suratpengantar->nojob = $orderanTrucking->nojobemkl;
            $suratpengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratpengantar->noseal = $orderanTrucking->noseal;
            $suratpengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip;
            $suratpengantar->omset = $tarif->nominal;
            $suratpengantar->discount = $request->persentaseperalihan ?? 0;
            $suratpengantar->totalomset = $tarif->nominal - ($tarif->nominal * ($request->discount / 100));
            $suratpengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratpengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratpengantar->agen_id = $orderanTrucking->agen_id;
            $suratpengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan;
            $suratpengantar->tarif_id = $orderanTrucking->tarif_id;
            $suratpengantar->persentaseperalihan = $request->persentaseperalihan ?? 0;
            $nominalperalihan = $request->nominalperalihan ?? 0;
            if ($request->persentaseperalihan != 0) {
                $nominalperalihan = $tarif->nominal * ($request->persentaseperalihan / 100);
            }

            $suratpengantar->nominalperalihan = $nominalperalihan;
            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->statusritasiomset = $request->statusritasiomset;
            $suratpengantar->cabang_id = $request->cabang_id;
            $suratpengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratpengantar->tolsupir = $upahsupirRincian->nominaltol;
            $suratpengantar->jarak = $upahsupir->jarak;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? '';
            $suratpengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $tarif->nominalton * $request->qtyton;
            $suratpengantar->mandorsupir_id = $trado->mandor_id;
            $suratpengantar->mandortrado_id = $trado->mandor_id;
            $suratpengantar->statusgudangsama = $request->statusgudangsama;
            $suratpengantar->statusbatalmuat = $request->statusbatalmuat;
            $suratpengantar->gudang = $request->gudang;
            $suratpengantar->modifiedby = auth('api')->user()->name;


            if ($suratpengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'EDIT SURAT PENGANTAR',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

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
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;
        }
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
        $upahSupir =  DB::table('upahsupir')
            ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
            ->join('upahsupirrincian', 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
            ->where('upahsupir.kotadari_id', $request->dari_id)
            ->where('upahsupir.kotasampai_id', $request->sampai_id)
            ->where('upahsupirrincian.container_id', $request->container_id)
            ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
            ->first();
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

    public function getTarifOmset($id)
    {
  
        $iddata=$id ??0;
        $tarifrincian = new TarifRincian();
        $omset=$tarifrincian->getid($iddata);
       

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
