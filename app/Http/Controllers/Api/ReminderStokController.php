<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderStok;

class ReminderStokController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $reminderStok = new ReminderStok();
        return response([
            'data' => $reminderStok->get(),
            'attributes' => [
                'totalRows' => $reminderStok->totalRows,
                'totalPages' => $reminderStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
    }
}
