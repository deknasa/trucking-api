<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StokPersediaan;
use App\Http\Requests\StoreStokPersediaanRequest;
use App\Http\Requests\UpdateStokPersediaanRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;

class StokPersediaanController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
    {
        if($request->keterangan){
            $stokPersediaan = new StokPersediaan();
            
            return response([
                'data' => $stokPersediaan->get(),
                'attributes' => [
                    'totalRows' => $stokPersediaan->totalRows,
                    'totalPages' => $stokPersediaan->totalPages
                ]
            ]);
        } else {
            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }

    }

}
