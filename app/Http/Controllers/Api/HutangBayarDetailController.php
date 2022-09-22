<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HutangBayarDetail;
use App\Http\Requests\StoreHutangBayarDetailRequest;

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

            if (isset($params['hutangbayarheader_id'])) {
                $query->where('detail.hutangbayarheader_id', $params['hutangbayarheader_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('hutangbayarheader_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'detail.nobukti',
                    'detail.supir_id',
                    'detail.pengeluarantruckingheader_nobukti',
                    'detail.nominal'
                );

                $hutangbayarDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',

                    'supir.namasupir as supir_id',
                    'pengeluarantruckingheader.nobukti as pengeluarantruckingheader_nobukti',
                )
                    ->leftJoin('supir', 'detail.supir_id', 'supir.id')
                    ->leftJoin('pengeluarantruckingheader', 'detail.pengeluarantruckingheader_nobukti', 'pengeluarantruckingheader.nobukti');

                $hutangbayarDetail = $query->get();
            }

            return response([
                'data' => $hutangbayarDetail
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
            'nominal' => 'required',
        ], [
            'nominal.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'supir_id' => 'pengeluarantruckingdetail',
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
            $hutangbayarDetail->supir_id = $request->supir_id;
            $hutangbayarDetail->pengeluarantruckingheader_nobukti = $request->pengeluarantruckingheader_nobukti;
            $hutangbayarDetail->nominal = $request->nominal;
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
