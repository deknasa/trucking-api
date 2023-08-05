<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Dashboard;
use App\Http\Requests\StoreDashboardRequest;
use App\Http\Requests\UpdateDashboardRequest;

class DashboardController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $dashboard = new Dashboard();



        return response([
            'data' => $dashboard->getTrado(),
        ]);
    }

}
