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
                    'header.id as id_header',
                    'header.nobukti as nobukti_header',
                    'header.tglbukti as tgl_header',
                    'header.keterangan as keterangan_header',
                    'header.tglkeluar as tglkeluar',
                    'trado.keterangan as trado_id',
                    'detail.servicein_nobukti',
                    'detail.keterangan',
                )
                    ->leftJoin('serviceoutheader as header', 'header.id', 'detail.serviceout_id')
                    ->leftJoin('trado', 'header.trado_id', 'trado.id');

                $serviceOutDetail = $query->get();
            } else {
                $query->select(
                    'detail.servicein_nobukti',
                    'detail.keterangan',
                );
                $serviceOutDetail = $query->get();
            }

            $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();

            return response([
                'data' => $serviceOutDetail,
                'user' => $getuser,
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
