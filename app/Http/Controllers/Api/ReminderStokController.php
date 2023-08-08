<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderStok;

class ReminderStokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $reminderStok = new ReminderStok();
        return response([
            'data' => [],
            'attributes' => [
                'totalRows' => $reminderStok->totalRows,
                'totalPages' => $reminderStok->totalPages
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
