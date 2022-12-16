<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePencairanGiroPengeluaranDetailRequest;
use App\Models\PencairanGiroPengeluaranHeader;
use App\Http\Requests\StorePencairanGiroPengeluaranHeaderRequest;
use App\Http\Requests\UpdatePencairanGiroPengeluaranHeaderRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PencairanGiroPengeluaranDetail;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pencairanGiro = new PencairanGiroPengeluaranHeader();

        return response([
            'data' => $pencairanGiro->get(),
            'attributes' => [
                'totalRows' => $pencairanGiro->totalRows,
                'totalPages' => $pencairanGiro->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePencairanGiroPengeluaranHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $group = 'PENCAIRAN GIRO BUKTI';
            $subgroup = 'PENCAIRAN GIRO BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pencairangiropengeluaranheader';
            $content['tgl'] = date('Y-m-d');

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->pengeluaranId); $i++) {
                $pencairanGiro = new PencairanGiroPengeluaranHeader();

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengeluaran = PengeluaranHeader::select('nobukti', 'keterangan')->where('id', $request->pengeluaranId[$i])->first();

                $cekPencairan = PencairanGiroPengeluaranHeader::where('pengeluaran_nobukti', $pengeluaran->nobukti)->first();

                if ($cekPencairan != null) {
                    $getDetail = PencairanGiroPengeluaranDetail::where('pencairangiropengeluaran_id', $cekPencairan->id)->get();
                    $getJurnalHeader = JurnalUmumHeader::where('nobukti', $cekPencairan->nobukti)->first();
                    $getJurnalDetail = JurnalUmumDetail::where('nobukti', $cekPencairan->nobukti)->get();

                    PencairanGiroPengeluaranDetail::where('pencairangiropengeluaran_id', $cekPencairan->id)->lockForUpdate()->delete();
                    JurnalUmumHeader::where('nobukti', $cekPencairan->nobukti)->lockForUpdate()->delete();
                    JurnalUmumDetail::where('nobukti', $cekPencairan->nobukti)->lockForUpdate()->delete();
                    PencairanGiroPengeluaranHeader::destroy($cekPencairan->id);

                    $logTrail = [
                        'namatabel' => strtoupper($pencairanGiro->getTable()),
                        'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN HEADER',
                        'idtrans' => $cekPencairan->id,
                        'nobuktitrans' => $cekPencairan->nobukti,
                        'aksi' => 'DELETE',
                        'datajson' => $cekPencairan->toArray(),
                        'modifiedby' => $cekPencairan->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    // DELETE PENCAIRAN GIRO PENGELUARAN DETAIL
                    $logTrailPencairanGiroDetail = [
                        'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
                        'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN DETAIL',
                        'idtrans' => $storedLogTrail['id'],
                        'nobuktitrans' => $cekPencairan->nobukti,
                        'aksi' => 'DELETE',
                        'datajson' => $getDetail->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedLogTrailPencairanGiroDetail = new StoreLogTrailRequest($logTrailPencairanGiroDetail);
                    app(LogTrailController::class)->store($validatedLogTrailPencairanGiroDetail);

                    // DELETE JURNAL HEADER
                    $logTrailJurnalHeader = [
                        'namatabel' => 'JURNALUMUMHEADER',
                        'postingdari' => 'DELETE JURNAL UMUM HEADER DARI PENCAIRAN GIRO',
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
                        'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI PENCAIRAN GIRO',
                        'idtrans' => $storedLogTrailJurnal['id'],
                        'nobuktitrans' => $getJurnalHeader->nobukti,
                        'aksi' => 'DELETE',
                        'datajson' => $getJurnalDetail->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
                    app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);
                } else {

                    $pencairanGiro->nobukti = $nobukti;
                    $pencairanGiro->tglbukti = date('Y-m-d');
                    $pencairanGiro->keterangan = $pengeluaran->keterangan;
                    $pencairanGiro->pengeluaran_nobukti = $pengeluaran->nobukti;
                    $pencairanGiro->statusapproval = $statusApproval->id;
                    $pencairanGiro->userapproval = '';
                    $pencairanGiro->tglapproval = '';
                    $pencairanGiro->modifiedby = auth('api')->user()->name;
                    $pencairanGiro->statusformat = $format->id;

                    $pencairanGiro->save();

                    $logTrail = [
                        'namatabel' => strtoupper($pencairanGiro->getTable()),
                        'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                        'idtrans' => $pencairanGiro->id,
                        'nobuktitrans' => $pencairanGiro->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $pencairanGiro->toArray(),
                        'modifiedby' => $pencairanGiro->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    $jurnalHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pencairanGiro->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($pencairanGiro->tglbukti)),
                        'keterangan' => $pencairanGiro->keterangan,
                        'postingdari' => "ENTRY PENCAIRAN GIRO PENGELUARAN",
                        'statusapproval' => $statusApproval->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                    ];

                    // STORE DETAIL

                    $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $request->pengeluaranId[$i])->get();
                    $jurnaldetail = [];
                    $baris = 0;
                    foreach ($pengeluaranDetail as $index => $value) {
                        $datadetail = [
                            'pencairangiropengeluaran_id' => $pencairanGiro->id,
                            'nobukti' => $pencairanGiro->nobukti,
                            'alatbayar_id' => $value->alatbayar_id,
                            'nowarkat' => $value->nowarkat,
                            'tgljatuhtempo' => $value->tgljatuhtempo,
                            'nominal' => $value->nominal,
                            'coadebet' => $value->coadebet,
                            'coakredit' => $value->coakredit,
                            'keterangan' => $value->keterangan,
                            'bulanbeban' => $value->bulanbeban,
                            'modifiedby' => auth('api')->user()->name

                        ];

                        //STORE 
                        $data = new StorePencairanGiroPengeluaranDetailRequest($datadetail);

                        $datadetails = app(PencairanGiroPengeluaranDetailController::class)->store($data);

                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }

                        $detaillog[] = $datadetails['detail']->toArray();


                        $jurnalDetail = [
                            [
                                'nobukti' => $pencairanGiro->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($pencairanGiro->tglbukti)),
                                'coa' =>  $value->coadebet,
                                'nominal' => $value->nominal,
                                'keterangan' => $value->keterangan,
                                'modifiedby' => auth('api')->user()->name,
                                'baris' => $baris,
                            ],
                            [
                                'nobukti' => $pencairanGiro->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($pencairanGiro->tglbukti)),
                                'coa' =>  $value->coakredit,
                                'nominal' => -$value->nominal,
                                'keterangan' => $value->keterangan,
                                'modifiedby' => auth('api')->user()->name,
                                'baris' => $baris,
                            ]
                        ];

                        $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                    }

                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $pencairanGiro->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);


                    $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

                    $baris++;
                }
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pencairanGiro
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    private function storeJurnal($header, $detail)
    {

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $nobukti = $header['nobukti'];
            $fetchId = JurnalUmumHeader::select('id')
                ->where('nobukti', '=', $nobukti)
                ->first();
            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($jurnal);

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $nobukti,
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
}
