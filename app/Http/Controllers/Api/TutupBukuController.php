<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreTutupBukuRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;

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
            $tgltutupbuku = date('Y-m-d', strtotime($request->tgltutupbuku));
            $parameter = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->first();
            
            $parameter->text = $tgltutupbuku;
            $parameter->modifiedby = auth('api')->user()->name;

            if ($parameter->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($parameter->getTable()),
                    'postingdari' => 'TUTUP BUKU',
                    'idtrans' => $parameter->id,
                    'nobuktitrans' => $parameter->id,
                    'aksi' => 'EDIT',
                    'datajson' => $parameter->toArray(),
                    'modifiedby' => $parameter->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
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
