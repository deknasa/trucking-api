<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOutDetail;
use App\Http\Requests\StoreServiceOutDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceOutDetailController extends Controller
{
     /**
     * @ClassName
     */
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'serviceout_id' => $request->serviceout_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];
        try {
            $query = ServiceOutDetail::from('serviceoutdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['serviceout_id'])) {
                $query->where('detail.serviceout_id', $params['serviceout_id']);
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('serviceout_id', $params['whereIn']);
            }
            if ($params['forReport']) {
                $query->select(
                    'detail.servicein_nobukti',
                    'detail.keterangan',
                );

                $serviceOutDetail = $query->get();
            } else {
                $query->select(
                    'detail.servicein_nobukti',
                    'detail.keterangan',
                );
                $serviceOutDetail = $query->get();
            }

            return response([
                'data' => $serviceOutDetail
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
    public function store(StoreServiceOutDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
           //'mekanik_id' => 'required',
            'keterangan' => 'required',
            'serviceout_id' => 'required'
        ], [
            'serviceout_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'serviceout_id' => 'serviceoutdetail',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $serviceoutdetail = new ServiceOutDetail();
            $serviceoutdetail->serviceout_id = $request->serviceout_id;
            $serviceoutdetail->nobukti = $request->nobukti;
            $serviceoutdetail->servicein_nobukti = $request->servicein_nobukti;
            $serviceoutdetail->keterangan = $request->keterangan;
            $serviceoutdetail->modifiedby = auth('api')->user()->name;
            
            $serviceoutdetail->save();
            
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $serviceoutdetail->id,
                    'tabel' => $serviceoutdetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }     
    }

    
}
