<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetUpahSupirRincianRequest;
use App\Models\UpahSupirRincian;
use App\Http\Requests\StoreUpahSupirRincianRequest;
use App\Http\Requests\UpdateUpahSupirRincianRequest;
use App\Models\UpahSupirTangkiRincian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpahSupirTangkiRincianController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
       
        $upahSupirRincian = new UpahSupirTangkiRincian();

        return response()->json([
            'data' => $upahSupirRincian->get(),
            'attributes' => [
                'totalRows' => $upahSupirRincian->totalRows,
                'totalPages' => $upahSupirRincian->totalPages,
                'totalNominal' => $upahSupirRincian->totalNominal
            ]
        ]);
    }
    public function get(GetUpahSupirRincianRequest $request)
    {
        $upahSupirRincian = new UpahSupirRincian();

        return response([
            'data' => $upahSupirRincian->getLookup(),
            'attributes' => [
                'totalRows' => $upahSupirRincian->totalRows,
                'totalPages' => $upahSupirRincian->totalPages
            ]
        ]);
    }


    public function setUpRow()
    {
        $upahSupirRincian = new UpahSupirTangkiRincian();

        return response([
            'status' => true,
            'detail' => $upahSupirRincian->setUpRow()
        ]);
    }
    public function setUpRowExcept($id)
    {
        $upahSupirRincian = new UpahSupirRincian();
        $rincian = $upahSupirRincian->where('upahsupir_id', $id)->get();
        foreach ($rincian as $e) {
            $data[] = [
                "container_id" => $e->container_id,
                "statuscontainer_id" => $e->statuscontainer_id
            ];
        }
        // return $data;
        return response([
            'status' => true,
            'detail' => $upahSupirRincian->setUpRowExcept($data)
        ]);
    }
}
