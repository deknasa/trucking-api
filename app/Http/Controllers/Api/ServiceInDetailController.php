<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInDetail;
use App\Http\Requests\StoreServiceInDetailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceInDetailController extends Controller
{

    public function index(Request $request)
    {
        $serviceInDetail = new ServiceInDetail();
        $idUser = auth('api')->user()->id;
        $getuser = User::select('name', 'cabang.namacabang as cabang_id')
            ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();

        return response([
            'data' => $serviceInDetail->get(),
            'user' => $getuser,
            'attributes' => [
                'totalRows' => $serviceInDetail->totalRows,
                'totalPages' => $serviceInDetail->totalPages
            ]
        ]);
           

          
    }

    public function store(StoreServiceInDetailRequest $request)
    {
        DB::beginTransaction();

        try {

            $serviceInDetail = new serviceInDetail();
            $serviceInDetail->servicein_id = $request->servicein_id;
            $serviceInDetail->nobukti = $request->nobukti;
            $serviceInDetail->mekanik_id =  $request->mekanik_id;
            $serviceInDetail->keterangan = $request->keterangan;
            $serviceInDetail->modifiedby = auth('api')->user()->name;

            $serviceInDetail->save();

            DB::commit();
            return [
                'error' => false,
                'detail' => $serviceInDetail,
                'id' => $serviceInDetail->id,
                'tabel' => $serviceInDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
