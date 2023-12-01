<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceLunasKePusat;
use App\Http\Requests\StoreinvoicelunaskepusatRequest;
use App\Http\Requests\UpdateinvoicelunaskepusatRequest;
use App\Http\Requests\InvoiceLunasKePusatRequest;

use stdClass;
use App\Models\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceLunasKePusatController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
        return response([
            'data' => $invoicelunaskepusat->get(),
            'attributes' => [
                'total' => $invoicelunaskepusat->totalPages,
                'records' => $invoicelunaskepusat->totalRows,
            ]
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
    public function store(StoreinvoicelunaskepusatRequest $request)
    {
        DB::beginTransaction();
        try {
  

            $data = [
                "nobukti" =>$request->nobukti,
                "tglbukti" =>$request->tglbukti,
                "agen_id" =>$request->agen_id,
                "nominal" =>$request->nominal,
                "tglbayar" => $request->tglbayar,
                "bayar" => $request->bayar,
                "sisa" => $request->sisa,
            ];
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processStore($data);
            $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas($InvoiceLunasKePusat->id)->position;
            $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $InvoiceLunasKePusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

  /**
     * @ClassName
     */
    public function show(invoicelunaskepusat $invoicelunaskepusat)
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
  
        return response([
            'status' => true,
            'data' => $invoicelunaskepusat
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoicelunaskepusat  $invoicelunaskepusat
     * @return \Illuminate\Http\Response
     */
    public function edit(invoicelunaskepusat $invoicelunaskepusat)
    {
        //
    }

    /**
     * @ClassName 
     */
    public function update(UpdateinvoicelunaskepusatRequest $request,  $id)
    {
        DB::beginTransaction();
        try {
            $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
            $data = [
                "nobukti" =>$request->nobukti,
                "tglbukti" =>$request->tglbukti,
                "agen_id" =>$request->agen_id,
                "nominal" =>$request->nominal,
                "tglbayar" => $request->tglbayar,
                "bayar" => $request->bayar,
                "sisa" => $request->sisa,
            ];
            $InvoiceLunasKePusat = InvoiceLunasKePusat::findOrFail($id);
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processUpdate($InvoiceLunasKePusat,$data);
            $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas($InvoiceLunasKePusat->trado_id)->position;
         
            $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $InvoiceLunasKePusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

     /**
     * @ClassName 
     */
    public function destroy(InvoiceLunasKepusatRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $InvoiceLunasKePusat = InvoiceLunasKePusat::findOrFail($id);
            $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();

            
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processDestroy($InvoiceLunasKePusat->id);
            $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas(0,true)->position;
        
            $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));
 
            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $InvoiceLunasKePusat
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}
