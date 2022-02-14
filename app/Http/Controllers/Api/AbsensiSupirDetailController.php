<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    ->join('absentrado', 'absentrado.id', '=', 'detail.absen_id', 'full outer');

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
}
