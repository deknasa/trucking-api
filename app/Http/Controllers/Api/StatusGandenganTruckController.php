<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StatusGandenganTruck;
use App\Http\Requests\StoreStatusGandenganTruckRequest;
use App\Http\Requests\UpdateStatusGandenganTruckRequest;
use Illuminate\Http\Request;

class StatusGandenganTruckController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $periode = date('Y-m-d', strtotime($request->periode)) ;

        $statusGandengan = new StatusGandenganTruck();
        return response([
            'data' => $statusGandengan->get($periode),
            'attributes' => [
                'totalRows' => $statusGandengan->totalRows,
                'totalPages' => $statusGandengan->totalPages
            ]
        ]);
    }
}
