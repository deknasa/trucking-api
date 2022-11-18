<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class AbsensiSupirDetailController extends Controller
{
    public function index(Request $request)
    {
        $params = [
            'id' => $request->id,
            'absensi_id' => $request->absensi_id,
            'withHeader' => $request->withHeader ?? false,
            'whereIn' => $request->whereIn ?? [],
            'forReport' => $request->forReport ?? false,
            'sortIndex' => $request->sortOrder ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        try {
            $query = AbsensiSupirDetail::from('absensisupirdetail as detail');

            if (isset($params['id'])) {
                $query->where('detail.id', $params['id']);
            }

            if (isset($params['absensi_id'])) {
                $query->where('detail.absensi_id', $params['absensi_id']);
            }

            if ($params['withHeader']) {
                $query->join('absensisupirheader', 'absensisupirheader.id', 'detail.absensi_id');
            }

            if (count($params['whereIn']) > 0) {
                $query->whereIn('absensi_id', $params['whereIn']);
            }

            if ($params['forReport']) {
                $query->select(
                    'header.id as id_header',
                    'header.nobukti as nobukti_header',
                    'header.tglbukti as tgl_header',
                    'header.keterangan as keterangan_header',
                    'header.kasgantung_nobukti as kasgantung_nobukti_header',
                    'header.nominal as nominal_header',
                    'trado.keterangan as trado',
                    'supir.namasupir as supir',
                    'absentrado.kodeabsen as status',
                    'detail.keterangan as keterangan_detail',
                    'detail.jam',
                    'detail.uangjalan',
                    'detail.absensi_id'
                )
                    ->join('absensisupirheader as header', 'header.id', 'detail.absensi_id')
                    ->join('trado', 'trado.id','detail.trado_id')
                    ->join('supir', 'supir.id','detail.supir_id')
                    ->leftjoin('absentrado', 'absentrado.id','detail.absen_id');

                $absensiSupirDetail = $query->get();
            } else {
                $query->select(
                    'trado.keterangan as trado',
                    'supir.namasupir as supir',
                    'absentrado.kodeabsen as status',
                    'detail.keterangan as keterangan_detail',
                    'detail.jam',
                    'detail.uangjalan',
                    'detail.absensi_id'
                )
                    ->join('trado', 'trado.id', '=', 'detail.trado_id')
                    ->join('supir', 'supir.id', '=', 'detail.supir_id')
                    ->leftjoin('absentrado', 'absentrado.id', '=', 'detail.absen_id');
                $absensiSupirDetail = $query->get();
            }
            $idUser = auth('api')->user()->id;
            $getuser = User::select('name','cabang.namacabang as cabang_id')
            ->where('user.id',$idUser)->join('cabang','user.cabang_id','cabang.id')->first();
           

            return response([
                'data' => $absensiSupirDetail,
                'user' => $getuser,
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $AbsensiSupirDetail = new AbsensiSupirDetail();

            $AbsensiSupirDetail->absensi_id = $request->absensi_id ?? '';
            $AbsensiSupirDetail->nobukti = $request->nobukti ?? '';
            $AbsensiSupirDetail->trado_id = $request->trado_id ?? '';
            $AbsensiSupirDetail->absen_id = $request->absen_id ?? '';
            $AbsensiSupirDetail->supir_id = $request->supir_id ?? '';
            $AbsensiSupirDetail->jam = $request->jam ?? '';
            $AbsensiSupirDetail->uangjalan = $request->uangjalan ?? '';
            $AbsensiSupirDetail->keterangan = $request->keterangan ?? '';
            $AbsensiSupirDetail->modifiedby = $request->modifiedby ?? '';

            $AbsensiSupirDetail->save();


            DB::commit();
            return response([
                'error' => false,
                'id' => $AbsensiSupirDetail->id,
                'tabel' => $AbsensiSupirDetail->getTable(),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function update(Request $request, AbsensiSupirDetail $absensiSupirDetail)
    {
        // 
    }

    public function destroy(AbsensiSupirDetail $absensiSupirDetail)
    {
        // 
    }
}
