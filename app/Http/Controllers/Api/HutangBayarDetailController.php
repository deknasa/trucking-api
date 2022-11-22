<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HutangBayarDetail;
use App\Http\Requests\StoreHutangBayarDetailRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class HutangBayarDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'hutangbayar_id' => $request->hutangbayar_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];


        try {
            $query = HutangBayarDetail::from('hutangbayardetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['hutangbayar_id'])) {
                $query->where('detail.hutangbayar_id', $params['hutangbayar_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('hutangbayar_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.keterangan as keteranganheader',
                    'header.pengeluaran_nobukti',
                    'header.coa',
                    'bank.namabank as bank',
                    'supplier.namasupplier as supplier',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.tglcair',
                    'detail.potongan',
                    'detail.hutang_nobukti',
                    'alatbayar.namaalatbayar as alatbayar_id',

                )
                ->join('hutangbayarheader as header','header.id','detail.hutangbayar_id')
                ->leftJoin('bank', 'header.bank_id', 'bank.id')
                ->leftJoin('supplier', 'header.supplier_id', 'supplier.id')
                ->leftJoin('alatbayar', 'detail.alatbayar_id', 'alatbayar.id');


                $hutangbayarDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',
                    'detail.keterangan',
                    'detail.tglcair',
                    'detail.potongan',
                    'detail.hutang_nobukti',

                    'alatbayar.namaalatbayar as alatbayar_id',
                )
                    ->leftJoin('alatbayar', 'detail.alatbayar_id', 'alatbayar.id');

                $hutangbayarDetail = $query->get();
            }
            $idUser = auth('api')->user()->id;
            $getuser = User::select('name','cabang.namacabang as cabang_id')
            ->where('user.id',$idUser)->join('cabang','user.cabang_id','cabang.id')->first();
           
            return response([
                'data' => $hutangbayarDetail,
                'user' => $getuser,
                
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StoreHutangBayarDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'keterangan' => 'required'
        ], [
            'keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan
        ]);

        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {
            $hutangbayarDetail = new HutangBayarDetail();
            $hutangbayarDetail->hutangbayar_id = $request->hutangbayar_id;
            $hutangbayarDetail->nobukti = $request->nobukti;
            $hutangbayarDetail->nominal = $request->nominal;
            $hutangbayarDetail->hutang_nobukti = $request->hutang_nobukti;
            $hutangbayarDetail->cicilan = $request->cicilan;
            $hutangbayarDetail->alatbayar_id = $request->alatbayar_id;
            $hutangbayarDetail->tglcair = date('Y-m-d', strtotime($request->tglcair));
            $hutangbayarDetail->potongan = $request->potongan;
            $hutangbayarDetail->keterangan = $request->keterangan;
            $hutangbayarDetail->modifiedby = auth('api')->user()->name;
            $hutangbayarDetail->save();

            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $hutangbayarDetail->id,
                    'tabel' => $hutangbayarDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }
}
