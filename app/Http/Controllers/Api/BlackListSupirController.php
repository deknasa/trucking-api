<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlackListSupir;
use App\Http\Requests\StoreBlackListSupirRequest;
use App\Http\Requests\UpdateBlackListSupirRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlackListSupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $blackListSupir = new BlackListSupir();

        return response([
            'data' => $blackListSupir->get(),
            'attributes' => [
                'totalRows' => $blackListSupir->totalRows,
                'totalPages' => $blackListSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreBlackListSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "nosim" => $request->nosim,
            ];
            /* Store header */
            $blackListSupir = (new BlackListSupir())->processStore($data);
            /* Set position and page */
            $blackListSupir->position = $this->getPosition($blackListSupir, $blackListSupir->getTable())->position;
            $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $blackListSupir
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function show(BlackListSupir $blackListSupir,$id)
    {
        $blackListSupir = new BlackListSupir();
        return response([
            'data' => $blackListSupir->findOrFail($id),
            'attributes' => [
                'totalRows' => $blackListSupir->totalRows,
                'totalPages' => $blackListSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateBlackListSupirRequest $request, BlackListSupir $blackListSupir, $id)
    {
        DB::beginTransaction();
        try {
            $data =[
                "namasupir" => $request->namasupir,
                "noktp" => $request->noktp,
                "nosim" => $request->nosim,
            ];
            /* Store header */
            $blackListSupir = BlackListSupir::findOrFail($id);
            $blackListSupir = (new BlackListSupir())->processUpdate($blackListSupir,$data);
            /* Set position and page */
            $blackListSupir->position = $this->getPosition($blackListSupir, $blackListSupir->getTable())->position;
            $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $blackListSupir
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

   /**
    * @ClassName 
    */
    public function destroy(BlackListSupir $blackListSupir,$id)
    {
        DB::beginTransaction();
        try {
            // dd($blackListSupir);
            $blackListSupir = (new BlackListSupir())->processDestroy($id);
            /* Set position and page */
            $blackListSupir->position = $this->getPosition($blackListSupir, $blackListSupir->getTable())->position;
            $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $blackListSupir->page = ceil($blackListSupir->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $blackListSupir
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
