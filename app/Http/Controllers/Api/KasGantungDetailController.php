<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KasGantungDetail;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Models\KasGantungHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KasGantungDetailController extends Controller
{

    /**
     * @ClassName 
     */
    public function index(): JsonResponse
    {
        $kasGantungDetail = new KasGantungDetail();

        return response()->json([
            'data' => $kasGantungDetail->get(),
            'attributes' => [
                'totalRows' => $kasGantungDetail->totalRows,
                'totalPages' => $kasGantungDetail->totalPages,
                'totalNominal' => $kasGantungDetail->totalNominal
            ]
        ]);
    }


    public function getKasgantung(): JsonResponse
    {
        $kasgantungDetail = new KasGantungDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {

            $fetch = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
            request()->kasgantung_id = $fetch->id;
            return response()->json([
                'data' => $kasgantungDetail->get(request()->kasgantung_id),
                'attributes' => [
                    'totalRows' => $kasgantungDetail->totalRows,
                    'totalPages' => $kasgantungDetail->totalPages,
                    'totalNominal' => $kasgantungDetail->totalNominal
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $kasgantungDetail->totalRows,
                    'totalPages' => $kasgantungDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }
    public function store(StoreKasGantungDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $kasgantungDetail = new KasGantungDetail();
            $entriluar = $request->entriluar ?? 0;

            $kasgantungDetail->kasgantung_id = $request->kasgantung_id;
            $kasgantungDetail->nobukti = $request->nobukti;
            $kasgantungDetail->nominal = $request->nominal;
            $kasgantungDetail->coa = $request->coa ?? '';
            $kasgantungDetail->keterangan = $request->keterangan ?? '';
            $kasgantungDetail->modifiedby = $request->modifiedby;
            $kasgantungDetail->save();

            // insert ke pengeluaran
            // if($entriluar == 1) {
            //     $nobukti = $request->pengeluaran_nobukti;
            //     $fetchId = PengeluaranHeader::select('id')
            //     ->where('nobukti','=',$nobukti)
            //     ->first();
            //     $id = $fetchId->id;
            //     $pengeluaranDetail = [
            //         'pengeluaran_id' => $id,
            //         'entriluar' => 1,
            //         'nobukti' => $nobukti,
            //         'alatbayar_id' => '',
            //         'nowarkat' => '',
            //         'tgljatuhtempo' => '',
            //         'nominal' => $request->nominal,
            //         'coadebet' => '',
            //         'coakredit' => '',
            //         'keterangan' => $request->keterangan_detail,
            //         'bulanbeban' => '',
            //         'modifiedby' => $request->modifiedby
            //     ];

            //     $detail = new StorePengeluaranDetailRequest($pengeluaranDetail);
            //     $tes = app(PengeluaranDetailController::class)->store($detail);
            // }

            DB::commit();

            return [
                'error' => false,
                'detail' => $kasgantungDetail,
                'id' => $kasgantungDetail->id,
                'tabel' => $kasgantungDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
