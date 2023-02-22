<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiSupirDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AbsensiSupirDetail;
use App\Models\AbsensiSupirHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class AbsensiSupirDetailController extends Controller
{
    public function index(Request $request)
    {

        $absensiSupirDetail =  new AbsensiSupirDetail ();
        return response()->json([
            'data' => $absensiSupirDetail->get(),
            'attributes' => [
                'totalRows' => $absensiSupirDetail->totalRows,
                'totalPages' => $absensiSupirDetail->totalPages,
                'totalNominal' => $absensiSupirDetail->totalNominal
            ]
        ]);
    }

    public function store(StoreAbsensiSupirDetailRequest $request)
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
            return [
                'error' => false,
                'detail' => $AbsensiSupirDetail,
                'id' => $AbsensiSupirDetail->id,
                'tabel' => $AbsensiSupirDetail->getTable(),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    public function getDetailAbsensi()
    {
        $tglbukti= date('Y-m-d', strtotime('now'));
        $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti',$tglbukti)->first();
        if (!$absensiSupirHeader) {
            return response([
                'data' => [],
                'total' => 0,
                "records" => 0,
            ]);
        }
        $request = new Request(['absensi_id' => $absensiSupirHeader->id]);
        
        return $this->index($request);
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
