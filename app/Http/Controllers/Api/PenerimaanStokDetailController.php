<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanStokDetail;
use App\Http\Requests\StorePenerimaanStokDetailRequest;
use App\Http\Requests\UpdatePenerimaanStokDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PenerimaanStokDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'penerimaanstokheader_id' => $request->penerimaanstokheader_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        
        try {
            $query = PenerimaanStokDetail::from('penerimaanstokdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['penerimaanstokheader_id'])) {
                $query->where('detail.penerimaanstokheader_id', $params['penerimaanstokheader_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('penerimaanstokheader_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.penerimaanstokheader_id',
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

                $penerimaanStokDetail = $query->get();
            } else {
                $query->select(
                    'detail.penerimaanstokheader_id',
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
                // ->leftJoin('penerimaanstok','penerimaanstokheader.penerimaanstok_id','penerimaanstok.id')

                ->leftJoin('penerimaanstokheader', 'detail.penerimaanstokheader_id', 'penerimaanstokheader.id')
                ->leftJoin('stok', 'detail.stok_id', 'stok.id');       
                 
                $penerimaanStokDetail = $query->get();
            }

            return response([
                'data' => $penerimaanStokDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StorePenerimaanStokDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'stok_id' => ['required',
                Rule::unique('penerimaanstokdetail')->where( function ($query) use ($request) {
                return $query->where('penerimaanstokheader_id', $request->penerimaanstokheader_id);
            })],
            'penerimaanstokheader_id' => 'required',
            'harga' => "required|numeric|gt:0",
            'persentasediscount' => "numeric|max:100",
            'detail_keterangan' => 'required',
            'vulkanisirke' => 'required',
            'qty' => "required|numeric|gt:0",
         ], [
             'stok_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
             'stok_id.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
             'penerimaanstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
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
            try {
                $penerimaanStokDetail = new PenerimaanStokDetail();
                $penerimaanStokDetail->penerimaanstokheader_id = $request->penerimaanstokheader_id;
                $penerimaanStokDetail->nobukti = $request->nobukti;
                $penerimaanStokDetail->stok_id = $request->stok_id;
                $penerimaanStokDetail->qty = $request->qty;
                $penerimaanStokDetail->harga = $request->harga;
                $penerimaanStokDetail->nominaldiscount = $nominaldiscount;
                $penerimaanStokDetail->total = $total;
                $penerimaanStokDetail->persentasediscount = $request->persentasediscount;
                $penerimaanStokDetail->vulkanisirke = $request->vulkanisirke;
                $penerimaanStokDetail->keterangan = $request->detail_keterangan;
                
                $penerimaanStokDetail->modifiedby = auth('api')->user()->name;
                
                
               
                DB::commit();
                if ($penerimaanStokDetail->save()) {
                    return [
                        'error' => false,
                        'id' => $penerimaanStokDetail->id,
                        'tabel' => $penerimaanStokDetail->getTable(),
                    ];
                }
            } catch (\Throwable $th) {
                throw $th;
                DB::rollBack();
            }        
        
    }


}
