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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PiutangDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $piutangDetail = new PiutangDetail();

        return response()->json([
            'data' => $piutangDetail->get(),
            'attributes' => [
                'totalRows' => $piutangDetail->totalRows,
                'totalPages' => $piutangDetail->totalPages,
                'totalNominal' => $piutangDetail->totalNominal
            ]
        ]);
    }

    public function history(): JsonResponse
    {
        $piutangDetail = new PiutangDetail();

        return response()->json([
            'data' => $piutangDetail->getHistory(),
            'attributes' => [
                'totalRows' => $piutangDetail->totalRows,
                'totalPages' => $piutangDetail->totalPages,
                'totalNominal' => $piutangDetail->totalNominal,
                'totalPotongan' => $piutangDetail->totalPotongan,
                'totalNominalLebih' => $piutangDetail->totalNominalLebih,
            ]
        ]);
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
            if ($request->entridetail == 1) {
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

    public function addrow(StorePiutangDetailRequest $request)
    {
        return true;
    }
}
