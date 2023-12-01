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
        $getdetail=0 ;
        $stok_id = request()->stok_id ?? 0;
        $trado_id = request()->trado_id ?? 0;
        $gandengan_id = request()->gandengan_id ?? 0;
        $gudang = request()->gudang ?? '';
        $stok = request()->stok ?? '';

        return response([
            'data' => $reminderSpk->get($getdetail,$stok_id,$trado_id,$gandengan_id,$gudang,$stok),
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

     /**
     * @ClassName 
     */
    public function export()
    {
        $reminderSpk = new ReminderSpkDetail();
        return response([
            'data' => $reminderSpk->getdetail(),
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

}
