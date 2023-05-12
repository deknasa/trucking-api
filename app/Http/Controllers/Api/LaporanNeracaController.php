<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanNeraca;

class LaporanNeracaController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => 0,
                'totalPages' => 0
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function report(Request $request)
    {
        $sampai = $request->sampai;

        $report = [
            [
                'judul' => 'PT. Transporindo Agung Sejahtera',
                'subjudul' => 'Laporan Neraca Divisi Trucking',
                'aktivalancar' => '30.292.637.247,41',

            ], 
        ];
        return response([
            'data' => $report
        ]);
    }
}
