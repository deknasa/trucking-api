<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderanEmkl;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderanEmklController extends Controller
{
    public function index()
    {
    
        $orderanemkl = new OrderanEmkl();
        $orderanemkl->setConnection('sqlsrvemkl');
        return response([
            'data' => $orderanemkl->get(),
            'attributes' => [
                'totalRows' => $orderanemkl->totalRows,
                'totalPages' => $orderanemkl->totalPages
            ]
        ]);
    }

    public function getTglJob()
    {

        // dd('test');
        $orderanemkl = new OrderanEmkl();
        $orderanemkl->setConnection('sqlsrvemkl');

        $data=$orderanemkl->getJob(request()->job);
        return response([
            "tgl" => $data
        ]);

   
    }
}
