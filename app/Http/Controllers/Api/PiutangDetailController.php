<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetPiutangDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PiutangDetail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PiutangDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'piutang_id' => $request->piutang_id,
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = PiutangDetail::from(
                DB::raw("piutangdetail as detail with (readuncommitted)")
            );

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['piutang_id'])) {
                $query->where('detail.piutang_id', $params['piutang_id']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.id as id_header',
                    'header.nobukti as nobukti_header',
                    'header.tglbukti as tgl_header',
                    'header.keterangan as keterangan_header',
                    'header.invoice_nobukti as invoice_nobukti',
                    'agen.namaagen as agen_id',
                    'detail.keterangan as keterangan_detail',
                    'detail.nominal',
                    'detail.invoice_nobukti as invoice_nobukti_detail'
                )
                    ->leftJoin('piutangheader as header', 'header.id', 'detail.piutang_id')
                    ->leftJoin('agen', 'header.agen_id','agen.id');

                $piutangDetail = $query->get();
            } else {
                // $piutangDetail = new PiutangDetail();

                // $piutangDetail = $piutangDetail->get($request->piutang_id);
                $query->select('detail.nobukti','detail.keterangan','detail.invoice_nobukti','detail.nominal');
                
                $piutangDetail = $query->get();
            }
            return response([
                'data' => $piutangDetail,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
       
    }

    public function store(StorePiutangDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $piutangdetail = new PiutangDetail();

            $piutangdetail->piutang_id = $request->piutang_id;
            $piutangdetail->nobukti = $request->nobukti;
            $piutangdetail->nominal = $request->nominal;
            $piutangdetail->keterangan = $request->keterangan;
            $piutangdetail->invoice_nobukti = $request->invoice_nobukti;
            $piutangdetail->modifiedby = auth('api')->user()->name;

            $piutangdetail->save();
            
            $datadetail = $piutangdetail;
            if($request->entridetail == 1) {
                $nobukti = $piutangdetail->nobukti;
                $getBaris = DB::table('jurnalumumdetail')->from(
                    DB::raw("jurnalumumdetail with (readuncommitted)")
                )->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();

                $getCOA = DB::table('parameter')->from(
                    DB::raw("parameter with (readuncommitted)")
                )->where("kelompok", "JURNAL INVOICE")->get();

                $coaKasKeluar = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();

                $memo = json_decode($coaKasKeluar->memo, true);


                if (is_null($getBaris)) {
                    $baris = 0;
                } else {
                    $baris = $getBaris->baris + 1;
                }
                $detailLogJurnal = [];
                for ($x = 0; $x <= 1; $x++) {
                    $memo = json_decode($getCOA[$x]->memo, true);
                    
                    if ($x == 1) {
                        $jurnaldetail = [
                            'jurnalumum_id' => $request->jurnal_id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $request->tglbukti,
                            // 'coa' =>  $getCOA[$x]->text,
                            'coa' =>  $memo['JURNAL'],
                            'nominal' => -$piutangdetail->nominal,
                            'keterangan' => $piutangdetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $jurnaldetail = [
                            'jurnalumum_id' => $request->jurnal_id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $request->tglbukti,
                            'coa' =>  $memo['JURNAL'],
                            'nominal' => $piutangdetail->nominal,
                            'keterangan' => $piutangdetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($jurnaldetail);
                    $detailJurnal = app(JurnalUmumDetailController::class)->store($detail);

                    
                    $detailLogJurnal[] = $detailJurnal['detail']->toArray();
                }

                $datadetail = [];
                $datadetail = [
                    'piutangdetail' => $piutangdetail,
                    'jurnaldetail' => $detailLogJurnal
                ];
            }
            DB::commit();

                return [
                    'error' => false,
                    'detail' => $datadetail,
                    'id' => $piutangdetail->id,
                    'tabel' => $piutangdetail->getTable(),
                ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
