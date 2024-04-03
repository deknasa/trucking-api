<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MandorAbsensiSupir;
use App\Http\Controllers\Controller;

class MandorAbsensiSupirHistoryController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $mandorabsensisupir = new MandorAbsensiSupir();
        return response([
            'data' => $mandorabsensisupir->get(),
            'attributes' => [
                'total' => $mandorabsensisupir->totalPages,
                'records' => $mandorabsensisupir->totalRows,
                'tradosupir' => $mandorabsensisupir->isTradoMilikSupir(),
            ]
        ]);
    }
}
