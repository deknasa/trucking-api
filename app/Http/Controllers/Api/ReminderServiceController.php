<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReminderService;


class ReminderServiceController extends Controller
{
     /**
     * @ClassName 
     */
    public function index()
    {
        $limit=8;
        $limit = ($limit) ? ($limit !== 0 ? $limit : 10) : 10;
        dd($limit);
        $reminder = new ReminderService();
        return response([
            'data' => $reminder->get(),
            'attributes' => [
                'totalRows' => $reminder->totalRows,
                'totalPages' => $reminder->totalPages
            ]
        ]);
    }
}
