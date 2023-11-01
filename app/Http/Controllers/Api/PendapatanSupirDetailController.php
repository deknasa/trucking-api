<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendapatanSupirDetail;
use App\Http\Requests\StorePendapatanSupirDetailRequest;
use App\Http\Requests\UpdatePendapatanSupirDetailRequest;
use App\Models\JurnalUmumDetail;
use App\Models\PendapatanSupirHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanSupirDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $pendapatanSupir = new PendapatanSupirDetail();

        return response()->json([
            'data' => $pendapatanSupir->get(),
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages,
                'totalNominal' => $pendapatanSupir->totalNominal,
                'totalGajiKenek' => $pendapatanSupir->totalGajiKenek,
                'totalAll' => $pendapatanSupir->totalAll,
            ]
        ]);
    }

        /**
     * @ClassName 
     */
    public function detailsupir(): JsonResponse
    {
        $pendapatanSupir = new PendapatanSupirDetail();

        return response()->json([
            'data' => $pendapatanSupir->getsupir(),
            'attributes' => [
                'totalRows' => $pendapatanSupir->totalRows,
                'totalPages' => $pendapatanSupir->totalPages,
                'totalNominal' => $pendapatanSupir->totalNominal,
                'totalGajiKenek' => $pendapatanSupir->totalGajiKenek
            ]
        ]);
    }

    public function jurnal(Request $request): JsonResponse
    {
        $jurnalDetail = new JurnalUmumDetail();


        if (request()->nobukti != 'false' && request()->nobukti != null) {
            if ($request->penerimaan == 'DPO') {
                $getDPO = (new PendapatanSupirHeader())->getNobuktiDPO($request->nobukti);
                if ($getDPO != null) {

                    $nobukti = $getDPO->penerimaan_nobukti;
                    return response()->json([
                        'data' => $jurnalDetail->getJurnalFromAnotherTable($nobukti),
                        'attributes' => [
                            'totalRows' => $jurnalDetail->totalRows,
                            'totalPages' => $jurnalDetail->totalPages,
                            'totalNominalDebet' => $jurnalDetail->totalNominalDebet,
                            'totalNominalKredit' => $jurnalDetail->totalNominalKredit,
                        ]
                    ]);
                }
            }
            if ($request->penerimaan == 'PJP') {
                $getPJP = (new PendapatanSupirHeader())->getNobuktiPJP($request->nobukti);
                if ($getPJP != null) {

                    $nobukti = $getPJP->penerimaan_nobukti;
                    return response()->json([
                        'data' => $jurnalDetail->getJurnalFromAnotherTable($nobukti),
                        'attributes' => [
                            'totalRows' => $jurnalDetail->totalRows,
                            'totalPages' => $jurnalDetail->totalPages,
                            'totalNominalDebet' => $jurnalDetail->totalNominalDebet,
                            'totalNominalKredit' => $jurnalDetail->totalNominalKredit,
                        ]
                    ]);
                }
            }
        }

        return response()->json([
            'data' => [],
            'attributes' => [
                'totalRows' => $jurnalDetail->totalRows,
                'totalPages' => $jurnalDetail->totalPages,
                'totalNominalDebet' => 0,
                'totalNominalKredit' => 0,
            ]
        ]);
    }

    public function store(StorePendapatanSupirDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $pendapatanSupirDetail = new PendapatanSupirDetail();

            $pendapatanSupirDetail->pendapatansupir_id = $request->pendapatansupir_id;
            $pendapatanSupirDetail->nobukti = $request->nobukti;
            $pendapatanSupirDetail->supir_id = $request->supir_id;
            $pendapatanSupirDetail->nominal = $request->nominal;
            $pendapatanSupirDetail->keterangan = $request->keterangan;
            $pendapatanSupirDetail->modifiedby = auth('api')->user()->name;

            $pendapatanSupirDetail->save();

            DB::commit();
            return [
                'error' => false,
                'detail' => $pendapatanSupirDetail,
                'id' => $pendapatanSupirDetail->id,
                'tabel' => $pendapatanSupirDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
