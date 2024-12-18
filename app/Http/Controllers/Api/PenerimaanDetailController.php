<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Models\PenerimaanGiroDetail;
use App\Models\PenerimaanGiroHeader;
use App\Models\PenerimaanHeader;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanDetailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $penerimaanDetail = new PenerimaanDetail();


        return response([
            'data' => $penerimaanDetail->get(),
            'attributes' => [
                'totalRows' => $penerimaanDetail->totalRows,
                'totalPages' => $penerimaanDetail->totalPages,
                'totalNominal' => $penerimaanDetail->totalNominal,
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function getPenerimaan(): JsonResponse
    {
        $penerimaanDetail = new PenerimaanDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {
            if (str_contains(request()->nobukti, 'BPGT')) {
                $fetch = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
                request()->penerimaangiro_id = $fetch->id;

                $penerimaanGiroDetail = new PenerimaanGiroDetail();
                return response()->json([
                    'data' => $penerimaanGiroDetail->get(request()->penerimaangiro_id),
                    'attributes' => [
                        'totalRows' => $penerimaanGiroDetail->totalRows,
                        'totalPages' => $penerimaanGiroDetail->totalPages,
                        'totalNominal' => $penerimaanGiroDetail->totalNominal
                    ]
                ]);
            } else {

                // dd(request()->nobukti);
                $fetch = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
                if (isset($fetch)) {
                    request()->penerimaan_id = $fetch->id;
                    return response()->json([
                        'data' => $penerimaanDetail->get(request()->penerimaan_id),
                        'attributes' => [
                            'totalRows' => $penerimaanDetail->totalRows,
                            'totalPages' => $penerimaanDetail->totalPages,
                            'totalNominal' => $penerimaanDetail->totalNominal
                        ]
                    ]);
    
                } else {
                    return response()->json([
                        'data' => [],
                        'attributes' => [
                            'totalRows' => $penerimaanDetail->totalRows,
                            'totalPages' => $penerimaanDetail->totalPages,
                            'totalNominal' => $penerimaanDetail->totalNominal
                        ]
                    ]);
                }
            }
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $penerimaanDetail->totalRows,
                    'totalPages' => $penerimaanDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }

    public function getDetail(){
        $penerimaanDetail = new PenerimaanDetail();

        return response()->json([
            'data' => $penerimaanDetail->findAllpengembalian(request()->penerimaan_id),
        ]);
    }
    public function store(StorePenerimaanDetailRequest $request)
    {
        DB::beginTransaction();

        try {

            $penerimaanDetail = new PenerimaanDetail();

            $penerimaanDetail->penerimaan_id = $request->penerimaan_id;
            $penerimaanDetail->nobukti = $request->nobukti;
            $penerimaanDetail->nowarkat = $request->nowarkat ?? '';
            $penerimaanDetail->tgljatuhtempo = $request->tgljatuhtempo;
            $penerimaanDetail->nominal = $request->nominal;
            $penerimaanDetail->coadebet = $request->coadebet;
            $penerimaanDetail->coakredit = $request->coakredit;
            $penerimaanDetail->keterangan = $request->keterangan;
            $penerimaanDetail->bank_id = $request->bank_id;
            $penerimaanDetail->invoice_nobukti = $request->invoice_nobukti ?? '';
            $penerimaanDetail->bankpelanggan_id = $request->bankpelanggan_id ?? 0;
            $penerimaanDetail->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti ?? '';
            $penerimaanDetail->bulanbeban = $request->bulanbeban;
            $penerimaanDetail->modifiedby = auth('api')->user()->name;

            $penerimaanDetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $penerimaanDetail,
                'id' => $penerimaanDetail->id,
                'tabel' => $penerimaanDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function addrow(StorePenerimaanDetailRequest $request)
    {
        return true;
    }

}
