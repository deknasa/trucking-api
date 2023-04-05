<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanGiroDetailRequest;
use App\Models\PenerimaanGiroHeader;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
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

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;
            if ($tanpaprosesnobukti == 0) {
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

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            }

            $penerimaanGiro = new PenerimaanGiroHeader();

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $penerimaanGiro->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanGiro->pelanggan_id = $request->pelanggan_id ?? '';
            $penerimaanGiro->agen_id = $request->agen_id ?? '';
            $penerimaanGiro->postingdari = $request->postingdari ?? 'ENTRY PENERIMAAN GIRO';
            $penerimaanGiro->diterimadari = $request->diterimadari;
            $penerimaanGiro->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanGiro->cabang_id = 0;
            $penerimaanGiro->statusapproval = $request->statusapproval ?? $statusApproval->id;
            $penerimaanGiro->userapproval = '';
            $penerimaanGiro->tglapproval = '';
            $penerimaanGiro->statusformat = $request->statusformat ?? $format->id;
            $penerimaanGiro->statuscetak = $statusCetak->id;
            $penerimaanGiro->modifiedby = auth('api')->user()->name;

            $penerimaanGiro->nobukti = ($tanpaprosesnobukti == 0) ?  $nobukti : $request->nobukti;

            $penerimaanGiro->save();

            $logTrail = [
                'namatabel' => strtoupper($penerimaanGiro->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN GIRO HEADER',
                'idtrans' => $penerimaanGiro->id,
                'nobuktitrans' => $penerimaanGiro->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $penerimaanGiro->toArray(),
                'modifiedby' => $penerimaanGiro->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $coadebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'DEBET')->first();
            $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'KREDIT')->first();

            $memodebet = json_decode($coadebet->memo, true);
            $memokredit = json_decode($coakredit->memo, true);

            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal;
            }
            for ($i = 0; $i < count($counter); $i++) {

                $datadetail = [
                    'penerimaangiro_id' => $penerimaanGiro->id,
                    'nobukti' => $penerimaanGiro->nobukti,
                    'nowarkat' => ($request->datadetail != '') ? $request->nowarkat : $request->nowarkat[$i],
                    'tgljatuhtempo' => ($request->datadetail != '') ? $penerimaanGiro->tglbukti : date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                    'coadebet' => $memodebet['JURNAL'],
                    'coakredit' => $memokredit['JURNAL'],
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                    'bank_id' => ($request->datadetail != '') ? $request->bank_id : $request->bank_id[$i],
                    'invoice_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['invoice_nobukti'] : $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i] ?? '',
                    'jenisbiaya' => $request->jenisbiaya[$i] ?? '',
                    'pelunasanpiutang_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['nobukti'] : $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => ($request->datadetail != '') ? '' : date('Y-m-d', strtotime($request->bulanbeban[$i])) ?? '',
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
                'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN GIRO DETAIL',
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
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'postingdari' => ($request->postingdari) ? 'ENTRY PENERIMAAN GIRO DARI ' . $request->postingdari : 'ENTRY PENERIMAAN GIRO',
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];
                $jurnaldetail = [];

                for ($i = 0; $i < count($counter); $i++) {
                    $detail = [];
                    $jurnalDetail = [
                        [
                            'nobukti' => $penerimaanGiro->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($penerimaanGiro->tglbukti)),
                            'coa' =>  $memodebet['JURNAL'],
                            'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $penerimaanGiro->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($penerimaanGiro->tglbukti)),
                            'coa' =>  $memokredit['JURNAL'],
                            'nominal' => ($request->datadetail != '') ? '-' . $request->datadetail[$i]['nominal'] : '-' . $request->nominal[$i],
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
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

            if ($tanpaprosesnobukti == 0) {

                /* Set position and page */
                $selected = $this->getPosition($penerimaanGiro, $penerimaanGiro->getTable());
                $penerimaanGiro->position = $selected->position;
                $penerimaanGiro->page = ceil($penerimaanGiro->position / ($request->limit ?? 10));
            }
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
    public function update(UpdatePenerimaanGiroHeaderRequest $request, PenerimaanGiroHeader $penerimaangiroheader)
    {
        DB::beginTransaction();

        try {
            $isUpdate = $request->isUpdate ?? 0;

            if ($isUpdate == 0) {

                $penerimaangiroheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $penerimaangiroheader->pelanggan_id = $request->pelanggan_id;
                $penerimaangiroheader->agen_id = $request->agen_id ?? '';
                $penerimaangiroheader->diterimadari = $request->diterimadari;
                $penerimaangiroheader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
                $penerimaangiroheader->modifiedby = auth('api')->user()->name;
            } else {
                $penerimaangiroheader->agen_id = $request->agen_id;
                $penerimaangiroheader->modifiedby = auth('api')->user()->name;
            }

            if ($penerimaangiroheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaangiroheader->getTable()),
                    'postingdari' => $request['postingdari'] ?? 'EDIT PENERIMAAN GIRO HEADER',
                    'idtrans' => $penerimaangiroheader->id,
                    'nobuktitrans' => $penerimaangiroheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaangiroheader->toArray(),
                    'modifiedby' => $penerimaangiroheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            PenerimaanGiroDetail::where('penerimaangiro_id', $penerimaangiroheader->id)->delete();

            $coadebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'DEBET')->first();
            $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'KREDIT')->first();

            $memodebet = json_decode($coadebet->memo, true);
            $memokredit = json_decode($coakredit->memo, true);

            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal;
            }
            $detaillog = [];
            for ($i = 0; $i < count($counter); $i++) {

                $datadetail = [
                    'penerimaangiro_id' => $penerimaangiroheader->id,
                    'nobukti' => $penerimaangiroheader->nobukti,
                    'nowarkat' => ($request->datadetail != '') ? $request->nowarkat : $request->nowarkat[$i],
                    'tgljatuhtempo' => ($request->datadetail != '') ? $penerimaangiroheader->tglbukti : date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                    'coadebet' => $memodebet['JURNAL'],
                    'coakredit' => $memokredit['JURNAL'],
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                    'bank_id' => ($request->datadetail != '') ? $request->bank_id : $request->bank_id[$i],
                    'invoice_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['invoice_nobukti'] : '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i] ?? '',
                    'jenisbiaya' => $request->jenisbiaya[$i] ?? '',
                    'pelunasanpiutang_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['nobukti'] : '-',
                    'bulanbeban' => ($request->datadetail != '') ? '' : date('Y-m-d', strtotime($request->bulanbeban[$i])) ?? '',
                    'modifiedby' => $penerimaangiroheader->modifiedby,
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
                'postingdari' => $request['postingdari'] ?? 'EDIT PENERIMAAN GIRO DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $penerimaangiroheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $penerimaangiroheader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $jurnaldetail = [];

            for ($i = 0; $i < count($counter); $i++) {
                $detail = [];
                $jurnalDetail = [
                    [
                        'nobukti' => $penerimaangiroheader->nobukti,
                        'tglbukti' => ($request->datadetail != '') ? $penerimaangiroheader->tglbukti : date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' =>  $memodebet['JURNAL'],
                        'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] :  $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ],
                    [
                        'nobukti' => $penerimaangiroheader->nobukti,
                        'tglbukti' => ($request->datadetail != '') ? $penerimaangiroheader->tglbukti : date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' =>  $memokredit['JURNAL'],
                        'nominal' => ($request->datadetail != '') ? '-' . $request->datadetail[$i]['nominal'] : '-' . $request->nominal[$i],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                    ]
                ];

                $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
            }


            $jurnalHeader = [
                'isUpdate' => 1,
                'postingdari' => $request->postingdari ?? "EDIT PENERIMAAN GIRO",
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $jurnaldetail
            ];
            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $penerimaangiroheader->nobukti)->first();
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            $jurnal = new UpdateJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->update($jurnal, $newJurnal);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            if ($isUpdate == 0) {

                /* Set position and page */
                $selected = $this->getPosition($penerimaangiroheader, $penerimaangiroheader->getTable());
                $penerimaangiroheader->position = $selected->position;
                $penerimaangiroheader->page = ceil($penerimaangiroheader->position / ($request->limit ?? 10));
            }
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaangiroheader
            ]);
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

        $getDetail = PenerimaanGiroDetail::lockForUpdate()->where('penerimaangiro_id', $id)->get();
        $penerimaanGiro = new PenerimaanGiroHeader();
        $penerimaanGiro = $penerimaanGiro->lockAndDestroy($id);

        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $penerimaanGiro->nobukti)->first();
        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $penerimaanGiro->nobukti)->get();

        JurnalUmumHeader::where('nobukti', $penerimaanGiro->nobukti)->delete();

        if ($penerimaanGiro) {
            $datalogtrail = [
                'namatabel' => strtoupper($penerimaanGiro->getTable()),
                'postingdari' => $request['postingdari'] ?? 'DELETE PENERIMAAN GIRO HEADER',
                'idtrans' => $penerimaanGiro->id,
                'nobuktitrans' => $penerimaanGiro->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanGiro->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($data);

            // DELETE PENERIMAANGIRO DETAIL
            $logTrailPenerimaanGiroDetail = [
                'namatabel' => 'PENERIMAANGIRODETAIL',
                'postingdari' => $request['postingdari'] ?? 'DELETE PENERIMAAN GIRO DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanGiro->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPenerimaanGiroDetail = new StoreLogTrailRequest($logTrailPenerimaanGiroDetail);
            app(LogTrailController::class)->store($validatedLogTrailPenerimaanGiroDetail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => $request['postingdari'] ?? 'DELETE JURNAL UMUM HEADER DARI PENERIMAAN GIRO',
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
                'postingdari' => $request['postingdari'] ?? 'DELETE JURNAL UMUM DETAIL DARI PENERIMAAN GIRO',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            DB::commit();
            if ($request->postingdari === null) {
                $selected = $this->getPosition($penerimaanGiro, $penerimaanGiro->getTable(), true);
                $penerimaanGiro->position = $selected->position;
                $penerimaanGiro->id = $selected->id;
                $penerimaanGiro->page = ceil($penerimaanGiro->position / ($request->limit ?? 10));
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanGiro
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {

            if ($request->giroId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
                    
                for ($i = 0; $i < count($request->giroId); $i++) {
                    $penerimaanGiro = PenerimaanGiroHeader::find($request->giroId[$i]);

                    if ($penerimaanGiro->statusapproval == $statusApproval->id) {
                        $penerimaanGiro->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $penerimaanGiro->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $penerimaanGiro->tglapproval = date('Y-m-d H:i:s');
                    $penerimaanGiro->userapproval = auth('api')->user()->name;

                    if ($penerimaanGiro->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($penerimaanGiro->getTable()),
                            'postingdari' => 'APPROVAL PENERIMAAN GIRO',
                            'idtrans' => $penerimaanGiro->id,
                            'nobuktitrans' => $penerimaanGiro->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $penerimaanGiro->toArray(),
                            'modifiedby' => $penerimaanGiro->modifiedby
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }
                
                DB::commit();
                return response([
                    'message' => 'Berhasil'
                ]);
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'penerimaan' => "PENERIMAAN GIRO $query->keterangan"
                    ],
                    'message' => "PENERIMAAN GIRO $query->keterangan",
                ], 422);
            }
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
                'postingdari' => $header['postingdari'] ?? 'ENTRY PENERIMAAN GIRO',
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
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

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
                        'modifiedby' => auth('api')->user()->name,
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
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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

    public function cekValidasiAksi($id)
    {
        $penerimaanGiro = new PenerimaanGiroHeader();
        $nobukti = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader"))->where('id', $id)->first();
        $cekdata = $penerimaanGiro->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }
}
