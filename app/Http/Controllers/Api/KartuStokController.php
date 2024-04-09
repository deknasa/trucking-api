<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetKartuStokRequest;
use App\Models\Gandengan;
use App\Models\Gudang;
use App\Models\KartuStok;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\Trado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetKartuStokRequest $request)
    {
        $kartuStok = new KartuStok();

        return response([
            'data' => $kartuStok->get(),
            'attributes' => [
                'totalRows' => $kartuStok->totalRows,
                'totalPages' => $kartuStok->totalPages
            ]
        ]);
    }


    public function default()
    {
        $kartuStok = new KartuStok();
        return response([
            'status' => true,
            'data' => $kartuStok->default(),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $kartuStok = new KartuStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stokdari = ($stokdari_id != null) ? $stokdari_id->namastok : '';
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $stoksampai = ($stoksampai_id != null) ? $stoksampai_id->namastok : '';
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            }
        }
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $user = Auth::user();
        $userCetak = $user->name;

        $report = [
            'stokdari' => $stokdari,
            'stoksampai' => $stoksampai,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text ?? "",
            'datafilter' => $datafilter ?? "",
            'judul' => $getJudul->text,
            'judulLaporan' => 'Laporan Kartu Stok',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        return response([
            'data' => $kartuStok->get(),
            'dataheader' => $report
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $kartuStok = new KartuStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
            }
        }
        
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $export = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text ?? "",
            'judul' => $getJudul->text,
            'datafilter' => $datafilter ?? "",
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        return response([
            'data' => $kartuStok->get(),
            'dataheader' => $export
        ]);
    }
}
