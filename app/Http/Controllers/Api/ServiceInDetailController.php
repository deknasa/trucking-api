<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInDetail;
use App\Http\Requests\StoreServiceInDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceInDetailController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'servicein_id' => $request->servicein_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = ServiceInDetail::from('serviceindetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['servicein_id'])) {
                $query->where('detail.servicein_id', $params['servicein_id']);
            }

            if ($params['withHeader']) {
                $query->join('servicein', 'servicein.id', 'detail.servicein_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('servicein_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'detail.mekanik_id',
                    'detail.keterangan',

                );
                $serviceInDetail = $query->get();
            } else {
                $query->select(
                    'mekanik.namamekanik as mekanik_id',
                    'detail.keterangan',

                )
                ->leftJoin('mekanik', 'detail.mekanik_id', 'mekanik.id')
                ;

                $serviceInDetail = $query->get();
            }

            return response([
                'data' => $serviceInDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(StoreServiceInDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'keterangan' => 'required',
        ], [
            'keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'keterangan' => 'keterangan',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        try {
            $serviceInDetail = new serviceInDetail();

            $serviceInDetail->servicein_id = $request->servicein_id;
            $serviceInDetail->nobukti = $request->nobukti;
            $serviceInDetail->mekanik_id =  $request->mekanik_id;
            $serviceInDetail->keterangan = $request->keterangan;
            $serviceInDetail->modifiedby = auth('api')->user()->name;
            
            $serviceInDetail->save();

            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $serviceInDetail->id,
                    'tabel' => $serviceInDetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}