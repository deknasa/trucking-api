<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranTruckingDetail;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Http\Requests\UpdatePengeluaranTruckingDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengeluaranTruckingDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pengeluarantruckingheader_id' => $request->pengeluarantruckingheader_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PengeluaranTruckingDetail::from('pengeluarantruckingdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pengeluarantruckingheader_id'])) {
                $query->where('detail.pengeluarantruckingheader_id', $params['pengeluarantruckingheader_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pengeluarantruckingheader_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'detail.nobukti',
                    'detail.supir_id',
                    'detail.penerimaantruckingheader_nobukti',
                    'detail.nominal'
                );

                $pengeluaranTruckingDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',

                    'supir.namasupir as supir_id',
                    'penerimaantruckingheader.nobukti as penerimaantruckingheader_nobukti',
                )
                ->leftJoin('supir', 'pengeluarantruckingdetail.supir_id', 'supir.id')
                ->leftJoin('penerimaantruckingheader', 'pengeluarantruckingdetail.nobukti', 'penerimaantruckingheader.nobukti');        

                $pengeluaranTruckingDetail = $query->get();
            }

            return response([
                'data' => $pengeluaranTruckingDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }


    
    public function store(StorePengeluaranTruckingDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
           'supir_id' => 'required',
           'penerimaantruckingheader_nobukti' => 'required',
            'nominal' => 'required',
        ], [
            'supir_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'penerimaantruckingheader_nobukti.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
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
            $pengeluarantruckingDetail = new PengeluaranTruckingDetail();
            
            $pengeluarantruckingDetail->pengeluarantruckingheader_id = $request->pengeluarantruckingheader_id;
            $pengeluarantruckingDetail->nobukti = $request->nobukti;
            $pengeluarantruckingDetail->supir_id = $request->supir_id;
            $pengeluarantruckingDetail->penerimaantruckingheader_nobukti = $request->penerimaantruckingheader_nobukti;
            $pengeluarantruckingDetail->nominal = $request->nominal;
            $pengeluarantruckingDetail->modifiedby = auth('api')->user()->name;
            
            $pengeluarantruckingDetail->save();
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $pengeluarantruckingDetail->id,
                    'tabel' => $pengeluarantruckingDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }


}
