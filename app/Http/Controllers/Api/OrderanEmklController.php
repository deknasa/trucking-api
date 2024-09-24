<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderanEmkl;
use App\Models\Parameter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderanEmklController extends Controller
{
    public function index()
    {

        $orderanemkl = new OrderanEmkl();
        $getParameter = (new Parameter())->cekText('ORDERAN EMKL REPLICATION', 'ORDERAN EMKL REPLICATION');
        $koneksi = ($getParameter == 'YA') ? 'sqlsrv' : 'sqlsrvemkl';
        $orderanemkl->setConnection($koneksi);
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
        $getParameter = (new Parameter())->cekText('ORDERAN EMKL REPLICATION', 'ORDERAN EMKL REPLICATION');
        $koneksi = ($getParameter == 'YA') ? 'sqlsrv' : 'sqlsrvemkl';
        $orderanemkl->setConnection($koneksi);

        $data = $orderanemkl->getJob(request()->job);
        return response([
            "tgl" => $data
        ]);
    }
}
