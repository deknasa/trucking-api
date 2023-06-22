<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvoiceChargeGandenganHeader;
use App\Models\InvoiceChargeGandenganDetail;
use App\Models\Parameter;
use App\Models\Trado;
use App\Http\Requests\StoreInvoiceChargeGandenganHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateInvoiceChargeGandenganHeaderRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreInvoiceChargeGandenganDetailRequest;
use App\Http\Requests\GetIndexRangeRequest;


class InvoiceChargeGandenganHeaderController extends Controller
{
    /**
     * @ClassName 
     * InvoiceChargeGandenganHeader
     * @Detail1 InvoiceChargeGandenganDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $invoice = new InvoiceChargeGandenganHeader();

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
    public function store(StoreInvoiceChargeGandenganHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            $group = 'INVOICE CHARGE GANDENGAN';
            $subgroup = 'INVOICE CHARGE GANDENGAN';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'invoicechargegandenganheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $total = array_sum($request->nominal_detail);
            $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
            $invoiceChargeGandenganHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceChargeGandenganHeader->agen_id = $request->agen_id;
            $invoiceChargeGandenganHeader->tglproses = date('Y-m-d', strtotime($request->tglproses));
            $invoiceChargeGandenganHeader->statusapproval = $statusApproval->id;
            $invoiceChargeGandenganHeader->statuscetak = $statusCetak->id;
            $invoiceChargeGandenganHeader->nominal = $total;
            $invoiceChargeGandenganHeader->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];


            $invoiceChargeGandenganHeader->statusformat = $format->id;
            $invoiceChargeGandenganHeader->nobukti = $nobukti;
            $invoiceChargeGandenganHeader->save();

            if ($invoiceChargeGandenganHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceChargeGandenganHeader->getTable()),
                    'postingdari' => 'ENTRY INVOICE CHARGE GANDENGAN',
                    'idtrans' => $invoiceChargeGandenganHeader->id,
                    'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $invoiceChargeGandenganHeader->toArray(),
                    'modifiedby' => $invoiceChargeGandenganHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                if ($request->nominal_detail) {
                    $total = array_sum($request->nominal_detail);

                    /* Store detail */
                    $detaillog = [];
                    for ($i = 0; $i < count($request->nominal_detail); $i++) {
                        $trado = Trado::where('kodetrado', $request->nopolisi_detail[$i])->firstOrFail();
                        $datadetail = [
                            "invoicechargegandengan_id" => $invoiceChargeGandenganHeader->id,
                            "nobukti" => $invoiceChargeGandenganHeader->nobukti,
                            "jobtrucking_detail" => $request->jobtrucking_detail[$i],
                            "tgltrip_detail" => $request->tgltrip_detail[$i],
                            "jumlahhari_detail" => $request->jumlahhari_detail[$i],
                            "trado_id" => $trado->id,
                            "total" => $total,
                            "nominal_detail" => $request->nominal_detail[$i],
                            "keterangan_detail" => $request->keterangan_detail[$i],
                        ];

                        $data = new StoreInvoiceChargeGandenganDetailRequest($datadetail);
                        $invoiceChargeGandenganDetail = app(InvoiceChargeGandenganDetailController::class)->store($data);

                        if ($invoiceChargeGandenganDetail['error']) {
                            return response($invoiceChargeGandenganDetail, 422);
                        } else {
                            $iddetail = $invoiceChargeGandenganDetail['id'];
                            $tabeldetail = $invoiceChargeGandenganDetail['tabel'];
                        }

                        $datadetaillog = [
                            "id" => $iddetail,
                            "invoicechargegandengan_id" => $invoiceChargeGandenganHeader->id,
                            "nobukti" => $invoiceChargeGandenganHeader->nobukti,
                            "nominal" => $request->nominal_detail[$i],
                            "keterangan" => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($invoiceChargeGandenganHeader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceChargeGandenganHeader->updated_at)),
                        ];
                        $detaillog[] = $datadetail;
                    }

                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY INVOICE EXTRA DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($invoiceChargeGandenganHeader, $invoiceChargeGandenganHeader->getTable());
            $invoiceChargeGandenganHeader->position = $selected->position;
            $invoiceChargeGandenganHeader->page = ceil($invoiceChargeGandenganHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $invoiceChargeGandenganHeader->page = ceil($invoiceChargeGandenganHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceChargeGandenganHeader
            ], 201);

            return response($invoiceChargeGandenganHeader, 422);
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
        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
        return response([
            'status' => true,
            'data' => $invoiceChargeGandenganHeader->find($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateInvoiceChargeGandenganHeaderRequest $request,  $id)
    {
        DB::beginTransaction();
        try {
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $total = array_sum($request->nominal_detail);
            $invoiceChargeGandenganHeader = InvoiceChargeGandenganHeader::findOrFail($id);

            $invoiceChargeGandenganHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $invoiceChargeGandenganHeader->agen_id = $request->agen_id;
            $invoiceChargeGandenganHeader->tglproses = date('Y-m-d', strtotime($request->tglproses));
            $invoiceChargeGandenganHeader->statusapproval = $statusApproval->id;
            $invoiceChargeGandenganHeader->statuscetak = $statusCetak->id;
            $invoiceChargeGandenganHeader->nominal = $total;
            $invoiceChargeGandenganHeader->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $invoiceChargeGandenganHeader->save();
            if ($invoiceChargeGandenganHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($invoiceChargeGandenganHeader->getTable()),
                    'postingdari' => 'ENTRY INVOICE CHARGE GANDENGAN',
                    'idtrans' => $invoiceChargeGandenganHeader->id,
                    'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $invoiceChargeGandenganHeader->toArray(),
                    'modifiedby' => $invoiceChargeGandenganHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                $invoiceChargeGandenganDetail = InvoiceChargeGandenganDetail::where('invoicechargegandengan_id', $invoiceChargeGandenganHeader->id)->lockForUpdate()->delete();
                if ($request->nominal_detail) {
                    $total = array_sum($request->nominal_detail);

                    /* Store detail */
                    $detaillog = [];
                    for ($i = 0; $i < count($request->nominal_detail); $i++) {
                        $trado = Trado::where('kodetrado', $request->nopolisi_detail[$i])->firstOrFail();
                        $datadetail = [
                            "invoicechargegandengan_id" => $invoiceChargeGandenganHeader->id,
                            "nobukti" => $invoiceChargeGandenganHeader->nobukti,
                            "jobtrucking_detail" => $request->jobtrucking_detail[$i],
                            "tgltrip_detail" => $request->tgltrip_detail[$i],
                            "jumlahhari_detail" => $request->jumlahhari_detail[$i],
                            "trado_id" => $trado->id,
                            "total" => $total,
                            "nominal_detail" => $request->nominal_detail[$i],
                            "keterangan_detail" => $request->keterangan_detail[$i],
                        ];

                        $data = new StoreInvoiceChargeGandenganDetailRequest($datadetail);
                        $invoiceChargeGandenganDetail = app(InvoiceChargeGandenganDetailController::class)->store($data);

                        if ($invoiceChargeGandenganDetail['error']) {
                            return response($invoiceChargeGandenganDetail, 422);
                        } else {
                            $iddetail = $invoiceChargeGandenganDetail['id'];
                            $tabeldetail = $invoiceChargeGandenganDetail['tabel'];
                        }

                        $datadetaillog = [
                            "id" => $iddetail,
                            "invoicechargegandengan_id" => $invoiceChargeGandenganHeader->id,
                            "nobukti" => $invoiceChargeGandenganHeader->nobukti,
                            "nominal" => $request->nominal_detail[$i],
                            "keterangan" => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'created_at' => date('d-m-Y H:i:s', strtotime($invoiceChargeGandenganHeader->created_at)),
                            'updated_at' => date('d-m-Y H:i:s', strtotime($invoiceChargeGandenganHeader->updated_at)),
                        ];
                        $detaillog[] = $datadetail;
                    }

                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY INVOICE EXTRA DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }
                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($invoiceChargeGandenganHeader, $invoiceChargeGandenganHeader->getTable());
            $invoiceChargeGandenganHeader->position = $selected->position;
            $invoiceChargeGandenganHeader->page = ceil($invoiceChargeGandenganHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $invoiceChargeGandenganHeader->page = ceil($invoiceChargeGandenganHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceChargeGandenganHeader
            ], 201);

            return response($invoiceChargeGandenganHeader, 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();


        $getDetail = InvoiceChargeGandenganDetail::lockForUpdate()->where('invoicechargegandengan_id', $id)->get();

        $request['postingdari'] = "DELETE INVOICE EXTRA";
        $invoiceChargeGandengan = new InvoiceChargeGandenganHeader();
        $invoiceChargeGandengan = $invoiceChargeGandengan->lockAndDestroy($id);

        if ($invoiceChargeGandengan) {
            $logTrail = [
                'namatabel' => strtoupper($invoiceChargeGandengan->getTable()),
                'postingdari' => 'DELETE INVOICE HEADER',
                'idtrans' => $invoiceChargeGandengan->id,
                'nobuktitrans' => $invoiceChargeGandengan->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $invoiceChargeGandengan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE INVOICE EXTRA DETAIL
            $logTrailInvoiceChargeGandenganDetail = [
                'namatabel' => 'INVOICECHARGEGANDENGANDETAIL',
                'postingdari' => 'DELETE INVOICE CHARGE GANDENGAN DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $invoiceChargeGandengan->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailInvoiceChargeGandenganDetail = new StoreLogTrailRequest($logTrailInvoiceChargeGandenganDetail);
            app(LogTrailController::class)->store($validatedLogTrailInvoiceChargeGandenganDetail);


            DB::commit();

            $selected = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable(), true);
            $invoiceChargeGandengan->position = $selected->position;
            $invoiceChargeGandengan->id = $selected->id;
            $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $invoiceChargeGandengan
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function cekvalidasi($id)
    {
        $InvoiceChargeGandenganHeader = InvoiceChargeGandenganHeader::findOrFail($id);
        $status = $InvoiceChargeGandenganHeader->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $InvoiceChargeGandenganHeader->statuscetak;
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
    public function getinvoicegandengan($id)
    {
        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
        return response([
            'status' => true,
            'data' => $invoiceChargeGandenganHeader->getInvoiceGandengan($id)
        ]);
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
    public function export($id)
    {
        $invoiceChargeGandengan = new InvoiceChargeGandenganHeader();
        return response([
            'data' => $invoiceChargeGandengan->getExport($id)
        ]);
    }
}
