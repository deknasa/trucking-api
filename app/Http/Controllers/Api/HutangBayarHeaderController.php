<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreHutangBayarHeaderRequest;
use App\Http\Requests\StoreHutangBayarDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdateHutangBayarHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\AkunPusat;
use App\Models\Error;
use App\Models\Supplier;
use App\Models\HutangBayarHeader;
use App\Models\HutangBayarDetail;
use App\Models\HutangDetail;
use App\Models\Parameter;
use App\Models\HutangHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\LogTrail;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\SaldoHutang;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class HutangBayarHeaderController extends Controller
{
    /**
     * @ClassName index
     */
    public function index()
    {
        $hutangbayarheader = new HutangBayarHeader();
        return response([
            'data' => $hutangbayarheader->get(),
            'attributes' => [
                'totalRows' => $hutangbayarheader->totalRows,
                'totalPages' => $hutangbayarheader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName store
     */
    public function store(StoreHutangBayarHeaderRequest $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            /* Store header */
            for ($i = 0; $i < count($request->hutang_id); $i++) {

                $cekSisa = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))->select('total')->where('nobukti', $request->hutang_id[$i])->first();

                $byrPotongan = $request->bayar[$i] + $request->potongan[$i];
                if ($byrPotongan > $cekSisa->total) {
                    $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                        ->first();
                    return response([
                        'errors' => [
                            "bayar.$i" =>
                            [$i => "$query->keterangan"]
                        ],
                        'message' => "The given data was invalid.",
                    ], 422);
                }
            }

            $group = 'PEMBAYARAN HUTANG BUKTI';
            $subgroup = 'PEMBAYARAN HUTANG BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'hutangbayarheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($getCoaDebet->memo, true);

            $hutangbayarheader = new HutangBayarHeader();
            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id ?? '';
            $hutangbayarheader->coa = $memo['JURNAL'];
            $hutangbayarheader->pengeluaran_nobukti = '';
            $hutangbayarheader->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $hutangbayarheader->userapproval = '';
            $hutangbayarheader->tglapproval = '';
            $hutangbayarheader->alatbayar_id = $request->alatbayar_id;
            $hutangbayarheader->tglcair = date('Y-m-d', strtotime($request->tglcair));
            $hutangbayarheader->statuscetak = $statusCetak->id;
            $hutangbayarheader->statusformat = $format->id;
            $hutangbayarheader->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $hutangbayarheader->nobukti = $nobukti;
            $hutangbayarheader->save();

            /* Store detail */
            $detaillog = [];



            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::where('nobukti', $request->hutang_id[$i])->first();
               
                if ($request->bayar[$i] > $hutang->total) {

                    $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'NBH')
                        ->first();
                    return response([
                        'errors' => [
                            "bayar.$i" => "$query->keterangan"
                        ],
                        'message' => "$query->keterangan",
                    ], 422);
                }
                $datadetail = [
                    'hutangbayar_id' => $hutangbayarheader->id,
                    'nobukti' => $hutangbayarheader->nobukti,
                    'hutang_nobukti' => $hutang->nobukti,
                    'nominal' => $request->bayar[$i],
                    'cicilan' => '',
                    'userid' => '',
                    'potongan' => $request->potongan[$i],
                    'keterangan' => $request->keterangandetail[$i],
                    'modifiedby' => $hutangbayarheader->modifiedby,
                ];

                $data = new StoreHutangBayarDetailRequest($datadetail);
                $datadetails = app(HutangBayarDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                $detaillog[] = $datadetails['detail']->toArray();
            }


            //INSERT TO PENGELUARAN
            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('coa', 'formatpengeluaran', 'tipe')->where('id', $hutangbayarheader->bank_id)->first();

            $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $bank->formatpengeluaran)->first();


            if ($bank->tipe == 'KAS') {
                $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
            }
            if ($bank->tipe == 'BANK') {
                $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
            }
            $group = $parameter->grp;
            $subgroup = $parameter->subgrp;
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $pengeluaranRequest = new Request();
            $pengeluaranRequest['group'] = $group;
            $pengeluaranRequest['subgroup'] = $subgroup;
            $pengeluaranRequest['table'] = 'pengeluaranheader';
            $pengeluaranRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobuktiPengeluaran = app(Controller::class)->getRunningNumber($pengeluaranRequest)->original['data'];

            $hutangbayarheader->pengeluaran_nobukti = $nobuktiPengeluaran;
            $hutangbayarheader->save();


            //LOGTRAIL HUTANG BAYAR HEADER
            $logTrail = [
                'namatabel' => strtoupper($hutangbayarheader->getTable()),
                'postingdari' => 'ENTRY HUTANG BAYAR HEADER',
                'idtrans' => $hutangbayarheader->id,
                'nobuktitrans' => $hutangbayarheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $hutangbayarheader->toArray(),
                'modifiedby' => $hutangbayarheader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            //LOGTRAIL HUTANG BAYAR DETAIL
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY HUTANG BAYAR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $hutangbayarheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $hutangbayarheader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            $pengeluaranDetail = [];

            $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
            $coaDebetpembelian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($coaDebet->memo, true);
            $memopembelian = json_decode($coaDebetpembelian->memo, true);

            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))->where('id', $request->hutang_id[$i])->first();
                $hutangDetail = HutangDetail::from(DB::raw("hutangdetail with (readuncommitted)"))->where('nobukti', $hutang->nobukti)->first();
                $detail = [];

                $query = HutangHeader::from(
                    DB::raw("hutangheader a with (readuncommitted)")
                )
                    ->select(
                        'a.nobukti'
                    )
                    ->join(db::Raw("penerimaanstokheader b with (readuncommitted)"), 'a.nobukti', 'b.hutang_nobukti')
                    ->first();

                if (isset($query)) {
                    $coa = $memopembelian['JURNAL'];
                } else {
                    $coa = $memo['JURNAL'];
                }

                $langsungcair = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LANGSUNG CAIR')->where('text', 'TIDAK LANGSUNG CAIR')->first();

                $queryalatbayar = AlatBayar::from(
                    db::raw("alatbayar a with (readuncommitted)")
                )
                    ->select(
                        'a.coa'
                    )
                    ->where('a.id', '=', $request->alatbayar_id)
                    ->where('a.statuslangsungcair', '=', $langsungcair->id)->first();

                $coakredit = $bank->coa;
                if (isset($queryalatbayar)) {
                    $coakredit =  $queryalatbayar->coa;
                }
                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $nobuktiPengeluaran,
                    'nowarkat' => '',
                    'tgljatuhtempo' => $hutangDetail->tgljatuhtempo,
                    'nominal' => $request->bayar[$i] - $request->potongan[$i],
                    'coadebet' => $coa,
                    'coakredit' => $coakredit,
                    'keterangan' => $request->keterangandetail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $pengeluaranDetail[] = $detail;
            }

            $supplierName = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $request->supplier_id)->first();
            $pengeluaranHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $nobuktiPengeluaran,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pelanggan_id' => $hutang->pelanggan_id,
                'statusjenistransaksi' => $jenisTransaksi->id,
                'postingdari' => 'ENTRY HUTANG BAYAR',
                'statusapproval' => $statusApproval->id,
                'alatbayar_id' => $request->alatbayar_id,
                'dibayarke' => $supplierName->namasupplier,
                'cabang_id' => '',
                'bank_id' => $hutangbayarheader->bank_id,
                'userapproval' => '',
                'tglapproval' => '',
                'transferkeac' => $supplierName->rekeningbank,
                'transferkean' => $supplierName->namarekening,
                'transferkebank' => $supplierName->bank,
                'statusformat' => $format->id,
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $pengeluaranDetail
            ];

            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            app(PengeluaranHeaderController::class)->store($pengeluaran);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable());
            $hutangbayarheader->position = $selected->position;
            $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangbayarheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = HutangBayarHeader::findAll($id);
        $detail = HutangBayarDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName update
     */
    public function update(UpdateHutangBayarHeaderRequest $request, HutangBayarHeader $hutangbayarheader)
    {

        DB::beginTransaction();

        try {

            for ($i = 0; $i < count($request->hutang_id); $i++) {

                $cekSisa = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))->select('total')->where('id', $request->hutang_id[$i])->first();


                $byrPotongan = $request->bayar[$i] + $request->potongan[$i];
                if ($byrPotongan > $cekSisa->total) {
                    $query =  Error::from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'STM')
                        ->first();
                    return response([
                        'errors' => [
                            "bayar.$i" =>
                            [$i => "$query->keterangan"]
                        ],
                        'message' => "The given data was invalid.",
                    ], 422);
                }
            }
            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->supplier_id = $request->supplier_id ?? '';
            $hutangbayarheader->tglcair = date('Y-m-d', strtotime($request->tglcair));
            $hutangbayarheader->modifiedby = auth('api')->user()->name;

            if ($hutangbayarheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangbayarheader->getTable()),
                    'postingdari' => 'EDIT HUTANG BAYAR HEADER',
                    'idtrans' => $hutangbayarheader->id,
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $hutangbayarheader->toArray(),
                    'modifiedby' => $hutangbayarheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                HutangBayarDetail::where('hutangbayar_id', $hutangbayarheader->id)->delete();

                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->hutang_id); $i++) {
                    $hutang = HutangHeader::where('id', $request->hutang_id[$i])->first();
                    if ($request->bayar[$i] > $hutang->total) {

                        $query = DB::table('error')->from(DB::raw("error with (readuncommitted)"))->select('keterangan')->where('kodeerror', '=', 'NBH')
                            ->first();
                        return response([
                            'errors' => [
                                "bayar.$i" => "$query->keterangan"
                            ],
                            'message' => "$query->keterangan",
                        ], 422);
                    }
                    $datadetail = [
                        'hutangbayar_id' => $hutangbayarheader->id,
                        'nobukti' => $hutangbayarheader->nobukti,
                        'hutang_nobukti' => $hutang->nobukti,
                        'nominal' => $request->bayar[$i],
                        'cicilan' => '',
                        'userid' => '',
                        'coa_id' => '',
                        'potongan' => $request->potongan[$i],
                        'keterangan' => $request->keterangandetail[$i],
                        'modifiedby' => $hutangbayarheader->modifiedby,
                    ];
                    $data = new StoreHutangBayarDetailRequest($datadetail);
                    $datadetails = app(HutangBayarDetailController::class)->store($data);
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    $detaillog[] = $datadetails['detail']->toArray();
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT HUTANG BAYAR DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $hutangbayarheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';




            $pengeluaranDetail = [];

            $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG')->where('subgrp', 'DEBET')->first();
            $coaDebetpembelian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMBAYARAN HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
            $memo = json_decode($coaDebet->memo, true);
            $memopembelian = json_decode($coaDebetpembelian->memo, true);
            $supplierName = Supplier::from(DB::raw("supplier with (readuncommitted)"))->where('id', $request->supplier_id)->first();


            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))
                    ->where('id', $request->hutang_id[$i])->first();
                $hutangDetail = HutangDetail::from(DB::raw("hutangdetail with (readuncommitted)"))
                    ->where('nobukti', $hutang->nobukti)->first();
                $detail = [];

                $query = HutangHeader::from(
                    DB::raw("hutangheader a with (readuncommitted)")
                )
                    ->select(
                        'a.nobukti'
                    )
                    ->join(db::Raw("penerimaanstokheader b with (readuncommitted)"), 'a.nobukti', 'b.hutang_nobukti')
                    ->first();

                if (isset($query)) {
                    $coa = $memopembelian['JURNAL'];
                } else {
                    $coa = $memo['JURNAL'];
                }

                $langsungcair = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LANGSUNG CAIR')->where('text', 'TIDAK LANGSUNG CAIR')->first();

                $queryalatbayar = AlatBayar::from(
                    db::raw("alatbayar a with (readuncommitted)")
                )
                    ->select(
                        'a.coa'
                    )
                    ->where('a.id', '=', $hutangbayarheader->alatbayar_id)
                    ->where('a.statuslangsungcair', '=', $langsungcair->id)->first();
                $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select('bank.coa')->whereRaw("bank.id = $hutangbayarheader->bank_id")
                    ->first();
                $coakredit = $bank->coa;
                if (isset($queryalatbayar)) {
                    $coakredit =  $queryalatbayar->coa;
                }

                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $hutangbayarheader->pengeluaran_nobukti,
                    'alatbayar_id' => $hutangbayarheader->alatbayar_id,
                    'nowarkat' => $hutangbayarheader->nowarkat,
                    'tgljatuhtempo' => $hutangDetail->tgljatuhtempo,
                    'nominal' => $request->bayar[$i] - $request->potongan[$i],
                    'coadebet' => $coa,
                    'coakredit' => $coakredit,
                    'keterangan' => $request->keterangandetail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $pengeluaranDetail[] = $detail;
            }
            $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->first();

            $pengeluaranHeader = [
                'isUpdate' => 1,
                'dibayarke' => $supplierName->namasupplier,
                'postingdari' => 'EDIT HUTANG BAYAR',
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $pengeluaranDetail
            ];

            $newPengeluaran = new PengeluaranHeader();
            $newPengeluaran = $newPengeluaran->findAll($get->id);
            $pengeluaran = new UpdatePengeluaranHeaderRequest($pengeluaranHeader);
            app(PengeluaranHeaderController::class)->update($pengeluaran, $newPengeluaran);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable());
            $hutangbayarheader->position = $selected->position;
            $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangbayarheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName destroy
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $getDetail = HutangBayarDetail::lockForUpdate()->where('hutangbayar_id', $id)->get();

        $request['postingdari'] = "DELETE HUTANG BAYAR";
        $hutangbayarheader = new HutangBayarHeader();
        $hutangbayarheader = $hutangbayarheader->lockAndDestroy($id);

        if ($hutangbayarheader) {
            $logTrail = [
                'namatabel' => strtoupper($hutangbayarheader->getTable()),
                'postingdari' => 'DELETE HUTANG BAYAR HEADER',
                'idtrans' => $hutangbayarheader->id,
                'nobuktitrans' => $hutangbayarheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $hutangbayarheader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE HUTANG BAYAR DETAIL
            $logTrailHutangBayarDetail = [
                'namatabel' => 'HUTANGBAYARDETAIL',
                'postingdari' => 'DELETE HUTANG BAYAR DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $hutangbayarheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailHutangBayarDetail = new StoreLogTrailRequest($logTrailHutangBayarDetail);
            app(LogTrailController::class)->store($validatedLogTrailHutangBayarDetail);

            $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->first();
            app(PengeluaranHeaderController::class)->destroy($request, $getPengeluaran->id);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable(), true);
            $hutangbayarheader->position = $selected->position;
            $hutangbayarheader->id = $selected->id;
            $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $hutangbayarheader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('hutangbayarheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'supplier' => Supplier::all(),
            'bank' => Bank::all(),
            'coa' => AkunPusat::all(),
            'alatbayar' => AlatBayar::all(),
            'hutangbayar' => HutangBayarHeader::all(),
            'pengeluaran' => PengeluaranHeader::all(),
            'hutangheader' => HutangHeader::all(),

        ];

        return response([
            'data' => $data
        ]);
    }

    public function getHutang($id, $field)
    {
        $hutang = new HutangHeader();
        return response([
            'data' => $hutang->getHutang($id, $field),
            'id' => $id,
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    public function getPembayaran($id, $supplierId)
    {
        $hutangBayar = new HutangBayarHeader();
        return response([
            'data' => $hutangBayar->getPembayaran($id, $supplierId),
            'attributes' => [
                'totalRows' => $hutangBayar->totalRows,
                'totalPages' => $hutangBayar->totalPages
            ]
        ]);
    }


    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $hutangBayar = HutangBayarHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($hutangBayar->statuscetak != $statusSudahCetak->id) {
                $hutangBayar->statuscetak = $statusSudahCetak->id;
                $hutangBayar->tglbukacetak = date('Y-m-d H:i:s');
                $hutangBayar->userbukacetak = auth('api')->user()->name;
                $hutangBayar->jumlahcetak = $hutangBayar->jumlahcetak + 1;

                if ($hutangBayar->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutangBayar->getTable()),
                        'postingdari' => 'PRINT HUTANG BAYAR HEADER',
                        'idtrans' => $hutangBayar->id,
                        'nobuktitrans' => $hutangBayar->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $hutangBayar->toArray(),
                        'modifiedby' => auth('api')->user()->name,
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
        $hutangBayar = HutangBayarHeader::find($id);
        $status = $hutangBayar->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $hutangBayar->statuscetak;
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

    public function comboapproval(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
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
}
