<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Dashboard;


class DashboardController extends Controller
{
    
    public function index()
    {
        $dashboard = new Dashboard();



        return response([
            'data' => $dashboard->getTrado(),
        ]);
    }

}
