<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreTutupBukuRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\TutupBuku;

class TutupBukuController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $parameter = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();

        return response([
            'data' => $parameter,
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreTutupBukuRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tgltutupbuku' => $request->tgltutupbuku,
            ];
            $parameter = (new TutupBuku())->processStore($data);
            
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Proses Tutup Buku Berhasil',
                'data' => $parameter
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
