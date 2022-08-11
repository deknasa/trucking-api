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
                    'mekanik.namamekanik as mekanik_id',
                    'detail.keterangan',

                )
                     ->join('mekanik', 'mekanik.id', '=', 'detail.mekanik_id');

                $serviceInDetail = $query->get();
            } else {
                $query->select(
                    'mekanik.namamekanik as mekanik_id',
                    'detail.keterangan',

                )
                     ->join('mekanik', 'mekanik.id', '=', 'detail.mekanik_id');

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
        // $upahsupir = new UpahSupir();

        // return response([
        //     'data' => $upahsupir->get(),
        //     'attributes' => [
        //         'totalRows' => $upahsupir->totalRows,
        //         'totalPages' => $upahsupir->totalPages
        //     ]
        // ]);
    }

    public function store(StoreServiceInDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'mekanik_id' => 'required',
            'keterangan' => 'required',
        ], [
            'mekanik_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'mekanik_id' => 'mekanik',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }

        try {
            $serviceindetail = new ServiceInDetail();
            $serviceindetail->servicein_id = $request->servicein_id;
            $serviceindetail->nobukti = $request->nobukti;
            $serviceindetail->mekanik_id = $request->mekanik_id;
            $serviceindetail->keterangan = $request->keterangan;
            $serviceindetail->modifiedby = auth('api')->user()->name;
            
            $serviceindetail->save();
            
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $serviceindetail->id,
                    'tabel' => $serviceindetail->getTable(),
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }     
    }

    
}
