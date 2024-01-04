<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexHistoriPengeluaranRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\HistoriPengeluaranStok;
use App\Models\Parameter;
use App\Models\Stok;
use App\Models\PengeluaranStok;

class HistoriPengeluaranStokController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexHistoriPengeluaranRequest $request)
    {
        $HistoriPengeluaranStok = new HistoriPengeluaranStok();


        return response([
            'data' => $HistoriPengeluaranStok->get(),
            'attributes' => [
                'totalRows' => $HistoriPengeluaranStok->totalRows,
                'totalPages' => $HistoriPengeluaranStok->totalPages
            ]
        ]);
    }

    public function default()
    {
        $histori = new HistoriPengeluaranStok();
        return response([
            'status' => true,
            'data' => $histori->default(),
        ]);
    }

    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report(Request $request)
    {
        $HistoriPengeluaranStok = new HistoriPengeluaranStok();


        $stokdari_id = Stok::find($request->stokdari_id);
        $stoksampai_id = Stok::find($request->stoksampai_id);
        $filter = PengeluaranStok::findOrFail($request->filter);


        $report = [
            'stokdari' => $stokdari_id->namastok,
            'stoksampai' => $stoksampai_id->namastok,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
            'filter' => $filter->keterangan,
        ];

        return response([
            'data' => $HistoriPengeluaranStok->get(),
            'dataheader' => $report
        ]);
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
    }
}
