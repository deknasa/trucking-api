<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreAbsensiSupirHeaderRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Models\AbsensiSupirHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAbsensiSupirHeaderRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\Parameter;


use Illuminate\Database\QueryException;

class AbsensiSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $absensiSupirHeader = new AbsensiSupirHeader();

        return response([
            'data' => $absensiSupirHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirHeader->totalRows,
                'totalPages' => $absensiSupirHeader->totalPages
            ]
        ]);
    }

    public function show($id)
    {
        $data = AbsensiSupirHeader::findAll($id);
        $detail = AbsensiSupirDetail::find($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    public function detail($id)
    {
        return response([
            'data' => AbsensiSupirDetail::with('trado', 'supir', 'absenTrado')->where('absensi_id', $id)->get()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAbsensiSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

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


            /* Store header */
            $absensisupir = new AbsensiSupirHeader();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


            $absensisupir->nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $absensisupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $absensisupir->keterangan = $request->keterangan ?? '';
            $absensisupir->kasgantung_nobukti = $request->kasgantung_nobukti ?? '';
            $absensisupir->nominal = array_sum($request->uangjalan);
            $absensisupir->statusformat = $format->id;
            $absensisupir->statuscetak = $statusCetak->id ?? 0;
            $absensisupir->modifiedby = auth('api')->user()->name;

            if ($absensisupir->save()) {
               
                $detaillog = [];
                for ($i = 0; $i < count($request->trado_id); $i++) {
                    /* Store Detail */
                    $datadetail = [
                        'absensi_id' => $absensisupir->id,
                        'nobukti' => $absensisupir->nobukti,
                        'trado_id' => $request->trado_id[$i],
                        'supir_id' => $request->supir_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'uangjalan' => $request->uangjalan[$i],
                        'absen_id' => $request->absen_id[$i] ?? '',
                        'jam' => $request->jam[$i],
                        'modifiedby' => $absensisupir->modifiedby,
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
                }

                //GET NO BUKTI KAS GANTUNG
                
                $format = DB::table('parameter')
                    ->where('grp', 'KAS GANTUNG')
                    ->where('subgrp', 'NOMOR KAS GANTUNG')
                    ->first();

                $noBuktiKasgantungRequest = new Request();
                $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
                $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
                $noBuktiKasgantungRequest['table'] = 'kasgantungheader';
                $noBuktiKasgantungRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiKasGantung = app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];

                $absensisupir->kasgantung_nobukti = $nobuktiKasGantung;
                $absensisupir->save();
                 /* Store Header LogTrail */
                 $logTrail = [
                    'namatabel' => strtoupper($absensisupir->getTable()),
                    'postingdari' => 'ENTRY ABSENSI SUPIR HEADER',
                    'idtrans' => $absensisupir->id,
                    'nobuktitrans' => $absensisupir->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $absensisupir->toArray(),
                    'modifiedby' => $absensisupir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // Store detail logtrail
                $detailLogTrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY ABSENSI SUPIR DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $absensisupir->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $absensisupir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);



                $kasGantungHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiKasGantung,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'penerima_id' => '',
                    'keterangan' => $request->keterangan,
                    'bank_id' => '',
                    'pengeluaran_nobukti' => '',
                    'coakaskeluar' => '',
                    'postingdari' => 'ENTRY ABSENSI SUPIR',
                    'tglkaskeluar' => '1900/1/1',
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name
                ];

                $kasGantungDetail = [];
                for ($i = 0; $i < count($request->uangjalan); $i++) {
                    $detail = [];

                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $nobuktiKasGantung,
                        'nominal' => $request->uangjalan[$i],
                        'coa' => '',
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' =>  auth('api')->user()->name
                    ];
                    $kasGantungDetail[] = $detail;
                }

                $kasGantung = $this->storeKasGantung($kasGantungHeader, $kasGantungDetail);


                // if (!$kasGantung['status'] AND @$kasGantung['errorCode'] == 2601) {
                //     goto ATAS;
                // }
                if (!$kasGantung['status']) {
                    throw new \Throwable($kasGantung['message']);
                }
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($absensisupir, $absensisupir->getTable());
            $absensisupir->position = $selected->position;
            $absensisupir->page = ceil($absensisupir->position / ($request->limit ?? 10));


            return response([
                'message' => 'Berhasil disimpan',
                'data' => $absensisupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAbsensiSupirHeaderRequest $request, AbsensiSupirHeader $absensiSupirHeader)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $absensiSupirHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $absensiSupirHeader->keterangan = $request->keterangan ?? '';
            $absensiSupirHeader->nominal = array_sum($request->uangjalan);
            $absensiSupirHeader->statuscetak = $statusCetak->id ?? 0;
            $absensiSupirHeader->modifiedby = auth('api')->user()->name;

            if ($absensiSupirHeader->save()) {
                /* Store Header LogTrail */
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'EDIT ABSENSI SUPIR HEADER',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                KasGantungDetail::where('nobukti', $request->kasgantung_nobukti)->delete();
                KasGantungHeader::where('nobukti', $request->kasgantung_nobukti)->delete();
                AbsensiSupirDetail::where('absensi_id', $absensiSupirHeader->id)->delete();
                $detaillog = [];
                for ($i = 0; $i < count($request->trado_id); $i++) {
                    /* Store Detail */
                    $datadetail = [
                        'absensi_id' => $absensiSupirHeader->id,
                        'nobukti' => $absensiSupirHeader->nobukti,
                        'trado_id' => $request->trado_id[$i],
                        'supir_id' => $request->supir_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'uangjalan' => $request->uangjalan[$i],
                        'absen_id' => $request->absen_id[$i] ?? '',
                        'jam' => $request->jam[$i],
                        'modifiedby' => $absensiSupirHeader->modifiedby,
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
                }

                $detailLogTrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT ABSENSI SUPIR DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $absensiSupirHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $absensiSupirHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($detailLogTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $group = 'KAS GANTUNG';
                $subgroup = 'NOMOR KAS GANTUNG';
                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $noBuktiKasgantungRequest = new Request();
                $noBuktiKasgantungRequest['group'] = 'KAS GANTUNG';
                $noBuktiKasgantungRequest['subgroup'] = 'NOMOR KAS GANTUNG';
                $noBuktiKasgantungRequest['table'] = 'kasgantungheader';
                $noBuktiKasgantungRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiKasGantung = app(Controller::class)->getRunningNumber($noBuktiKasgantungRequest)->original['data'];

                $absensiSupirHeader->kasgantung_nobukti = $nobuktiKasGantung;
                $absensiSupirHeader->save();


                $kasGantungHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiKasGantung,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'penerima_id' => '',
                    'keterangan' => $request->keterangan,
                    'bank_id' => '',
                    'pengeluaran_nobukti' => '',
                    'coakaskeluar' => '',
                    'postingdari' => 'ENTRY ABSENSI SUPIR',
                    'tglkaskeluar' => '1900/1/1',
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name
                ];

                $kasGantungDetail = [];
                for ($i = 0; $i < count($request->uangjalan); $i++) {
                    $detail = [];

                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $nobuktiKasGantung,
                        'nominal' => $request->uangjalan[$i],
                        'coa' => '',
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' =>  auth('api')->user()->name
                    ];
                    $kasGantungDetail[] = $detail;
                }

                $kasGantung = $this->storeKasGantung($kasGantungHeader, $kasGantungDetail);


                // if (!$kasGantung['status'] AND @$kasGantung['errorCode'] == 2601) {
                //     goto ATAS;
                // }
                if (!$kasGantung['status']) {
                    throw new \Throwable($kasGantung['message']);
                }
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable());
            $absensiSupirHeader->position = $selected->position;
            $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil diubah',
                'data' => $absensiSupirHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(AbsensiSupirHeader $absensiSupirHeader, Request $request)
    {
        DB::beginTransaction();
        try {
            $getDetail = AbsensiSupirDetail::from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->where('absensi_id', $absensiSupirHeader->id)->get();
            $getKasgantungHeader = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))
            ->where('nobukti', $absensiSupirHeader->kasgantung_nobukti)->first();
            $getKasgantungDetail = KasGantungDetail::from(DB::raw("kasgantungdetail with (readuncommitted)"))
            ->where('nobukti', $absensiSupirHeader->kasgantung_nobukti)->get();

            $isDelete = AbsensiSupirHeader::where('id', $absensiSupirHeader->id)->delete();
            KasGantungHeader::where('nobukti', $absensiSupirHeader->kasgantung_nobukti)->delete();

            if ($isDelete) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirHeader->getTable()),
                    'postingdari' => 'DELETE ABSENSI SUPIR HEADER',
                    'idtrans' => $absensiSupirHeader->id,
                    'nobuktitrans' => $absensiSupirHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $absensiSupirHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // DELETE ABSENSI SUPIR DETAIL
                $logTrailAbsensiSupirDetail = [
                    'namatabel' => 'ABSENSISUPIRDETAIL',
                    'postingdari' => 'DELETE ABSENSI SUPIR DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $absensiSupirHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailAbsensiSupirDetail = new StoreLogTrailRequest($logTrailAbsensiSupirDetail);
                app(LogTrailController::class)->store($validatedLogTrailAbsensiSupirDetail);

                // DELETE KAS GANTUNG HEADER
                $logTrailKasgantungHeader = [
                    'namatabel' => 'KASGANTUNGHEADER',
                    'postingdari' => 'DELETE KAS GANTUNG HEADER DARI ABSENSI SUPIR',
                    'idtrans' => $getKasgantungHeader->id,
                    'nobuktitrans' => $getKasgantungHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getKasgantungHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailKasgantungHeader = new StoreLogTrailRequest($logTrailKasgantungHeader);
                $storedLogTrailKasgantung = app(LogTrailController::class)->store($validatedLogTrailKasgantungHeader);

                // DELETE KAS GANTUNG DETAIL
                $logTrailKasgantungDetail = [
                    'namatabel' => 'KASGANTUNGDETAIL',
                    'postingdari' => 'DELETE KAS GANTUNG DETAIL DARI ABSENSI SUPIR',
                    'idtrans' => $storedLogTrailKasgantung['id'],
                    'nobuktitrans' => $getKasgantungHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getKasgantungDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailKasgantungDetail = new StoreLogTrailRequest($logTrailKasgantungDetail);
                app(LogTrailController::class)->store($validatedLogTrailKasgantungDetail);

                DB::commit();
    
                $selected = $this->getPosition($absensiSupirHeader, $absensiSupirHeader->getTable(), true);
                $absensiSupirHeader->position = $selected->position;
                $absensiSupirHeader->id = $selected->id;
                $absensiSupirHeader->page = ceil($absensiSupirHeader->position / ($request->limit ?? 10));
    
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $absensiSupirHeader
                ]);
            }
            return response([
                'message' => 'Gagal dihapus',
            ], 500);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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


    public function cekvalidasi($id)
    {
        $absensisupir = AbsensiSupirHeader::find($id);
        $statusdatacetak = $absensisupir->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('absensisupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
