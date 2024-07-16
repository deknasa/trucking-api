<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusOliTrado;

class StatusOliTradoController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     * @Detail SaldoOliTradoDetailController* 
     */
    public function index()
    {

        $tgldari = date('Y-m-d', strtotime(request()->dari)) ?? '1900-01-01';
        $tglsampai = date('Y-m-d', strtotime(request()->sampai)) ?? '1900-01-01';
        $trado_id = request()->trado_id ?? 0;

        if (request()->status == 'all') {
            $statusoli = 0;
        } else {
            $statusoli = request()->status ?? 0;
        }

        $statusOli = new StatusOliTrado();
        return response([
            'data' => $statusOli->get($tgldari, $tglsampai, $trado_id, $statusoli),
            'attributes' => [
                'totalRows' => $statusOli->totalRows,
                'totalPages' => $statusOli->totalPages
            ]
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
