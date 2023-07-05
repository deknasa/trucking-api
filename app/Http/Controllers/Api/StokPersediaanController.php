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

class StokPersediaanController extends Controller
{
    /**
     * @ClassName
     */
    public function index(GetStokPersediaanRequest $request)
    {
            $stokPersediaan = new StokPersediaan();
            
            return response([
                'data' => $stokPersediaan->get(),
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
     */
    public function export(Request $request)
    {
        $stokPersediaan = new StokPersediaan();

        $filter = Parameter::find($request->filter);
        if ($filter) {
            if($filter->text == 'GUDANG'){
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter =$getdatafilter->gudang;
            } else if($filter->text == 'TRADO'){
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter =$getdatafilter->keterangan;
            } else if($filter->text == 'GANDENGAN'){
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter =$getdatafilter->keterangan;
            } 
        }

        $user = Auth::user();
        $userCetak = $user->name;

        $report = [
            'filter' => $filter->text??"",
            'datafilter' => $datafilter??"",
            'judul' => 'PT TRANSPORINDO AGUNG SEJAHTERA',
            'judulLaporan' => 'Laporan Stok Persediaan',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
        ];

        return response([
            'data' => $stokPersediaan->get(),
            'dataheader' => $report
        ]);
    }


    /**
     * @ClassName
     */
    public function report(Request $request)
    {
        $stokPersediaan = new StokPersediaan();

        $filter = Parameter::find($request->filter);
        if ($filter) {
            if($filter->text == 'GUDANG'){
                $getdatafilter = Gudang::find($request->datafilter);
                $datafilter =$getdatafilter->gudang;
            } else if($filter->text == 'TRADO'){
                $getdatafilter = Trado::find($request->datafilter);
                $datafilter =$getdatafilter->keterangan;
            } else if($filter->text == 'GANDENGAN'){
                $getdatafilter = Gandengan::find($request->datafilter);
                $datafilter =$getdatafilter->keterangan;
            } 
        }

        $user = Auth::user();
        $userCetak = $user->name;

        $report = [
            'filter' => $filter->text??"",
            'datafilter' => $datafilter??"",
            'judul' => 'PT TRANSPORINDO AGUNG SEJAHTERA',
            'judulLaporan' => 'Laporan Stok Persediaan',
            'user' => $userCetak,
            'tglCetak' => date('d-m-Y H:i:s'),
        ];

        return response([
            'data' => $stokPersediaan->get(),
            'dataheader' => $report
        ]);
    }
}
