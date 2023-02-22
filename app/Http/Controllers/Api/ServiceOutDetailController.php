<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOutDetail;
use App\Http\Requests\StoreServiceOutDetailRequest;
use App\Models\User;
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
        $serviceOutDetail = new ServiceOutDetail();
        $idUser = auth('api')->user()->id;
        $getuser = User::select('name', 'cabang.namacabang as cabang_id')
            ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();

            return response([
                'data' => $serviceOutDetail->get(),
                'user' => $getuser,
                'attributes' => [
                    'totalRows' => $serviceOutDetail->totalRows,
                    'totalPages' => $serviceOutDetail->totalPages,
                ]
            ]);
        
    }

    /**
     * @ClassName
     */
    public function store(StoreServiceOutDetailRequest $request)
    {
        DB::beginTransaction();

        try {
            $serviceoutdetail = new ServiceOutDetail();
            $serviceoutdetail->serviceout_id = $request->serviceout_id;
            $serviceoutdetail->nobukti = $request->nobukti;
            $serviceoutdetail->servicein_nobukti = $request->servicein_nobukti;
            $serviceoutdetail->keterangan = $request->keterangan;
            $serviceoutdetail->modifiedby = auth('api')->user()->name;
            $serviceoutdetail->save();

            DB::commit();

            return [
                'error' => false,
                'detail' => $serviceoutdetail,
                'id' => $serviceoutdetail->id,
                'tabel' => $serviceoutdetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }
}
