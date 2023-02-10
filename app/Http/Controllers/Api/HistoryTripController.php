<?php

namespace App\Http\Controllers\Api;

use App\Models\SuratPengantar;
use App\Models\UpahSupir;
use App\Models\Tarifrincian;
use App\Models\UpahSupirRincian;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMandorTripRequest;

class HistoryTripController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)//list history 
    {
        $suratPengantar = new SuratPengantar();
        return response([
            'data' => $suratPengantar->getHistory(),
            'attributes' => [
                'totalRows' => $suratPengantar->totalRows,
                'totalPages' => $suratPengantar->totalPages
            ]
        ]);
    }
}
