<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStokHeader;
use App\Models\PenerimaanStok;
use App\Models\Parameter;
use App\Models\StokPersediaan;
use App\Models\Stok;
use App\Http\Requests\StorePenerimaanStokDetailRequest;
use App\Http\Requests\UpdatePenerimaanStokDetailRequest;
use App\Models\HutangDetail;
use App\Models\HutangHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PenerimaanStokDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $penerimaanStokDetail = new PenerimaanStokDetail();
        return response([
            'data' => $penerimaanStokDetail->get(),
            'attributes' => [
                'totalRows' => $penerimaanStokDetail->totalRows,
                'totalPages' => $penerimaanStokDetail->totalPages,
                'totalNominal' => $penerimaanStokDetail->totalNominal
            ]
        ]);
    }
    
    public function hutang(): JsonResponse
    {
        $hutangDetail = new HutangDetail();
        if(request()->nobukti != 'false' && request()->nobukti != null){
            $fetch = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
            request()->hutang_id = $fetch->id;
            return response()->json([
                'data' => $hutangDetail->get(request()->hutang_id),
                'attributes' => [
                    'totalRows' => $hutangDetail->totalRows,
                    'totalPages' => $hutangDetail->totalPages,
                    'totalNominal' => $hutangDetail->totalNominal
                ]
            ]);
        }else{
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $hutangDetail->totalRows,
                    'totalPages' => $hutangDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }

    /**
     * @ClassName 
     */
    public function store(StorePenerimaanStokDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make(
            $request->all(),
            [
                'stok_id' => [
                    'required',
                    Rule::unique('penerimaanstokdetail')->where(function ($query) use ($request) {
                        return $query->where('penerimaanstokheader_id', $request->penerimaanstokheader_id);
                    })
                ],
                'penerimaanstokheader_id' => 'required',
                // 'harga' => "required|numeric|gt:0",
                'detail_keterangan' => 'required',
                // 'qty' => "required|numeric|gt:0",
            ],
            [
                'stok_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'stok_id.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
                'penerimaanstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                // 'qty.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                // 'qty.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                // 'harga.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                // 'harga.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            ],
            [
                'stok_id' => 'stok',
                //  'keterangan' => 'keterangan Detail',
                // 'qty' => 'qty',
                'persentasediscount' => 'persentase discount',
            ],
        );
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        $total = $request->qty * $request->harga;
        $nominaldiscount = $total * ($request->persentasediscount / 100);
        $total -= $nominaldiscount;
        $penerimaanstokheader = PenerimaanStokHeader::where('id', $request->penerimaanstokheader_id)->first();
        try {
            $stok= Stok::where('id', $request->stok_id)->first();
            $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();
            
            if ($stok->statusreuse==$stokreuse->id) {
                $reuse=true;
            } else {
                $reuse=false;
            }

            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
            if ($penerimaanstokheader->penerimaanstok_id == $spb->text) {

                $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
                    ->where('format', '=', $penerimaanstokheader->statusformat)
                    ->first();
                    
                $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
                if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                    $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
                        ->where("gudang_id", $penerimaanstokheader->gudang_id)->firstorFail();
                }
            }

            $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
            if ($penerimaanstokheader->penerimaanstok_id == $kor->text) {

                $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
                    ->where('format', '=', $penerimaanstokheader->statusformat)
                    ->first();
                    
                $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
                if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                    $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
                        ->where("gudang_id", $penerimaanstokheader->gudang_id)->firstorFail();
                }
            }   
            

            $reuse = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
            if ($penerimaanstokheader->penerimaanstok_id == $reuse->text) {
//  return [
//     'error' => true,
//     'gudang'=> $penerimaanstokheader,
//     'sdf'=> StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)->get()
//  ];
                $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
                    ->where('format', '=', $penerimaanstokheader->statusformat)
                    ->first();
                    
                $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
                if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                    $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
                        ->where("gudang_id", $penerimaanstokheader->gudang_id)->firstorFail();
                }
            }              

            $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
            if ($penerimaanstokheader->penerimaanstok_id == $pg->text and $reuse==true) {
                $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
                    ->where('format', '=', $penerimaanstokheader->statusformat)
                    ->first();
                    
                $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
                if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                    $stokpersediaangudangke  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
                    ->where("gudang_id", $penerimaanstokheader->gudangke_id)->firstorFail();

                    $stokpersediaangudangdari  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
                        ->where("gudang_id", $penerimaanstokheader->gudangdari_id)->firstorFail();
                }
            }


            $penerimaanStokDetail = new PenerimaanStokDetail();
            $penerimaanStokDetail->penerimaanstokheader_id = $request->penerimaanstokheader_id;
            $penerimaanStokDetail->nobukti = $request->nobukti;
            $penerimaanStokDetail->stok_id = $request->stok_id;
            $penerimaanStokDetail->qty = $request->qty;
            $penerimaanStokDetail->harga = $request->harga;
            $penerimaanStokDetail->nominaldiscount = $nominaldiscount;
            $penerimaanStokDetail->total = $total;
            $penerimaanStokDetail->penerimaanstok_nobukti = $request->detail_penerimaanstoknobukti;
            $penerimaanStokDetail->persentasediscount = $request->persentasediscount ?? 0;
            $penerimaanStokDetail->vulkanisirke = $request->vulkanisirke ?? '';
            $penerimaanStokDetail->keterangan = $request->detail_keterangan;

            $penerimaanStokDetail->modifiedby = auth('api')->user()->name;



            DB::commit();
            if ($penerimaanStokDetail->save()) {
                $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
                if ($penerimaanstokheader->penerimaanstok_id == $spb->text) {

                    if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {

                        $stokpersediaan->qty += $request->qty;
                        $stokpersediaan->save();
                    }
                }

                $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
                if ($penerimaanstokheader->penerimaanstok_id == $kor->text) {

                    if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {

                        $stokpersediaan->qty += $request->qty;
                        $stokpersediaan->save();
                    }
                }

                $reuse = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
                if ($penerimaanstokheader->penerimaanstok_id == $reuse->text) {

                    if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {

                        $stokpersediaan->qty += $request->qty;
                        $stokpersediaan->save();
                    }
                }                

                if ($penerimaanstokheader->penerimaanstok_id == $pg->text  and $reuse==true ) {
                    if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {

                        $stokpersediaangudangke->qty += $request->qty;
                        $stokpersediaangudangke->save();

                        $stokpersediaangudangdari->qty -= $request->qty;
                        $stokpersediaangudangdari->save();

                    } 
                }

                return [
                    'error' => false,
                    'id' => $penerimaanStokDetail->id,
                    'tabel' => $penerimaanStokDetail->getTable(),
                    'detail' => $penerimaanStokDetail
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
