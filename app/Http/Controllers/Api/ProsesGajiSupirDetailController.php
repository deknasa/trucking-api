<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GajiSupirDetail;
use App\Http\Requests\StoreGajiSupirDetailRequest;
use App\Http\Requests\StoreProsesGajiSupirDetailRequest;
use App\Http\Requests\UpdateGajiSupirDetailRequest;
use App\Models\ProsesGajiSupirDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProsesGajiSupirDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'prosesgajisupir_id' => $request->prosesgajisupir_id,
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
            $query = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail as detail with (readuncommitted)"));

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['prosesgajisupir_id'])) {
                $query->where('detail.prosesgajisupir_id', $params['prosesgajisupir_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('prosesgajisupir_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'header.nobukti',
                    'header.tglbukti',
                    'supir.namasupir as supir_id',
                    'trado.keterangan as trado_id',
                    'detail.gajisupir_nobukti',
                    'detail.nominal',
                    'detail.keterangan as keterangan_detail'
                )
                ->leftJoin(DB::raw("prosesgajisupirheader as header with (readuncommitted)"),'header.id','detail.prosesgajisupir_id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"),'detail.supir_id','supir.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"),'detail.trado_id','trado.id');

                $prosesgajisupirDetail = $query->get();
            } else {
                $query->select(
                    'detail.gajisupir_nobukti',
                    'supir.namasupir as supir_id',
                    'trado.keterangan as trado_id',
                    'detail.keterangan',
                    'detail.nominal',
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"),'detail.supir_id','supir.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"),'detail.trado_id','trado.id');
                $totalRows =  $query->count();
                $query->skip($params['offset'])->take($params['limit']);
                $prosesgajisupirDetail = $query->get();
            }
            return response([
                'data' => $prosesgajisupirDetail,
                'total' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1,
                "records" =>$totalRows ?? 0,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    
    public function store(StoreProsesGajiSupirDetailRequest $request)
    {
        DB::beginTransaction();
        try {
            $gajisupirdetail = new ProsesGajiSupirDetail();
            
            $gajisupirdetail->prosesgajisupir_id = $request->prosesgajisupir_id;
            $gajisupirdetail->nobukti = $request->nobukti;
            $gajisupirdetail->gajisupir_nobukti = $request->gajisupir_nobukti;
            $gajisupirdetail->supir_id = $request->supir_id;
            $gajisupirdetail->trado_id = $request->trado_id;
            $gajisupirdetail->nominal = $request->nominal;
            $gajisupirdetail->keterangan = $request->keterangan;
            
            $gajisupirdetail->modifiedby = auth('api')->user()->name;
            
            $gajisupirdetail->save();
           
            DB::commit();
           
            return [
                'error' => false,
                'detail' => $gajisupirdetail,
                'id' => $gajisupirdetail->id,
                'tabel' => $gajisupirdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }        
    }



}
