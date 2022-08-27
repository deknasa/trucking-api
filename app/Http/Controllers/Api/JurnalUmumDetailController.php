<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmumDetail;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JurnalUmumDetailController extends Controller
{
    
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'jurnalumum_id' => $request->jurnalumum_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = JurnalUmumDetail::from('jurnalumumdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['jurnalumum_id'])) {
                $query->where('detail.jurnalumum_id', $params['jurnalumum_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('jurnalumum_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'detail.nobukti',
                    'detail.tglbukti',
                    'detail.coa',
                    'detail.nominal',
                    'detail.keterangan',
                );

                $jurnalUmumDetail = $query->get();
            } else {

                $query->select(
                    'detail.nobukti',
                    'detail.tglbukti',
                    'detail.coa',
                    'detail.nominal',
                    'detail.keterangan',
                );
                $jurnalUmumDetail = $query->get();
                
                // $jurnalUmumDetail = $query->get(['nobukti','tglbukti','coa','nominal','keterangan']);
            }

            return response([
                'data' => $jurnalUmumDetail
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
    public function store(StoreJurnalUmumDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
           'coa' => 'required',
            'keterangan' => 'required',
        ], [
            'coa.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'coa' => 'jurnalumumdetail',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $jurnalumumDetail = new JurnalUmumDetail();
            
            $jurnalumumDetail->jurnalumum_id = $request->jurnalumum_id;
            $jurnalumumDetail->nobukti = $request->nobukti;
            $jurnalumumDetail->tglbukti = $request->tglbukti;
            $jurnalumumDetail->coa = $request->coa;
            $jurnalumumDetail->nominal = $request->nominal;
            $jurnalumumDetail->keterangan = $request->keterangan ?? '';
            $jurnalumumDetail->modifiedby = auth('api')->user()->name;
            
            $jurnalumumDetail->save();
            
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $jurnalumumDetail->id,
                    'tabel' => $jurnalumumDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }        
    }


}
