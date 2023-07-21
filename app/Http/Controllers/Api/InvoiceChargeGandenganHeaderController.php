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
                'nopolisi_detail' => $request->nopolisi_detail,
                'gandengan_detail' => $request->gandengan_detail,
                'tgltrip_detail' => $request->tgltrip_detail,
                'tglkembali_detail' => $request->tglkembali_detail,
                'jumlahhari_detail' => $request->jumlahhari_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail,
                'jenisorder_detail' => $request->jenisorder_detail,
                'namagudang_detail' => $request->namagudang_detail,
            ];
            
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processStore($data);
            $invoiceChargeGandengan->position = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable())->position;
            $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceChargeGandengan
            ], 201);
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoiceChargeGandengan = InvoiceChargeGandenganHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceChargeGandengan->statuscetak != $statusSudahCetak->id) {
                $invoiceChargeGandengan->statuscetak = $statusSudahCetak->id;
                $invoiceChargeGandengan->tglbukacetak = date('Y-m-d H:i:s');
                $invoiceChargeGandengan->userbukacetak = auth('api')->user()->name;
                $invoiceChargeGandengan->jumlahcetak = $invoiceChargeGandengan->jumlahcetak + 1;
                if ($invoiceChargeGandengan->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceChargeGandengan->getTable()),
                        'postingdari' => 'PRINT INVOICE CHARGE GANDENGAN HEADER',
                        'idtrans' => $invoiceChargeGandengan->id,
                        'nobuktitrans' => $invoiceChargeGandengan->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceChargeGandengan->toArray(),
                        'modifiedby' => $invoiceChargeGandengan->modifiedby
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
