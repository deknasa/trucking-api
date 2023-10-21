<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderSpk;

class ReminderSpkController extends Controller
{
    /**
     * @ClassName 
     * ReminderSpkController
     * @Detail1 ReminderSpkDetailController
     */
    public function index()
    {
        $reminderSpk = new ReminderSpk();
        return response([
            'data' => $reminderSpk->get(),
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

}
