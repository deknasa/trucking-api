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
            $subgroup = 'PENCAIRAN GIRO KASBANK BUKTI';

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
                    
                        PencairanGiroPengeluaranDetail::where('pencairangiropengeluaran_id', $cekPencairan->id)->delete();
                        JurnalUmumHeader::where('nobukti', $cekPencairan->nobukti)->delete();
                        JurnalUmumDetail::where('nobukti', $cekPencairan->nobukti)->delete();
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
                        app(LogTrailController::class)->store($validatedLogTrail);
                    
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

                        $datadetaillog = [
                            'id' => $iddetail,
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
                            'modifiedby' => $pencairanGiro->modifiedby,
                            'created_at' => date('d-m-Y H:i:s', strtotime($pencairanGiro->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($pencairanGiro->updated_at)),
                        ];

                        $detaillog[] = $datadetaillog;

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                            'idtrans' =>  $iddetail,
                            'nobuktitrans' => $pencairanGiro->nobukti,
                            'aksi' => 'ENTRY',
                            'datajson' => $detaillog,
                            'modifiedby' => $request->modifiedby,
                        ];

                        $data = new StoreLogTrailRequest($datalogtrail);
                        app(LogTrailController::class)->store($data);

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

            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);

                app(JurnalUmumDetailController::class)->store($jurnal);
            }

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


    public function show(PencairanGiroPengeluaranHeader $pencairanGiroPengeluaranHeader)
    {
        //
    }

    /**
     * @ClassName
     */
    public function destroy(StorePencairanGiroPengeluaranHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {

            $pencairanGiro = new PencairanGiroPengeluaranHeader();

            for ($i = 0; $i < count($request->pengeluaranId); $i++) {
                $pengeluaran = PengeluaranHeader::select('nobukti')->where('id', $request->pengeluaranId[$i])->first();
                $get = PencairanGiroPengeluaranHeader::where('pengeluaran_nobukti', $pengeluaran->nobukti)->first();

                if ($get == null) {
                    DB::rollBack();
                    return response([
                        'status' => false,
                        'message' => 'NO BUKTI KAS/BANK BELUM DIPROSES'
                    ], 500);
                }
                PencairanGiroPengeluaranDetail::where('pencairangiropengeluaran_id', $get->id)->delete();
                JurnalUmumHeader::where('nobukti', $get->nobukti)->delete();
                JurnalUmumDetail::where('nobukti', $get->nobukti)->delete();
                PencairanGiroPengeluaranHeader::destroy($get->id);

                $logTrail = [
                    'namatabel' => strtoupper($pencairanGiro->getTable()),
                    'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN HEADER',
                    'idtrans' => $get->id,
                    'nobuktitrans' => $get->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $get->toArray(),
                    'modifiedby' => $get->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }

            DB::commit();

            $selected = $this->getPosition($pencairanGiro, $pencairanGiro->getTable(), true);
           
            $pencairanGiro->position = $selected->position;
            $pencairanGiro->id = $selected->id;
            $pencairanGiro->page = ceil($pencairanGiro->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pencairanGiro
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }
}
