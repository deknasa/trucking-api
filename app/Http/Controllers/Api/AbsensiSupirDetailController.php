<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AbsensiSupirDetail;
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
                    'header.tgl as tgl_header',
                    'header.keterangan as keterangan_header',
                    'header.kasgantung_nobukti as kasgantung_nobukti_header',
                    'header.nominal as nominal_header',
                    'trado.nama as trado',
                    'supir.namasupir as supir',
                    'absentrado.kodeabsen as status',
                    'detail.keterangan as keterangan_detail',
                    'detail.jam',
                    'detail.uangjalan',
                    'detail.absensi_id'
                )
                    ->join('absensisupirheader as header', 'header.id', 'detail.absensi_id')
                    ->join('trado', 'trado.id', '=', 'detail.trado_id', 'full outer')
                    ->join('supir', 'supir.id', '=', 'detail.supir_id', 'full outer')
                    ->join('absentrado', 'absentrado.id', '=', 'detail.absen_id', 'full outer')
                    ->orderBy('header.nobukti', 'asc');

                $absensiSupirDetail = $query->get();
            } else {
                $query->with('trado', 'supir', 'absenTrado');
                $absensiSupirDetail = $query->get();
            }

            return response([
                'data' => $absensiSupirDetail
            ]);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        // AbsensiSupirDetail::insert($request->only('trado_id', 'supir_id', 'absen_id', 'uangjalan', 'jam', 'keterangan_detail'));

        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'trado_id' => 'required',
        ], [
            'trado_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'trado_id' => 'Trado',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'messages' => $validator->messages()
            ];
        }

        try {
            $AbsensiSupirDetail = new AbsensiSupirDetail();

            $AbsensiSupirDetail->absensi_id = $request->absensi_id;
            $AbsensiSupirDetail->trado_id = $request->trado_id;
            $AbsensiSupirDetail->absen_id = $request->absen_id;
            $AbsensiSupirDetail->supir_id = $request->supir_id;
            $AbsensiSupirDetail->jam = $request->jam;
            $AbsensiSupirDetail->uangjalan = $request->uangjalan;
            $AbsensiSupirDetail->keterangan = $request->keterangan;
            $AbsensiSupirDetail->modifiedby = $request->modifiedby;

            $AbsensiSupirDetail->save();
            $datajson = [
                'id' => $AbsensiSupirDetail->id,
                'absensi_id' => $request->absensi_id,
                'trado_id' => $request->trado_id,
                'absen_id' => $request->absen_id,
                'supir_id' => $request->supir_id,
                'jam' => $request->jam,
                'uangjalan' => $request->uangjalan,
                'keterangan' => $request->keterangan,
                'modifiedby' => strtoupper($request->modifiedby),
                'created_at' => date('d-m-Y H:i:s',strtotime($AbsensiSupirDetail->created_at)),
                'updated_at' => date('d-m-Y H:i:s',strtotime($AbsensiSupirDetail->updated_at)),
            ];

            $datalogtrail = [
                'namatabel' => 'ABSENSISUPIRDETAIL',
                'postingdari' => 'ENTRY ABSENSI SUPIR DETAIL',
                'idtrans' => $AbsensiSupirDetail->absensi_id,
                'nobuktitrans' => $AbsensiSupirDetail->id,
                'aksi' => 'ENTRY',
                'datajson' => $datajson,
                'modifiedby' => $AbsensiSupirDetail->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);            
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                ];
            }
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
