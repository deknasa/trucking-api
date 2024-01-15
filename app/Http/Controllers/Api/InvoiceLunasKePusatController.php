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
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
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
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreinvoicelunaskepusatRequest $request)
    {
        DB::beginTransaction();
        try {


            $data = [
                "invoiceheader_id" => $request->invoiceheader_id,
                "nobukti" => $request->nobukti,
                "tglbukti" => $request->tglbukti,
                "agen_id" => $request->agen_id,
                "nominal" => $request->nominal,
                "tglbayar" => $request->tglbayar,
                "bayar" => $request->bayar,
                "sisa" => $request->sisa,
                "potongan" => $request->potongan,
            ];
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processStore($data);
            $InvoiceLunasKePusat->position = $request->indexRow;
            // $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas($InvoiceLunasKePusat->id)->position;
            // $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

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
    public function show($id)
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
        $dataInvoice = $invoicelunaskepusat->getinvoicelunas($id);
        return response([
            'status' => true,
            'data' => $dataInvoice
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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateinvoicelunaskepusatRequest $request, InvoiceLunasKePusat $invoicelunaskepusat)
    {
        DB::beginTransaction();
        try {
            $data = [
                "invoiceheader_id" => $request->invoiceheader_id,
                "nobukti" => $request->nobukti,
                "tglbukti" => $request->tglbukti,
                "agen_id" => $request->agen_id,
                "nominal" => $request->nominal,
                "tglbayar" => $request->tglbayar,
                "bayar" => $request->bayar,
                "sisa" => $request->sisa,
                "potongan" => $request->potongan,
            ];
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processUpdate($invoicelunaskepusat, $data);
            $InvoiceLunasKePusat->position = $request->indexRow;
            // $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas($InvoiceLunasKePusat->trado_id)->position;

            // $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil disimpan',
                'data' => $InvoiceLunasKePusat
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(InvoiceLunasKepusatRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $InvoiceLunasKePusat = (new InvoiceLunasKePusat())->processDestroy($id);
            $InvoiceLunasKePusat->position = $request->indexRow;
            // $InvoiceLunasKePusat->position = $this->getPositionInvoiceLunas(0,true)->position;

            // $InvoiceLunasKePusat->page = ceil($InvoiceLunasKePusat->position / ($request->limit ?? 10));

            DB::commit();
            return response([
                'message' => 'Berhasil dihapus',
                'data' => $InvoiceLunasKePusat
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekValidasi(Request $request, $invoiceheader_id)
    {
        $cekInvoicePusat = DB::table("invoicelunaskepusat")->from(DB::raw("invoicelunaskepusat with (readuncommitted)"))
            ->where('invoiceheader_id', $invoiceheader_id)->first();
        if ($cekInvoicePusat != '') {
            return response([
                'errors' => false
            ]);
        } else {
            $getError = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'BPI')
                ->first();
            return response([
                'errors' => true,
                'message' => 'INVOICE LUNAS KE PUSAT ' . $getError->keterangan
            ]);
        }
    }

    public function cekValidasiAdd(Request $request, $invoiceheader_id)
    {

        // $now = date("Y-m-d");
        $getinvoice = db::table("invoicelunaskepusat")->from(DB::raw("invoicelunaskepusat with (readuncommitted)"))->where('invoiceheader_id', $invoiceheader_id)->first();

        if ($getinvoice != null) {
            $getError = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SPI')
                ->first();

            return response([
                'errors' => true,
                'message' => 'INVOICE LUNAS KE PUSAT ' . $getError->keterangan
            ]);
        } else {
            return response([
                'errors' => false,
            ]);
        }
    }
    
    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();
        return response([
            'data' => $invoicelunaskepusat->get(),
            'judul' => $getJudul->text
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
        $invoicelunaskepusat = new InvoiceLunasKePusat();
        return response([
            'data' => $invoicelunaskepusat->report(),
        ]);
    }
}
