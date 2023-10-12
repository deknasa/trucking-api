<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\OpnameDetail;
use App\Http\Requests\StoreOpnameDetailRequest;
use App\Http\Requests\UpdateOpnameDetailRequest;

class OpnameDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $opnameDetail = new OpnameDetail();

        return response()->json([
            'data' => $opnameDetail->get(),
            'attributes' => [
                'totalRows' => $opnameDetail->totalRows,
                'totalPages' => $opnameDetail->totalPages,
            ]
        ]);
    }

}
