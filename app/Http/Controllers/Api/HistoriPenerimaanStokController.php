<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexHistoriPenerimaanRequest;
use Illuminate\Http\Request;



use App\Models\HistoriPenerimaanStok;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\Penerimaanstok;
use Illuminate\Support\Facades\DB;


class HistoriPenerimaanStokController extends Controller
{
    /**
     * @ClassName
     */
    public function index(GetIndexHistoriPenerimaanRequest $request)
    {
            $HistoriPenerimaanStok = new HistoriPenerimaanStok();
            return response([
                'data' => $HistoriPenerimaanStok->get(),
                'attributes' => [
                    'totalRows' => $HistoriPenerimaanStok->totalRows,
                    'totalPages' => $HistoriPenerimaanStok->totalPages
                ]
            ]);

    }

    public function default()
    {
        $histori = new HistoriPenerimaanStok();
        return response([
            'status' => true,
            'data' => $histori->default(),
        ]);
    }
    
    /**
     * @ClassName
     */
    public function report(Request $request)
    {
        $HistoriPenerimaanStok = new HistoriPenerimaanStok();


        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = PenerimaanStok::findOrFail($request->filter);
       

        $report = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->keterangan,
        ];

        return response([
            'data' => $HistoriPenerimaanStok->get(),
            'dataheader' => $report
        ]);
    }
    
    /**
     * @ClassName
     */
    public function export(){

    }
}
