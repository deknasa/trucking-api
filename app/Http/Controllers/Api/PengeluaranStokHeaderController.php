<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokHeader;
use App\Models\PengeluaranStokDetail;
use App\Models\StokPersediaan;
use App\Models\Stok;

use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranStokHeaderRequest;
use App\Http\Requests\UpdatePengeluaranStokHeaderRequest;
use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\StorePengeluaranStokDetailFifoRequest;

class PengeluaranStokHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        return response([
            'data' => $pengeluaranStokHeader->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStokHeader->totalRows,
                'totalPages' => $pengeluaranStokHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $idpenerimaan = $request->pengeluaranstok_id;
            $fetchFormat =  DB::table('pengeluaranstok')
                ->where('id', $idpenerimaan)
                ->first();
            // dd($fetchFormat);
            $statusformat = $fetchFormat->statusformat;

            $fetchGrp = Parameter::where('id', $statusformat)->first();

            $format = DB::table('parameter')
                ->where('grp', $fetchGrp->grp)
                ->where('subgrp', $fetchGrp->subgrp)
                ->first();
            $content = new Request();
            $content['group'] = $fetchGrp->grp;
            $content['subgroup'] = $fetchGrp->subgrp;
            $content['table'] = 'pengeluaranstokheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

            if ($request->pengeluaranstok_id == $spk->text) {
                $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
                $request->gudang_id = $gudangkantor->text;
            }

            /* Store header */
            $pengeluaranStokHeader = new PengeluaranStokHeader();
            $pengeluaranStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranStokHeader->keterangan        = ($request->keterangan == null) ? "" : $request->keterangan;
            $pengeluaranStokHeader->pengeluaranstok_id = ($request->pengeluaranstok_id == null) ? "" : $request->pengeluaranstok_id;
            $pengeluaranStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $pengeluaranStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $pengeluaranStokHeader->supir_id         = ($request->supir_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $pengeluaranStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $pengeluaranStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $pengeluaranStokHeader->servicein_nobukti    = ($request->servicein_nobukti == null) ? "" : $request->servicein_nobukti;
            $pengeluaranStokHeader->kerusakan_id         = ($request->kerusakan_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->statusformat      = ($request->statusformat_id == null) ? "" : $request->statusformat_id;
            $pengeluaranStokHeader->modifiedby        = auth('api')->user()->name;
            $pengeluaranStokHeader->statuscetak        = $statusCetak->id ?? 0;
            $request->sortname                 = $request->sortname ?? 'id';
            $request->sortorder                = $request->sortorder ?? 'asc';
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengeluaranStokHeader->nobukti = $nobukti;
            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'ENTRY PENGELUARAN STOK HEADER',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranStokHeader->toArray(),
                    'modifiedby' => $pengeluaranStokHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                if ($request->detail_harga) {

                    /* Store detail */
                    $detaillog = [];

                    for ($i = 0; $i < count($request->detail_harga); $i++) {
                        $datadetail = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "qty" => $request->detail_qty[$i],
                            "harga" => $request->detail_harga[$i],
                            "persentasediscount" => $request->detail_persentasediscount[$i],
                            "vulkanisirke" => $request->detail_vulkanisirke[$i],
                            "detail_keterangan" => $request->detail_keterangan[$i],
                        ];

                        $data = new StorePengeluaranStokDetailRequest($datadetail);
                        $pengeluaranStokDetail = app(PengeluaranStokDetailController::class)->store($data);

                        if ($pengeluaranStokDetail['error']) {
                            return response($pengeluaranStokDetail, 422);
                        } else {
                            $iddetail = $pengeluaranStokDetail['id'];
                            $tabeldetail = $pengeluaranStokDetail['tabel'];
                        }
                        $detaillog[] = $pengeluaranStokDetail['detail']->toArray();


                        $datadetailfifo = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "gudang_id" => $request->gudang_id,
                            "tglbukti" => $request->tglbukti,
                            "qty" => $request->detail_qty[$i],
                        ];

                        $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                        $pengeluaranStokDetailFifo = app(PengeluaranStokDetailFifoController::class)->store($datafifo);

                        if ($pengeluaranStokDetailFifo['error']) {
                            return response($pengeluaranStokDetailFifo, 422);
                        } else {
                            $tabeldetail = $pengeluaranStokDetailFifo['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY PENGELUARAN STOK DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $pengeluaranStokHeader->nobukti,
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
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable());
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengeluaranStokHeader->find($id),
            'detail' => PengeluaranStokDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdatePengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {
        try {

            /* Store header */
            $pengeluaranStokHeader = PengeluaranStokHeader::lockForUpdate()->findOrFail($id);

            $pengeluaranStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranStokHeader->keterangan        = ($request->keterangan == null) ? "" : $request->keterangan;
            $pengeluaranStokHeader->pengeluaranstok_id = ($request->pengeluaranstok_id == null) ? "" : $request->pengeluaranstok_id;
            $pengeluaranStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $pengeluaranStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $pengeluaranStokHeader->supir_id         = ($request->supir_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $pengeluaranStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $pengeluaranStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $pengeluaranStokHeader->servicein_nobukti    = ($request->servicein_nobukti == null) ? "" : $request->servicein_nobukti;
            $pengeluaranStokHeader->kerusakan_id         = ($request->kerusakan_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->statusformat      = ($request->statusformat_id == null) ? "" : $request->statusformat_id;
            $pengeluaranStokHeader->modifiedby        = auth('api')->user()->name;
            $request->sortname                 = $request->sortname ?? 'id';
            $request->sortorder                = $request->sortorder ?? 'asc';
            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN STOK HEADER',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranStokHeader->toArray(),
                    'modifiedby' => $pengeluaranStokHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                if ($request->detail_harga) {


                    /*Update  di stok persediaan*/

                    $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
                    if ($request->pengeluaranstok_id == $spk->text) {
                        $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
                            ->where('statusformat', '=', $request->statusformat_id)
                            ->first();

                        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

                        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                            $datadetail = PengeluaranStokDetail::select('stok_id', 'qty')
                                ->where('pengeluaranstokheader_id', '=', $id)
                                ->get();

                            $datadetail = json_decode($datadetail, true);

                            foreach ($datadetail as $item) {
                                $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $item['stok_id'])
                                    ->where("gudang_id", ($request->gudang_id))->firstorFail();
                                $stokpersediaan->qty += $item['qty'];
                                $stokpersediaan->save();
                            }
                        }
                    }


                    /* Delete existing detail */
                    $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->lockForUpdate()->delete();
                    /* Store detail */
                    $detaillog = [];

                    for ($i = 0; $i < count($request->detail_harga); $i++) {
                        $datadetail = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "qty" => $request->detail_qty[$i],
                            "harga" => $request->detail_harga[$i],
                            "persentasediscount" => $request->detail_persentasediscount[$i],
                            "vulkanisirke" => $request->detail_vulkanisirke[$i],
                            "detail_keterangan" => $request->detail_keterangan[$i],
                        ];

                        $data = new StorePengeluaranStokDetailRequest($datadetail);
                        $pengeluaranStokDetail = app(PengeluaranStokDetailController::class)->store($data);

                        if ($pengeluaranStokDetail['error']) {
                            return response($pengeluaranStokDetail, 422);
                        } else {
                            $iddetail = $pengeluaranStokDetail['id'];
                            $tabeldetail = $pengeluaranStokDetail['tabel'];
                        }
                        $detaillog[] = $pengeluaranStokDetail['detail']->toArray();
                    }
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY PENGELUARAN STOK HEADER',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $pengeluaranStokHeader->nobukti,
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
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable());
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {
        DB::beginTransaction();

        $getDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();
        $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $id)->first();
        $delete = $pengeluaranStokHeader->lockForUpdate()->where('id', $id)->delete();


        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                'postingdari' => 'DELETE PENGELUARAN STOK',
                'idtrans' => $pengeluaranStokHeader->id,
                'nobuktitrans' => $pengeluaranStokHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranStokHeader->toArray(),
                'modifiedby' => $pengeluaranStokHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            /*Update  di stok persediaan*/

            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
            if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {
                $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
                    ->where('statusformat', '=', $pengeluaranStokHeader->statusformat_id)
                    ->first();

                $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

                if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                    $datadetail = PengeluaranStokDetail::select('stok_id', 'qty')
                        ->where('pengeluaranstokheader_id', '=', $id)
                        ->get();

                    $datadetail = json_decode($datadetail, true);

                    foreach ($datadetail as $item) {
                        $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $item['stok_id'])
                            ->where("gudang_id", ($pengeluaranStokHeader->gudang_id))->firstorFail();
                        $stokpersediaan->qty += $item['qty'];
                        $stokpersediaan->save();
                    }
                }
            }

            // DELETE PENGELUARAN STOK DETAIL
            $logTrailPengeluaranStokDetail = [
                'namatabel' => 'PENGELUARANSTOKDETAIL',
                'postingdari' => 'DELETE PENGELUARAN STOK DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pengeluaranStokHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranStokDetail = new StoreLogTrailRequest($logTrailPengeluaranStokDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengeluaranStokDetail);
            DB::commit();

            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable(), true);
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->id = $selected->id;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStokHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
}
