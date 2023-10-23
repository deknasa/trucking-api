<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpStnk;

class ExpStnkController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $expStnk = new ExpStnk();
        return response([
            // 'data' => $expStnk->get(),
            'data' => $expStnk->reminderemailstnk(),
            'attributes' => [
                'totalRows' => $expStnk->totalRows,
                'totalPages' => $expStnk->totalPages
            ]
        ]);
    }

}