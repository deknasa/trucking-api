<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ApprovalOpname;
use App\Http\Requests\StoreApprovalOpnameRequest;
Use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateApprovalOpnameRequest;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;

class ApprovalOpnameController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $parameter = Parameter::where('grp', 'OPNAME STOK')->where('subgrp', 'OPNAME STOK')->first();

        return response([
            'data' => $parameter,
        ]);
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
     * @ClassName
     */
    public function store(StoreApprovalOpnameRequest $request)
    {
        DB::beginTransaction();

        try {
            $statusopname = $request->statusopname ?? 'TIDAK';
            $parameter = Parameter::where('grp', 'OPNAME STOK')->where('subgrp', 'OPNAME STOK')->first();
            
            $parameter->text = $statusopname;
            $parameter->modifiedby = auth('api')->user()->name;

            if ($parameter->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($parameter->getTable()),
                    'postingdari' => 'OPNAME STOK',
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApprovalOpname  $approvalOpname
     * @return \Illuminate\Http\Response
     */
    public function show(ApprovalOpname $approvalOpname)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApprovalOpname  $approvalOpname
     * @return \Illuminate\Http\Response
     */
    public function edit(ApprovalOpname $approvalOpname)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateApprovalOpnameRequest  $request
     * @param  \App\Models\ApprovalOpname  $approvalOpname
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateApprovalOpnameRequest $request, ApprovalOpname $approvalOpname)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApprovalOpname  $approvalOpname
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApprovalOpname $approvalOpname)
    {
        //
    }
}
