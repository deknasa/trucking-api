<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpkHarianDetail;

class SpkHarianDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $reminderSpk = new SpkHarianDetail();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }
}
