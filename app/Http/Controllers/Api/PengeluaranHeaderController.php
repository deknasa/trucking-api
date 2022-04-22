<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranHeader;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengeluaranHeaderController extends Controller
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
     * @param  \App\Http\Requests\StorePengeluaranHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePengeluaranHeaderRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'nobukti' => 'required',
        ], [
            'nobukti.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ], [
            'nobukti' => 'NoBukti',
        ]);
        if (!$validator->passes()) {
            return [
                'error' => true,
                'messages' => $validator->messages()
            ];
        }

        try {
            $pengeluaranHeader = new PengeluaranHeader();

            $pengeluaranHeader->nobukti = $request->nobukti;
            $pengeluaranHeader->tglbukti = $request->tglbukti;
            $pengeluaranHeader->pelanggan_id = $request->pelanggan_id;
            $pengeluaranHeader->keterangan = $request->keterangan ?? '';
            $pengeluaranHeader->statusjenistransaksi = $request->statusjenistransaksi;
            $pengeluaranHeader->postingdari = $request->postingdari;
            $pengeluaranHeader->statusapproval = $request->statusapproval;
            $pengeluaranHeader->dibayarke = $request->dibayarke;
            $pengeluaranHeader->cabang_id = $request->cabang_id;
            $pengeluaranHeader->bank_id = $request->bank_id;
            $pengeluaranHeader->userapproval = $request->userapproval;
            $pengeluaranHeader->tglapproval = $request->tglapproval;
            $pengeluaranHeader->modifiedby = $request->modifiedby;
            
            $pengeluaranHeader->save();
            
           
            DB::commit();
            if ($validator->passes()) {
                return [
                    'error' => false,
                    'id' => $pengeluaranHeader->id,
                    'tabel' => $pengeluaranHeader->getTable(),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $errorCode = @$e->errorInfo[1];

            return response([
                'error' => true,
                'errorCode' => $errorCode,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PengeluaranHeader  $pengeluaranHeader
     * @return \Illuminate\Http\Response
     */
    public function show(PengeluaranHeader $pengeluaranHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PengeluaranHeader  $pengeluaranHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(PengeluaranHeader $pengeluaranHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePengeluaranHeaderRequest  $request
     * @param  \App\Models\PengeluaranHeader  $pengeluaranHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PengeluaranHeader  $pengeluaranHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(PengeluaranHeader $pengeluaranHeader)
    {
        //
    }
}
