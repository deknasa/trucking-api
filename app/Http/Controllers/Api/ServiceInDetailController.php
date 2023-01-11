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
        $params = [
            'id' => $request->id,
            'servicein_id' => $request->servicein_id,
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
            $query = ServiceInDetail::from(DB::raw("serviceindetail as detail with (readuncommitted)"));

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
                    'header.id as id_header',
                    'header.nobukti as nobukti_header',
                    'header.tglbukti as tgl_header',
                    'header.keterangan as keterangan_header',
                    'header.tglmasuk as tglmasuk',
                    'trado.keterangan as trado_id',
                    'mekanik.namamekanik as mekanik_id',
                    'detail.keterangan',
                    'detail.nobukti'
                )
                    ->leftJoin('serviceinheader as header', 'header.id', 'detail.servicein_id')
                    ->leftJoin('trado', 'header.trado_id', 'trado.id')
                    ->leftJoin('mekanik', 'detail.mekanik_id', 'mekanik.id');

                $serviceInDetail = $query->get();
            } else {
                $query->select(
                    'mekanik.namamekanik as mekanik_id',
                    'detail.keterangan',
                    'detail.nobukti'

                )
                    ->leftJoin('mekanik', 'detail.mekanik_id', 'mekanik.id');
                    $totalRows =  $query->count();
                    $query->skip($params['offset'])->take($params['limit']);
                $serviceInDetail = $query->get();
            }
            $idUser = auth('api')->user()->id;
            $getuser = User::select('name', 'cabang.namacabang as cabang_id')
                ->where('user.id', $idUser)->join('cabang', 'user.cabang_id', 'cabang.id')->first();


            return response([
                'data' => $serviceInDetail,
                'user' => $getuser,
                'attributes' => [
                    'totalRows' => $totalRows ?? 0,
                    'totalPages' => $params['limit'] > 0 ? ceil( $totalRows / $params['limit']) : 1
                ]
            ]);

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
