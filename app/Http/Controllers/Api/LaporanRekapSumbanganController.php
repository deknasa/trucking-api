<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanRekapSumbangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanRekapSumbanganController extends Controller
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
        $dari = $request->dari;

        // $report = LaporanRekapSumbangan::getReport($sampai, $dari);
        $report = [
            [
                'nobukti' => 'INV 0001/II/2023',
                'container' => '2x20"',
                'nominal' => '5125121',
                'nobst' => 'BST 0001/II/2023'
            ],
            [
                'nobukti' => 'INV 0002/II/2023',
                'container' => '20"',
                'nominal' => '912478',
                'nobst' => 'BST 0002/II/2023'
            ]
        ];
        return response([
            'data' => $report
        ]);
    }
}
