<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StatusGandenganTrado;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StatusGandenganTradoController extends Controller
{
    /**
     * @ClassName
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $statusGandengan = new StatusGandenganTrado();
        return response([
            'data' => $statusGandengan->get(),
            'attributes' => [
                'totalRows' => $statusGandengan->totalRows,
                'totalPages' => $statusGandengan->totalPages
            ]
        ]);
    }

}
