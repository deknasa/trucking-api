<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderOli;

class ReminderOliController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
       
        $reminderOli = new ReminderOli();
        // dd(system('getmac'));
        return response([
            'data' => $reminderOli->get(),
            'attributes' => [
                'totalRows' => $reminderOli->totalRows,
                'totalPages' => $reminderOli->totalPages
            ]
        ]);

    }

    /**
     * @ClassName 
     */
    public function export()
    {
    }

}