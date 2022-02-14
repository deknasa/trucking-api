<?php


namespace App\Http\Controllers;

namespace App\Http\Controllers\Api;


use App\Models\LogTrail;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateLogTrailRequest;
use App\Http\Requests\DestroyLogTrailRequest;


use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class LogTrailController extends Controller
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
        DB::beginTransaction();
        try {
            $LogTrail = new LogTrail();

            $LogTrail->namatabel = $request->namatabel;
            $LogTrail->postingdari = $request->postingdari;
            $LogTrail->idtrans = $request->idtrans;
            $LogTrail->nobuktitrans = $request->nobuktitrans;
            $LogTrail->aksi = $request->aksi;
            $LogTrail->datajson = $request->datajson;
            $LogTrail->modifiedby = $request->modifiedby;

            $LogTrail->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
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
