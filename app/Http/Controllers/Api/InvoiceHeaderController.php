<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\InvoiceDetailController as ApiInvoiceDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Models\InvoiceHeader;
use App\Http\Requests\StoreInvoiceHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateInvoiceHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Models\InvoiceDetail;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\LogTrail;
use App\Models\OrderanTrucking;
use App\Models\Parameter;
use App\Models\PiutangDetail;
use App\Models\PiutangHeader;
use App\Models\SuratPengantar;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class InvoiceHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $invoice = new InvoiceHeader();

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
    public function store(StoreInvoiceHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $group = 'INVOICE BUKTI';
            $subgroup = 'INVOICE BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'invoiceheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            date_default_timezone_set('Asia/Jakarta');

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $invoice = new InvoiceHeader();
            $invoice->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoice->keterangan = $request->keterangan;
            $invoice->nominal = '';
            $invoice->tglterima = date('Y-m-d', strtotime($request->tglterima));
            $invoice->tgljatuhtempo = date('Y-m-d');
            $invoice->agen_id = $request->agen_id;
            $invoice->jenisorder_id = $request->jenisorder_id;
            $invoice->cabang_id = $request->cabang_id;
            $invoice->piutang_nobukti = $request->piutang_nobukti ?? '';
            $invoice->statusapproval = $statusApproval->id;
            $invoice->userapproval = '';
            $invoice->tglapproval = '';
            $invoice->statuscetak = $statusCetak->id;
            $invoice->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $invoice->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $invoice->modifiedby = auth('api')->user()->name;
            $invoice->statusformat = $format->id;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $invoice->nobukti = $nobukti;

            $invoice->save();
            $logTrail = [
                'namatabel' => strtoupper($invoice->getTable()),
                'postingdari' => 'ENTRY INVOICE HEADER',
                'idtrans' => $invoice->id,
                'nobuktitrans' => $invoice->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $invoice->toArray(),
                'modifiedby' => $invoice->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            /* Store detail */

            $detaillog = [];
            $total = 0;
            for ($i = 0; $i < count($request->sp_id); $i++) {

                $SP = SuratPengantar::where('id', $request->sp_id[$i])->first();
                $orderantrucking = OrderanTrucking::where('nobukti', $SP->jobtrucking)->first();
                $total = $total + $orderantrucking->nominal;
                $getSP = SuratPengantar::where('jobtrucking', $SP->jobtrucking)->get();

                $allSP = "";
                foreach($getSP as $value){
                   $allSP = $allSP. $value->nobukti .', ';
                }
                $datadetail = [
                    'invoice_id' => $invoice->id,
                    'nobukti' => $invoice->nobukti,
                    'nominal' => $orderantrucking->nominal,
                    'keterangan' => $SP->keterangan,
                    'orderantrucking_nobukti' => $SP->jobtrucking,
                    'suratpengantar_nobukti' => $allSP,
                    'modifiedby' => $invoice->modifiedby,
                ];

                // STORE 
                $data = new StoreInvoiceDetailRequest($datadetail);

                $datadetails = app(ApiInvoiceDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }


                $datadetaillog = [
                    'id' => $iddetail,
                    'invoice_id' => $invoice->id,
                    'nobukti' => $invoice->nobukti,
                    'nominal' => $orderantrucking->nominal,
                    'keterangan' => $SP->keterangan,
                    'orderantrucking_nobukti' => $SP->jobtrucking,
                    'suratpengantar_nobukti' => $allSP,
                    'modifiedby' => $invoice->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($invoice->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($invoice->updated_at)),

                ];


                $detaillog[] = $datadetaillog;

            }

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY INVOICE DETAIL',
                'idtrans' =>  $invoice->id,
                'nobuktitrans' => $invoice->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $invoice->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);
            $group = 'PIUTANG BUKTI';
            $subgroup = 'PIUTANG BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $nobuktiPiutang = new Request();
            $nobuktiPiutang['group'] = 'PIUTANG BUKTI';
            $nobuktiPiutang['subgroup'] = 'PIUTANG BUKTI';
            $nobuktiPiutang['table'] = 'piutangheader';
            $nobuktiPiutang['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $piutang_nobukti = app(Controller::class)->getRunningNumber($nobuktiPiutang)->original['data'];

            $invoice->piutang_nobukti = $piutang_nobukti;
            $invoice->nominal = $total;
            $invoice->save();
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $piutangHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang_nobukti,
                'tglbukti' => date('Y-m-d', strtotime($invoice->tglbukti)),
                'keterangan' => $invoice->keterangan,
                'postingdari' => "ENTRY INVOICE",
                'nominal' => $invoice->nominal,
                'invoice_nobukti' => $invoice->nobukti,
                'agen_id' => $invoice->agen_id,
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => 1,
            ];

            $piutangDetail = [];
            for ($i = 0; $i < count($request->sp_id); $i++) {
                $detail = [];

                $SP = SuratPengantar::where('id', $request->sp_id[$i])->first();
                $orderantrucking = OrderanTrucking::where('nobukti', $SP->jobtrucking)->first();
                
                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $piutang_nobukti,
                    'nominal' => $orderantrucking->nominal,
                    'keterangan' => $SP->keterangan,
                    'invoice_nobukti' => $invoice->nobukti,
                    'modifiedby' =>  auth('api')->user()->name
                ];

                $piutangDetail[] = $detail;
            }

            $piutang = $this->storePiutang($piutangHeader, $piutangDetail);

            if (!$piutang['status']) {
                throw new \Throwable($piutang['message']);
            }
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($invoice, $invoice->getTable());
            $invoice->position = $selected->position;
            $invoice->page = ceil($invoice->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $invoice
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $invoice = new InvoiceHeader();
        return response([
            'status' => true,
            'data' => $invoice->findAll($id)
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateInvoiceHeaderRequest $request, InvoiceHeader $invoiceheader)
    {
        DB::beginTransaction();

        try {

            $invoiceheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceheader->keterangan = $request->keterangan;
            $invoiceheader->nominal = '';
            $invoiceheader->tglterima = date('Y-m-d', strtotime($request->tglterima));
            $invoiceheader->agen_id = $request->agen_id;
            $invoiceheader->jenisorder_id = $request->jenisorder_id;
            $invoiceheader->cabang_id = $request->cabang_id;
            $invoiceheader->piutang_nobukti = $request->piutang_nobukti ?? '';
            $invoiceheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $invoiceheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $invoiceheader->modifiedby = auth('api')->user()->name;


            if ($invoiceheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceheader->getTable()),
                    'postingdari' => 'EDIT INVOICE HEADER',
                    'idtrans' => $invoiceheader->id,
                    'nobuktitrans' => $invoiceheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $invoiceheader->toArray(),
                    'modifiedby' => $invoiceheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $getPiutang = PiutangHeader::where('invoice_nobukti', $invoiceheader->nobukti)->first();

                JurnalUmumHeader::where('nobukti', $getPiutang->nobukti)->lockForUpdate()->delete();
                JurnalUmumDetail::where('nobukti', $getPiutang->nobukti)->lockForUpdate()->delete();
                PiutangHeader::where('invoice_nobukti', $invoiceheader->nobukti)->lockForUpdate()->delete();
                PiutangDetail::where('invoice_nobukti', $invoiceheader->nobukti)->lockForUpdate()->delete();
                InvoiceDetail::where('invoice_id', $invoiceheader->id)->lockForUpdate()->delete();

                /* Store detail */
                $detaillog = [];
                $total = 0;
                for ($i = 0; $i < count($request->sp_id); $i++) {

                    $SP = SuratPengantar::where('id', $request->sp_id[$i])->first();

                    $orderantrucking = OrderanTrucking::where('nobukti', $SP->jobtrucking)->first();
                    $total = $total + $orderantrucking->nominal;
                    $getSP = SuratPengantar::where('jobtrucking', $SP->jobtrucking)->get();

                    $allSP = "";
                    foreach($getSP as $value){
                       $allSP = $allSP. $value->nobukti .', ';
                    }

                    $datadetail = [
                        'invoice_id' => $invoiceheader->id,
                        'nobukti' => $invoiceheader->nobukti,
                        'nominal' => $orderantrucking->nominal,
                        'keterangan' => $SP->keterangan,
                        'orderantrucking_nobukti' => $SP->jobtrucking,
                        'suratpengantar_nobukti' => $allSP,
                        'modifiedby' => $invoiceheader->modifiedby,
                    ];
                    $data = new StoreInvoiceDetailRequest($datadetail);

                    $datadetails = app(ApiInvoiceDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'invoice_id' => $invoiceheader->id,
                        'nobukti' => $invoiceheader->nobukti,
                        'nominal' => $orderantrucking->nominal,
                        'keterangan' => $SP->keterangan,
                        'orderantrucking_nobukti' => $SP->jobtrucking,
                        'suratpengantar_nobukti' => $allSP,
                        'modifiedby' => $invoiceheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($invoiceheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceheader->updated_at)),

                    ];

                    $detaillog[] = $datadetaillog;


                }

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT INVOICE DETAIL',
                    'idtrans' =>  $invoiceheader->id,
                    'nobuktitrans' => $invoiceheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $invoiceheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            
            $group = 'PIUTANG BUKTI';
            $subgroup = 'PIUTANG BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $nobuktiPiutang = new Request();
            $nobuktiPiutang['group'] = 'PIUTANG BUKTI';
            $nobuktiPiutang['subgroup'] = 'PIUTANG BUKTI';
            $nobuktiPiutang['table'] = 'piutangheader';
            $nobuktiPiutang['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $piutang_nobukti = app(Controller::class)->getRunningNumber($nobuktiPiutang)->original['data'];

            $invoiceheader->piutang_nobukti = $piutang_nobukti;
            $invoiceheader->nominal = $total;
            $invoiceheader->save();
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $piutangHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang_nobukti,
                'tglbukti' => date('Y-m-d', strtotime($invoiceheader->tglbukti)),
                'keterangan' => $invoiceheader->keterangan,
                'postingdari' => "ENTRY INVOICE",
                'nominal' => $invoiceheader->nominal,
                'invoice_nobukti' => $invoiceheader->nobukti,
                'agen_id' => $invoiceheader->agen_id,
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => 1,
            ];

            $piutangDetail = [];
            for ($i = 0; $i < count($request->sp_id); $i++) {
                $detail = [];

                $SP = SuratPengantar::where('id', $request->sp_id[$i])->first();
                $orderantrucking = OrderanTrucking::where('nobukti', $SP->jobtrucking)->first();
                
                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $piutang_nobukti,
                    'nominal' => $orderantrucking->nominal,
                    'keterangan' => $SP->keterangan,
                    'invoice_nobukti' => $invoiceheader->nobukti,
                    'modifiedby' =>  auth('api')->user()->name
                ];

                $piutangDetail[] = $detail;
            }

            $piutang = $this->storePiutang($piutangHeader, $piutangDetail);

            if (!$piutang['status']) {
                throw new \Throwable($piutang['message']);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($invoiceheader, $invoiceheader->getTable());
            $invoiceheader->position = $selected->position;
            $invoiceheader->page = ceil($invoiceheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $invoiceheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(InvoiceHeader $invoiceheader, Request $request)
    {
        DB::beginTransaction();
        try { 
            $getPiutang = PiutangHeader::where('invoice_nobukti', $invoiceheader->nobukti)->first();

            $getDetail = InvoiceDetail::where('invoice_id', $invoiceheader->id)->get();
            $getPiutangHeader = PiutangHeader::where('invoice_nobukti', $invoiceheader->nobukti)->first();
            $getPiutangDetail = PiutangDetail::where('invoice_nobukti', $invoiceheader->nobukti)->get();
            $getJurnalHeader = JurnalUmumHeader::where('nobukti', $getPiutang->nobukti)->first();
            $getJurnalDetail = JurnalUmumDetail::where('nobukti', $getPiutang->nobukti)->get();

            JurnalUmumHeader::where('nobukti', $getPiutang->nobukti)->lockForUpdate()->delete();
            JurnalUmumDetail::where('nobukti', $getPiutang->nobukti)->lockForUpdate()->delete();
            PiutangHeader::where('invoice_nobukti', $invoiceheader->nobukti)->lockForUpdate()->delete();
            PiutangDetail::where('invoice_nobukti', $invoiceheader->nobukti)->lockForUpdate()->delete();
            $delete = InvoiceDetail::where('invoice_id', $invoiceheader->id)->lockForUpdate()->delete();
            $delete = InvoiceHeader::destroy($invoiceheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceheader->getTable()),
                    'postingdari' => 'DELETE INVOICE HEADER',
                    'idtrans' => $invoiceheader->id,
                    'nobuktitrans' => $invoiceheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $invoiceheader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                // DELETE INVOICE DETAIL
                $logTrailInvoiceDetail = [
                    'namatabel' => 'INVOICEDETAIL',
                    'postingdari' => 'DELETE INVOICE DETAIL',
                    'idtrans' => $invoiceheader->id,
                    'nobuktitrans' => $invoiceheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailInvoiceDetail = new StoreLogTrailRequest($logTrailInvoiceDetail);
                app(LogTrailController::class)->store($validatedLogTrailInvoiceDetail);

                // DELETE PIUTANG HEADER
                $logTrailPiutangHeader = [
                    'namatabel' => 'PIUTANG',
                    'postingdari' => 'DELETE PIUTANG HEADER DARI INVOICE',
                    'idtrans' => $getPiutangHeader->id,
                    'nobuktitrans' => $getPiutangHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getPiutangHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPiutangHeader = new StoreLogTrailRequest($logTrailPiutangHeader);
                app(LogTrailController::class)->store($validatedLogTrailPiutangHeader);
               
                // DELETE PIUTANG DETAIL
                $logTrailPiutangDetail = [
                    'namatabel' => 'PIUTANGDETAIL',
                    'postingdari' => 'DELETE PIUTANG DETAIL DARI INVOICE',
                    'idtrans' => $getPiutangHeader->id,
                    'nobuktitrans' => $getPiutangHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getPiutangDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPiutangDetail = new StoreLogTrailRequest($logTrailPiutangDetail);
                app(LogTrailController::class)->store($validatedLogTrailPiutangDetail);

                // DELETE JURNAL HEADER
                $logTrailJurnalHeader = [
                    'namatabel' => 'JURNALUMUMHEADER',
                    'postingdari' => 'DELETE JURNAL UMUM HEADER DARI INVOICE',
                    'idtrans' => $getJurnalHeader->id,
                    'nobuktitrans' => $getJurnalHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
                app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);
               
                // DELETE JURNAL DETAIL
                $logTrailJurnalDetail = [
                    'namatabel' => 'JURNALUMUMDETAIL',
                    'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI INVOICE',
                    'idtrans' => $getJurnalHeader->id,
                    'nobuktitrans' => $getJurnalHeader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getJurnalDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
                app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            }
            DB::commit();

            $selected = $this->getPosition($invoiceheader, $invoiceheader->getTable(), true);
            $invoiceheader->position = $selected->position;
            $invoiceheader->id = $selected->id;
            $invoiceheader->page = ceil($invoiceheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $invoiceheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('invoiceheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getSP(Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        $cekSP = DB::table('suratpengantar')
            ->whereRaw("agen_id = $request->agen_id")
            ->whereRaw("jenisorder_id = $request->jenisorder_id")
            ->whereRaw("tglbukti >= '$dari'")
            ->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("suratpengantar.jobtrucking not in(select orderantrucking_nobukti from invoicedetail)");
 
        if ($cekSP->first()) {
            return response([
                "data" => $invoice->getSP($request)
            ]);
        } else {

            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'NSP')
                ->first();
            return response([
                'message' => "$query->keterangan",
            ], 422);
        }
    }

    public function getEdit($id, Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        return response([
            "data" => $invoice->getEdit($id, $request)
        ]);
    }

    public function comboapproval(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }

    public function storePiutang($piutangHeader, $piutangDetail)
    {
        try {


            $piutang = new StorePiutangHeaderRequest($piutangHeader);
            $header = app(PiutangHeaderController::class)->store($piutang);

            $nobukti = $piutangHeader['nobukti'];
            $fetchId = PiutangHeader::select('id')
                ->whereRaw("nobukti = '$nobukti'")
                ->first();
            $idPiutang = $fetchId->id;
            $fetchJurnal = JurnalUmumHeader::whereRaw("nobukti = '$nobukti'")->first();

            $detailLogPiutang = [];
            $detailLogJurnal = [];
            foreach ($piutangDetail as $value) {

                $value['piutang_id'] = $idPiutang;
                $piutangDetail = new StorePiutangDetailRequest($value);
                $detailPiutang = app(PiutangDetailController::class)->store($piutangDetail);

                $piutangs = $detailPiutang['detail'];
                $dataDetailLogPiutang = [
                    'id' => $piutangs->id,
                    'piutang_id' => $piutangs->piutang_id,
                    'nobukti' => $piutangs->nobukti,
                    'nominal' => $piutangs->nominal,
                    'keterangan' => $piutangs->keterangan,
                    'invoice_nobukti' => $piutangs->invoice_nobukti ?? '',
                    'modifiedby' => $piutangs->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($piutangs->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($piutangs->updated_at)),
                ];
                $detailLogPiutang[] = $dataDetailLogPiutang;

                // JURNAL

                $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();
                $getCOA = DB::table('parameter')->where("kelompok","COA INVOICE")->get();
                
                if(is_null($getBaris)) {
                    $baris = 0;
                }else{
                    $baris = $getBaris->baris+1;
                }
                
                for ($x = 0; $x <= 1; $x++) {
                    
                    if ($x == 1) {
                        $datadetail = [
                            'jurnalumum_id' => $fetchJurnal->id,
                            'nobukti' => $fetchJurnal->nobukti,
                            'tglbukti' => $fetchJurnal->tglbukti,
                            'coa' =>  $getCOA[$x]->text,
                            'nominal' => -$piutangDetail->nominal,
                            'keterangan' => $piutangDetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $fetchJurnal->id,
                            'nobukti' => $fetchJurnal->nobukti,
                            'tglbukti' => $fetchJurnal->tglbukti,
                            'coa' =>  $getCOA[$x]->text,
                            'nominal' => $piutangDetail->nominal,
                            'keterangan' => $piutangDetail->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($datadetail);
                    $detailJurnal = app(JurnalUmumDetailController::class)->store($detail); 
                
                    
                    $jurnals = $detailJurnal['detail'];
                    $dataDetailLogJurnal = [
                        'id' => $jurnals->id,
                        'jurnalumum_id' =>  $jurnals->jurnalumum_id,
                        'nobukti' => $jurnals->nobukti,
                        'tglbukti' => $jurnals->tglbukti,
                        'coa' => $jurnals->coa,
                        'nominal' => $jurnals->nominal,
                        'keterangan' => $jurnals->keterangan,
                        'modifiedby' => $jurnals->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($jurnals->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($jurnals->updated_at)),
                        'baris' => $jurnals->baris,
                    ];
                    $detailLogJurnal[] = $dataDetailLogJurnal;
                }

            }

            $datalogtrail = [
                'namatabel' => $detailPiutang['tabel'],
                'postingdari' => 'ENTRY INVOICE HEADER',
                'idtrans' =>  $idPiutang,
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogPiutang,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            $datalogtrail = [
                'namatabel' => $detailJurnal['tabel'],
                'postingdari' => 'ENTRY PIUTANG DARI INVOICE ',
                'idtrans' =>  $fetchJurnal->id,
                'nobuktitrans' => $fetchJurnal->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogJurnal,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $invoice = InvoiceHeader::lockForUpdate()->findOrFail($id);
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($invoice->statusapproval == $statusApproval->id) {
                $invoice->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $invoice->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $invoice->tglapproval = date('Y-m-d H:i:s');
            $invoice->userapproval = auth('api')->user()->name;

            if ($invoice->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoice->getTable()),
                    'postingdari' => 'APPROVED INVOICE',
                    'idtrans' => $invoice->id,
                    'nobuktitrans' => $invoice->nobukti,
                    'aksi' => $aksi,
                    'datajson' => $invoice->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoice = InvoiceHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoice->statuscetak != $statusSudahCetak->id) {
                $invoice->statuscetak = $statusSudahCetak->id;
                $invoice->tglbukacetak = date('Y-m-d H:i:s');
                $invoice->userbukacetak = auth('api')->user()->name;
                $invoice->jumlahcetak = $invoice->jumlahcetak+1;

                if ($invoice->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoice->getTable()),
                        'postingdari' => 'PRINT INVOICE HEADER',
                        'idtrans' => $invoice->id,
                        'nobuktitrans' => $invoice->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $invoice->toArray(),
                        'modifiedby' => $invoice->modifiedby
                    ];
    
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                    DB::commit();
                }
            }


            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = InvoiceHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }
}
