<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLogTrailRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Parameter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TutupBukuController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
    }

    /**
     * @ClassName
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $tglterakhir = date('Y-m-d', strtotime($request->tglterakhir));
            $tgltutupbuku = date('Y-m-d', strtotime($request->tgltutupbuku));
            $parameter = Parameter::where('grp', 'TUTUP BUKU')->where('subgrp', 'TUTUP BUKU')->where('text', $tglterakhir)->first();
            if (!$parameter) {
                return response([
                    'status' => false,
                    'statusText' => 'Tanggal Terakhir Tutup Buku Salah !!! <br> Proses Tutup Buku Gagal !!!',
                ], 423);
            }
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
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
