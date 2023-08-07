<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderSpk;

class ReminderSpkController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $reminderSpk = new ReminderSpk();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

}
