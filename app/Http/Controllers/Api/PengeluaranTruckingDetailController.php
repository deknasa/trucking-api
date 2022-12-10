<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTruckingDetail;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Http\Requests\UpdatePengeluaranTruckingDetailRequest;
use App\Models\User;
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
                    'header.nobukti',
                    'header.tglbukti',
                    'header.coa',
                    'header.pengeluaran_nobukti',
                    'header.keterangan',
                    'bank.namabank as bank',
                    'pengeluarantrucking.keterangan as pengeluarantrucking',
                    'supir.namasupir as supir_id',
                    'detail.penerimaantruckingheader_nobukti',
                    'detail.nominal'
                ) 
                ->leftJoin('pengeluarantruckingheader as header','header.id','detail.pengeluarantruckingheader_id')
                ->leftJoin('pengeluarantrucking', 'header.pengeluarantrucking_id','pengeluarantrucking.id')
                ->leftJoin('bank', 'header.bank_id', 'bank.id')
                ->leftJoin('supir', 'detail.supir_id', 'supir.id');

                $pengeluaranTruckingDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',

                    'supir.namasupir as supir_id',
                    'detail.penerimaantruckingheader_nobukti',
                )
                ->leftJoin('supir', 'detail.supir_id', 'supir.id');
                
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
            return [
                'error' => false,
                'id' => $pengeluarantruckingDetail->id,
                'tabel' => $pengeluarantruckingDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }


}
