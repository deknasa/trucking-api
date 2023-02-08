<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\MandorAbsensiSupir;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirDetail;
use App\Models\Trado;
use App\Models\Parameter;
use App\Http\Requests\StoreMandorAbsensiSupirRequest;
use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MandorAbsensiSupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mandorabsensisupir = new MandorAbsensiSupir();
        return response([
            'data' => $mandorabsensisupir->get(),
            'attributes' => [
                'totalRows' => $mandorabsensisupir->totalRows,
                'totalPages' => $mandorabsensisupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreMandorAbsensiSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            $absensiSupir = AbsensiSupirHeader::where('tglbukti',date('Y-m-d',strtotime('now')))->first();
            if (!$absensiSupir) {
                $absensiSupir = new AbsensiSupirHeader();

                $group = 'ABSENSI';
                $subgroup = 'ABSENSI';
                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();
                $content = new Request();
                
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'absensisupirheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                
                $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

                $absensiSupir->nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $absensiSupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $absensiSupir->statusformat = $format->id;
                $absensiSupir->modifiedby = auth('api')->user()->name;
                $absensiSupir->statuscetak = $statusCetak->id ?? 0;
    

                $noBuktiKasgantungRequest = new Request();
                $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
                $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
                $noBuktiKasgantungRequest['table'] = 'kasgantungheader';
                $noBuktiKasgantungRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                $nobuktiKasGantung = app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];

                $absensiSupir->kasgantung_nobukti = $nobuktiKasGantung;
                $absensiSupir->save();


                $kasGantungHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiKasGantung,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'penerima_id' => '',
                    'bank_id' => '',
                    'pengeluaran_nobukti' => '',
                    'coakaskeluar' => '',
                    'postingdari' => 'ENTRY ABSENSI SUPIR',
                    'tglkaskeluar' => '1900/1/1',
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name
                ];

                $kasGantungDetail = [];
                
                $detail = [];

                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $nobuktiKasGantung,
                    'nominal' => 0,
                    'coa' => '',
                    'keterangan' => $request->keterangan,
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $kasGantungDetail[] = $detail;
                

                $kasGantung = $this->storeKasGantung($kasGantungHeader, $kasGantungDetail);
                if (!$kasGantung['status']) {
                    throw new \Throwable($kasGantung['message']);
                }
            }
            $absensiSupir->modifiedby = auth('api')->user()->name;
            $absensiSupir->save();
            $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $absensiSupir->id)->where('trado_id',$request->trado_id)->delete();
            $datadetail = [
                'absensi_id' => $absensiSupir->id,
                'nobukti' => $absensiSupir->nobukti,
                'trado_id' => $request->trado_id,
                'supir_id' => $request->supir_id,
                'keterangan' => $request->keterangan,
                'absen_id' => $request->absen_id ?? '',
                'jam' => $request->jam,
                'modifiedby' => $absensiSupir->modifiedby,
            ];
    
            $data = new StoreAbsensiSupirDetailRequest($datadetail);
            $datadetails = app(AbsensiSupirDetailController::class)->store($data);


            if ($datadetails['error']) {
                return response($datadetails, 422);
            } else {
                $iddetail = $datadetails['id'];
                $tabeldetail = $datadetails['tabel'];
            }
            $detaillog[] = $datadetails['detail'];
              /* Store Header LogTrail */
            $logTrail = [
                'namatabel' => strtoupper($absensiSupir->getTable()),
                'postingdari' => 'INPUT MANDOR ABSENSI SUPIR HEADER',
                'idtrans' => $absensiSupir->id,
                'nobuktitrans' => $absensiSupir->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $absensiSupir->toArray(),
                'modifiedby' => $absensiSupir->modifiedby
            ];
    
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
            // Store detail logtrail
            $detailLogTrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'INPUT MANDOR ABSENSI SUPIR DETAIL',
                'idtrans' => $absensiSupir->id,
                'nobuktitrans' => $absensiSupir->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $absensiSupir->modifiedby
            ];
            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $datadetails
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
        
        
    }

    /**
     * @ClassName
     */
    public function show($id)
    {

        $mandorabsensisupir = new MandorAbsensiSupir();
        $isTradoAbsen = $mandorabsensisupir->isAbsen($id);
        if (!$isTradoAbsen) {
            $isTradoAbsen = $mandorabsensisupir->getTrado($id);
        }
        return response([
            'status' => true,
            'data' => $isTradoAbsen
        ]);

    }

    public function storeKasGantung($kasGantungHeader, $kasGantungDetail)
    {
        try {


            $kasGantung = new StoreKasGantungHeaderRequest($kasGantungHeader);
            $header = app(KasGantungHeaderController::class)->store($kasGantung);

            $nobukti = $kasGantungHeader['nobukti'];
            $detailLog = [];
            foreach ($kasGantungDetail as $value) {

                $value['kasgantung_id'] = $header->original['data']['id'];
                $value['pengeluaran_nobukti'] = $header->original['data']['pengeluaran_nobukti'];
                $kasGantungDetail = new StoreKasGantungDetailRequest($value);
                $datadetails = app(KasGantungDetailController::class)->store($kasGantungDetail);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY ABSENSI SUPIR',
                'idtrans' =>  $header->original['idlogtrail'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMandorAbsensiSupirRequest $request, MandorAbsensiSupir $mandorAbsensiSupir)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function destroy(MandorAbsensiSupir $mandorAbsensiSupir)
    {
        //
    }
}
