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
use App\Models\Error;
use App\Models\PiutangDetail;
use App\Http\Requests\StoreInvoiceExtraHeaderRequest;
use App\Http\Requests\UpdateInvoiceExtraHeaderRequest;
use App\Http\Requests\DestroyInvoiceExtraHeaderRequest;

use App\Models\InvoiceExtraDetail;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreInvoiceExtraDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;

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
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'invoiceextraheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            /* Store header */
            $invoiceExtraHeader = new InvoiceExtraHeader();
            $invoiceExtraHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceExtraHeader->nominal    = $request->nominal;
            $invoiceExtraHeader->agen_id    = $request->agen_id;
            // $invoiceExtraHeader->pelanggan_id = $request->pelanggan_id;            
            $invoiceExtraHeader->statusapproval = $statusApproval->id;
            $invoiceExtraHeader->userapproval = '';
            $invoiceExtraHeader->tglapproval = '';
            $invoiceExtraHeader->statuscetak = $statusCetak->id;
            $invoiceExtraHeader->statusformat = $format->id;
            $invoiceExtraHeader->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $invoiceExtraHeader->nobukti = $nobukti;

            $invoiceExtraHeader->save();

           

            if ($request->nominal_detail) {

                /* Store detail */
                $detaillog = [];

                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $datadetail = [
                        "invoiceextra_id" => $invoiceExtraHeader->id,
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
                        "id" => $iddetail,
                        "invoiceextra_id" => $invoiceExtraHeader->id,
                        "nobukti" => $invoiceExtraHeader->nobukti,
                        "nominal" => $request->nominal_detail[$i],
                        "keterangan" => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'created_at' => date('d-m-Y H:i:s', strtotime($invoiceExtraHeader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceExtraHeader->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }
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

            $invoiceExtraHeader->piutang_nobukti = $piutang_nobukti;
            $invoiceExtraHeader->save();
            
            $logTrail = [
                'namatabel' => strtoupper($invoiceExtraHeader->getTable()),
                'postingdari' => 'ENTRY INVOICE EXTRA HEADER',
                'idtrans' => $invoiceExtraHeader->id,
                'nobuktitrans' => $invoiceExtraHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $invoiceExtraHeader->toArray(),
                'modifiedby' => $invoiceExtraHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY INVOICE EXTRA DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $invoiceExtraHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

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

            $piutangHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $piutang_nobukti,
                'tglbukti' => date('Y-m-d', strtotime($invoiceExtraHeader->tglbukti)),
                'postingdari' => "ENTRY INVOICE EXTRA",
                'nominal' => $invoiceExtraHeader->nominal,
                'invoice_nobukti' => $invoiceExtraHeader->nobukti,
                'agen_id' => $invoiceExtraHeader->agen_id,
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => 1,
                'datadetail' => $piutangDetail
            ];

            $piutang = new StorePiutangHeaderRequest($piutangHeader);
            app(PiutangHeaderController::class)->store($piutang);


            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($invoiceExtraHeader, $invoiceExtraHeader->getTable());
            $invoiceExtraHeader->position = $selected->position;
            $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceExtraHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(InvoiceExtraHeader $invoiceExtraHeader, $id)
    {
        $data = $invoiceExtraHeader->findAll($id);
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
            /* Store header */

            $invoiceextraheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceextraheader->nominal    = $request->nominal;
            $invoiceextraheader->agen_id    = $request->agen_id;
            // $invoiceextraheader->pelanggan_id = $request->pelanggan_id;
            $invoiceextraheader->modifiedby = auth('api')->user()->name;


            if ($invoiceextraheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceextraheader->getTable()),
                    'postingdari' => 'EDIT INVOICE EXTRA HEADER',
                    'idtrans' => $invoiceextraheader->id,
                    'nobuktitrans' => $invoiceextraheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $invoiceextraheader->toArray(),
                    'modifiedby' => $invoiceextraheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Delete existing detail */

                $penerimaanStokDetail = InvoiceExtraDetail::where('invoiceextra_id', $invoiceextraheader->id)->lockForUpdate()->delete();
                if ($request->nominal_detail) {

                    /* Store detail */
                    $detaillog = [];

                    for ($i = 0; $i < count($request->nominal_detail); $i++) {
                        $datadetail = [
                            "invoiceextra_id" => $invoiceextraheader->id,
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
                            "id" => $iddetail,
                            "invoiceextra_id" => $invoiceextraheader->id,
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
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'EDIT INVOICE EXTRA DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $invoiceextraheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }

                $piutangDetail = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];

                    $detail = [
                        'entriluar' => 1,
                        'nominal' => $request->nominal_detail[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'invoice_nobukti' => $invoiceextraheader->nobukti,
                        'modifiedby' =>  auth('api')->user()->name
                    ];

                    $piutangDetail[] = $detail;
                }

                $piutangHeader = [
                    'proseslain' => 1,
                    'tglbukti' => date('Y-m-d', strtotime($invoiceextraheader->tglbukti)),
                    'postingdari' => "EDIT INVOICE EXTRA",
                    'nominal' => $invoiceextraheader->nominal,
                    'agen_id' => $invoiceextraheader->agen_id,
                    'datadetail' => $piutangDetail
                ];

                $get = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $invoiceextraheader->nobukti)->first();
                $newPiutang = new PiutangHeader();
                $newPiutang = $newPiutang->findUpdate($get->id);
                $piutang = new UpdatePiutangHeaderRequest($piutangHeader);
                app(PiutangHeaderController::class)->update($piutang, $newPiutang);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();
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
    public function destroy(DestroyInvoiceExtraHeaderRequest $request, $id)
    {
        DB::beginTransaction();


        $getDetail = InvoiceExtraDetail::lockForUpdate()->where('invoiceextra_id', $id)->get();

        $request['postingdari'] = "DELETE INVOICE EXTRA";
        $invoiceExtra = new InvoiceExtraHeader();
        $invoiceExtra = $invoiceExtra->lockAndDestroy($id);

        if ($invoiceExtra) {
            $logTrail = [
                'namatabel' => strtoupper($invoiceExtra->getTable()),
                'postingdari' => 'DELETE INVOICE HEADER',
                'idtrans' => $invoiceExtra->id,
                'nobuktitrans' => $invoiceExtra->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $invoiceExtra->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE INVOICE EXTRA DETAIL
            $logTrailInvoiceExtraDetail = [
                'namatabel' => 'INVOICEEXTRADETAIL',
                'postingdari' => 'DELETE INVOICE EXTRA DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $invoiceExtra->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailInvoiceExtraDetail = new StoreLogTrailRequest($logTrailInvoiceExtraDetail);
            app(LogTrailController::class)->store($validatedLogTrailInvoiceExtraDetail);

            $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $invoiceExtra->nobukti)->first();
            app(PiutangHeaderController::class)->destroy($request, $getPiutang->id);

            DB::commit();

            $selected = $this->getPosition($invoiceExtra, $invoiceExtra->getTable(), true);
            $invoiceExtra->position = $selected->position;
            $invoiceExtra->id = $selected->id;
            $invoiceExtra->page = ceil($invoiceExtra->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $invoiceExtra
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }


    /**
     * @ClassName
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->extraId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->extraId); $i++) {
                    $invoice = InvoiceExtraHeader::find($request->extraId[$i]);
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
                            'postingdari' => 'APPROVAL INVOICE EXTRA',
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
                        'penerimaan' => "INVOICE EXTRA $query->keterangan"
                    ],
                    'message' => "INVOICE EXTRA $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function bukaCetak($id)
    {
        DB::beginTransaction();

        try {
            $invoiceExtraHeader = InvoiceExtraHeader::findOrFail($id);
            $statusCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceExtraHeader->statuscetak == $statusCetak->id) {
                $invoiceExtraHeader->statuscetak = $statusBelumCetak->id;
            } else {
                $invoiceExtraHeader->statuscetak = $statusCetak->id;
            }

            $invoiceExtraHeader->tglbukacetak = date('Y-m-d', time());
            $invoiceExtraHeader->userbukacetak = auth('api')->user()->name;

            if ($invoiceExtraHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceExtraHeader->getTable()),
                    'postingdari' => 'BUKA/BELUM CETAK Invoice Extra Header',
                    'idtrans' => $invoiceExtraHeader->id,
                    'nobuktitrans' => $invoiceExtraHeader->nobukti,
                    'aksi' => 'BUKA/BELUM CETAK',
                    'datajson' => $invoiceExtraHeader->toArray(),
                    'modifiedby' => $invoiceExtraHeader->modifiedby
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

    public function cekvalidasi($id)
    {
        $pengeluaran = InvoiceExtraHeader::findOrFail($id);
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoiceExtra = InvoiceExtraHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceExtra->statuscetak != $statusSudahCetak->id) {
                $invoiceExtra->statuscetak = $statusSudahCetak->id;
                $invoiceExtra->tglbukacetak = date('Y-m-d H:i:s');
                $invoiceExtra->userbukacetak = auth('api')->user()->name;
                $invoiceExtra->jumlahcetak = $invoiceExtra->jumlahcetak + 1;
                if ($invoiceExtra->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceExtra->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA',
                        'idtrans' => $invoiceExtra->id,
                        'nobuktitrans' => $invoiceExtra->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceExtra->toArray(),
                        'modifiedby' => $invoiceExtra->modifiedby
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
}
