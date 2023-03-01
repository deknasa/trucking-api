<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gandengan;
use App\Models\Gudang;
use App\Models\KartuStok;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\Trado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        // dd('test');
            $kartuStok = new KartuStok();

            
            return response([
                'data' => $kartuStok->get(),
                'attributes' => [
                    'totalRows' => $kartuStok->totalRows,
                    'totalPages' => $kartuStok->totalPages
                ]
            ]);
    }
    
    /**
     * @ClassName
     */
    public function report(Request $request)
    {
        $kartuStok = new KartuStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = Parameter::find($request->filter);
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

        $report = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text,
            'datafilter' => $datafilter
        ];

        return response([
            'data' => $kartuStok->get(),
            'dataheader' => $report
        ]);
    }
    
    /**
     * @ClassName
     */
    public function export(Request $request)
    {
        $kartuStok = new KartuStok();

        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = Parameter::find($request->filter);
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

        $export = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->text,
            'datafilter' => $datafilter
        ];

        return response([
            'data' => $kartuStok->get(),
            'dataheader' => $export
        ]);
    }
}
