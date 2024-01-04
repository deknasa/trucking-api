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
use App\Http\Requests\GetIndexRangeRequest;

use App\Models\InvoiceExtraDetail;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreInvoiceExtraDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;
use Illuminate\Http\JsonResponse;

class InvoiceExtraHeaderController extends Controller
{
    /**
     * @ClassName 
     * InvoiceExtraHeader
     * @Detail1 InvoiceExtraDetailController
     */
    public function index(GetIndexRangeRequest $request)
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreInvoiceExtraHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'nominal' => $request->nominal,
                'agen_id' => $request->agen_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $invoiceExtra = (new InvoiceExtraHeader())->processStore($data);
            $invoiceExtra->position = $this->getPosition($invoiceExtra, $invoiceExtra->getTable())->position;
            if ($request->limit == 0) {
                $invoiceExtra->page = ceil($invoiceExtra->position / (10));
            } else {
                $invoiceExtra->page = ceil($invoiceExtra->position / ($request->limit ?? 10));
            }
            $invoiceExtra->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceExtra->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceExtra
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $data = (new InvoiceExtraHeader)->findAll($id);
        $detail = (new InvoiceExtraDetail)->getAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateInvoiceExtraHeaderRequest $request, InvoiceExtraHeader $invoiceextraheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'nominal' => $request->nominal,
                'agen_id' => $request->agen_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $invoiceExtraHeader = (new InvoiceExtraHeader())->processUpdate($invoiceextraheader, $data);
            $invoiceExtraHeader->position = $this->getPosition($invoiceExtraHeader, $invoiceExtraHeader->getTable())->position;
            if ($request->limit == 0) {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / (10));
            } else {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / ($request->limit ?? 10));
            }
            $invoiceExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceExtraHeader
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
    public function destroy(DestroyInvoiceExtraHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $invoiceExtraHeader = (new InvoiceExtraHeader())->processDestroy($id, 'DELETE INVOICE EXTRA');
            $selected = $this->getPosition($invoiceExtraHeader, $invoiceExtraHeader->getTable(), true);
            $invoiceExtraHeader->position = $selected->position;
            $invoiceExtraHeader->id = $selected->id;
            if ($request->limit == 0) {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / (10));
            } else {
                $invoiceExtraHeader->page = ceil($invoiceExtraHeader->position / ($request->limit ?? 10));
            }
            $invoiceExtraHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceExtraHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceExtraHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
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
                        'penerimaan' => "INVOICE $query->keterangan"
                    ],
                    'message' => "INVOICE $query->keterangan",
                ], 422);
            }
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
        $aksi = request()->aksi ?? '';

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
                ->first();
            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
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
                'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoiceExtraHeader = InvoiceExtraHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceExtraHeader->statuscetak != $statusSudahCetak->id) {
                $invoiceExtraHeader->statuscetak = $statusSudahCetak->id;
                $invoiceExtraHeader->tglbukacetak = date('Y-m-d H:i:s');
                $invoiceExtraHeader->userbukacetak = auth('api')->user()->name;
                $invoiceExtraHeader->jumlahcetak = $invoiceExtraHeader->jumlahcetak + 1;
                if ($invoiceExtraHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceExtraHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA HEADER',
                        'idtrans' => $invoiceExtraHeader->id,
                        'nobuktitrans' => $invoiceExtraHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceExtraHeader->toArray(),
                        'modifiedby' => $invoiceExtraHeader->modifiedby
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

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $invoiceExtra = new InvoiceExtraHeader();
        return response([
            'data' => $invoiceExtra->getExport($id)
        ]);
    }

    public function cekvalidasiAksi($id)
    {
        $invoiceHeader = new InvoiceExtraHeader();
        $nobukti = InvoiceExtraHeader::from(DB::raw("invoiceextraheader"))->where('id', $id)->first();
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
}
