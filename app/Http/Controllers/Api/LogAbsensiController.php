<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LogAbsensiController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $logAbensi = new LogAbsensi();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $logAbensi->totalRows,
                'totalPages' => $logAbensi->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function export()
    {
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
