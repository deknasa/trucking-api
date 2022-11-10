<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;

class HariLiburController extends Controller
{
    public function index()
    {
        $hariLibur = new HariLibur();

        return response([
            'data' => $hariLibur->get(),
            'attributes' => [
                'totalRows' => $hariLibur->totalRows,
                'totalPages' => $hariLibur->totalPages
            ]
        ]);
    }
}
