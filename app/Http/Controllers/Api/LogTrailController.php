<?php

namespace App\Http\Controllers;

use App\Models\logtrail;
use App\Http\Requests\StorelogtrailRequest;
use App\Http\Requests\UpdatelogtrailRequest;

class LogtrailController extends Controller
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
     * @param  \App\Http\Requests\StorelogtrailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLogTrailRequest $request)
    {
        try {
            $LogTrail = new LogTrail();
            $LogTrail->ntabel = $request->ntabel;
            $LogTrail->postfrom = $request->postfrom;
            $LogTrail->idtrans = $request->idtrans;
            $LogTrail->aksi = $request->aksi;
            $LogTrail->datajson = $request->datajson;
            $LogTrail->modifiedby = $request->modifiedby;

            $LogTrail->save();
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function show(LogTrail $logtrail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function edit(LogTrail $logtrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatelogtrailRequest  $request
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLogTrailRequest $request, logtrail $logtrail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\logtrail  $logtrail
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogTrail $logtrail)
    {
        //
    }
}
