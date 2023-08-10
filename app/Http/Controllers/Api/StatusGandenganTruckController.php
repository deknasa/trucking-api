<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\StatusGandenganTruck;
use App\Http\Requests\StoreStatusGandenganTruckRequest;
use App\Http\Requests\UpdateStatusGandenganTruckRequest;

class StatusGandenganTruckController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $statusGandengan = new StatusGandenganTruck();
        return response([
            'data' => $statusGandengan->get(),
            'attributes' => [
                'totalRows' => $statusGandengan->totalRows,
                'totalPages' => $statusGandengan->totalPages
            ]
        ]);
    }
}
