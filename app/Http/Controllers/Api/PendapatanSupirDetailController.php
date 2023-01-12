<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendapatanSupirDetail;
use App\Http\Requests\StorePendapatanSupirDetailRequest;
use App\Http\Requests\UpdatePendapatanSupirDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanSupirDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'pendapatansupir_id' => $request->pendapatansupir_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = PendapatanSupirDetail::from(DB::raw("pendapatansupirdetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['pendapatansupir_id'])) {
                $query->where('detail.pendapatansupir_id', $params['pendapatansupir_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('pendapatansupir_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'bank.namabank as bank',
                    'header.tgldari',
                    'header.tglsampai',
                    'header.periode',
                    'supir.namasupir as supir_id',
                    'detail.keterangan',
                    'detail.nominal'
                ) 
                ->leftJoin(DB::raw("pendapatansupirheader as header with (readuncommitted)"),'header.id','detail.pendapatansupir_id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'detail.supir_id', 'supir.id');
                
                $pendapatanSupirDetail = $query->get();
            } else {
                $query->select(
                    'detail.nobukti',
                    'supir.namasupir as supir_id',
                    'detail.keterangan',
                    'detail.nominal'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'detail.supir_id', 'supir.id');
                $pendapatanSupirDetail = $query->get();
            }
            return response([
                'data' => $pendapatanSupirDetail,
                
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

   
    public function store(StorePendapatanSupirDetailRequest $request)
    {
        DB::beginTransaction();
       
        try {
            $pendapatanSupirDetail = new PendapatanSupirDetail();
            
            $pendapatanSupirDetail->pendapatansupir_id = $request->pendapatansupir_id;
            $pendapatanSupirDetail->nobukti = $request->nobukti;
            $pendapatanSupirDetail->supir_id = $request->supir_id;
            $pendapatanSupirDetail->nominal = $request->nominal;
            $pendapatanSupirDetail->keterangan = $request->keterangan;
            $pendapatanSupirDetail->modifiedby = auth('api')->user()->name;
            
            $pendapatanSupirDetail->save();
           
            DB::commit();
            return [
                'error' => false,
                'detail' => $pendapatanSupirDetail,
                'id' => $pendapatanSupirDetail->id,
                'tabel' => $pendapatanSupirDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }   
    }

    
}
