<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderSpkDetail;

class ReminderSpkDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $reminderSpk = new ReminderSpkDetail();
        return response([
            'data' => $reminderSpk->get(),
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

}
