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
use App\Models\SuratPengantar;
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
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\Supir;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpParser\Node\Stmt\Else_;

class PengeluaranTruckingHeaderController extends Controller
{

    /**
     * @ClassName 
     * PengeluaranTruckingHeader
     * @Detail PengeluaranTruckingDetailController
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranTruckingHeaderRequest $request): JsonResponse
    {
        // dd($request);
        DB::beginTransaction();
        try {
            /* Store header */
            $idpengeluaran = request()->pengeluarantrucking_id;
            $fetchFormat =  DB::table('pengeluarantrucking')->where('id', $idpengeluaran)->first();

            $keterangan = $request->keterangan;
            $nojobtrucking_detail = $request->nojobtrucking_detail;
            $noinvoice_detail = $request->noinvoice_detail;
            $nominal = $request->nominal;

            if ($fetchFormat->kodepengeluaran == "BST" || $fetchFormat->kodepengeluaran == "OTOK" || $fetchFormat->kodepengeluaran == "OTOL") {
                $detail = json_decode($request->detail);

                $keterangan = $detail->keterangan;
                $nojobtrucking_detail = $detail->nojobtrucking_detail;
                $noinvoice_detail = $detail->noinvoice_detail;
                $nominal = $detail->nominal;
            }
            if ($fetchFormat->kodepengeluaran == "KLAIM") {
                $statusPosting = DB::table(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
                $request->postingpinjaman = $statusPosting->id;
            }

            if ($fetchFormat->kodepengeluaran != "PJT" && $fetchFormat->kodepengeluaran != "PJK") {
                $statusPosting = DB::table(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
                $request->statusposting = $statusPosting->id;
            }

            $pengeluaranTruckingHeader = (new PengeluaranTruckingHeader())->processStore([
                'pengeluarantrucking_id' => $request->pengeluarantrucking_id,
                "supirheader_id" => $request->supirheader_id,
                "karyawanheader_id" => $request->karyawanheader_id,
                "tradoheader_id" => $request->tradoheader_id,
                "gandenganheader_id" => $request->gandenganheader_id,
                "statuscabang" => $request->statuscabang,
                "bank_id" => $request->bank_id,
                "agen_id" => $request->agen_id,
                "containerheader_id" => $request->containerheader_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "statusapproval" => $request->statusapproval,
                "statusposting" => $request->statusposting,
                "postingpinjaman" => $request->postingpinjaman,
                "periode" => $request->periode,
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "jenisorderan_id" => $request->jenisorderan_id,
                'coa' => $request->coa,
                "pengeluaran_nobukti" => $request->pengeluaran_nobukti,
                "nominalpenarikan" => $request->nominalpenarikan,
                "keterangan_header" => $request->keterangan_header,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "supir_id" => $request->supir_id,
                "trado_id" => $request->trado_id,
                "suratpengantar_nobukti" => $request->suratpengantar_nobukti,
                "statustitipanemkl" => $request->detail_statustitipanemkl,
                "container_id" => $request->container_id,
                "pelanggan_id" => $request->pelanggan_id,
                "karyawan_id" => $request->karyawan_id,
                "penerimaantruckingheader_nobukti" => $request->penerimaantruckingheader_nobukti,
                "statusformat" => $request->statusformat,
                "qty" => $request->qty,
                "stok_id" => $request->stok_id,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
                "harga" => $request->harga,
                "nominaltagih" => $request->nominaltagih,
                "nominaltambahan" => $request->nominaltambahan,
                "keterangantambahan" => $request->keterangantambahan,
                "nominal" => $nominal,
                "jenisorder_id" => $request->jenisorder_id,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                "coakredit" => $request->coakredit,
                "keterangan" => $keterangan,
                "noinvoice_detail" => $noinvoice_detail,
                "nojobtrucking_detail" => $nojobtrucking_detail,
                "bank_detail" => $request->bank_detail,
                "pemutihansupir_nobukti" => $request->pemutihansupir_nobukti,
            ]);
            /* Set position and page */
            if ($request->button == 'btnSubmit') {

                $pengeluaranTruckingHeader->position = $this->getPosition($pengeluaranTruckingHeader, $pengeluaranTruckingHeader->getTable())->position;
                if ($request->limit == 0) {
                    $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / (10));
                } else {
                    $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
                }
                $pengeluaranTruckingHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $pengeluaranTruckingHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
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
        // $posting = DB::table('parameter')->where('grp', "STATUS POSTING")->where('text', "POSTING")->first();
        $data['postingpinjaman'] = $data->statusposting;
        // dd($data);
        if ($data->kodepengeluaran == 'BST') {
            $pengeluaranTrucking = new PengeluaranTruckingHeader();
            $detail = $pengeluaranTrucking->getShowInvoice($id, $data->periodedari, $data->periodesampai);
        } else if ($data->kodepengeluaran == 'OTOK') {
            $pengeluaranTrucking = new PengeluaranTruckingHeader();
            request()->limit = 0;
            $detail = $pengeluaranTrucking->getEditOtok('show', $id, $data->periodedari, $data->periodesampai, $data->agen_id, $data->containerheader_id);
        } else if ($data->kodepengeluaran == 'OTOL') {
            $pengeluaranTrucking = new PengeluaranTruckingHeader();
            request()->limit = 0;
            $detail = $pengeluaranTrucking->getEditOtol('show', $id, $data->periodedari, $data->periodesampai, $data->agen_id, $data->containerheader_id);
        } else {
            $detail = PengeluaranTruckingDetail::getAll($id, $data->kodepengeluaran);
        }
        // $details = [];
        // foreach ($detail as $r ) {
        //     // $r->surat
        //     if ($r->suratpengantar_nobukti) {
        //         $suratpengantar =  DB::table('saldosuratpengantar')->from(
        //             DB::raw("suratpengantar with (readuncommitted)")
        //         )->where('suratpengantar.nobukti',$r->suratpengantar_nobukti);
        //         if (!$suratpengantar->first()) {
        //             $suratpengantar = DB::table('saldosuratpengantar')->from(
        //                 DB::raw("saldosuratpengantar suratpengantar with (readuncommitted)")
        //             )->where('suratpengantar.nobukti',$r->suratpengantar_nobukti);
        //         }
        //         $suratpengantar->select(
        //             'pelanggan.namapelanggan as pelanggan_id',
        //             'jenisorder.keterangan as jenisorder_id',
        //             'container.keterangan as container_id',
        //         )
        //         ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
        //         ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'suratpengantar.pelanggan_id', 'pelanggan.id')
        //         ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'suratpengantar.jenisorder_id', 'jenisorder.id');
        //         // dd($suratpengantar->toSql());
        //         $sp = $suratpengantar->first();
        //         $r->container_id = $sp->container_id;
        //         $r->pelanggan_id = $sp->pelanggan_id;
        //         $r->jenisorder_id = $sp->jenisorder_id;
        //         $details[] =  $r;
        //     }
        //     // $details[] = $r;
        // }


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
    public function update(UpdatePengeluaranTruckingHeaderRequest $request, PengeluaranTruckingHeader $pengeluaranTruckingHeader, $id)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $idpengeluaran = request()->pengeluarantrucking_id;
            $fetchFormat =  DB::table('pengeluarantrucking')->where('id', $idpengeluaran)->first();

            $keterangan = $request->keterangan;
            $nojobtrucking_detail = $request->nojobtrucking_detail;
            $noinvoice_detail = $request->noinvoice_detail;
            $nominal = $request->nominal;

            if ($fetchFormat->kodepengeluaran == "BST" || $fetchFormat->kodepengeluaran == "OTOK" || $fetchFormat->kodepengeluaran == "OTOL") {
                $detail = json_decode($request->detail);

                $keterangan = $detail->keterangan;
                $nojobtrucking_detail = $detail->nojobtrucking_detail;
                $noinvoice_detail = $detail->noinvoice_detail;
                $nominal = $detail->nominal;
            }
            if ($fetchFormat->kodepengeluaran == "KLAIM") {
                $statusPosting = DB::table(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
                $request->postingpinjaman = $statusPosting->id;
            }


            if ($fetchFormat->kodepengeluaran != "PJT" && $fetchFormat->kodepengeluaran != "PJK") {
                $statusPosting = DB::table(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
                $request->statusposting = $statusPosting->id;
            }


            $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrfail($id);
            $pengeluaranTruckingHeader = (new PengeluaranTruckingHeader())->processUpdate($pengeluaranTruckingHeader, [

                'pengeluarantrucking_id' => $request->pengeluarantrucking_id,
                "supirheader_id" => $request->supirheader_id,
                "tradoheader_id" => $request->tradoheader_id,
                "karyawanheader_id" => $request->karyawanheader_id,
                "gandenganheader_id" => $request->gandenganheader_id,
                "statuscabang" => $request->statuscabang,
                "bank_id" => $request->bank_id,
                "agen_id" => $request->agen_id,
                "containerheader_id" => $request->containerheader_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "statusapproval" => $request->statusapproval,
                "statusposting" => $request->statusposting,
                "postingpinjaman" => $request->postingpinjaman,
                "periode" => $request->periode,
                "tgldari" => $request->tgldari,
                "tglsampai" => $request->tglsampai,
                "jenisorderan_id" => $request->jenisorderan_id,
                "nominalpenarikan" => $request->nominalpenarikan,
                "keterangan_header" => $request->keterangan_header,
                'coa' => $request->coa,
                "pengeluaran_nobukti" => $request->pengeluaran_nobukti,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "supir_id" => $request->supir_id,
                "trado_id" => $request->trado_id,
                "suratpengantar_nobukti" => $request->suratpengantar_nobukti,
                "statustitipanemkl" => $request->detail_statustitipanemkl,
                "container_id" => $request->container_id,
                "pelanggan_id" => $request->pelanggan_id,
                "karyawan_id" => $request->karyawan_id,
                "penerimaantruckingheader_nobukti" => $request->penerimaantruckingheader_nobukti,
                "statusformat" => $request->statusformat,
                "qty" => $request->qty,
                "stok_id" => $request->stok_id,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
                "harga" => $request->harga,
                "nominaltagih" => $request->nominaltagih,
                "nominaltambahan" => $request->nominaltambahan,
                "keterangantambahan" => $request->keterangantambahan,
                "nominal" => $nominal,
                "jenisorder_id" => $request->jenisorder_id,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                "coakredit" => $request->coakredit,
                "keterangan" => $keterangan,
                "noinvoice_detail" => $noinvoice_detail,
                "nojobtrucking_detail" => $nojobtrucking_detail,
                "bank_detail" => $request->bank_detail,
            ]);
            /* Set position and page */
            $pengeluaranTruckingHeader->position = $this->getPosition($pengeluaranTruckingHeader, $pengeluaranTruckingHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / (10));
            } else {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranTruckingHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranTruckingHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyPengeluaranTruckingHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $pengeluaranTruckingHeader = PengeluaranTruckingHeader::findOrfail($id);
            $pengeluaranTruckingHeader = (new PengeluaranTruckingHeader())->processDestroy($id, $postingdari = "PENGELUARAN TRUCKING");
            /* Set position and page */
            $selected = $this->getPosition($pengeluaranTruckingHeader, $pengeluaranTruckingHeader->getTable(), true);
            $pengeluaranTruckingHeader->position = $selected->position;
            $pengeluaranTruckingHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / (10));
            } else {
                $pengeluaranTruckingHeader->page = ceil($pengeluaranTruckingHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranTruckingHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranTruckingHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


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
                // $pengeluaranTruckingHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $pengeluaranTruckingHeader->userbukacetak = auth('api')->user()->name;
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
        $pengeluarantrucking = PengeluaranTruckingHeader::find($id);
        $nobukti = $pengeluarantrucking->nobukti ?? '';
        $status = $pengeluarantrucking->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluarantrucking->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $aksi = request()->aksi;
        $pengeluarantrucking_id = $pengeluarantrucking->pengeluarantrucking_id;
        $aco_id = db::table("pengeluarantrucking")->from(db::raw("pengeluarantrucking a with (readuncommitted)"))
            ->select(
                'a.aco_id'
            )->where('a.id', $pengeluarantrucking_id)
            ->first()->aco_id ?? 0;

        $user_id = auth('api')->user()->id;
        $user = auth('api')->user()->user;
        $role = db::table("userrole")->from(db::raw("userrole a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->join(db::raw("acl b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->where('a.user_id', $user_id)
            ->where('b.aco_id', $aco_id)
            // ->tosql();
            ->first();

        if ($aksi == 'EDIT' || $aksi == 'DELETE') {

            if (!isset($role)) {
                $acl = db::table('useracl')->from(db::raw("useracl a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )->where('a.user_id', $user_id)
                    ->where('a.aco_id', $aco_id)
                    ->first();

                if (!isset($acl)) {
                    $query = DB::table('error')
                        ->select(db::raw("'USER " . $user . " '+keterangan as keterangan"))
                        ->where('kodeerror', '=', 'TPH')
                        ->first();

                    $data = [
                        'error' => true,
                        'message' => $query->keterangan,
                        'kodeerror' => 'TPH',
                        'statuspesan' => 'warning',
                    ];
                    $passes = false;
                    return response($data);
                }
            }
        }

        $pengeluaran = $pengeluarantrucking->pengeluaran_nobukti ?? '';
        // dd($pengeluaran);
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';

        if ($idpengeluaran != 0 && ($aksi == 'EDIT' || $aksi == 'DELETE')) {
            $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
            $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut;
            } else {
                return $validasipengeluaran;
            }
        }





        lanjut:

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pengeluarantruckingheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;


            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('PENGELUARAN TRUCKING');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'pengeluarantruckingheader', $useredit);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {
                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                    // 'force' => $force
                ];

                return response($data);
            }
        } else {

            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->createLockEditing($id, 'pengeluarantruckingheader', $useredit);
            }

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }


    public function cekValidasiAksi($id)
    {
        $pengeluaran = new PengeluaranTruckingHeader();
        $nobukti = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();
        // $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->pengeluaran_nobukti);

        $pengeluaranNobukti = $nobukti->pengeluaran_nobukti ?? '';
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaranNobukti)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';
        // $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
        // $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
        // if ($msg == false) {
        //     goto lanjut;
        // } else {
        //     return $validasipengeluaran;
        // }


        lanjut:
        $PengeluaranTruckingHeader = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();
        $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
            ->where('kodepengeluaran', "KLAIM")
            ->first();
        // if ($klaim->id == $PengeluaranTruckingHeader->pengeluarantrucking_id) {
        //     $cekdata = $pengeluaran->cekvalidasiklaim($id);
        // } else {
        // dd($nobukti->pengeluaran_nobukti);
        $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->nobukti);
        // $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->pengeluaran_nobukti);
        // }

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        if ($cekdata['kondisi'] == true) {
            $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;


            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('pengeluarantruckingheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'pengeluarantruckingheader', $useredit);
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
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

    public function getDepositoKaryawan(Request $request)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $data = $penerimaanTrucking->getDepositoKaryawan($request->karyawan_id);
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
        $tgldari = (request()->tgldari != '') ? request()->tgldari : $getPelunasan->periodedari;
        $tglsampai = (request()->tglsampai != '') ? request()->tglsampai : $getPelunasan->periodesampai;
        if ($aksi == 'edit') {
            $data = $pengeluaranTrucking->getEditPelunasan($id, $tgldari, $tglsampai);
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

    public function getTarikDepositoKaryawan($id, $aksi)
    {
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        $getKaryawan = $pengeluaranTrucking->find($id);
        if ($aksi == 'edit') {
            $data = $pengeluaranTrucking->getTarikDepositoKaryawan($id, $getKaryawan->karyawan_id);
        } else {
            $data = $pengeluaranTrucking->getDeleteTarikDepositoKaryawan($id, $getKaryawan->karyawan_id);
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
        // dd($invoiceHeader->totalNominal);
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

    public function getbiayalapangan(Request $request)
    {
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
        return response([
            'data' => $pengeluarantruckingheader->getBiayaLapangan(),
        ]);
    }

    public function getOtok(Request $request)
    {
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));
        $agen_id = $request->agen_id;
        $container_id = $request->container_id;
        $invoiceHeader = new InvoiceHeader();
        $data = $invoiceHeader->getInvoiceOtok($tgldari, $tglsampai, $agen_id, $container_id);
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
    public function getEditOtok($id)
    {
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        if (request()->aksi == 'show') {
            $data = $pengeluaranTrucking->getEditOtok(request()->aksi, $id, request()->tgldari, request()->tglsampai, request()->agen_id, request()->container_id);
        } else {
            $data = $pengeluaranTrucking->getEditOtok(request()->aksi, $id, request()->tgldari, request()->tglsampai, request()->agen_id, request()->container_id);
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


    public function getOtol(Request $request)
    {
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));
        $agen_id = $request->agen_id;
        $container_id = $request->container_id;
        $invoiceHeader = new InvoiceHeader();
        $data = $invoiceHeader->getInvoiceOtol($tgldari, $tglsampai, $agen_id, $container_id);
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
    public function getEditOtol($id)
    {
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        if (request()->aksi == 'show') {
            $data = $pengeluaranTrucking->getEditOtol(request()->aksi, $id, request()->tgldari, request()->tglsampai, request()->agen_id, request()->container_id);
        } else {
            $data = $pengeluaranTrucking->getEditOtol(request()->aksi, $id, request()->tgldari, request()->tglsampai, request()->agen_id, request()->container_id);
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
     * @Keterangan CETAK DATA
     */
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $pengeluaranTruckingHeader = new PengeluaranTruckingHeader();
        $pengeluaran_TruckingHeader = $pengeluaranTruckingHeader->getExport($id);

        if ($request->export == true) {
            $pengeluaranTruckingDetail = new PengeluaranTruckingDetail();
            $pengeluaran_TruckingDetail = $pengeluaranTruckingDetail->get();

            $styleArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            );

            $style_number = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],

                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            ];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pengeluaran_TruckingHeader->judul);
            $sheet->setCellValue('A2', $pengeluaran_TruckingHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:G1');
            $sheet->mergeCells('A2:G2');

            $alphabets = range('A', 'Z');

            switch ($pengeluaran_TruckingHeader->statusformat) {
                case '122':
                    //PJT
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'SUPIR',
                            'index' => 'supir_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }

                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->supir_id);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('C')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalKredit)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (PJT)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '251':
                    //TDE
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 9;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],

                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ]
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Supir',
                            'index' => 'supir',
                        ],
                        [
                            'label' => 'Trado',
                            'index' => 'trado',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI PENERIMAAN TRUCKING',
                            'index' => 'penerimaantruckingheader_nobukti',
                        ],
                        [
                            'label' => 'SUPIR',
                            'index' => 'supir_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }

                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->penerimaantruckingheader_nobukti);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->supir_id);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("E$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('D')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(E" . ($detail_table_header_row + 1) . ":E" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("E$total_start_row", $totalKredit)->getStyle("E$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("E$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELURAN TRUCKING (TDE)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '289':
                    //BST
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 9;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],

                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ]
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Periode Dari',
                            'index' => 'periodedari',
                        ],
                        [
                            'label' => 'Periode Sampai',
                            'index' => 'periodesampai',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        if ($header_right_column['index'] == 'periodedari' || $header_right_column['index'] == 'periodesampai') {
                            $pengeluaran_TruckingHeader->{$header_right_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_right_column['index']}));
                        }
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI INVOICE',
                            'index' => 'invoice_nobukti',
                        ],
                        [
                            'label' => 'NO ORDERAN TRUCKING',
                            'index' => 'orderantrucking_nobukti',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->invoice_nobukti);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->orderantrucking_nobukti);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("E$detail_start_row", $response_detail->nominal);

                        $sheet->getColumnDimension('D')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(E" . ($detail_table_header_row + 1) . ":E" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("E$total_start_row", $totalKredit)->getStyle("E$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("E$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (BST)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;

                case '297':
                    //BSB
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'SUPIR',
                            'index' => 'supir_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->supir_id);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('C')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalKredit)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (BSB)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '298':
                    //KBBM
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 9;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],

                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ]
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Periode Dari',
                            'index' => 'periodedari',
                        ],
                        [
                            'label' => 'Periode Sampai',
                            'index' => 'periodesampai',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        if ($header_right_column['index'] == 'periodedari' || $header_right_column['index'] == 'periodesampai') {
                            $pengeluaran_TruckingHeader->{$header_right_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_right_column['index']}));
                        }
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI PENERIMAAN TRUCKING',
                            'index' => 'penerimaantruckingheader_nobukti',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }

                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->penerimaantruckingheader_nobukti);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                        $sheet->getColumnDimension('C')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalKredit)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (KBBM)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '279':
                    //BLS
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'SUPIR',
                            'index' => 'supir_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->supir_id);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('C')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalKredit)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (BLS)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '318':
                    //KLAIM
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluarantrucking_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Supir',
                            'index' => 'supir',
                        ],
                        [
                            'label' => 'Trado',
                            'index' => 'trado',
                        ],

                    ];
                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI PENGELUARAN STOK',
                            'index' => 'pengeluaranstok_nobukti',
                        ],
                        [
                            'label' => 'STOK',
                            'index' => 'stok_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'HARGA',
                            'index' => 'harga',
                            'format' => 'currency'
                        ],
                        [
                            'label' => 'QTY',
                            'index' => 'qty',
                            'format' => 'currency'
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->pengeluaranstok_nobukti);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->stok_id);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("E$detail_start_row", $response_detail->harga);
                        $sheet->setCellValue("F$detail_start_row", $response_detail->qty);
                        $sheet->setCellValue("G$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('D')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row:G$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':F' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':F' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(G" . ($detail_table_header_row + 1) . ":G" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("G$total_start_row", $totalKredit)->getStyle("G$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("G$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $sheet->getColumnDimension('F')->setAutoSize(true);
                    $sheet->getColumnDimension('G')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (KLAIM)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '369':
                    //PJK
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ]

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'KARYAWAN',
                            'index' => 'karyawan_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }

                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->karyawan_id);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                        $sheet->getColumnDimension('C')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalKredit)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (PJK)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '411':
                    //BBT
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ]

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'STATUS TITIPAN EMKL',
                            'index' => 'statustitipanemkl',
                        ],
                        [
                            'label' => 'NO BUKTI SURAT PENGANTAR',
                            'index' => 'suratpengantar_nobukti',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'JENIS ORDER',
                            'index' => 'jenisorderan',
                        ],
                        [
                            'label' => 'TRADO',
                            'index' => 'trado_id',
                        ],
                        [
                            'label' => 'NOMINAL TAGIH',
                            'index' => 'nominaltagih',
                            'format' => 'currency'
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->statustitipanemkl);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->suratpengantar_nobukti);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("E$detail_start_row", $response_detail->jenisorderan);
                        $sheet->setCellValue("F$detail_start_row", $response_detail->trado_id);
                        $sheet->setCellValue("G$detail_start_row", $response_detail->nominaltagih);
                        $sheet->setCellValue("H$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);

                        $sheet->getStyle("A$detail_start_row:F$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("G$detail_start_row:H$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':G' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':G' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(H" . ($detail_table_header_row + 1) . ":H" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("H$total_start_row", $totalKredit)->getStyle("H$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("H$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setWidth(50);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $sheet->getColumnDimension('F')->setAutoSize(true);
                    $sheet->getColumnDimension('G')->setAutoSize(true);
                    $sheet->getColumnDimension('H')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (BBT)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '545':
                    //TDEK
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 9;
                    $detail_start_row = $detail_table_header_row + 1;


                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ]

                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluarantrucking[$header_column['index']] = date('d-m-Y', strtotime($pengeluarantrucking[$header_column['index']]));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluarantrucking[$header_column['index']]);
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluarantrucking[$header_right_column['index']]);
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI PENERIMAAN TRUCKING',
                            'index' => 'penerimaantruckingheader_nobukti',
                        ],
                        [
                            'label' => 'KARYAWAN',
                            'index' => 'karyawan_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluarantrucking_details as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail[$detail_column['index']] : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail['penerimaantruckingheader_nobukti']);
                        $sheet->setCellValue("C$detail_start_row", $response_detail['karyawan_id']);
                        $sheet->setCellValue("D$detail_start_row", $response_detail['keterangan']);
                        $sheet->setCellValue("E$detail_start_row", $response_detail['nominal']);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('D')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(E" . ($detail_table_header_row + 1) . ":E" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("E$total_start_row", $totalKredit)->getStyle("E$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("E$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'Laporan Pengeluaran Trucking (TDEK)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    $writer->save('php://output');
                    break;
                case '556':
                case '557':
                    //OTOK&OTOL
                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 10;
                    $detail_start_row = $detail_table_header_row + 1;


                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],

                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],
                        [
                            'label' => 'Periode Dari',
                            'index' => 'periodedari',
                        ],
                        [
                            'label' => 'Periode Sampai',
                            'index' => 'periodesampai',
                        ],
                        [
                            'label' => 'Customer',
                            'index' => 'agen_id',
                        ],
                        [
                            'label' => 'Container',
                            'index' => 'containerheader_id',
                        ],

                    ];
                    // dd($pengeluarantrucking);
                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluarantrucking[$header_column['index']] = date('d-m-Y', strtotime($pengeluarantrucking[$header_column['index']]));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluarantrucking[$header_column['index']]);
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        if ($header_right_column['index'] == 'periodedari' || $header_right_column['index'] == 'periodesampai') {
                            $pengeluarantrucking[$header_right_column['index']] = date('d-m-Y', strtotime($pengeluarantrucking[$header_right_column['index']]));
                        }
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluarantrucking[$header_right_column['index']]);
                    }

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI INVOICE',
                            'index' => 'invoice_nobukti',
                        ],
                        [
                            'label' => 'NO ORDERAN TRUCKING',
                            'index' => 'orderantrucking_nobukti',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluarantrucking_details as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail[$detail_column['index']] : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail['invoice_nobukti']);
                        $sheet->setCellValue("C$detail_start_row", $response_detail['orderantrucking_nobukti']);
                        $sheet->setCellValue("D$detail_start_row", $response_detail['keterangan']);
                        $sheet->setCellValue("E$detail_start_row", $response_detail['nominal']);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('D')->setWidth(50);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalKredit = "=SUM(E" . ($detail_table_header_row + 1) . ":E" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("E$total_start_row", $totalKredit)->getStyle("E$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("E$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'Laporan Pengeluaran Trucking (' . $pengeluarantrucking['kodepengeluaran'] . ')' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    $writer->save('php://output');
                    break;
                default:

                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                    $sheet->setCellValue('A1', $pengeluaran_TruckingHeader->judul);
                    $sheet->setCellValue('A2', $pengeluaran_TruckingHeader->judulLaporan);
                    $sheet->getStyle("A1")->getFont()->setSize(11);
                    $sheet->getStyle("A2")->getFont()->setSize(11);
                    $sheet->getStyle("A1")->getFont()->setBold(true);
                    $sheet->getStyle("A2")->getFont()->setBold(true);
                    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                    $sheet->mergeCells('A1:G1');
                    $sheet->mergeCells('A2:G2');

                    $header_start_row = 4;
                    $header_right_start_row = 4;
                    $detail_table_header_row = 8;
                    $detail_start_row = $detail_table_header_row + 1;

                    $alphabets = range('A', 'Z');

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Pengeluaran',
                            'index' => 'pengeluaran_nobukti',
                        ],
                    ];

                    $header_right_columns = [
                        [
                            'label' => 'Pengeluaran Trucking',
                            'index' => 'pengeluarantrucking_id',
                        ],

                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],

                    ];

                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'SUPIR',
                            'index' => 'supir_id',
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ],
                        [
                            'label' => 'TANDA TANGAN',
                        ],
                    ];

                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        if ($header_column['index'] == 'tglbukti') {
                            $pengeluaran_TruckingHeader->{$header_column['index']} = date('d-m-Y', strtotime($pengeluaran_TruckingHeader->{$header_column['index']}));
                        }
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                        $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pengeluaran_TruckingHeader->{$header_right_column['index']});
                    }
                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }

                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($pengeluaran_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->supir_id);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->nominal);
                        $sheet->setCellValue("D$detail_start_row", $response_index + 1);

                        if (($response_index + 1) % 2 == 0) {
                            $sheet->getStyle("D$detail_start_row")->getAlignment()->setHorizontal('center');
                        } else {
                            $sheet->getStyle("D$detail_start_row")->getAlignment()->setHorizontal('left');
                        }
                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("C$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':B' . $total_start_row);
                    $totalKredit = "=SUM(C" . ($detail_table_header_row + 1) . ":C" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $sheet->setCellValue("C$total_start_row", $totalKredit)->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("C$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENGELUARAN TRUCKING (' . $pengeluaran_TruckingHeader->kodepengeluaran . ')' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
            }
        } else {
            return response([
                'data' => $pengeluaran_TruckingHeader
            ]);
        }
    }

    /**
     * @ClassName 
     * @Keterangan PINJAMAN SUPIR
     */
    public function pengeluarantruckingpinjamansupir() {}

    /**
     * @ClassName 
     * @Keterangan PENARIKAN DEPOSITO SUPIR
     */
    public function pengeluarantruckingpenarikandeposito() {}

    /**
     * @ClassName 
     * @Keterangan SUMBANGAN SOSIAL
     */
    public function pengeluarantruckingsumbangansosial() {}

    /**
     * @ClassName 
     * @Keterangan INSENTIF SUPIR
     */
    public function pengeluarantruckinginsentifsupir() {}
    /**
     * @ClassName 
     * @Keterangan PELUNASAN HUTANG BBM
     */
    public function pengeluarantruckingpelunasanhutangbbm() {}

    /**
     * @ClassName 
     * @Keterangan BIAYA LAIN SUPIR
     */
    public function pengeluarantruckingbiayalainsupir() {}

    /**
     * @ClassName 
     * @Keterangan KLAIM SUPIR
     */
    public function pengeluarantruckingklaimsupir() {}

    /**
     * @ClassName 
     * @Keterangan PINJAMAN KARYAWAN
     */
    public function pengeluarantruckingpinjamankaryawan() {}

    /**
     * @ClassName 
     * @Keterangan TITIPAN EMKL
     */
    public function pengeluarantruckingtitipanemkl() {}
    /**
     * @ClassName 
     * @Keterangan LAPANGAN LEMBUR
     */
    public function pengeluarantruckinglapanganlembur() {}

    /**
     * @ClassName 
     * @Keterangan LAPANGAN NGINAP
     */
    public function pengeluarantruckinglapangannginap() {}
    /**
     * @ClassName 
     * @Keterangan BIAYA PORTAL
     */
    public function pengeluarantruckingportal() {}

    /**
     * @ClassName 
     * @Keterangan BIAYA GAJI SUPIR
     */
    public function pengeluarantruckinggajisupir() {}
    /**
     * @ClassName 
     * @Keterangan BIAYA LAIN INSENTIF
     */
    public function pengeluarantruckingbiayainsentif() {}
    /**
     * @ClassName 
     * @Keterangan LAPANGAN UANG JALAN
     */
    public function pengeluarantruckinglapanganuangjalan() {}
    /**
     * @ClassName 
     * @Keterangan PENARIKAN DEPOSITO KARYAWAN
     */
    public function pengeluarantruckingpenarikandepositokaryawan() {}
    /**
     * @ClassName
     * @Keterangan BIAYA OTOBON
     */
    public function pengeluarantruckingotobon() {}
    /**
     * @ClassName 
     * @Keterangan BIAYA LAPANGAN
     */
    public function pengeluarantruckingbiayalapangan() {}

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}

    /**
     * @ClassName 
     * @Keterangan BIAYA UANG MAKAN SUPIR
     */
    public function pengeluarantruckingbiayauangmakansupir() {}

    /**
     * @ClassName 
     * @Keterangan BIAYA LAPANGAN KAWAL
     */
    public function pengeluarantruckingbiayalapangankawal() {}

    /**
     * @ClassName 
     * @Keterangan BIAYA LAPANGAN BORONGAN
     */
    public function pengeluarantruckingbiayalapanganborongan() {}

    /**
     * @ClassName
     * @Keterangan BIAYA OTOBON KANTOR
     */
    public function pengeluarantruckingotobonkantor() {}
    /**
     * @ClassName
     * @Keterangan BIAYA OTOBON LAPANGAN
     */
    public function pengeluarantruckingotobonlapangan() {}

    /**
     * @ClassName 
     * @Keterangan LAPANGAN TAMBAHAN TOL
     */
    public function pengeluarantruckinglapangantambahantol() {}

    public function getSisaDeposito() {
        $data = (new PengeluaranTruckingHeader())->getSisaDeposito();
        return response([
            'data' => $data
        ]);
    }
}
