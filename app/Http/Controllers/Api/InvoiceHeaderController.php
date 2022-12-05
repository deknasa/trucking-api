<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\InvoiceDetailController as ApiInvoiceDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Models\InvoiceHeader;
use App\Http\Requests\StoreInvoiceHeaderRequest;
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
                $total = $total + $SP->omset;
                $getSP = SuratPengantar::where('jobtrucking', $SP->jobtrucking)->get();

                $allSP = "";
                foreach($getSP as $value){
                   $allSP = $allSP. $value->nobukti .', ';
                }
                $datadetail = [
                    'invoice_id' => $invoice->id,
                    'nobukti' => $invoice->nobukti,
                    'nominal' => $SP->omset,
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
                    'nominal' => $SP->omset,
                    'keterangan' => $SP->keterangan,
                    'orderantrucking_nobukti' => $SP->jobtrucking,
                    'suratpengantar_nobukti' => $allSP,
                    'modifiedby' => $invoice->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($invoice->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($invoice->updated_at)),

                ];


                $detaillog[] = $datadetaillog;

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY INVOICE DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $invoice->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $invoice->modifiedby,
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
                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $piutang_nobukti,
                    'nominal' => $SP->omset,
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
                    $total = $total + $SP->omset;
                    $getSP = SuratPengantar::where('jobtrucking', $SP->jobtrucking)->get();

                    $allSP = "";
                    foreach($getSP as $value){
                       $allSP = $allSP. $value->nobukti .', ';
                    }

                    $datadetail = [
                        'invoice_id' => $invoiceheader->id,
                        'nobukti' => $invoiceheader->nobukti,
                        'nominal' => $SP->omset,
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
                        'nominal' => $value->omset,
                        'keterangan' => $value->keterangan,
                        'orderantrucking_nobukti' => $value->jobtrucking,
                        'suratpengantar_nobukti' => $allSP,
                        'modifiedby' => $invoiceheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($invoiceheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceheader->updated_at)),

                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT INVOICE DETAIL',
                        'idtrans' =>  $iddetail,
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
                        $detail = [
                            'entriluar' => 1,
                            'nobukti' => $piutang_nobukti,
                            'nominal' => $SP->omset,
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
                    'modifiedby' => $invoiceheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

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
            }
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

    public function getEdit($id)
    {
        $invoice = new InvoiceHeader();
        return response([
            "data" => $invoice->getEdit($id)
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

    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $invoice = InvoiceHeader::find($id);
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($invoice->statusapproval == $statusApproval->id) {
                $invoice->statusapproval = $statusNonApproval->id;
            } else {
                $invoice->statusapproval = $statusApproval->id;
            }

            $invoice->tglapproval = date('Y-m-d H:i:s');
            $invoice->userapproval = auth('api')->user()->name;

            if ($invoice->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoice->getTable()),
                    'postingdari' => 'UN/APPROVE Invoice',
                    'idtrans' => $invoice->id,
                    'nobuktitrans' => $invoice->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $invoice->toArray(),
                    'modifiedby' => $invoice->modifiedby
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

    public function cekapproval($id)
    {
        $invoice = InvoiceHeader::find($id);
        $status = $invoice->statusapproval;

        if ($status == '3') {
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
