<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\InvoiceExtraHeader;
use App\Models\PiutangHeader;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;
use App\Models\PiutangDetail;
use App\Http\Requests\StoreInvoiceExtraHeaderRequest;
use App\Http\Requests\UpdateInvoiceExtraHeaderRequest;

use App\Models\InvoiceExtraDetail;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreInvoiceExtraDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\StorePiutangDetailRequest;


class InvoiceExtraHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $invoice = new InvoiceExtraHeader();

        return response([
            "data" => $invoice->get(),
            "attributes" => [
                'totalRows' => $invoice->totalRows,
                'totalPages' => $invoice->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreInvoiceExtraHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'INVOICE EXTRA BUKTI';
            $subgroup = 'INVOICE EXTRA BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'invoiceextraheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            /* Store header */
            $invoiceExtraHeader = new InvoiceExtraHeader();
            $invoiceExtraHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceExtraHeader->keterangan = $request->keterangan;
            $invoiceExtraHeader->nominal    = $request->nominal;
            $invoiceExtraHeader->agen_id    = $request->agen_id;
            $invoiceExtraHeader->pelanggan_id = $request->pelanggan_id;
            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $invoiceExtraHeader->nobukti = $nobukti;
            
            if ($invoiceExtraHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceExtraHeader->getTable()),
                    'postingdari' => 'ENTRY INVOICE EXTRA HEADER',
                    'idtrans' => $invoiceExtraHeader->id,
                    'nobuktitrans' => $invoiceExtraHeader->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $invoiceExtraHeader->toArray(),
                    'modifiedby' => $invoiceExtraHeader->modifiedby
                ];
                
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                if ($request->nominal_detail) {
                    
                    /* Store detail */
                    $detaillog = [];
        
                    for ($i=0; $i <count($request->nominal_detail) ; $i++) { 
                        $datadetail = [
                            "invoiceextra_id" =>$invoiceExtraHeader->id,
                            "nobukti" => $invoiceExtraHeader->nobukti,
                            "nominal_detail" => $request->nominal_detail[$i],
                            "keterangan_detail" => $request->keterangan_detail[$i],
                        ];
    
                        $data = new StoreInvoiceExtraDetailRequest($datadetail);
                        $invoiceExtraDetail = app(InvoiceExtraDetailController::class)->store($data);
                        
                        if ($invoiceExtraDetail['error']) {
                            return response($invoiceExtraDetail, 422);
                        } else {
                            $iddetail = $invoiceExtraDetail['id'];
                            $tabeldetail = $invoiceExtraDetail['tabel'];
                        }
    
                        $datadetaillog = [
                            "invoiceextra_id" =>$invoiceExtraHeader->id,
                            "nobukti" => $invoiceExtraHeader->nobukti,
                            "nominal" => $request->nominal_detail[$i],
                            "keterangan" => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($invoiceExtraHeader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceExtraHeader->updated_at)),
                        ];
                        $detaillog[] = $datadetaillog;
        
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY INVOICE EXTRA DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $invoiceExtraHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
        
                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
                $group = 'PIUTANG BUKTI';
                $subgroup = 'PIUTANG BUKTI';
                $format = DB::table('parameter')
                    ->where('grp', $group )
                    ->where('subgrp', $subgroup)
                    ->first();
                    
                $nobuktiPiutang = new Request();
                $nobuktiPiutang['group'] = 'PIUTANG BUKTI';
                $nobuktiPiutang['subgroup'] = 'PIUTANG BUKTI';
                $nobuktiPiutang['table'] = 'piutangheader';
                $nobuktiPiutang['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                
                $piutang_nobukti = app(Controller::class)->getRunningNumber($nobuktiPiutang)->original['data'];
                
                
                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                
                $piutangHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $piutang_nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($invoiceExtraHeader->tglbukti)),
                    'keterangan' => $invoiceExtraHeader->keterangan,
                    'postingdari' => "ENTRY INVOICE",
                    'nominal' => $invoiceExtraHeader->nominal,
                    'invoice_nobukti' => $invoiceExtraHeader->nobukti,
                    'agen_id' => $invoiceExtraHeader->agen_id,
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => 1,
                ];
                
                $piutangDetail = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];
                    
                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $piutang_nobukti,
                        'nominal' => $request->nominal_detail[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'invoice_nobukti' => $invoiceExtraHeader->nobukti,
                        'modifiedby' =>  auth('api')->user()->name
                    ];
    
                    $piutangDetail[] = $detail;
                }
                
                $piutang = $this->storePiutang($piutangHeader, $piutangDetail);
                if (!$piutang['status']) {
                    throw new \Throwable($piutang['message']);
                }
                DB::commit();
            }
                
            /* Set position and page */
            $selected = $this->getPosition($invoiceExtraHeader, $invoiceExtraHeader->getTable());
            $invoiceExtraHeader->position = $selected->position;
            $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceExtraHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        } 
    }

    public function show(InvoiceExtraHeader $invoiceExtraHeader,$id)
    {
        $data = $invoiceExtraHeader->find($id);
        $detail = new InvoiceExtraDetail();
        $detail = $detail->getAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateInvoiceExtraHeaderRequest $request, InvoiceExtraHeader $invoiceextraheader)
    {
        DB::beginTransaction();

        try {

            $group = 'INVOICE EXTRA BUKTI';
            $subgroup = 'INVOICE EXTRA BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'invoiceextraheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            /* Store header */
            
            $invoiceextraheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceextraheader->keterangan = $request->keterangan;
            $invoiceextraheader->nominal    = $request->nominal;
            $invoiceextraheader->agen_id    = $request->agen_id;
            $invoiceextraheader->pelanggan_id = $request->pelanggan_id;
            
            
            if ($invoiceextraheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceextraheader->getTable()),
                    'postingdari' => 'EDIT INVOICE EXTRA HEADER',
                    'idtrans' => $invoiceextraheader->id,
                    'nobuktitrans' => $invoiceextraheader->id,
                    'aksi' => 'EDIT',
                    'datajson' => $invoiceextraheader->toArray(),
                    'modifiedby' => $invoiceextraheader->modifiedby
                ];
                
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Delete existing detail */
                $getPiutang = PiutangHeader::where('invoice_nobukti', $invoiceextraheader->nobukti)->first();
                
                if ($getPiutang) {
                    JurnalUmumHeader::where('nobukti',$getPiutang->nobukti)->lockForUpdate()->delete();
                    JurnalUmumDetail::where('nobukti',$getPiutang->nobukti)->lockForUpdate()->delete();
                    PiutangHeader::where('invoice_nobukti',$invoiceextraheader->nobukti)->lockForUpdate()->delete();
                    PiutangDetail::where('invoice_nobukti',$invoiceextraheader->nobukti)->lockForUpdate()->delete();
                }
                $penerimaanStokDetail = InvoiceExtraDetail::where('invoiceextra_id',$invoiceextraheader->id)->lockForUpdate()->delete();
                if ($request->nominal_detail) {
    
                    /* Store detail */
                    $detaillog = [];
        
                    for ($i=0; $i <count($request->nominal_detail) ; $i++) { 
                        $datadetail = [
                            "invoiceextra_id" =>$invoiceextraheader->id,
                            "nobukti" => $invoiceextraheader->nobukti,
                            "nominal_detail" => $request->nominal_detail[$i],
                            "keterangan_detail" => $request->keterangan_detail[$i],
                        ];
    
                        $data = new StoreInvoiceExtraDetailRequest($datadetail);
                        $invoiceExtraDetail = app(InvoiceExtraDetailController::class)->store($data);
                        
                        if ($invoiceExtraDetail['error']) {
                            return response($invoiceExtraDetail, 422);
                        } else {
                            $iddetail = $invoiceExtraDetail['id'];
                            $tabeldetail = $invoiceExtraDetail['tabel'];
                        }
    
                        $datadetaillog = [
                            "invoiceextra_id" =>$invoiceextraheader->id,
                            "nobukti" => $invoiceextraheader->nobukti,
                            "nominal" => $request->nominal_detail[$i],
                            "keterangan" => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($invoiceextraheader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceextraheader->updated_at)),
                        ];
                        $detaillog[] = $datadetaillog;
        
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY INVOICE EXTRA DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $invoiceextraheader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
        
                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
                $group = 'PIUTANG BUKTI';
                $subgroup = 'PIUTANG BUKTI';
                $format = DB::table('parameter')
                    ->where('grp', $group )
                    ->where('subgrp', $subgroup)
                    ->first();
                    
                $nobuktiPiutang = new Request();
                $nobuktiPiutang['group'] = 'PIUTANG BUKTI';
                $nobuktiPiutang['subgroup'] = 'PIUTANG BUKTI';
                $nobuktiPiutang['table'] = 'piutangheader';
                $nobuktiPiutang['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                
                $piutang_nobukti = app(Controller::class)->getRunningNumber($nobuktiPiutang)->original['data'];
                
                
                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                
                $piutangHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $piutang_nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($invoiceextraheader->tglbukti)),
                    'keterangan' => $invoiceextraheader->keterangan,
                    'postingdari' => "ENTRY INVOICE",
                    'nominal' => $invoiceextraheader->nominal,
                    'invoice_nobukti' => $invoiceextraheader->nobukti,
                    'agen_id' => $invoiceextraheader->agen_id,
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => 1,
                ];
                
                $piutangDetail = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];
                    
                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $piutang_nobukti,
                        'nominal' => $request->nominal_detail[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'invoice_nobukti' => $invoiceextraheader->nobukti,
                        'modifiedby' =>  auth('api')->user()->name
                    ];
    
                    $piutangDetail[] = $detail;
                }
                
                $piutang = $this->storePiutang($piutangHeader, $piutangDetail);
                if (!$piutang['status']) {
                    throw new \Throwable($piutang['message']);
                }
                
                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($invoiceextraheader, $invoiceextraheader->getTable());
            $invoiceextraheader->position = $selected->position;
            $invoiceextraheader->page = ceil($invoiceextraheader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $invoiceextraheader->page = ceil($invoiceextraheader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceextraheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        } 
    }

    /**
     * @ClassName 
     */
    public function destroy(InvoiceExtraHeader $invoiceextraheader,Request $request)
    {
        DB::beginTransaction();

        try {

            
            $delete = InvoiceExtraDetail::where('invoiceextra_id', $invoiceextraheader->id)->lockForUpdate()->delete();
            $delete = InvoiceExtraHeader::destroy($invoiceextraheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceextraheader->getTable()),
                    'postingdari' => 'DELETE INVOICE HEADER',
                    'idtrans' => $invoiceextraheader->id,
                    'nobuktitrans' => $invoiceextraheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $invoiceextraheader->toArray(),
                    'modifiedby' => $invoiceextraheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($invoiceextraheader, $invoiceextraheader->getTable(), true);
                $invoiceextraheader->position = $selected->position;
                $invoiceextraheader->id = $selected->id;
                $invoiceextraheader->page = ceil($invoiceextraheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $invoiceextraheader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function storePiutang($piutangHeader,$piutangDetail)
    {
        try {

            
            $piutang = new StorePiutangHeaderRequest($piutangHeader);
            $header = app(PiutangHeaderController::class)->store($piutang);
           
            $nobukti = $piutangHeader['nobukti'];
            $fetchId = PiutangHeader::select('id')
                ->whereRaw("nobukti = '$nobukti'")
                ->first();
            $id = $fetchId->id;

            foreach ($piutangDetail as $value) {
                
                $value['piutang_id'] = $id;
                $piutangDetails = new StorePiutangDetailRequest($value);
                $tes = app(PiutangDetailController::class)->store($piutangDetails);
                
            }

            
            return [
                'status' => true
            ];

        } catch (\Throwable $th) {
            throw $th;
            
        }
    }
}
