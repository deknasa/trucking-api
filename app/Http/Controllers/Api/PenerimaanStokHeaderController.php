<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanStok;
use App\Models\PenerimaanStokHeader;
use App\Models\PenerimaanStokDetail;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanStokHeaderRequest;
use App\Http\Requests\UpdatePenerimaanStokHeaderRequest;
use App\Models\Parameter;
use App\Models\StokPersediaan;

use App\Http\Requests\StorePenerimaanStokDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanStokHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $penerimaanStokHeader = new PenerimaanStokHeader();
        return response([
            'data' => $penerimaanStokHeader->get(),
            'attributes' => [
                'totalRows' => $penerimaanStokHeader->totalRows,
                'totalPages' => $penerimaanStokHeader->totalPages
            ]
        ]);
    }



    /**
     * @ClassName 
     */
    public function store(StorePenerimaanStokHeaderRequest $request)
    {

        DB::beginTransaction();

        try {

            $idpenerimaan = $request->penerimaanstok_id;
            $fetchFormat =  DB::table('penerimaanstok')
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
            $content['table'] = 'penerimaanstokheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $statusCetak = Parameter::where('grp','STATUSCETAK')->where('text','BELUM CETAK')->first();

            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
     
            if ($request->penerimaanstok_id == $spb->text) {
                $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
                $request->gudang_id=$gudangkantor->text;

            }            

            /* Store header */
            $penerimaanStokHeader = new PenerimaanStokHeader();

            $penerimaanStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $penerimaanStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $penerimaanStokHeader->nobon             = ($request->nobon == null) ? "" : $request->nobon;
            $penerimaanStokHeader->hutang_nobukti    = ($request->hutang_nobukti == null) ? "" : $request->hutang_nobukti;
            $penerimaanStokHeader->keterangan        = ($request->keterangan == null) ? "" : $request->keterangan;
            $penerimaanStokHeader->coa               = ($request->coa == null) ? "" : $request->coa;
            $penerimaanStokHeader->statusformat      = ($request->statusformat_id == null) ? "" : $request->statusformat_id;
            $penerimaanStokHeader->penerimaanstok_id = ($request->penerimaanstok_id == null) ? "" : $request->penerimaanstok_id;
            $penerimaanStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $penerimaanStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $penerimaanStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $penerimaanStokHeader->gudangdari_id     = ($request->gudangdari_id == null) ? "" : $request->gudangdari_id;
            $penerimaanStokHeader->gudangke_id       = ($request->gudangke_id == null) ? "" : $request->gudangke_id;
            $penerimaanStokHeader->modifiedby        = auth('api')->user()->name;
            $penerimaanStokHeader->statuscetak        = $statusCetak->id;
            $request->sortname                 = $request->sortname ?? 'id';
            $request->sortorder                = $request->sortorder ?? 'asc';
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $penerimaanStokHeader->nobukti = $nobukti;

            if ($penerimaanStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                    'postingdari' => 'ENTRY PENERIMAAN STOK HEADER',
                    'idtrans' => $penerimaanStokHeader->id,
                    'nobuktitrans' => $penerimaanStokHeader->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaanStokHeader->toArray(),
                    'modifiedby' => $penerimaanStokHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */
                $detaillog = [];

                for ($i = 0; $i < count($request->detail_harga); $i++) {
                    $datadetail = [
                        "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                        "nobukti" => $penerimaanStokHeader->nobukti,
                        "stok_id" => $request->detail_stok_id[$i],
                        "qty" => $request->detail_qty[$i],
                        "harga" => $request->detail_harga[$i],
                        "persentasediscount" => $request->detail_persentasediscount[$i],
                        "vulkanisirke" => $request->detail_vulkanisirke[$i],
                        "detail_keterangan" => $request->detail_keterangan[$i],
                    ];

                    $data = new StorePenerimaanStokDetailRequest($datadetail);
                    $penerimaanStokDetail = app(PenerimaanStokDetailController::class)->store($data);

                    if ($penerimaanStokDetail['error']) {
                        return response($penerimaanStokDetail, 422);
                    } else {
                        $iddetail = $penerimaanStokDetail['id'];
                        $tabeldetail = $penerimaanStokDetail['tabel'];
                    }
                    $detaillog[] = $penerimaanStokDetail['detail']->toArray();
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY PENERIMAAN STOK DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $penerimaanStokHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable());
            $penerimaanStokHeader->position = $selected->position;
            $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PenerimaanStokHeader $penerimaanStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $penerimaanStokHeader->find($id),
            'detail' => PenerimaanStokDetail::getAll($id),
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaanStokHeaderRequest $request, PenerimaanStokHeader $penerimaanStokHeader, $id)
    {
        try {
            /* Store header */
            $penerimaanStokHeader = PenerimaanStokHeader::lockForUpdate()->findOrFail($id);

            $penerimaanStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $penerimaanStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $penerimaanStokHeader->nobon             = ($request->nobon == null) ? "" : $request->nobon;
            $penerimaanStokHeader->hutang_nobukti    = ($request->hutang_nobukti == null) ? "" : $request->hutang_nobukti;
            $penerimaanStokHeader->keterangan        = ($request->keterangan == null) ? "" : $request->keterangan;
            $penerimaanStokHeader->coa               = ($request->coa == null) ? "" : $request->coa;
            $penerimaanStokHeader->statusformat      = ($request->statusformat_id == null) ? "" : $request->statusformat_id;
            $penerimaanStokHeader->penerimaanstok_id = ($request->penerimaanstok_id == null) ? "" : $request->penerimaanstok_id;
            $penerimaanStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $penerimaanStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $penerimaanStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $penerimaanStokHeader->gudangdari_id     = ($request->gudangdari_id == null) ? "" : $request->gudangdari_id;
            $penerimaanStokHeader->gudangke_id       = ($request->gudangke_id == null) ? "" : $request->gudangke_id;
            $penerimaanStokHeader->modifiedby        = auth('api')->user()->name;
            $request->sortname                 = $request->sortname ?? 'id';
            $request->sortorder                = $request->sortorder ?? 'asc';
            if ($penerimaanStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN STOK HEADER',
                    'idtrans' => $penerimaanStokHeader->id,
                    'nobuktitrans' => $penerimaanStokHeader->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanStokHeader->toArray(),
                    'modifiedby' => $penerimaanStokHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /*Update  di stok persediaan*/
                $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
                    ->where('statusformat', '=', $request->statusformat_id)
                    ->first();

                $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

                if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                    $datadetail = PenerimaanStokDetail::select('stok_id', 'qty')
                        ->where('penerimaanstokheader_id', '=', $id)
                        ->get();

                    $datadetail = json_decode($datadetail, true);

                    foreach ($datadetail as $item) {
                        $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $item['stok_id'])
                            ->where("gudang_id", ($request->gudang_id))->firstorFail();
                        $stokpersediaan->qty -= $item['qty'];
                        $stokpersediaan->save();
                    }
                }



                /* Delete existing detail */
                $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $id)->lockForUpdate()->delete();
                /* Store detail */
                $detaillog = [];

                for ($i = 0; $i < count($request->detail_harga); $i++) {
                    $datadetail = [
                        "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                        "nobukti" => $penerimaanStokHeader->nobukti,
                        "stok_id" => $request->detail_stok_id[$i],
                        "qty" => $request->detail_qty[$i],
                        "harga" => $request->detail_harga[$i],
                        "persentasediscount" => $request->detail_persentasediscount[$i],
                        "vulkanisirke" => $request->detail_vulkanisirke[$i],
                        "detail_keterangan" => $request->detail_keterangan[$i],
                    ];

                    $data = new StorePenerimaanStokDetailRequest($datadetail);
                    $penerimaanStokDetail = app(PenerimaanStokDetailController::class)->store($data);

                    if ($penerimaanStokDetail['error']) {
                        return response($penerimaanStokDetail, 422);
                    } else {
                        $iddetail = $penerimaanStokDetail['id'];
                        $tabeldetail = $penerimaanStokDetail['tabel'];
                    }

                    $detaillog[] = $penerimaanStokDetail['detail']->toArray();
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT PENERIMAAN STOK HEADER',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $penerimaanStokHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable());
            $penerimaanStokHeader->position = $selected->position;
            $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(PenerimaanStokHeader $penerimaanStokHeader, $id)
    {
        DB::beginTransaction();

        $penerimaanStokHeader = PenerimaanStokHeader::where('id', $id)->first();

        /*Update  di stok persediaan*/
        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
            ->where('statusformat', '=', $penerimaanStokHeader->statusformat)
            ->first();

        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            $datadetail = PenerimaanStokDetail::select('stok_id', 'qty')
                ->where('penerimaanstokheader_id', '=', $id)
                ->get();

            $datadetail = json_decode($datadetail, true);

            foreach ($datadetail as $item) {
                $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $item['stok_id'])
                    ->where("gudang_id", ($penerimaanStokHeader->gudang_id))->firstorFail();
                $stokpersediaan->qty -= $item['qty'];
                $stokpersediaan->save();
            }
        }


        $getDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $id)->get();
        $delete = $penerimaanStokHeader->lockForUpdate()->where('id',$id)->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                'postingdari' => 'DELETE PENERIMAAN STOK',
                'idtrans' => $penerimaanStokHeader->id,
                'nobuktitrans' => $penerimaanStokHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanStokHeader->toArray(),
                'modifiedby' => $penerimaanStokHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENERIMAAN STOK DETAIL
            $logTrailPenerimaanStokDetail = [
                'namatabel' => 'PENERIMAANSTOKDETAIL',
                'postingdari' => 'DELETE PENERIMAAN STOK DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanStokHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPenerimaanStokDetail = new StoreLogTrailRequest($logTrailPenerimaanStokDetail);
            app(LogTrailController::class)->store($validatedLogTrailPenerimaanStokDetail);
            DB::commit();

            $selected = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable(), true);
            $penerimaanStokHeader->position = $selected->position;
            $penerimaanStokHeader->id = $selected->id;
            $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanStokHeader
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
