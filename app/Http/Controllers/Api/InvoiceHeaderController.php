<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\InvoiceDetailController as ApiInvoiceDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Models\InvoiceHeader;
use App\Http\Requests\StoreInvoiceHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateInvoiceHeaderRequest;
use App\Http\Requests\DestroyInvoiceHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;
use App\Models\Error;
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

            // if ($request->nominalretribusi != '') {
            //     $request->validate([
            //         'nominalretribusi' => 'required|array',
            //         'nominalretribusi.*' => 'required|numeric|gt:0'
            //     ], [
            //         'nominalretribusi.*.numeric' => 'nominal retribusi harus ' . app(ErrorController::class)->geterror('BTSANGKA')->keterangan,
            //         'nominalretribusi.*.gt' => 'nominal retribusi Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
            //     ]);
            // }
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

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $invoice = new InvoiceHeader();
            $invoice->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoice->nominal = '';
            $invoice->tglterima = date('Y-m-d', strtotime($request->tglterima));
            $invoice->tgljatuhtempo = date('Y-m-d');
            $invoice->agen_id = $request->agen_id;
            $invoice->jenisorder_id = $request->jenisorder_id;
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

            /* Store detail */

            $detaillog = [];
            $total = 0;
            for ($i = 0; $i < count($request->sp_id); $i++) {

                $SP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('id', $request->sp_id[$i])->first();
                $orderantrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))
                    ->where('nobukti', $SP->jobtrucking)->first();

                $total = $total + $orderantrucking->nominal + $request->nominalretribusi[$i] + $request->nominalextra[$i];

                $getSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('jobtrucking', $SP->jobtrucking)->get();

                $allSP = "";
                foreach ($getSP as $key => $value) {
                    if ($key == 0) {
                        $allSP = $allSP . $value->nobukti;
                    } else {
                        $allSP = $allSP . ',' . $value->nobukti;
                    }
                }

                $datadetail = [
                    'invoice_id' => $invoice->id,
                    'nobukti' => $invoice->nobukti,
                    'nominal' => $orderantrucking->nominal,
                    'nominalextra' => $request->nominalextra[$i],
                    'nominalretribusi' => $request->nominalretribusi[$i],
                    'total' => $orderantrucking->nominal + $request->nominalretribusi[$i] + $request->nominalextra[$i],
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

                $detaillog[] = $datadetails['detail']->toArray();
            }

            $group = 'PIUTANG BUKTI';
            $subgroup = 'PIUTANG BUKTI';
            $formatPiutang = DB::table('parameter')
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

            // store log header
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
            // store log detail
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY INVOICE DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $invoice->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $invoice->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);



            $piutangDetail[] = [
                'entriluar' => 1,
                'nobukti' => $piutang_nobukti,
                'nominal' => $invoice->nominal,
                'keterangan' => "TAGIHAN INVOICE $request->jenisorder $request->agen periode $request->tgldari s/d $request->tglsampai",
                'invoice_nobukti' => $invoice->nobukti,
                'modifiedby' =>  auth('api')->user()->name
            ];
            // for ($i = 0; $i < count($request->sp_id); $i++) {
            //     $detail = [];

            //     $SP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            //         ->where('id', $request->sp_id[$i])->first();
            //     $orderantrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))
            //         ->where('nobukti', $SP->jobtrucking)->first();

            //     $detail = [];

            //     $piutangDetail[] = $detail;
            // }

            $piutangHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang_nobukti,
                'tglbukti' => date('Y-m-d', strtotime($invoice->tglbukti)),
                'postingdari' => "ENTRY INVOICE",
                'nominal' => $invoice->nominal,
                'invoice_nobukti' => $invoice->nobukti,
                'agen_id' => $invoice->agen_id,
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $formatPiutang->id,
                'jenisinvoice' => 'UTAMA',
                'datadetail' => $piutangDetail
            ];


            $piutang = new StorePiutangHeaderRequest($piutangHeader);
            app(PiutangHeaderController::class)->store($piutang);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
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
            $invoiceheader->nominal = '';
            $invoiceheader->tglterima = date('Y-m-d', strtotime($request->tglterima));
            $invoiceheader->agen_id = $request->agen_id;
            $invoiceheader->jenisorder_id = $request->jenisorder_id;
            $invoiceheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $invoiceheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $invoiceheader->modifiedby = auth('api')->user()->name;


            $invoiceheader->save();

            // $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))
            // ->where('invoice_nobukti', $invoiceheader->nobukti)->first();

            // JurnalUmumHeader::where('nobukti', $getPiutang->nobukti)->delete();
            // PiutangHeader::where('invoice_nobukti', $invoiceheader->nobukti)->delete();
            InvoiceDetail::where('invoice_id', $invoiceheader->id)->delete();

            /* Store detail */
            $detaillog = [];
            $total = 0;
            for ($i = 0; $i < count($request->sp_id); $i++) {

                $SP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('id', $request->sp_id[$i])->first();

                $orderantrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))
                    ->where('nobukti', $SP->jobtrucking)->first();
                $total = $total + $orderantrucking->nominal + $request->nominalretribusi[$i] + $request->nominalextra[$i];
                $getSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                    ->where('jobtrucking', $SP->jobtrucking)->get();

                $allSP = "";
                foreach ($getSP as $key => $value) {
                    if ($key == 0) {
                        $allSP = $allSP . $value->nobukti;
                    } else {
                        $allSP = $allSP . ',' . $value->nobukti;
                    }
                }

                $datadetail = [
                    'invoice_id' => $invoiceheader->id,
                    'nobukti' => $invoiceheader->nobukti,
                    'nominal' => $orderantrucking->nominal,
                    'nominalextra' => $request->nominalextra[$i],
                    'nominalretribusi' => $request->nominalretribusi[$i],
                    'total' => $orderantrucking->nominal + $request->nominalretribusi[$i] + $request->nominalextra[$i],
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


                $detaillog[] = $datadetails['detail']->toArray();
            }


            $invoiceheader->nominal = $total;
            $invoiceheader->save();

            // store log header
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

            // store log detail
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'EDIT INVOICE DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $invoiceheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $invoiceheader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            $piutangDetail[] = [
                'entriluar' => 1,
                'nobukti' => $invoiceheader->piutang_nobukti,
                'nominal' => $invoiceheader->nominal,
                'keterangan' => "TAGIHAN INVOICE $request->jenisorder $request->agen periode $request->tgldari s/d $request->tglsampai",
                'invoice_nobukti' => $invoiceheader->nobukti,
                'modifiedby' =>  auth('api')->user()->name
            ];
            // for ($i = 0; $i < count($request->sp_id); $i++) {
            //     $detail = [];

            //     $SP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            //         ->where('id', $request->sp_id[$i])->first();
            //     $orderantrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))
            //         ->where('nobukti', $SP->jobtrucking)->first();

            //     $detail = [
            //         'entriluar' => 1,
            //         'nominal' => $orderantrucking->nominal + $request->nominalretribusi[$i],
            //         'keterangan' => $SP->keterangan,
            //         'invoice_nobukti' => $invoiceheader->nobukti,
            //         'modifiedby' =>  auth('api')->user()->name
            //     ];

            //     $piutangDetail[] = $detail;
            // }

            $piutangHeader = [
                'proseslain' => 1,
                'tglbukti' => date('Y-m-d', strtotime($invoiceheader->tglbukti)),
                'postingdari' => "EDIT INVOICE",
                'nominal' => $invoiceheader->nominal,
                'agen_id' => $invoiceheader->agen_id,
                'datadetail' => $piutangDetail
            ];

            $get = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('nobukti', $invoiceheader->piutang_nobukti)->first();
            $newPiutang = new PiutangHeader();
            $newPiutang = $newPiutang->findUpdate($get->id);
            $piutang = new UpdatePiutangHeaderRequest($piutangHeader);
            app(PiutangHeaderController::class)->update($piutang, $newPiutang);

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
    public function destroy(DestroyInvoiceHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        $getDetail = InvoiceDetail::lockForUpdate()->where('invoice_id', $id)->get();

        $request['postingdari'] = "DELETE INVOICE";
        $invoice = new InvoiceHeader();
        $invoice = $invoice->lockAndDestroy($id);

        if ($invoice) {
            $logTrail = [
                'namatabel' => strtoupper($invoice->getTable()),
                'postingdari' => 'DELETE INVOICE HEADER',
                'idtrans' => $invoice->id,
                'nobuktitrans' => $invoice->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $invoice->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE INVOICE DETAIL
            $logTrailInvoiceDetail = [
                'namatabel' => 'INVOICEDETAIL',
                'postingdari' => 'DELETE INVOICE DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $invoice->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailInvoiceDetail = new StoreLogTrailRequest($logTrailInvoiceDetail);
            app(LogTrailController::class)->store($validatedLogTrailInvoiceDetail);

            $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('nobukti', $invoice->piutang_nobukti)->first();
            app(PiutangHeaderController::class)->destroy($request, $getPiutang->id);

            DB::commit();

            $selected = $this->getPosition($invoice, $invoice->getTable(), true);
            $invoice->position = $selected->position;
            $invoice->id = $selected->id;
            $invoice->page = ceil($invoice->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $invoice
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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

        $cekSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
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
            return response([
                "data" => []
            ]);
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

    public function getAllEdit($id, Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        return response([
            "data" => $invoice->getAllEdit($id, $request)
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
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
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



            $detailLogPiutang = [];
            $detailLogJurnal = [];
            foreach ($piutangDetail as $value) {

                $value['piutang_id'] = $header->original['data']['id'];
                $value['entridetail'] = 1;
                $value['jurnal_id'] = $header->original['idlogtrail']['jurnal_id'];
                $value['tglbukti'] = $piutangHeader['tglbukti'];
                $piutangDetail = new StorePiutangDetailRequest($value);
                $detailPiutang = app(PiutangDetailController::class)->store($piutangDetail);

                $detailLogPiutang[] = $detailPiutang['detail']['piutangdetail']->toArray();
                $detailLogJurnal = array_merge($detailLogJurnal, $detailPiutang['detail']['jurnaldetail']);
            }

            $datalogtrail = [
                'namatabel' => strtoupper($detailPiutang['tabel']),
                'postingdari' => 'ENTRY INVOICE HEADER',
                'idtrans' =>  $header->original['idlogtrail']['piutang'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogPiutang,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $datalogtrail = [
                'namatabel' => strtoupper('JURNALUMUMDETAIL'),
                'postingdari' => 'ENTRY PIUTANG DARI INVOICE ',
                'idtrans' =>  $header->original['idlogtrail']['jurnal'],
                'nobuktitrans' => $nobukti,
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


    /**
     * @ClassName
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->invoiceId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->invoiceId); $i++) {
                    $invoice = InvoiceHeader::find($request->invoiceId[$i]);
                    if ($invoice->statusapproval == $statusApproval->id) {
                        $invoice->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $invoice->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $invoice->tglapproval = date('Y-m-d', time());
                    $invoice->userapproval = auth('api')->user()->name;

                    if ($invoice->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($invoice->getTable()),
                            'postingdari' => 'APPROVAL INVOICE',
                            'idtrans' => $invoice->id,
                            'nobuktitrans' => $invoice->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $invoice->toArray(),
                            'modifiedby' => auth('api')->user()->name
                        ];

                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    }
                }
                DB::commit();
                return response([
                    'message' => 'Berhasil'
                ]);
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'penerimaan' => "INVOICE $query->keterangan"
                    ],
                    'message' => "INVOICE $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoice = InvoiceHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoice->statuscetak != $statusSudahCetak->id) {
                $invoice->statuscetak = $statusSudahCetak->id;
                $invoice->tglbukacetak = date('Y-m-d H:i:s');
                $invoice->userbukacetak = auth('api')->user()->name;
                $invoice->jumlahcetak = $invoice->jumlahcetak + 1;

                if ($invoice->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoice->getTable()),
                        'postingdari' => 'PRINT INVOICE HEADER',
                        'idtrans' => $invoice->id,
                        'nobuktitrans' => $invoice->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $invoice->toArray(),
                        'modifiedby' => auth('api')->user()->name,
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
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
                ->first();
            // $keterangan = $query['0'];
            $keterangan = [
                'keterangan' => 'No Bukti '.$pengeluaran->nobukti.' '.$query->keterangan
            ];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
                ->first();
            // $keterangan = $query['0'];
            //  dd($query->keterangan);
            $keterangan = [
                'keterangan' => 'No Bukti '.$pengeluaran->nobukti.' '.$query->keterangan
            ];
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
    /**
     * @ClassName
     */
    public function report()
    {
    }

    /**
     * @ClassName
     */
    public function export()
    {
    }
}
