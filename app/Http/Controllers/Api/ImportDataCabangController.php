<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ImportDataCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportDataCabangRequest;

class ImportDataCabangController extends Controller
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
    public function store(ImportDataCabangRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = [
                'cabang' => $request->cabang,
                'import' => $request->import,
                'priode' => $request->priode,
            ];
            $importDataCabang = (new ImportDataCabang())->processStore($data);
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
