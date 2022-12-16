<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanGiroDetailRequest;
use App\Models\PenerimaanGiroHeader;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdatePenerimaanGiroHeaderRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PenerimaanGiroDetail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanGiroHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $penerimaanGiro = new PenerimaanGiroHeader();

        return response([
            'data' => $penerimaanGiro->get(),
            'attributes' => [
                'totalRows' => $penerimaanGiro->totalRows,
                'totalPages' => $penerimaanGiro->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePenerimaanGiroHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $group = 'PENERIMAAN GIRO BUKTI';
            $subgroup = 'PENERIMAAN GIRO BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'penerimaangiroheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $penerimaanGiro = new PenerimaanGiroHeader();
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $penerimaanGiro->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanGiro->pelanggan_id = $request->pelanggan_id;
            $penerimaanGiro->keterangan = $request->keterangan;
            $penerimaanGiro->postingdari = $request->postingdari ?? 'ENTRY PENERIMAAN GIRO';
            $penerimaanGiro->diterimadari = $request->diterimadari;
            $penerimaanGiro->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanGiro->cabang_id = 0;
            $penerimaanGiro->statusapproval = $request->statusapproval ?? $statusApproval->id;
            $penerimaanGiro->userapproval = '';
            $penerimaanGiro->tglapproval = '';
            $penerimaanGiro->statusformat = $format->id;
            $penerimaanGiro->statuscetak = $statusCetak->id;
            $penerimaanGiro->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $penerimaanGiro->nobukti = $nobukti;

            $penerimaanGiro->save();

            $logTrail = [
                'namatabel' => strtoupper($penerimaanGiro->getTable()),
                'postingdari' => 'ENTRY PENERIMAAN GIRO HEADER',
                'idtrans' => $penerimaanGiro->id,
                'nobuktitrans' => $penerimaanGiro->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $penerimaanGiro->toArray(),
                'modifiedby' => $penerimaanGiro->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $coadebet = DB::table('parameter')->select('text')->where('grp', 'COA PENERIMAAN GIRO DEBET')->first();
            $coakredit = DB::table('parameter')->select('text')->where('grp', 'COA PENERIMAAN GIRO KREDIT')->first();

            $detaillog = [];
            for ($i = 0; $i < count($request->nominal); $i++) {


                $datadetail = [
                    'penerimaangiro_id' => $penerimaanGiro->id,
                    'nobukti' => $penerimaanGiro->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal[$i],
                    'coadebet' => $coadebet->text,
                    'coakredit' => $coakredit->text,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $request->bank_id[$i],
                    'pelanggan_id' => $penerimaanGiro->pelanggan_id,
                    'invoice_nobukti' => $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i] ?? '',
                    'jenisbiaya' => $request->jenisbiaya[$i] ?? '',
                    'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i])) ?? '',
                    'modifiedby' => $penerimaanGiro->modifiedby,
                ];

                // STORE 
                $data = new StorePenerimaanGiroDetailRequest($datadetail);

                $datadetails = app(PenerimaanGiroDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detaillog[] = $datadetails['detail']->toArray();

            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PENERIMAAN GIRO DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanGiro->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $penerimaanGiro->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            if ($penerimaanGiro->save()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanGiro->nobukti,
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => 'ENTRY PENERIMAAN GIRO',
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];
                $jurnaldetail = [];

                for ($i = 0; $i < count($request->nominal); $i++) {
                    $detail = [];
                    $jurnalDetail = [
                        [
                            'nobukti' => $penerimaanGiro->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $coadebet->text,
                            'nominal' => $request->nominal[$i],
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $penerimaanGiro->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $coakredit->text,
                            'nominal' => -$request->nominal[$i],
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];


                    $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                }

                $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

                if (!$jurnal['status']) {
                    throw new Exception($jurnal['message']);
                }
            }
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($penerimaanGiro, $penerimaanGiro->getTable());
            $penerimaanGiro->position = $selected->position;
            $penerimaanGiro->page = ceil($penerimaanGiro->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiro
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $data = PenerimaanGiroHeader::findAll($id);
        $detail = PenerimaanGiroDetail::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePenerimaanGiroHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerimaanGiroHeader = PenerimaanGiroHeader::lockForUpdate()->findOrFail($id);
            $penerimaanGiroHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanGiroHeader->pelanggan_id = $request->pelanggan_id;
            $penerimaanGiroHeader->keterangan = $request->keterangan;
            $penerimaanGiroHeader->diterimadari = $request->diterimadari;
            $penerimaanGiroHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanGiroHeader->modifiedby = auth('api')->user()->name;

            if ($penerimaanGiroHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanGiroHeader->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN GIRO HEADER',
                    'idtrans' => $penerimaanGiroHeader->id,
                    'nobuktitrans' => $penerimaanGiroHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanGiroHeader->toArray(),
                    'modifiedby' => $penerimaanGiroHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            PenerimaanGiroDetail::where('penerimaangiro_id', $penerimaanGiroHeader->id)->lockForUpdate()->delete();

            $coadebet = DB::table('parameter')->select('text')->where('grp', 'COA PENERIMAAN GIRO DEBET')->first();
            $coakredit = DB::table('parameter')->select('text')->where('grp', 'COA PENERIMAAN GIRO KREDIT')->first();
            $detaillog = [];
            for ($i = 0; $i < count($request->nominal); $i++) {
                $invoice = '-';
                $pelunasanpiutang = '-';
                if (isset($request->pelunasan_id[$i])) {
                    $getLunas = DB::table('pelunasanpiutangdetail')->select('invoice_nobukti', 'nobukti')->where('id', $request->pelunasan_id[$i])->first();
                    $invoice = $getLunas->invoice_nobukti;
                    $pelunasanpiutang = $getLunas->nobukti;
                }
                $datadetail = [
                    'penerimaangiro_id' => $penerimaanGiroHeader->id,
                    'nobukti' => $penerimaanGiroHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal[$i],
                    'coadebet' => $coadebet->text,
                    'coakredit' => $coakredit->text,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $request->bank_id[$i],
                    'pelanggan_id' => $penerimaanGiroHeader->pelanggan_id,
                    'invoice_nobukti' => $invoice,
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i] ?? '',
                    'jenisbiaya' => $request->jenisbiaya[$i] ?? '',
                    'pelunasanpiutang_nobukti' => $pelunasanpiutang,
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i])) ?? '',
                    'modifiedby' => $penerimaanGiroHeader->modifiedby,
                ];

                // STORE 
                $data = new StorePenerimaanGiroDetailRequest($datadetail);

                $datadetails = app(PenerimaanGiroDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detaillog[] = $datadetails['detail']->toArray();

            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'EDIT PENERIMAAN GIRO DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanGiroHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $penerimaanGiroHeader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            JurnalUmumHeader::where('nobukti', $penerimaanGiroHeader->nobukti)->lockForUpdate()->delete();
            JurnalUmumDetail::where('nobukti', $penerimaanGiroHeader->nobukti)->lockForUpdate()->delete();

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $penerimaanGiroHeader->nobukti,
                'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => 'ENTRY PENERIMAAN GIRO',
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];
            $jurnaldetail = [];

            for ($i = 0; $i < count($request->nominal); $i++) {
                $detail = [];
                $jurnalDetail = [
                    [
                        'nobukti' => $penerimaanGiroHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' =>  $coadebet->text,
                        'nominal' => $request->nominal[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ],
                    [
                        'nobukti' => $penerimaanGiroHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' =>  $coakredit->text,
                        'nominal' => -$request->nominal[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];

                $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
            }



            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);


            // if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
            //     goto ATAS;
            // }

            if (!$jurnal['status']) {
                throw new Exception($jurnal['message']);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable());
            $penerimaanGiroHeader->position = $selected->position;
            $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(PenerimaanGiroHeader $penerimaangiroheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $getDetail = PenerimaanGiroDetail::where('penerimaangiro_id', $penerimaangiroheader->id)->get();
            $getJurnalHeader = JurnalUmumHeader::where('nobukti', $penerimaangiroheader->nobukti)->first();
            $getJurnalDetail = JurnalUmumDetail::where('nobukti', $penerimaangiroheader->nobukti)->get();

            $delete = PenerimaanGiroDetail::where('penerimaangiro_id', $penerimaangiroheader->id)->lockForUpdate()->delete();
            $delete = JurnalUmumHeader::where('nobukti', $penerimaangiroheader->nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumDetail::where('nobukti', $penerimaangiroheader->nobukti)->lockForUpdate()->delete();

            $delete = PenerimaanGiroHeader::destroy($penerimaangiroheader->id);
            
            if ($delete) {
                $datalogtrail = [
                    'namatabel' => strtoupper($penerimaangiroheader->getTable()),
                    'postingdari' => 'DELETE PENERIMAAN GIRO HEADER',
                    'idtrans' => $penerimaangiroheader->id,
                    'nobuktitrans' => $penerimaangiroheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $penerimaangiroheader->toArray(),
                    'modifiedby' =>auth('api')->user()->name
                ];
    
                $data = new StoreLogTrailRequest($datalogtrail);
                $storedLogTrail = app(LogTrailController::class)->store($data);

                // DELETE PENERIMAANGIRO DETAIL
                $logTrailPenerimaanGiroDetail = [
                    'namatabel' => 'PENERIMAANGIRODETAIL',
                    'postingdari' => 'DELETE PENERIMAAN GIRO DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $penerimaangiroheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPenerimaanGiroDetail = new StoreLogTrailRequest($logTrailPenerimaanGiroDetail);
                app(LogTrailController::class)->store($validatedLogTrailPenerimaanGiroDetail);

                // DELETE JURNAL HEADER
                $logTrailJurnalHeader = [
                    'namatabel' => 'JURNALUMUMHEADER',
                    'postingdari' => 'DELETE JURNAL UMUM HEADER DARI PENERIMAAN GIRO',
                    'idtrans' => $getJurnalHeader->id,
                    'nobuktitrans' => $getJurnalHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
                $storedLogTrailJurnal = app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);

                
                // DELETE JURNAL DETAIL
                
                $logTrailJurnalDetail = [
                    'namatabel' => 'JURNALUMUMDETAIL',
                    'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI PENERIMAAN GIRO',
                    'idtrans' => $storedLogTrailJurnal['id'],
                    'nobuktitrans' => $getJurnalHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
                app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);
            } 
            DB::commit();

            $selected = $this->getPosition($penerimaangiroheader, $penerimaangiroheader->getTable(), true);
            $penerimaangiroheader->position = $selected->position;
            $penerimaangiroheader->id = $selected->id;
            $penerimaangiroheader->page = ceil($penerimaangiroheader->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaangiroheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanGiro = PenerimaanGiroHeader::find($id);
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($penerimaanGiro->statusapproval == $statusApproval->id) {
                $penerimaanGiro->statusapproval = $statusNonApproval->id;
            } else {
                $penerimaanGiro->statusapproval = $statusApproval->id;
            }

            $penerimaanGiro->tglapproval = date('Y-m-d H:i:s');
            $penerimaanGiro->userapproval = auth('api')->user()->name;

            if ($penerimaanGiro->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanGiro->getTable()),
                    'postingdari' => 'UN/APPROVE PENERIMAAN GIRO',
                    'idtrans' => $penerimaanGiro->id,
                    'nobuktitrans' => $penerimaanGiro->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $penerimaanGiro->toArray(),
                    'modifiedby' => $penerimaanGiro->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function storeJurnal($header, $detail)
    {

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($jurnal);
                
                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY PENERIMAAN GIRO',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function tarikPelunasan($id)
    {
        $penerimaan = new PenerimaanGiroHeader();
        // ($id!='') ? $tarik = $penerimaan->tarikPelunasan($id) : $tarik = $penerimaan->tarikPelunasan();
        return response([
            'data' => $penerimaan->tarikPelunasan($id),
        ]);
    }

    public function getPelunasan($id)
    {
        $get = new PenerimaanGiroHeader();
        return response([
            'data' => $get->getPelunasan($id),
        ]);
    }
    
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $piutang = PenerimaanGiroHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($piutang->statuscetak != $statusSudahCetak->id) {
                $piutang->statuscetak = $statusSudahCetak->id;
                $piutang->tglbukacetak = date('Y-m-d H:i:s');
                $piutang->userbukacetak = auth('api')->user()->name;

                if ($piutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($piutang->getTable()),
                        'postingdari' => 'PRINT PIUTANG HEADER',
                        'idtrans' => $piutang->id,
                        'nobuktitrans' => $piutang->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $piutang->toArray(),
                        'modifiedby' => $piutang->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }


            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = PenerimaanGiroHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
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
}
