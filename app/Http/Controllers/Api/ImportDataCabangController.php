<?php

namespace App\Http\Controllers\Api;

use PDOException;
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
                'periode' => $request->periode,
            ];
            $importDataCabang = (new ImportDataCabang())->processStore($data);
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $importDataCabang
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    
    public function testkoneksi() {
        try {
            
            $test = (new ImportDataCabang())->testkoneksi();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $test
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            $test = "Database asdfasdfasdfasfasdfasdfsadfdas asdf asdf asd fasd fad asd't work <br>$th";
        }
    
        return $test;
    }
    public function xxx() {
        try {
            $dbconnect = DB::connection('sqlsrv2')->getPDO();
            
            $dbname = DB::connection('sqlsrv2')->getDatabaseName();
                # code...
            echo "Connected successfully to the database. Database name is :".$dbname;
        } catch (\Exception $e) {
            $test = "Database doesn't work";
        }
    
        return $test;
    }

    
}
