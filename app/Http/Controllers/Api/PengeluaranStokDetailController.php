<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokDetail;
use App\Models\PengeluaranStokHeader;
use App\Models\Parameter;
use App\Models\StokPersediaan;
use App\Models\Stok;

use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\UpdatePengeluaranStokDetailRequest;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PengeluaranStokDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pengeluaranstokheader_id' => $request->pengeluaranstokheader_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        // return $params;
        try {
            $query = PengeluaranStokDetail::from('pengeluaranstokdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pengeluaranstokheader_id'])) {
                $query->where('detail.pengeluaranstokheader_id', $params['pengeluaranstokheader_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pengeluaranstokheader_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.pengeluaranstokheader_id',
                    'detail.nobukti',
                    'stok.namastok as stok',
                    'detail.stok_id',
                    'detail.qty',
                    'detail.harga',
                    'detail.persentasediscount',
                    'detail.nominaldiscount',
                    'detail.total',
                    'detail.keterangan',
                    'detail.vulkanisirke',
                    'detail.modifiedby',
                );

                $pengeluaranStokDetail = $query->get();
            } else {
                $query->select(
                    'detail.pengeluaranstokheader_id',
                    'detail.nobukti',
                    'detail.stok_id',
                    'stok.namastok as stok',
                    'detail.qty',
                    'detail.harga',
                    'detail.persentasediscount',
                    'detail.nominaldiscount',
                    'detail.total',
                    'detail.keterangan',
                    'detail.vulkanisirke',
                    'detail.modifiedby',
                )
                // ->leftJoin('pengeluaranstok','pengeluaranstokheader.pengeluaranstok_id','pengeluaranstok.id')

                ->leftJoin('pengeluaranstokheader', 'detail.pengeluaranstokheader_id', 'pengeluaranstokheader.id')
                ->leftJoin('stok', 'detail.stok_id', 'stok.id');       
                 
                $pengeluaranStokDetail = $query->get();
            }

            return response([
                'data' => $pengeluaranStokDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'stok_id' => ['required',
                Rule::unique('pengeluaranstokdetail')->where( function ($query) use ($request) {
                return $query->where('pengeluaranstokheader_id', $request->pengeluaranstokheader_id);
            })],
            'pengeluaranstokheader_id' => 'required',
            'harga' => "required|numeric|gt:0",
            'persentasediscount' => "numeric|max:100",
            'detail_keterangan' => 'required',
            // 'vulkanisirke' => 'required',
            'qty' => "required|numeric|gt:0",
         ], [
             'stok_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'stok_id.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
             'pengeluaranstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'qty.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'qty.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'harga.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'harga.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'persentasediscount.max' => ':attribute' . ' ' . app(ErrorController::class)->geterror('MAX')->keterangan,
         ], [
             'stok_id' => 'stok',
            //  'keterangan' => 'keterangan Detail',
             'qty' => 'qty',
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
        $nominaldiscount = $total * ($request->persentasediscount/100);
        $total -= $nominaldiscount;
        $pengeluaranstokheader = PengeluaranStokHeader::where('id', $request->pengeluaranstokheader_id)->first();

            try {

                $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
     
                if ($pengeluaranstokheader->pengeluaranstok_id == $spk->text) {

                    $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
                        ->where('statusformat', '=', $pengeluaranstokheader->statusformat)
                        ->first();
    
                    $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
                    if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                        $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
                            ->where("gudang_id", $pengeluaranstokheader->gudang_id)->firstorFail();
                    }
                }
    
                
                $pengeluaranStokDetail = new PengeluaranStokDetail();
                $pengeluaranStokDetail->pengeluaranstokheader_id = $request->pengeluaranstokheader_id;
                $pengeluaranStokDetail->nobukti = $request->nobukti;
                $pengeluaranStokDetail->stok_id = $request->stok_id;
                $pengeluaranStokDetail->qty = $request->qty;
                $pengeluaranStokDetail->harga = $request->harga;
                $pengeluaranStokDetail->nominaldiscount = $nominaldiscount;
                $pengeluaranStokDetail->total = $total;
                $pengeluaranStokDetail->persentasediscount = $request->persentasediscount ?? 0;
                $pengeluaranStokDetail->vulkanisirke = $request->vulkanisirke ?? 0;
                $pengeluaranStokDetail->keterangan = $request->detail_keterangan;
                
                $pengeluaranStokDetail->modifiedby = auth('api')->user()->name;
                
                
               
                DB::commit();
                if ($pengeluaranStokDetail->save()) {

                    $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
                    if ($pengeluaranstokheader->pengeluaranstok_id == $spk->text) {
    
                        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
    
                            $stokpersediaan->qty -= $request->qty;
                            $stokpersediaan->save();
                        }
                    }

                    return [
                        'error' => false,
                        'id' => $pengeluaranStokDetail->id,
                        'tabel' => $pengeluaranStokDetail->getTable(),
                        'detail' => $pengeluaranStokDetail
                    ];
                }
            } catch (\Throwable $th) {
                throw $th;
                DB::rollBack();
            }        
        
    }
}
