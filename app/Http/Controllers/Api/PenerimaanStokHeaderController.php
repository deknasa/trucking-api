<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanStok;
use App\Models\PenerimaanStokHeader;
use App\Models\PenerimaanStokDetail;
use App\Models\HutangHeader;
use App\Models\HutangDetail;


use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanStokHeaderRequest;
use App\Http\Requests\UpdatePenerimaanStokHeaderRequest;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\UpdateHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;

use App\Models\Parameter;
use App\Models\Error;
use App\Models\StokPersediaan;

use App\Http\Requests\StorePenerimaanStokDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
        $keteranganheader = $request->keterangan ?? '';
        DB::beginTransaction();

        try {
            $request->validate([
                "supplier" => Rule::requiredIf($request->penerimaanstok_id == '3')
            ]);

            $idpenerimaan = $request->penerimaanstok_id;
            $fetchFormat =  Penerimaanstok::where('id', $idpenerimaan)->first();

            $statusformat = $fetchFormat->format;
            $fetchGrp = Parameter::where('id', $statusformat)->first();

            $content = new Request();
            $content['group'] = $fetchGrp->grp;
            $content['subgroup'] = $fetchGrp->subgrp;
            $content['table'] = 'penerimaanstokheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            
            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
            
            $hutang_nobukti = "";
            if ($request->penerimaanstok_id == $spb->text) {
                $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
                $request->gudang_id = $gudangkantor->text;

                $group = 'HUTANG BUKTI';
                $subgroup = 'HUTANG BUKTI';


                $nobuktiHutang = new Request();
                $nobuktiHutang['group'] = 'HUTANG BUKTI';
                $nobuktiHutang['subgroup'] = 'HUTANG BUKTI';
                $nobuktiHutang['table'] = 'hutangheader';
                $nobuktiHutang['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $hutang_nobukti = app(Controller::class)->getRunningNumber($nobuktiHutang)->original['data'];
            }

            /* Store header */
            $penerimaanStokHeader = new PenerimaanStokHeader();

            $penerimaanStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $penerimaanStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $penerimaanStokHeader->nobon             = ($request->nobon == null) ? "" : $request->nobon;
            $penerimaanStokHeader->hutang_nobukti    = ($request->hutang_nobukti == null) ? $hutang_nobukti : $request->hutang_nobukti;
            $penerimaanStokHeader->keterangan        = ($request->keterangan == null) ? "" : $request->keterangan;
            $penerimaanStokHeader->coa               = ($request->coa == null) ? "" : $request->coa;
            $penerimaanStokHeader->statusformat      = $statusformat;
            $penerimaanStokHeader->penerimaanstok_id = ($request->penerimaanstok_id == null) ? "" : $request->penerimaanstok_id;
            $penerimaanStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $penerimaanStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $penerimaanStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $penerimaanStokHeader->gudangdari_id     = ($request->gudangdari_id == null) ? "" : $request->gudangdari_id;
            $penerimaanStokHeader->gudangke_id       = ($request->gudangke_id == null) ? "" : $request->gudangke_id;
            $penerimaanStokHeader->tradodari_id     = ($request->tradodari_id == null) ? "" : $request->tradodari_id;
            $penerimaanStokHeader->tradoke_id       = ($request->tradoke_id == null) ? "" : $request->tradoke_id;
            $penerimaanStokHeader->gandengandari_id     = ($request->gandengandari_id == null) ? "" : $request->gandengandari_id;
            $penerimaanStokHeader->gandenganke_id       = ($request->gandenganke_id == null) ? "" : $request->gandenganke_id;
            $penerimaanStokHeader->gandengan_id       = ($request->gandengan_id == null) ? "" : $request->gandengan_id;
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

                if ($request->penerimaanstok_id == $spb->text) {
                    $group = 'HUTANG BUKTI';
                    $subgroup = 'HUTANG BUKTI';

                    $format = DB::table('parameter')->from(
                        db::Raw("parameter with (readuncommitted)")
                    )
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $totalharga = 0;
                    $detaildata = [];
                    for ($i = 0; $i < count($request->detail_harga); $i++) {
                        $totalsat = ($request->detail_qty[$i] * $request->detail_harga[$i]);
                        $totalharga += $totalsat;
                        $detaildata[] = ($request->detail_qty[$i] * $request->detail_harga[$i]);
                    }

                    $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
                    $memo = json_decode($getCoaDebet->memo, true);



                    $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'KREDIT')->first();
                    $memoKredit = json_decode($getCoaKredit->memo, true);

                    $hutangRequest = [
                        'proseslain' => 'PEMBELIAN STOK',
                        'nobukti' => $hutang_nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'keterangan' => $keteranganheader,
                        'coa' => $memo['JURNAL'],
                        'pelanggan_id' =>  0,
                        'supplier_id' => ($request->supplier_id == null) ? "" : $request->supplier_id,
                        'postingdari' => 'PENERIMAAN STOK PEMBELIAN',
                        'modifiedby' => auth('api')->user()->name,
                        'total' => $totalharga,
                        'tgljatuhtempo' => $request->tglbukti,
                        'total_detail' => $detaildata,
                        'keterangan_detail' => $request->detail_keterangan,
                        'coadebet' => $memo['JURNAL'],
                        'coakredit' => $memoKredit['JURNAL'],
                    ];
               
                    $hutang = new StoreHutangHeaderRequest($hutangRequest);
                    app(HutangHeaderController::class)->store($hutang);
                }


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
            
            $request->validate([
                "supplier" => Rule::requiredIf($request->penerimaanstok_id == '3')
            ]);
            /* Store header */
            $idpenerimaan = $request->penerimaanstok_id;
            $fetchFormat =  Penerimaanstok::where('id', $idpenerimaan)->first();

            $statusformat = $fetchFormat->format;
            $fetchGrp = Parameter::where('id', $statusformat)->first();
            $penerimaanStokHeader = PenerimaanStokHeader::lockForUpdate()->findOrFail($id);

            $penerimaanStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $penerimaanStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $penerimaanStokHeader->nobon             = ($request->nobon == null) ? "" : $request->nobon;
            $penerimaanStokHeader->hutang_nobukti    = ($request->hutang_nobukti == null) ? "" : $request->hutang_nobukti;
            $penerimaanStokHeader->keterangan        = ($request->keterangan == null) ? "" : $request->keterangan;
            $penerimaanStokHeader->coa               = ($request->coa == null) ? "" : $request->coa;
            $penerimaanStokHeader->statusformat      = ($statusformat == null) ? "" : $statusformat;
            $penerimaanStokHeader->penerimaanstok_id = ($request->penerimaanstok_id == null) ? "" : $request->penerimaanstok_id;
            $penerimaanStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $penerimaanStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $penerimaanStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $penerimaanStokHeader->gudangdari_id     = ($request->gudangdari_id == null) ? "" : $request->gudangdari_id;
            $penerimaanStokHeader->gudangke_id       = ($request->gudangke_id == null) ? "" : $request->gudangke_id;
            $penerimaanStokHeader->tradodari_id     = ($request->tradodari_id == null) ? "" : $request->tradodari_id;
            $penerimaanStokHeader->tradoke_id       = ($request->tradoke_id == null) ? "" : $request->tradoke_id;
            $penerimaanStokHeader->gandengandari_id     = ($request->gandengandari_id == null) ? "" : $request->gandengandari_id;
            $penerimaanStokHeader->gandenganke_id       = ($request->gandenganke_id == null) ? "" : $request->gandenganke_id;
            $penerimaanStokHeader->gandengan_id       = ($request->gandengan_id == null) ? "" : $request->gandengan_id;
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
                    ->where('format', '=', $penerimaanStokHeader->statusformat)
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

                $spb = Parameter::from(
                    db::Raw("parameter with (readuncommitted)")
                )
                    ->where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

                if ($request->penerimaanstok_id == $spb->text) {
                    $group = 'HUTANG BUKTI';
                    $subgroup = 'HUTANG BUKTI';

                    $format = DB::table('parameter')->from(
                        db::Raw("parameter with (readuncommitted)")
                    )
                        ->where('grp', $group)
                        ->where('subgrp', $subgroup)
                        ->first();
                    $totalharga = 0;
                    $detaildata = [];
                    for ($i = 0; $i < count($request->detail_harga); $i++) {
                        $totalsat = ($request->detail_qty[$i] * $request->detail_harga[$i]);
                        $totalharga += $totalsat;
                        $detaildata[] = ($request->detail_qty[$i] * $request->detail_harga[$i]);
                    }

                    $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
                    $memo = json_decode($getCoaDebet->memo, true);



                    $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'KREDIT')->first();
                    $memoKredit = json_decode($getCoaKredit->memo, true);

                    $hutangHeader=HutangHeader::where('nobukti','=',$penerimaanStokHeader->hutang_nobukti  )->first();
                    // return response($hutangHeader,422);

                    $hutangRequest = [
                        'proseslain' => 'PEMBELIAN STOK',
                        'nobukti' => $request->hutang_nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'keterangan' => $request->keterangan,
                        'coa' => $memo['JURNAL'],
                        'pelanggan_id' =>  0,
                        'supplier_id' => ($request->supplier_id == null) ? "" : $request->supplier_id,
                        'postingdari' => 'PENERIMAAN STOK PEMBELIAN',
                        'modifiedby' => auth('api')->user()->name,
                        'total' => $totalharga,
                        'tgljatuhtempo' => $request->tglbukti,
                        'total_detail' => $detaildata,
                        'keterangan_detail' => $request->detail_keterangan,
                        'coadebet' => $memo['JURNAL'],
                        'coakredit' => $memoKredit['JURNAL'],
                        'id'=>$hutangHeader->id,
                    ];

                    $hutangheader = new HutangHeader();
                    $hutangheader->id=$hutangHeader->id;
                    $hutang = new UpdateHutangHeaderRequest($hutangRequest);
                    app(HutangHeaderController::class)->update($hutang,$hutangheader);
                }

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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $penerimaanStokHeader = PenerimaanStokHeader::where('id', $id)->first();

        /*Update  di stok persediaan*/
        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
            ->where('format', '=', $penerimaanStokHeader->statusformat)
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
        $penerimaanStok = new PenerimaanStokHeader();
        $penerimaanStok = $penerimaanStok->lockAndDestroy($id);

        $idhutang = HutangHeader::from(
            db::Raw("hutangheader with (readuncommitted)")
        )->where("nobukti", "=", $penerimaanStokHeader->hutang_nobukti)->first();



        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        $hutang_nobukti = "";
        if ($request->penerimaanstok_id == $spb->text) {
            $hutangRequest = [
                'proseslain' => 'PEMBELIAN STOK',
            ];
            $hutang = new StoreHutangHeaderRequest($hutangRequest);
            app(HutangHeaderController::class)->destroy($hutang, $idhutang->id);
        }

        // app(HutangHeaderController::class)->destroy($hutangRequest,$idhutang->id);

        if ($penerimaanStok) {
            $logTrail = [
                'namatabel' => strtoupper($penerimaanStok->getTable()),
                'postingdari' => 'DELETE PENERIMAAN STOK',
                'idtrans' => $penerimaanStok->id,
                'nobuktitrans' => $penerimaanStok->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanStok->toArray(),
                'modifiedby' => $penerimaanStok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENERIMAAN STOK DETAIL
            $logTrailPenerimaanStokDetail = [
                'namatabel' => 'PENERIMAANSTOKDETAIL',
                'postingdari' => 'DELETE PENERIMAAN STOK DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanStok->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPenerimaanStokDetail = new StoreLogTrailRequest($logTrailPenerimaanStokDetail);
            app(LogTrailController::class)->store($validatedLogTrailPenerimaanStokDetail);
            DB::commit();

            $selected = $this->getPosition($penerimaanStok, $penerimaanStok->getTable(), true);
            $penerimaanStok->position = $selected->position;
            $penerimaanStok->id = $selected->id;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanStok
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = PenerimaanStokHeader::findOrFail($id);
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
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanStokHeader = PenerimaanStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanStokHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanStokHeader->statuscetak = $statusSudahCetak->id;
                $penerimaanStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanStokHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanStokHeader->jumlahcetak = $penerimaanStokHeader->jumlahcetak + 1;
                if ($penerimaanStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA',
                        'idtrans' => $penerimaanStokHeader->id,
                        'nobuktitrans' => $penerimaanStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanStokHeader->toArray(),
                        'modifiedby' => $penerimaanStokHeader->modifiedby
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaanstokheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
