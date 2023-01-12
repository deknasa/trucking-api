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
                // return $request->limit;

        $params = [
            'id' => $request->id,
            'penerimaantruckingheader_id' => $request->penerimaantruckingheader_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
            'offset' => $request->offset ?? (($request->page - 1) * $request->limit),
            'limit' => $request->limit ?? 10,
        ];
        $totalRows = 0;
        try {
            $query = PenerimaanTruckingDetail::from(DB::raw("penerimaantruckingdetail as detail with (readuncommitted)"));

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
                    'bank.namabank as bank',
                    'penerimaantrucking.keterangan as penerimaantrucking',
                    'supir.namasupir as supir_id',
                    'detail.pengeluarantruckingheader_nobukti',
                    'detail.nominal'
                )
                ->leftJoin(DB::raw("penerimaantruckingheader as header with (readuncommitted)"),'header.id','detail.penerimaantruckingheader_id')
                ->leftJoin(DB::raw("penerimaantrucking with (readuncommitted)"), 'header.penerimaantrucking_id','penerimaantrucking.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'detail.supir_id', 'supir.id');

                $penerimaanTruckingDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'detail.nominal',

                    'supir.namasupir as supir_id',
                    'detail.pengeluarantruckingheader_nobukti',
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'detail.supir_id', 'supir.id');
                $totalRows =  $query->count();
                    $query->skip($params['offset'])->take($params['limit']);
                $penerimaanTruckingDetail = $query->get();
            }
            return response([
                'data' => $penerimaanTruckingDetail,
                'attributes' => [
                    'totalRows' => $totalRows ?? 0,
                    'totalPages' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1
                ]
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
