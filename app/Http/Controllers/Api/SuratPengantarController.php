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
use App\Models\Kota;
use App\Models\Parameter;
use App\Http\Requests\StoreSuratPengantarRequest;
use App\Http\Requests\UpdateSuratPengantarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
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

    /**
     * @ClassName 
     */
    public function store(StoreSuratPengantarRequest $request)
    {
        DB::beginTransaction();

        try {
            // $content = new Request();
            // $content['group'] = 'SURATPENGANTAR';
            // $content['subgroup'] = 'SURATPENGANTAR';
            // $content['table'] = 'suratpengantar';


            $group = 'SURAT PENGANTAR';
            $subgroup = 'SURAT PENGANTAR';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'suratpengantar';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();
            
            $trado = Trado::find($request->trado_id);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();

            $suratpengantar = new SuratPengantar();

            $suratpengantar->jobtrucking = $request->jobtrucking;
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $request->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan;
            $suratpengantar->nourutorder = $request->nourutorder ?? 1;
            $suratpengantar->upah_id = $upahsupir->id;
            $suratpengantar->dari_id = $request->dari_id;
            $suratpengantar->sampai_id = $request->sampai_id;
            $suratpengantar->container_id = $request->container_id;
            $suratpengantar->nocont = $request->nocont;
            $suratpengantar->nocont2 = $request->nocont2 ?? '';
            $suratpengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratpengantar->trado_id = $request->trado_id;
            $suratpengantar->supir_id = $request->supir_id;
            $suratpengantar->nojob = $request->nojob;
            $suratpengantar->nojob2 = $request->nojob2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip ?? 0;
            $suratpengantar->omset = $request->omset;
            $suratpengantar->discount = $request->discount ?? 0;
            $suratpengantar->totalomset = $request->omset - ($request->omset * ($request->discount / 100));
            $suratpengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratpengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratpengantar->agen_id = $request->agen_id;
            $suratpengantar->jenisorder_id = $request->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan;
            $suratpengantar->tarif_id = $request->tarif_id;
            // $suratpengantar->gajiritasi = $request->gajiritasi ?? 0;
            $tarif = Tarif::find($request->tarif_id);
            $persentaseperalihan = $request->persentaseperalihan ?? 0;
            $nominalperalihan = $request->nominalperalihan ?? 0;
            if ($persentaseperalihan != 0) {
                $nominalperalihan = $tarif->nominal * ($persentaseperalihan / 100);
            }

            $suratpengantar->nominalperalihan = $nominalperalihan;
            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglsp));
            $suratpengantar->statusritasiomset = $request->statusritasiomset;
            $suratpengantar->cabang_id = $request->cabang_id;
            $suratpengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratpengantar->tolsupir = $upahsupirRincian->nominaltol ?? 0;
            $suratpengantar->jarak = $upahsupir->jarak ?? 0;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? '';
            $suratpengantar->nilaitagihlain = $request->nilaitagihlain ?? 0;
            $suratpengantar->tujuantagih = $request->tujuantagih ?? '';
            $suratpengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratpengantar->nominalstafle = $request->nominalstafle ?? 0;
            $suratpengantar->statusnotif = $request->statusnotif ?? 0;
            $suratpengantar->statusoneway = $request->statusoneway ?? 0;
            $suratpengantar->statusedittujuan = $request->statusedittujuan ?? 0;
            $suratpengantar->upahbongkardepo = $request->upahbongkardepo ?? 0;
            $suratpengantar->upahmuatdepo = $request->upahmuatdepo ?? 0;
            $suratpengantar->hargatol = $upahsupirRincian->hargatol ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $request->totalton ?? 0;
            $suratpengantar->mandorsupir_id = $trado->supir_id  ?? 0;
            $suratpengantar->mandortrado_id = $trado->mandor_id ?? 0;
            $suratpengantar->statustrip = $request->statustrip ?? 0;
            $suratpengantar->notripasal = $request->notripasal ?? '';
            $suratpengantar->tgldoor = date('Y-m-d', strtotime($request->tgldoor));
            $suratpengantar->statusdisc = $request->statusdisc ?? 0;
            $suratpengantar->modifiedby = auth('api')->user()->name;
            $suratpengantar->statusformat = $format->id;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $suratpengantar->nobukti = $nobukti;

            // try {
            //     $suratpengantar->save();
            // } catch (\Exception $e) {
            //     $errorCode = @$e->errorInfo[1];
            //     if ($errorCode == 2601) {
            //         goto TOP;
            //     }
            // }
            if ($suratpengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'ENTRY SURAT PENGANTAR',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                if($request->nominal[0] != 0){
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
        }catch (\Throwable $th) {
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
            // $suratpengantar = DB::table((new SuratPengantar())->getTable())->findOrFail($suratpengantar->id);
            $upahsupir = UpahSupir::where('kotadari_id', $request->dari_id)->where('kotasampai_id', $request->sampai_id)->first();
            
            $trado = Trado::find($request->trado_id);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $request->container_id)->where('statuscontainer_id', $request->statuscontainer_id)->first();

            $suratpengantar->jobtrucking = $request->jobtrucking;
            $suratpengantar->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $suratpengantar->pelanggan_id = $request->pelanggan_id;
            $suratpengantar->keterangan = $request->keterangan;
            $suratpengantar->nourutorder = $request->nourutorder ?? 1;
            $suratpengantar->upah_id = $upahsupir->id;
            $suratpengantar->dari_id = $request->dari_id;
            $suratpengantar->sampai_id = $request->sampai_id;
            $suratpengantar->container_id = $request->container_id;
            $suratpengantar->nocont = $request->nocont;
            $suratpengantar->nocont2 = $request->nocont2 ?? '';
            $suratpengantar->statuscontainer_id = $request->statuscontainer_id;
            $suratpengantar->trado_id = $request->trado_id;
            $suratpengantar->supir_id = $request->supir_id;
            $suratpengantar->nojob = $request->nojob;
            $suratpengantar->nojob2 = $request->nojob2 ?? '';
            $suratpengantar->statuslongtrip = $request->statuslongtrip ?? 0;
            $suratpengantar->omset = $request->omset;
            $suratpengantar->discount = $request->discount ?? 0;
            $suratpengantar->totalomset = $request->omset - ($request->omset * ($request->discount / 100));
            $suratpengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratpengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratpengantar->agen_id = $request->agen_id;
            $suratpengantar->jenisorder_id = $request->jenisorder_id;
            $suratpengantar->statusperalihan = $request->statusperalihan;
            $suratpengantar->tarif_id = $request->tarif_id;
            // $suratpengantar->gajiritasi = $request->gajiritasi ?? 0;
            $tarif = Tarif::find($request->tarif_id);
            $persentaseperalihan = $request->persentaseperalihan ?? 0;
            $nominalperalihan = $request->nominalperalihan ?? 0;
            if ($persentaseperalihan != 0) {
                $nominalperalihan = $tarif->nominal * ($persentaseperalihan / 100);
            }

            $suratpengantar->nominalperalihan = $nominalperalihan;
            $suratpengantar->biayatambahan_id = $request->biayatambahan_id ?? 0;
            $suratpengantar->nosp = $request->nosp;
            $suratpengantar->tglsp = date('Y-m-d', strtotime($request->tglsp));
            $suratpengantar->statusritasiomset = $request->statusritasiomset;
            $suratpengantar->cabang_id = $request->cabang_id;
            $suratpengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratpengantar->tolsupir = $upahsupirRincian->nominaltol ?? 0;
            $suratpengantar->jarak = $upahsupir->jarak ?? 0;
            $suratpengantar->nosptagihlain = $request->nosptagihlain ?? '';
            $suratpengantar->nilaitagihlain = $request->nilaitagihlain ?? 0;
            $suratpengantar->tujuantagih = $request->tujuantagih ?? '';
            $suratpengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratpengantar->nominalstafle = $request->nominalstafle ?? 0;
            $suratpengantar->statusnotif = $request->statusnotif ?? 0;
            $suratpengantar->statusoneway = $request->statusoneway ?? 0;
            $suratpengantar->statusedittujuan = $request->statusedittujuan ?? 0;
            $suratpengantar->upahbongkardepo = $request->upahbongkardepo ?? 0;
            $suratpengantar->upahmuatdepo = $request->upahmuatdepo ?? 0;
            $suratpengantar->hargatol = $upahsupirRincian->hargatol ?? 0;
            $suratpengantar->qtyton = $request->qtyton ?? 0;
            $suratpengantar->totalton = $request->totalton ?? 0;
            $suratpengantar->mandorsupir_id = $trado->supir_id  ?? 0;
            $suratpengantar->mandortrado_id = $trado->mandor_id ?? 0;
            $suratpengantar->statustrip = $request->statustrip ?? 0;
            $suratpengantar->notripasal = $request->notripasal ?? '';
            $suratpengantar->tgldoor = date('Y-m-d', strtotime($request->tgldoor));
            $suratpengantar->statusdisc = $request->statusdisc ?? 0;
            $suratpengantar->modifiedby = auth('api')->user()->name;


            if ($suratpengantar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'EDIT SURAT PENGANTAR',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->id,
                    'aksi' => 'EDIT',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                if($request->nominal[0] != 0){
                    
                    SuratPengantarBiayaTambahan::where('suratpengantar_id',$suratpengantar->id)->lockForUpdate()->delete();
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
    public function destroy(SuratPengantar $suratpengantar, Request $request)
    {
        DB::beginTransaction();
        try {
            $del = SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratpengantar->id)->lockForUpdate()->delete();
            // $delete = SuratPengantar::destroy($suratpengantar->id);
            $delete = $suratpengantar->lockForUpdate()->delete();

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($suratpengantar->getTable()),
                    'postingdari' => 'DELETE SURAT PENGANTAR',
                    'idtrans' => $suratpengantar->id,
                    'nobuktitrans' => $suratpengantar->id,
                    'aksi' => 'DELETE',
                    'datajson' => $suratpengantar->toArray(),
                    'modifiedby' => $suratpengantar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();

            $selected = $this->getPosition($suratpengantar, $suratpengantar->getTable(), true);
            $suratpengantar->position = $selected->position;
            $suratpengantar->id = $selected->id;
            $suratpengantar->page = ceil($suratpengantar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $suratpengantar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
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

    public function cekUpahSupir(Request $request) {
        $upahSupir =  DB::table('upahsupir')
                    ->select('upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi')
                    ->join('upahsupirrincian', 'upahsupir.id', 'upahsupirrincian.upahsupir_id')
                    ->where('upahsupir.kotadari_id', $request->dari_id)
                    ->where('upahsupir.kotasampai_id', $request->sampai_id)
                    ->where('upahsupirrincian.container_id', $request->container_id)
                    ->where('upahsupirrincian.statuscontainer_id', $request->statuscontainer_id)
                    ->first();
        if($upahSupir != null) {
            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '1',
            ];

            return response($data);
        }else{
            $query = DB::table('error')
            ->select('keterangan')
            ->where('kodeerror', '=', 'USBA')
            ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'upahsupirbelumada',
                'kodestatus' => '0',
            ];

            return response($data);
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

        return response([
            'data' => $data
        ]);
    }

}
