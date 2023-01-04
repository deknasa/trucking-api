<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokHeader;
use App\Models\PengeluaranStokDetail;
use App\Models\PenerimaanStokDetail;
use App\Models\PengeluaranStokDetailFifo;
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
            $pengeluaranStokHeader->kerusakan_id         = ($request->kerusakan_id == null) ? "" : $request->kerusakan_id;
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
                            "modifiedby" => auth('api')->user()->name,
                        ];

                        $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                        $pengeluaranStokDetailFifo = app(PengeluaranStokDetailFifoController::class)->store($datafifo);


                        if ($pengeluaranStokDetailFifo['error']) {
                            return response($pengeluaranStokDetailFifo, 422);
                        } 
                        // dd('test');
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



        DB::beginTransaction();
        
        try {


            $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $id)->first();



            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
            if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {

                $querypengeluaranstokdetail = PengeluaranStokDetail::from(
                    "pengeluaranstokdetail as i"
                )
                    ->select(
                        'i.stok_id',

                    )
                    ->where('i.pengeluaranstokheader_id', '=', $id)
                    ->orderBy('i.id', 'Asc')
                    ->get();


                $datastokdetail = json_decode($querypengeluaranstokdetail, true);
                foreach ($datastokdetail as $item) {

                    $reset = $this->resethpp($pengeluaranStokHeader->id, $item['stok_id'], false);


                    if (!$reset['status']) {
                        throw new \Throwable($reset['message']);
                    }
                }
            }
            // dd('test');
        
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

                        $datadetailfifo = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "gudang_id" => $request->gudang_id,
                            "tglbukti" => $request->tglbukti,
                            "qty" => $request->detail_qty[$i],
                            "modifiedby" => auth('api')->user()->name,
                        ];

                        $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                        $pengeluaranStokDetailFifo = app(PengeluaranStokDetailFifoController::class)->store($datafifo);

                        $reset = $this->resethppedit($pengeluaranStokHeader->id, $request->detail_stok_id[$i]);


                        if (!$reset['status']) {
                            throw new \Throwable($reset['message']);
                        }
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



        $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $id)->first();

        /*Update  di stok persediaan*/

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {

            $querypengeluaranstokdetail = PengeluaranStokDetail::from(
                "pengeluaranstokdetail as i"
            )
                ->select(
                    'i.stok_id',

                )
                ->where('i.pengeluaranstokheader_id', '=', $id)
                ->orderBy('i.id', 'Asc')
                ->get();


            $datastokdetail = json_decode($querypengeluaranstokdetail, true);
            foreach ($datastokdetail as $item) {

                $reset = $this->resethpp($pengeluaranStokHeader->id, $item['stok_id'], true);


                if (!$reset['status']) {
                    throw new \Throwable($reset['message']);
                }
            }
        }

        DB::beginTransaction();
        $getDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();
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

    private function resethppedit($id, $stok_id)
    {
        try {

            $temphpp = '##temphppedit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphpp, function ($table) {
                $table->unsignedBigInteger('id')->default(0);
                $table->string('nobukti', 100)->default('');
                $table->double('qty', 15, 2)->default(0);
                $table->unsignedBigInteger('pengeluaranstokheader_id')->default(0);
            });


            $querytemphpp = PengeluaranStokDetail::from(
                "pengeluaranstokdetail as i"
            )
                ->select(
                    'i.id',
                    'i.nobukti',
                    'i.qty',
                    'i.pengeluaranstokheader_id'
                )
                ->whereRaw("i.pengeluaranstokheader_id>" . $id);



            DB::table($temphpp)->insertUsing([
                'id',
                'nobukti',
                'qty',
                'pengeluaranstokheader_id'
            ], $querytemphpp);

            $querytemphpp = DB::table($temphpp)->from(
                $temphpp . " as i"
            )
                ->select(
                    'i.id',
                    'i.nobukti',
                    'i.qty',
                    'a.id as pengeluaranstokheader_id',
                    'a.tglbukti',
                    'a.modifiedby',

                )
                ->join('pengeluaranstokheader as a', 'i.nobukti', 'a.nobukti')
                ->orderBy('i.pengeluaranstokheader_id', 'Asc')
                ->orderBy('i.id', 'Asc')
                ->get();


            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

            $datafifo = json_decode($querytemphpp, true);
            foreach ($datafifo as $item) {


                $datadetailfifo = [
                    "pengeluaranstokheader_id" => $item['pengeluaranstokheader_id'],
                    "nobukti" => $item['nobukti'],
                    "stok_id" => $stok_id,
                    "gudang_id" => $gudangkantor->text,
                    "tglbukti" => $item['tglbukti'],
                    "qty" => $item['qty'],
                    "modifiedby" => $item['modifiedby'],
                ];
                $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                app(PengeluaranStokDetailFifoController::class)->store($datafifo);
            }
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            // DB::rollBack();

            throw $th;
        }
    }

    private function resethpp($id, $stok_id, $hapus)
    {
        // DB::beginTransaction();

        try {
            $temphpp = '##temphpp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphpp, function ($table) {
                $table->unsignedBigInteger('id')->default(0);
                $table->string('nobukti', 100)->default('');
                $table->double('qty', 15, 2)->default(0);
                $table->unsignedBigInteger('pengeluaranstokheader_id')->default(0);
            });


            $querytemphpp = PengeluaranStokDetail::from(
                "pengeluaranstokdetail as i"
            )
                ->select(
                    'i.id',
                    'i.nobukti',
                    'i.qty',
                    'i.pengeluaranstokheader_id'
                )
                ->whereRaw("i.pengeluaranstokheader_id>" . $id);



            DB::table($temphpp)->insertUsing([
                'id',
                'nobukti',
                'qty',
                'pengeluaranstokheader_id'
            ], $querytemphpp);

            $querydetailfifo = PengeluaranStokDetailFifo::from(
                "pengeluaranstokdetailfifo as i"
            )
                ->select(
                    'i.id',
                    'i.penerimaanstok_qty',
                    'i.penerimaanstokheader_nobukti',
                    'i.pengeluaranstokheader_id',
                )
                ->whereRaw("i.pengeluaranstokheader_id>=" . $id . " and i.stok_id=" . $stok_id)
                ->orderBy('i.id', 'Asc')
                ->get();


            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();


            $datadetail = json_decode($querydetailfifo, true);
            //  dd($datadetail);
            foreach ($datadetail as $item) {


                $datapenerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $stok_id)
                    ->where("nobukti", $item['penerimaanstokheader_nobukti'])
                    ->firstorFail();

                // dump( $stok_id);
                // dd($item['penerimaanstokheader_nobukti']);
                $datapenerimaanstokdetail->qtykeluar -= $item['penerimaanstok_qty'];
                $datapenerimaanstokdetail->save();

                if ($hapus == true) {
                    $datastokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $stok_id)
                        ->where("gudang_id", $gudangkantor->text)
                        ->firstorFail();
                    $datastokpersediaan->qty += $item['penerimaanstok_qty'];
                    $datastokpersediaan->save();
                }

                $datapengeluaranstokdetailfifo = PengeluaranStokDetailFifo::lockForUpdate()->where("stok_id", $stok_id)
                    ->where("id", $item['id'])
                    ->firstorFail();
                // dd($datapengeluaranstokdetailfifo);
                $datapengeluaranstokdetailfifo->delete();
            }
        // dd('test');

            if ($hapus == true) {
                $querytemphpp = DB::table($temphpp)->from(
                    $temphpp . " as i"
                )
                    ->select(
                        'i.id',
                        'i.nobukti',
                        'i.qty',
                        'a.id as pengeluaranstokheader_id',
                        'a.tglbukti',
                        'a.modifiedby',

                    )
                    ->join('pengeluaranstokheader as a', 'i.nobukti', 'a.nobukti')
                    ->orderBy('i.pengeluaranstokheader_id', 'Asc')
                    ->orderBy('i.id', 'Asc')
                    ->get();




                $datafifo = json_decode($querytemphpp, true);
                foreach ($datafifo as $item) {


                    $datadetailfifo = [
                        "pengeluaranstokheader_id" => $item['pengeluaranstokheader_id'],
                        "nobukti" => $item['nobukti'],
                        "stok_id" => $stok_id,
                        "gudang_id" => $gudangkantor->text,
                        "tglbukti" => $item['tglbukti'],
                        "qty" => $item['qty'],
                        "modifiedby" => $item['modifiedby'],
                    ];
                    $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                    app(PengeluaranStokDetailFifoController::class)->store($datafifo);

                    $datastokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $stok_id)
                        ->where("gudang_id", $gudangkantor->text)
                        ->firstorFail();
                    $datastokpersediaan->qty -= $item['qty'];
                    $datastokpersediaan->save();
                }
            }


            // DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            // DB::rollBack();

            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = PengeluaranStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranStokHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranStokHeader->statuscetak = $statusSudahCetak->id;
                $pengeluaranStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaranStokHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranStokHeader->jumlahcetak = $pengeluaranStokHeader->jumlahcetak+1;
                if ($pengeluaranStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA',
                        'idtrans' => $pengeluaranStokHeader->id,
                        'nobuktitrans' => $pengeluaranStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranStokHeader->toArray(),
                        'modifiedby' => $pengeluaranStokHeader->modifiedby
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
}
