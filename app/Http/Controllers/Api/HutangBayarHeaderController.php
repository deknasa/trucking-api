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
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\AkunPusat;
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
        DB::beginTransaction();

        try {
            /* Store header */

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

            $hutangbayarheader = new HutangBayarHeader();
            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->keterangan = $request->keterangan;
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id;
            $hutangbayarheader->coa = $request->coa;
            $hutangbayarheader->pengeluaran_nobukti = '';
            $hutangbayarheader->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $hutangbayarheader->userapproval = '';
            $hutangbayarheader->tglapproval = '';
            $hutangbayarheader->statuscetak = $statusCetak->id;
            $hutangbayarheader->statusformat = $format->id;
            $hutangbayarheader->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $hutangbayarheader->nobukti = $nobukti;
            $hutangbayarheader->save();

            /* Store detail */
            $detaillog = [];

            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::where('id', $request->hutang_id[$i])->first();
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
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'tglcair' => $request->tglcair[$i],
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


            //INSERT TO PENGELUARAN
            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select('coa', 'statusformatpengeluaran', 'tipe')->where('id', $hutangbayarheader->bank_id)->first();

            $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $bank->statusformatpengeluaran)->first();


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

            $pengeluaranHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $nobuktiPengeluaran,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pelanggan_id' => $hutang->pelanggan_id,
                'keterangan' => $request->keterangan,
                'statusjenistransaksi' => $jenisTransaksi->id,
                'postingdari' => 'ENTRY HUTANG BAYAR',
                'statusapproval' => $statusApproval->id,
                'dibayarke' => '',
                'cabang_id' => '',
                'bank_id' => $hutangbayarheader->bank_id,
                'userapproval' => '',
                'tglapproval' => '',
                'transferkeac' => '',
                'transferkean' => '',
                'transferkebank' => '',
                'statusformat' => $format->id,
                'modifiedby' => auth('api')->user()->name
            ];

            $pengeluaranDetail = [];
            $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'COA PEMBAYARAN HUTANG DEBET')->first();

            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))->where('id', $request->hutang_id[$i])->first();
                $hutangDetail = HutangDetail::from(DB::raw("hutangdetail with (readuncommitted)"))->where('nobukti', $hutang->nobukti)->first();
                $detail = [];

                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $nobuktiPengeluaran,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => '',
                    'tgljatuhtempo' => $hutangDetail->tgljatuhtempo,
                    'nominal' => $request->bayar[$i] - $request->potongan[$i],
                    'coadebet' => $coaDebet->text,
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangandetail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $pengeluaranDetail[] = $detail;
            }


            $pengeluaran = $this->storePengeluaran($pengeluaranHeader, $pengeluaranDetail);

            if (!$pengeluaran['status']) {
                throw new \Throwable($pengeluaran['message']);
            }

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
            return response($th->getMessage());
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
            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->keterangan = $request->keterangan ?? '';
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id;
            $hutangbayarheader->coa = $request->coa;
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

                PengeluaranHeader::where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->delete();
                JurnalUmumHeader::where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->delete();
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
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'tglcair' => $request->tglcair[$i],
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

            //INSERT TO PENGELUARAN
            $bank = Bank::select('coa', 'statusformatpengeluaran', 'tipe')->where('id', $hutangbayarheader->bank_id)->first();
            $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('id', $bank->statusformatpengeluaran)->first();

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

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

            $pengeluaranHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $nobuktiPengeluaran,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pelanggan_id' => $hutang->pelanggan_id,
                'keterangan' => $request->keterangan,
                'statusjenistransaksi' => $jenisTransaksi->id,
                'postingdari' => 'ENTRY HUTANG BAYAR',
                'statusapproval' => $statusApproval->id,
                'dibayarke' => '',
                'cabang_id' => '',
                'bank_id' => $hutangbayarheader->bank_id,
                'userapproval' => '',
                'tglapproval' => '',
                'transferkeac' => '',
                'transferkean' => '',
                'transferkebank' => '',
                'statusformat' => $format->id,
                'modifiedby' => auth('api')->user()->name
            ];

            $pengeluaranDetail = [];
            $coaDebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'COA PEMBAYARAN HUTANG DEBET')->first();
            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))
                    ->where('id', $request->hutang_id[$i])->first();
                $hutangDetail = HutangDetail::from(DB::raw("hutangdetail with (readuncommitted)"))
                    ->where('nobukti', $hutang->nobukti)->first();
                $detail = [];

                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $nobuktiPengeluaran,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => '',
                    'tgljatuhtempo' => $hutangDetail->tgljatuhtempo,
                    'nominal' => $request->bayar[$i] - $request->potongan[$i],
                    'coadebet' => $coaDebet->text,
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangandetail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $pengeluaranDetail[] = $detail;
            }


            $pengeluaran = $this->storePengeluaran($pengeluaranHeader, $pengeluaranDetail);


            // if (!$pengeluaran['status'] AND @$pengeluaran['errorCode'] == 2601) {
            //     goto ATAS;
            // }
            if (!$pengeluaran['status']) {
                throw new \Throwable($pengeluaran['message']);
            }

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
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName destroy
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $getDetail = HutangBayarDetail::lockForUpdate()->where('hutangbayar_id', $id)->get();

        $hutangbayarheader = new HutangBayarHeader();
        $hutangbayarheader = $hutangbayarheader->lockAndDestroy($id);

        $getPengeluaranHeader = PengeluaranHeader::lockForUpdate()->where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->first();
        $getPengeluaranDetail = PengeluaranDetail::lockForUpdate()->where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->get();
        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->first();
        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->get();

        PengeluaranHeader::where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->delete();
        JurnalUmumHeader::where('nobukti', $hutangbayarheader->pengeluaran_nobukti)->delete();

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

            // DELETE PENGELUARAN HEADER
            $logTrailPengeluaranHeader = [
                'namatabel' => 'PENGELUARANHEADER',
                'postingdari' => 'DELETE PENGELUARAN HEADER DARI HUTANG BAYAR',
                'idtrans' => $getPengeluaranHeader->id,
                'nobuktitrans' => $getPengeluaranHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getPengeluaranHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranHeader = new StoreLogTrailRequest($logTrailPengeluaranHeader);
            $storedLogTrailPengeluaran = app(LogTrailController::class)->store($validatedLogTrailPengeluaranHeader);

            // DELETE PENGELUARAN DETAIL
            $logTrailPengeluaranDetail = [
                'namatabel' => 'PENGELUARANDETAIL',
                'postingdari' => 'DELETE PENGELUARAN DETAIL DARI HUTANG BAYAR',
                'idtrans' => $storedLogTrailPengeluaran['id'],
                'nobuktitrans' => $getPengeluaranHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getPengeluaranDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranDetail = new StoreLogTrailRequest($logTrailPengeluaranDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengeluaranDetail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => 'DELETE JURNAL UMUM HEADER DARI HUTANG BAYAR',
                'idtrans' => $getJurnalHeader->id,
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
            $storedLogTrailJurnal = app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);

            // DELETE JURNAL DETAIL
            $logTrailJurnalDetail = [
                'namatabel' => 'JURNALUMUMDETAIL',
                'postingdari' => 'DELETE JURNAL UMUM DETAIL DARI HUTANG BAYAR',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

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

    public function getHutang($id)
    {
        $hutang = new HutangHeader();
        return response([
            'data' => $hutang->getHutang($id),
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

    public function storePengeluaran($pengeluaranHeader, $pengeluaranDetail)
    {
        try {


            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            $header = app(PengeluaranHeaderController::class)->store($pengeluaran);

            $nobukti = $pengeluaranHeader['nobukti'];

            $detailLogPengeluaran = [];
            $detailLogJurnal = [];
            foreach ($pengeluaranDetail as $value) {

                $value['pengeluaran_id'] = $header->original['data']['id'];
                $value['entridetail'] = 1;
                $value['jurnal_id'] = $header->original['idlogtrail']['jurnal_id'];
                $value['tglbukti'] = $pengeluaranHeader['tglbukti'];
                $pengeluaranDetail = new StorePengeluaranDetailRequest($value);
                $detailPengeluaran = app(PengeluaranDetailController::class)->store($pengeluaranDetail);

                $detailLogPengeluaran[] = $detailPengeluaran['detail']['pengeluarandetail']->toArray();
                $detailLogJurnal = array_merge($detailLogJurnal, $detailPengeluaran['detail']['jurnaldetail']);
            }

            $datalogtrail = [
                'namatabel' => strtoupper($detailPengeluaran['tabel']),
                'postingdari' => 'ENTRY HUTANG BAYAR',
                'idtrans' =>  $header->original['idlogtrail']['pengeluaran'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogPengeluaran,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            $datalogtrail = [
                'namatabel' => strtoupper('JURNALUMUMDETAIL'),
                'postingdari' => 'ENTRY PENGELUARAN DARI HUTANG BAYAR',
                'idtrans' =>  $header->original['idlogtrail']['jurnal'],
                'nobuktitrans' => $nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detailLogJurnal,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
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
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
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
