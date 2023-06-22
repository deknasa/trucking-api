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
use App\Models\Error;
use Illuminate\Http\JsonResponse;

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
    public function store(StoreInvoiceChargeGandenganHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tglproses' => $request->tglproses,
                'id_detail' => $request->id_detail,
                'jobtrucking_detail' => $request->jobtrucking_detail,
                'tgltrip_detail' => $request->tgltrip_detail,
                'jumlahhari_detail' => $request->jumlahhari_detail,
                'nopolisi_detail' => $request->nopolisi_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail
            ];
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processStore($data);
            $invoiceChargeGandengan->position = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable())->position;
            $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));

            DB::commit();

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
                'data' => $invoiceChargeGandengan
            ], 201);
<<<<<<< HEAD

            return response($invoiceChargeGandenganHeader, 422);
=======
>>>>>>> a8acf1ecf07bf2c9c32e09b26f20fb000ded3599
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
        return response([
            'status' => true,
            'data' => $invoiceChargeGandenganHeader->find($id),
            'detail' => $invoiceChargeGandenganHeader->getInvoiceGandengan($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateInvoiceChargeGandenganHeaderRequest $request, InvoiceChargeGandenganHeader $invoicechargegandenganheader): JsonResponse
    {
        DB::beginTransaction();
        try {

<<<<<<< HEAD
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
=======
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tglproses' => $request->tglproses,
                'id_detail' => $request->id_detail,
                'jobtrucking_detail' => $request->jobtrucking_detail,
                'tgltrip_detail' => $request->tgltrip_detail,
                'jumlahhari_detail' => $request->jumlahhari_detail,
                'nopolisi_detail' => $request->nopolisi_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail
            ];
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processUpdate($invoicechargegandenganheader, $data);
            $invoiceChargeGandengan->position = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable())->position;
            $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceChargeGandengan
            ]);
>>>>>>> a8acf1ecf07bf2c9c32e09b26f20fb000ded3599
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processDestroy($id, 'DELETE INVOICE CHARGE GANDENGAN');
            $selected = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable(), true);
            $invoiceChargeGandengan->position = $selected->position;
            $invoiceChargeGandengan->id = $selected->id;
            $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceChargeGandengan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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
