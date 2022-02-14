<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Api;


use App\Models\Acos;
use App\Http\Requests\StoreAcosRequest;
use App\Http\Requests\UpdateAcosRequest;
use App\Http\Requests\DestroyAcosRequest;


use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class AcosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAcosRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAcosRequest $request)
    {
        DB::beginTransaction();
        try {
            $Acos = new Acos();

            $Acos->class = $request->class;
            $Acos->method = $request->method;
            $Acos->nama = $request->nama;
            $Acos->modifiedby = $request->modifiedby;

            $Acos->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAcosRequest  $request
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAcosRequest $request, Acos $acos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Acos  $acos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Acos $acos)
    {
        //
    }
}
