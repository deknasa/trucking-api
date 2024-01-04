<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyInvoiceChargeGandenganRequest;
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
     * @Detail InvoiceChargeGandenganDetailController
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreInvoiceChargeGandenganHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tglproses' => $request->tglproses,
                'tgljatuhtempo' => $request->tgljatuhtempo,
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
            if ($request->limit==0) {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / (10));
            } else {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));
            }
            $invoiceChargeGandengan->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceChargeGandengan->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateInvoiceChargeGandenganHeaderRequest $request, InvoiceChargeGandenganHeader $invoicechargegandenganheader): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tglproses' => $request->tglproses,
                'tgljatuhtempo' => $request->tgljatuhtempo,
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
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processUpdate($invoicechargegandenganheader, $data);
            $invoiceChargeGandengan->position = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable())->position;
            if ($request->limit==0) {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / (10));
            } else {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));
            }
            $invoiceChargeGandengan->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceChargeGandengan->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyInvoiceChargeGandenganRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $invoiceChargeGandengan = (new InvoiceChargeGandenganHeader())->processDestroy($id, 'DELETE INVOICE CHARGE GANDENGAN');
            $selected = $this->getPosition($invoiceChargeGandengan, $invoiceChargeGandengan->getTable(), true);
            $invoiceChargeGandengan->position = $selected->position;
            $invoiceChargeGandengan->id = $selected->id;
            if ($request->limit==0) {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / (10));
            } else {
                $invoiceChargeGandengan->page = ceil($invoiceChargeGandengan->position / ($request->limit ?? 10));
            }
            $invoiceChargeGandengan->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceChargeGandengan->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
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
                ->first();

            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $InvoiceChargeGandenganHeader->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
                ->first();

            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $InvoiceChargeGandenganHeader->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];
            return response($data);
        }
    }

    public function cekvalidasiAksi($id)
    {
        $invoiceHeader = new InvoiceChargeGandenganHeader();
        $nobukti = InvoiceChargeGandenganHeader::from(DB::raw("invoicechargegandenganheader"))->where('id', $id)->first();
        $cekdata = $invoiceHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
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
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }


    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $invoiceChargeGandengan = new InvoiceChargeGandenganHeader();
        return response([
            'data' => $invoiceChargeGandengan->getExport($id)
        ]);
    }
}
