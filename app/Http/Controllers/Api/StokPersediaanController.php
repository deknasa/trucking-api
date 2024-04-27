<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetStokPersediaanRequest;
use App\Models\StokPersediaan;
use App\Http\Requests\StoreStokPersediaanRequest;
use App\Http\Requests\UpdateStokPersediaanRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\Gandengan;
use App\Models\Gudang;
use App\Models\Trado;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StokPersediaanController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetStokPersediaanRequest $request)
    {
        $stokPersediaan = new StokPersediaan();

        // dd($request->all());
        $filter = $request->filter ?? 0;
        $gudang = $request->gudang ?? '';
        $gudang_id = $request->gudang_id ?? 0;
        $trado = $request->trado ?? '';
        $trado_id = $request->trado_id ?? 0;
        $gandengan = $request->gandengan ?? '';
        $gandengan_id = $request->gandengan_id ?? 0;
        $keterangan = $request->keterangan ?? -1;
        $data = $request->data ?? 0;


        return response([
            'data' => $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data),
            'attributes' => [
                'totalRows' => $stokPersediaan->totalRows,
                'totalPages' => $stokPersediaan->totalPages
            ]
        ]);
    }

    public function default()
    {
        $persediaan = new StokPersediaan();
        return response([
            'status' => true,
            'data' => $persediaan->default(),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(Request $request)
    {
        $stokPersediaan = new StokPersediaan();

        $gudang = "";
        $trado = "";
        $gandengan = "";
        $data = $request->data ?? 0;
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
                $gudang = $datafilter;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $trado = $datafilter;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $gandengan = $datafilter;
            }
        }

        $user = Auth::user();
        $userCetak = $user->name;
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $report = [
            'filter' => $filter->text ?? "",
            'datafilter' => $datafilter ?? "",
            'judul' => $getJudul->text,
            'judulLaporan' => 'Laporan Stok Persediaan',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        $filter = $request->filter ?? 0;
        $gudang = $request->gudang ?? $gudang;
        $gudang_id = $request->gudang_id ?? $getdatafilter->id;
        $trado = $request->trado ?? $trado;
        $trado_id = $request->trado_id ?? $getdatafilter->id;
        $gandengan = $request->gandengan ?? $gandengan;
        $gandengan_id = $request->gandengan_id ?? $getdatafilter->id;
        $keterangan = $request->keterangan ?? -1;
        $data = $request->data ?? $request->datafilter;
        
        return response([
            'data' => $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data, $request->forReport,true),
            'dataheader' => $report
        ]);
    }


    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $stokPersediaan = new StokPersediaan();

        $gudang = "";
        $trado = "";
        $gandengan = "";
        $filter = Parameter::find($request->filter);
        if ($filter) {
            if ($filter->text == 'GUDANG') {
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter = $getdatafilter->gudang;
                $gudang = $datafilter;
            } else if ($filter->text == 'TRADO') {
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $trado = $datafilter;
            } else if ($filter->text == 'GANDENGAN') {
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter = $getdatafilter->keterangan;
                $gandengan = $datafilter;
            }
        }
        $filterText = $filter->text ?? '';

        $user = Auth::user();
        $userCetak = $user->name;
        // dd($request->all());
        $filter = $request->filter ?? 0;
        $gudang = $request->gudang ?? $gudang;
        $gudang_id = $request->gudang_id ?? $getdatafilter->id;
        $trado = $request->trado ?? $trado;
        $trado_id = $request->trado_id ?? $getdatafilter->id;
        $gandengan = $request->gandengan ?? $gandengan;
        $gandengan_id = $request->gandengan_id ?? $getdatafilter->id;
        $keterangan = $request->keterangan ?? -1;
        $data = $request->data ?? $request->datafilter;
        $getJudul = DB::table('parameter')
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $getCabang = DB::table('cabang')->from(DB::raw("cabang with (readuncommitted)"))
            ->select('cabang.namacabang')
            ->join("parameter", 'parameter.text', 'cabang.id')
            ->where('parameter.grp', 'ID CABANG')
            ->first();
        $report = [
            'filter' => $filterText,
            'datafilter' => $datafilter ?? "",
            'judul' => $getJudul->text,
            'judulLaporan' => 'Laporan Stok Persediaan',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
            'namacabang' => 'CABANG ' . $getCabang->namacabang
        ];

        return response([
            'data' => $stokPersediaan->get($filter, $gudang, $gudang_id, $trado, $trado_id, $gandengan, $gandengan_id, $keterangan, $data,true),
            'dataheader' => $report
        ]);
    }
}
