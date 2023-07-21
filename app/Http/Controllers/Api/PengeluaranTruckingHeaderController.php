<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranTruckingHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetInvoiceRequest;
use App\Http\Requests\GetPengeluaranRangeRequest;
use App\Models\PengeluaranTruckingHeader;
use App\Models\AlatBayar;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\InvoiceHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use PhpParser\Node\Stmt\Else_;

class PengeluaranTruckingHeaderController extends Controller
{

       /**
     * @ClassName 
     * PengeluaranTruckingHeader
     * @Detail1 PengeluaranTruckingDetailController
     */
    public function index(GetPengeluaranRangeRequest $request)
    {
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePengeluaranTruckingHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $pengeluaranTruckingHeader = (new PengeluaranTruckingHeader())->processStore($request->all());
            /* Set position and page */
            $pengeluaranTruckingHeader->position = $this->getPosition($pengeluaranTruckingHeader, $pengeluaranTruckingHeader->getTable())->position;
            $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranTruckingHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        DB::beginTransaction();
    }


    public function show($id)
    {

        $data = PengeluaranTruckingHeader::findAll($id);
        $posting = DB::table('parameter')->where('grp', "STATUS POSTING")->where('text', "POSTING")->first();
        $bukanPosting = DB::table('parameter')->where('grp', "STATUS POSTING")->where('text', "BUKAN POSTING")->first();
        $data['postingpinjaman'] = $bukanPosting->id;
        if ($data->pengeluarantrucking_nobukti != "") {
            $data['postingpinjaman'] = $posting->id;
        }
        // dd($data);
        if ($data->kodepengeluaran == 'BST') {
            $pengeluaranTrucking = new PengeluaranTruckingHeader();
            $detail = $pengeluaranTrucking->getShowInvoice($id, $data->periodedari, $data->periodesampai);
        } else {
            $detail = PengeluaranTruckingDetail::getAll($id);
        }
        // if (condition) {
        //     # code...
        // }
        // foreach ($detail as $r ) {
        //     if (isset($r->qty)) {
        //         $pengeluaranstok = DB::table('pengeluaranstokdetail')->where('nobukti',$r->pengeluaranstok_nobukti)->first();
        //         // dd($pengeluaranstok->qty);
        //         $r->maxqty =$pengeluaranstok->qty;
        //         dd($r);
        //     }
        // }
        // dd($details);
        // $datas = array_merge($data, $detail);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePengeluaranTruckingHeaderRequest $request, PengeluaranTruckingHeader $pengeluaranTruckingHeader, $id)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrfail($id);
            $pengeluaranTruckingHeader = (new PengeluaranTruckingHeader())->processUpdate($pengeluaranTruckingHeader, $request->all());
            /* Set position and page */
            $pengeluaranTruckingHeader->position = $this->getPosition($pengeluaranTruckingHeader, $pengeluaranTruckingHeader->getTable())->position;
            $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranTruckingHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function storePinjamanPosting($data)
    {
        // dd($data);
        $fetchFormat = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
            ->where('kodepengeluaran', "PJT")
            ->first();
        if ($fetchFormat->kodepengeluaran != 'BLS') {
            $request['coa'] = $fetchFormat->coapostingdebet;
        }
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();

        $format = DB::table('parameter')
            ->where('grp', $fetchGrp->grp)
            ->where('subgrp', $fetchGrp->subgrp)
            ->first();

        $content = new Request();
        $content['group'] = $fetchGrp->grp;
        $content['subgroup'] = $fetchGrp->subgrp;
        $content['table'] = 'pengeluarantruckingheader';
        $content['tgl'] = date('Y-m-d', strtotime($data['tglbukti']));

        $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
        $data['nobukti'] = $nobukti;
        $data['coa'] = $nobukti;
        $data['statusformat'] = $fetchFormat->id;
        $data["tanpaprosesnobukti"] = 2;
        $store = new StorePengeluaranTruckingHeaderRequest($data);
        // dd($data['nobukti']);

        try {
            $this->store($store);
            return [
                'error' => false,
                'nobukti' => $nobukti
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }


    /**
     * @ClassName
     */
    public function destroy(DestroyPengeluaranTruckingHeaderRequest $request, $id)
    {
        dd($id);
        DB::beginTransaction();
        try {
            /* Store header */
            $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrfail($id);
            $pengeluaranTruckingHeader = (new PengeluaranTruckingHeader())->processDestroy($id, $postingdari = "PENGELUARAN TRUCKING");
            /* Set position and page */
            $pengeluaranTruckingHeader->position = $this->getPosition($pengeluaranTruckingHeader, $pengeluaranTruckingHeader->getTable())->position;
            $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranTruckingHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranTruckingHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranTruckingHeader->statuscetak = $statusSudahCetak->id;
                $pengeluaranTruckingHeader->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaranTruckingHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranTruckingHeader->jumlahcetak = $pengeluaranTruckingHeader->jumlahcetak + 1;
                if ($pengeluaranTruckingHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranTruckingHeader->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN TRUCKING HEADER',
                        'idtrans' => $pengeluaranTruckingHeader->id,
                        'nobuktitrans' => $pengeluaranTruckingHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranTruckingHeader->toArray(),
                        'modifiedby' => $pengeluaranTruckingHeader->modifiedby
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
        $pengeluaran = PengeluaranTruckingHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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


    public function cekValidasiAksi($id)
    {
        $pengeluaran = new PengeluaranTruckingHeader();
        $nobukti = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();
        // $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->pengeluaran_nobukti);

        $PengeluaranTruckingHeader = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
            ->where('kodepengeluaran', "KLAIM")
            ->first();
        if ($klaim->id == $PengeluaranTruckingHeader->pengeluarantrucking_id) {
            $cekdata = $pengeluaran->cekvalidasiklaim($id);
        } else {
            // dd($nobukti->pengeluaran_nobukti);
            $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->nobukti);
            // $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->pengeluaran_nobukti);
        }


        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function getdeposito(Request $request)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $data = $penerimaanTrucking->getDeposito($request->supir);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getpelunasan(Request $request)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $data = $penerimaanTrucking->getPelunasan($request->tgldari, $request->tglsampai);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getEditPelunasan($id, $aksi)
    {
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        $getPelunasan = $pengeluaranTrucking->find($id);
        ///echo json_encode($getPelunasan);die;

        if ($aksi == 'edit') {
            $data = $pengeluaranTrucking->getEditPelunasan($id, $getPelunasan->periodedari, $getPelunasan->periodesampai);
        } else {
            $data = $pengeluaranTrucking->getDeleteEditPelunasan($id, $getPelunasan->periodedari, $getPelunasan->periodesampai);
        }
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getTarikDeposito($id, $aksi)
    {
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        $getSupir = $pengeluaranTrucking->find($id);
        if ($aksi == 'edit') {
            $data = $pengeluaranTrucking->getTarikDeposito($id, $getSupir->supir_id);
        } else {
            $data = $pengeluaranTrucking->getDeleteTarikDeposito($id, $getSupir->supir_id);
        }
        return response([
            'status' => true,
            'data' => $data
        ]);
        // return $pengeluaranTrucking->getTarikDeposito($id);
    }

    public function getInvoice(GetInvoiceRequest $request)
    {
        $tgldari = $request->tgldari;
        $tglsampai = $request->tglsampai;
        $invoiceHeader = new InvoiceHeader();
        $data = $invoiceHeader->getInvoicePengeluaran($tgldari, $tglsampai);
        // $data = $pengeluaranTrucking->getTarikDeposito($pengeluaranTrucking->pengeluarantruckingdetail[0]->supir_id);
        return response([
            'status' => true,
            'data' => $data,
            'attributes' => [
                'totalRows' => $invoiceHeader->totalRows,
                'totalPages' => $invoiceHeader->totalPages,
                'totalNominal' => $invoiceHeader->totalNominal,
            ]
        ]);
    }

    public function getEditInvoice($id)
    {
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        if (request()->aksi == 'show') {
            $data = $pengeluaranTrucking->getShowInvoice($id, request()->tgldari, request()->tglsampai);
        } else {
            $data = $pengeluaranTrucking->getEditInvoice($id, request()->tgldari, request()->tglsampai);
        }

        return response([
            'status' => true,
            'data' => $data,
            'attributes' => [
                'totalRows' => $pengeluaranTrucking->totalRows,
                'totalPages' => $pengeluaranTrucking->totalPages,
                'totalNominal' => $pengeluaranTrucking->totalNominal,
            ]
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluarantruckingheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {}

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
        return response([
            'data' => $pengeluarantruckingheader->getExport($id)
        ]);
    }
}
