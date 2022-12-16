<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTruckingDetail;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;
use App\Http\Requests\UpdatePenerimaanTruckingDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenerimaanTruckingDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'penerimaantruckingheader_id' => $request->penerimaantruckingheader_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PenerimaanTruckingDetail::from('penerimaantruckingdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['penerimaantruckingheader_id'])) {
                $query->where('detail.penerimaantruckingheader_id', $params['penerimaantruckingheader_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('penerimaantruckingheader_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'header.coa',
                    'header.penerimaan_nobukti',
                    'header.keterangan',
                    'bank.namabank as bank',
                    'penerimaantrucking.keterangan as penerimaantrucking',
                    'supir.namasupir as supir_id',
                    'detail.pengeluarantruckingheader_nobukti',
                    'detail.nominal'
                )
                ->leftJoin('penerimaantruckingheader as header','header.id','detail.penerimaantruckingheader_id')
                ->leftJoin('penerimaantrucking', 'header.penerimaantrucking_id','penerimaantrucking.id')
                ->leftJoin('bank', 'header.bank_id', 'bank.id')
                ->leftJoin('supir', 'detail.supir_id', 'supir.id');

                $penerimaanTruckingDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',

                    'supir.namasupir as supir_id',
                    'detail.pengeluarantruckingheader_nobukti',
                )
                ->leftJoin('supir', 'detail.supir_id', 'supir.id');
                
                $penerimaanTruckingDetail = $query->get();
            }
            return response([
                'data' => $penerimaanTruckingDetail,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }


    
    public function store(StorePenerimaanTruckingDetailRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $penerimaantruckingDetail = new PenerimaanTruckingDetail();
            
            $penerimaantruckingDetail->penerimaantruckingheader_id = $request->penerimaantruckingheader_id;
            $penerimaantruckingDetail->nobukti = $request->nobukti;
            $penerimaantruckingDetail->supir_id = $request->supir_id;
            $penerimaantruckingDetail->pengeluarantruckingheader_nobukti = $request->pengeluarantruckingheader_nobukti;
            $penerimaantruckingDetail->nominal = $request->nominal;
            $penerimaantruckingDetail->modifiedby = auth('api')->user()->name;
            
            $penerimaantruckingDetail->save();
           
            DB::commit();
            return [
                'error' => false,
                'detail' => $penerimaantruckingDetail,
                'id' => $penerimaantruckingDetail->id,
                'tabel' => $penerimaantruckingDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }


}
