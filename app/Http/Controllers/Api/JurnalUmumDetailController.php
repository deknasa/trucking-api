<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;

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
                $id = $request->jurnalumum_id;
                $data = JurnalUmumHeader::find($id);
                $nobukti = $data['nobukti'];
                
                $jurnalUmumDetail = DB::table('jurnalumumdetail AS A')
                    ->select(['A.coa as coadebet','b.coa as coakredit','A.nominal','A.keterangan','A.nobukti','A.tglbukti'])            
                    ->join(DB::raw("(SELECT baris,coa FROM jurnalumumdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                        function($join)
                        {
                        $join->on('A.baris', '=', 'B.baris');
                        })
                    ->where([
                        ['A.nobukti','=',$nobukti],
                        ['A.nominal', '>=' ,'0']
                    ])
                    ->get();        
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
            $jurnalumumDetail->baris = $request->baris;
            
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
            throw $th;
            DB::rollBack();
        }        
    }


}
