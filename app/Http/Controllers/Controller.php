<?php

namespace App\Http\Controllers;

use App\Helpers\App as AppHelper;
use App\Models\Parameter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Instance of app helper
     * 
     * @var \App\Helpers\App
     */
    public $appHelper;

    public function __construct()
    {
        $this->appHelper = new AppHelper();
    }

    public function getRunningNumber(Request $request): Response
    {
        $request->validate([
            'group' => 'required',
            'subgroup' => 'required',
            'table' => 'required',
        ]);
        
        $parameter = DB::table('parameter')
            ->where('grp', $request->group)
            ->where('subgrp', $request->subgroup)
            ->first();

        if (!isset($parameter->text)) {
            return response([
                'status' => false,
                'message' => 'Parameter tidak ditemukan'
            ]);
        }
            
        $text = $parameter->text;
        $lastRow = DB::table($request->table)->count();
        
        $runningNumber = $this->appHelper->runningNumber($text, $lastRow);

        return response([
            'status' => true,
            'data' => $runningNumber
        ]);
    }
}
