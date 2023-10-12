<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\InvoiceDetailController as ApiInvoiceDetailController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Http\Requests\GetIndexRangeRequest;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class InvoiceHeaderController extends Controller
{
   /**
     * @ClassName 
     * InvoiceHeader
     * @Detail1 InvoiceDetailController
    */
    public function index(GetIndexRangeRequest $request)
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
    public function store(StoreInvoiceHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'tglterima' => $request->tglterima,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'statuspilihaninvoice' => $request->statuspilihaninvoice,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'sp_id' => $request->sp_id,
                'nominalretribusi' => $request->nominalretribusi,
                'nominalextra' => $request->nominalextra,
                'omset' => $request->omset,
                'agen' => $request->agen,
                'jenisorder' => $request->jenisorder
            ];
            $invoiceHeader = (new InvoiceHeader())->processStore($data);
            $invoiceHeader->position = $this->getPosition($invoiceHeader, $invoiceHeader->getTable())->position;
            if ($request->limit==0) {
                $invoiceHeader->page = ceil($invoiceHeader->position / (10));
            } else {
                $invoiceHeader->page = ceil($invoiceHeader->position / ($request->limit ?? 10));
            }
            $invoiceHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $invoice = (new InvoiceHeader)->findAll($id);
        return response([
            'status' => true,
            'data' => $invoice
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateInvoiceHeaderRequest $request, InvoiceHeader $invoiceheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'tglterima' => $request->tglterima,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'agen_id' => $request->agen_id,
                'jenisorder_id' => $request->jenisorder_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'statuspilihaninvoice' => $request->statuspilihaninvoice,
                'sp_id' => $request->sp_id,
                'nominalretribusi' => $request->nominalretribusi,
                'nominalextra' => $request->nominalextra,
                'omset' => $request->omset,
                'agen' => $request->agen,
                'jenisorder' => $request->jenisorder
            ];

            $invoiceHeader = (new InvoiceHeader())->processUpdate($invoiceheader, $data);
            $invoiceHeader->position = $this->getPosition($invoiceHeader, $invoiceHeader->getTable())->position;
            if ($request->limit==0) {
                $invoiceHeader->page = ceil($invoiceHeader->position / (10));
            } else {
                $invoiceHeader->page = ceil($invoiceHeader->position / ($request->limit ?? 10));
            }
            $invoiceHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroyInvoiceHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $invoiceHeader = (new InvoiceHeader())->processDestroy($id, 'DELETE INVOICE');
            $selected = $this->getPosition($invoiceHeader, $invoiceHeader->getTable(), true);
            $invoiceHeader->position = $selected->position;
            $invoiceHeader->id = $selected->id;
            if ($request->limit==0) {
                $invoiceHeader->page = ceil($invoiceHeader->position / (10));
            } else {
                $invoiceHeader->page = ceil($invoiceHeader->position / ($request->limit ?? 10));
            }
            $invoiceHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceHeader
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
        $datahasil=$invoice->getSPSearch($request);
        // $dari = date('Y-m-d', strtotime($request->tgldari));
        // $sampai = date('Y-m-d', strtotime($request->tglsampai));

        // $cekSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
        //     ->whereRaw("agen_id = $request->agen_id")
        //     ->whereRaw("jenisorder_id = $request->jenisorder_id")
        //     ->whereRaw("tglbukti >= '$dari'")
        //     ->whereRaw("tglbukti <= '$sampai'")
        //     ->whereRaw("nocont != ''")
        //     ->whereRaw("noseal != ''")
        //     ->whereRaw("suratpengantar.jobtrucking not in(select orderantrucking_nobukti from invoicedetail)");

        // dd($datahasil);

        if (isset($datahasil)) {
            return response([
                // "data" => $invoice->getSP($request)
                "data" => $datahasil
            ]);
        } else {
            return response([
                // "data" => $invoice->getSP($request)
                "data" => []
            ]);

        }
        // if ($cekSP->first()) {
        //     return response([
        //         // "data" => $invoice->getSP($request)
        //         "data" => $datahasil
        //     ]);
        // } else {
        //     return response([
        //         "data" => []
        //     ]);
        // }
    }

    public function getEdit($id, Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        return response([
            // "data" => $invoice->getEdit($id, $request)
            "data" => $invoice->getSPSearch( $request)
                        
        ]);
    }

    public function getAllEdit($id, Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        return response([
            // "data" => $invoice->getAllEdit($id, $request)
            "data" => $invoice->getSPSearch( $request)
        ]);
    }

    public function comboapproval(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
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
            $invoiceHeader = InvoiceHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceHeader->statuscetak != $statusSudahCetak->id) {
                $invoiceHeader->statuscetak = $statusSudahCetak->id;
                $invoiceHeader->tglbukacetak = date('Y-m-d H:i:s');
                $invoiceHeader->userbukacetak = auth('api')->user()->name;
                $invoiceHeader->jumlahcetak = $invoiceHeader->jumlahcetak + 1;
                if ($invoiceHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE HEADER',
                        'idtrans' => $invoiceHeader->id,
                        'nobuktitrans' => $invoiceHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceHeader->toArray(),
                        'modifiedby' => $invoiceHeader->modifiedby
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
                'kodeerror' =>'SDC',
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
        $invoiceHeader = new InvoiceHeader();
        $nobukti = InvoiceHeader::from(DB::raw("invoiceheader"))->where('id', $id)->first();
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
        $invoiceheader = new InvoiceHeader();
        return response([
            'data' => $invoiceheader->getExport($id)
        ]);
    }
}
