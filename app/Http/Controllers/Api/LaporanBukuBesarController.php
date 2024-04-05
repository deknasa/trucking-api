<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiLaporanBukuBesarRequest;
use App\Models\AkunPusat;
use App\Models\Cabang;
use App\Models\LaporanBukuBesar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanBukuBesarController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(ValidasiLaporanBukuBesarRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
          
            $laporanbukubesar = new LaporanBukuBesar();

            
            $coadari_id = AkunPusat::find($request->coadari_id);
            $coasampai_id = AkunPusat::find($request->coasampai_id);
            // $cabang_id = auth('api')->user()->cabang_id;
            $cabang = Cabang::find($request->cabang_id);
            $dataHeader = [
                'coadari' => $coadari_id->coa,
                'coasampai' => $coasampai_id->coa,
                'ketcoadari' => $coadari_id->keterangancoa,
                'ketcoasampai' => $coasampai_id->keterangancoa,
                'dari' => $request->dari,
                'sampai' => $request->sampai,
                'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
            ];
         
            return response([
                'data' => $laporanbukubesar->getReport(),
                'dataheader' => $dataHeader
            ]);
        }
    }
    
    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(ValidasiLaporanBukuBesarRequest $request)
    {
        if ($request->isCheck) {
            return response([
                'data' => 'ok'
            ]);
        } else {
          
            $laporanbukubesar = new LaporanBukuBesar();

            $coadari_id = ($request->coadari_id != '') ? AkunPusat::find($request->coadari_id) : '';
            $coasampai_id = ($request->coasampai_id != '') ? AkunPusat::find($request->coasampai_id) : '';
            // $cabang_id = auth('api')->user()->cabang_id;
            $cabang = Cabang::find($request->cabang_id);
            $dataHeader = [
                'coadari' => ($coadari_id != '') ? $coadari_id->coa : '',
                'coasampai' => ($coasampai_id != '') ? $coasampai_id->coa : '',
                'ketcoadari' => ($coadari_id != '') ? $coadari_id->keterangancoa : '',
                'ketcoasampai' => ($coasampai_id != '') ? $coasampai_id->keterangancoa : '',
                'dari' => $request->dari,
                'sampai' => $request->sampai,
                'cabang' => ($cabang == '') ? '' : 'CABANG ' . $cabang->namacabang
            ];
         
            return response([
                'data' => $laporanbukubesar->getReport(),
                'dataheader' => $dataHeader
            ]);
        }
    }
}
