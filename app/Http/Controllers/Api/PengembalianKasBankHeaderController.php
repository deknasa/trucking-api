<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Controllers\Controller;
use App\Models\PengembalianKasBankHeader;
use App\Models\PengeluaranHeader;
use App\Models\Error;
use App\Models\PengeluaranDetail;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;
use App\Http\Requests\StorePengembalianKasBankHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasBankHeaderRequest;

use App\Models\PengembalianKasBankDetail;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StorePengembalianKasBankDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Models\AlatBayar;
use App\Models\Bank;

class PengembalianKasBankHeaderController extends Controller
{
    /**
     * @ClassName 
     * PengembalianKasBankHeader
     * @Detail PengembalianKasBankDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $pengembalianKasBankHeader = new PengembalianKasBankHeader();

        return response([
            'data' => $pengembalianKasBankHeader->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasBankHeader->totalRows,
                'totalPages' => $pengembalianKasBankHeader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pengembalianKasBankHeader = new PengembalianKasBankHeader();
        return response([
            'status' => true,
            'data' => $pengembalianKasBankHeader->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengembalianKasBankHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $group = 'PENGEMBALIAN KASBANK BUKTI';
            $subgroup = 'PENGEMBALIAN KASBANK BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pengembaliankasbankheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader = new PengembalianKasBankHeader();

            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $request->bank_id)->first();

            $jenisTransaksi = Bank::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JENIS TRANSAKSI')->where('text', $bank->tipe)->first();
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
            $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
            if ($bank->tipe == 'BANK' && $alatBayar->id == $request->alatbayar_id) {
                $request->validate([
                    'transferkeac' => 'required',
                    'transferkean' => 'required',
                    'transferkebank' => 'required',
                ]);
            }

            $pengembalianKasBankHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader->statusjenistransaksi = $jenisTransaksi->id;

            $pengembalianKasBankHeader->postingdari = $request->postingdari ?? 'ENTRY PENGEMBALIAN KAS BANK';
            $pengembalianKasBankHeader->statusapproval = $statusApproval->id;
            $pengembalianKasBankHeader->statuscetak = $statusCetak->id;
            $pengembalianKasBankHeader->dibayarke = $request->dibayarke ?? '';
            $pengembalianKasBankHeader->bank_id = $request->bank_id;
            $pengembalianKasBankHeader->cabang_id = $request->cabang_id;
            $pengembalianKasBankHeader->alatbayar_id = $request->alatbayar_id;
            $pengembalianKasBankHeader->transferkeac = $request->transferkeac ?? '';
            $pengembalianKasBankHeader->transferkean = $request->transferkean ?? '';
            $pengembalianKasBankHeader->transferkebank = $request->transferkebank ?? '';
            $pengembalianKasBankHeader->statusformat = $format->id;
            $pengembalianKasBankHeader->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengembalianKasBankHeader->nobukti = $nobukti;
            $pengembalianKasBankHeader->save();
            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {


                $datadetail = [
                    'pengembaliankasbank_id' => $pengembalianKasBankHeader->id,
                    'nobukti' => $pengembalianKasBankHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                    'modifiedby' => auth('api')->user()->name,
                ];


                $data = new StorePengembalianKasBankDetailRequest($datadetail);
                $datadetails = app(PengembalianKasBankDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                $detaillog[] = $datadetail;
            }

            $getFormatPengeluaran = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('id', $bank->formatpengeluaran)->first();

            $group = $getFormatPengeluaran->grp;
            $subgroup = $getFormatPengeluaran->subgrp;
            $formatPengeluaran = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $pengeluaranRequest = new Request();
            $pengeluaranRequest['group'] = $group;
            $pengeluaranRequest['subgroup'] = $subgroup;
            $pengeluaranRequest['table'] = 'pengeluaranheader';
            $pengeluaranRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobuktiPengeluaran = app(Controller::class)->getRunningNumber($pengeluaranRequest)->original['data'];

            $pengembalianKasBankHeader->pengeluaran_nobukti = $nobuktiPengeluaran;
            $pengembalianKasBankHeader->save();

            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                'postingdari' => 'ENTRY PENGEMBALIAN KAS BANK HEADER',
                'idtrans' => $pengembalianKasBankHeader->id,
                'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengembalianKasBankHeader->toArray(),
                'modifiedby' => $pengembalianKasBankHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PENGEMBALIAN KAS BANK DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            $nowarkat = [];
            $tgljatuhtempo = [];
            $nominal_detail = [];
            $coadebet = [];
            $coakredit = [];
            $keterangan_detail = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
                

                $nowarkat[] = $request->nowarkat[$i];
                $tgljatuhtempo[] =  date('Y-m-d', strtotime($request->tgljatuhtempo[$i]));
                $nominal_detail[] = $request->nominal_detail[$i];
                $coadebet[] =  $request->coadebet[$i];
                $coakredit[] = $bank->coa;
                $keterangan_detail[] = $request->keterangan_detail[$i];
            }
            
            //SOTRE PENGELUARAN
            $pengeluaranHeader = [
                'tanpaprosesnobukti' => 1,
                "nobukti" => $nobuktiPengeluaran,
                "tglbukti" => $pengembalianKasBankHeader->tglbukti,
                "pelanggan_id" => 0,
                "statusjenistransaksi" => $pengembalianKasBankHeader->statusjenistransaksi,
                "postingdari" => $pengembalianKasBankHeader->postingdari,
                "statusapproval" => $pengembalianKasBankHeader->statusapproval,
                "dibayarke" => $pengembalianKasBankHeader->dibayarke,
                "bank_id" => $pengembalianKasBankHeader->bank_id,
                "userapproval" => $pengembalianKasBankHeader->userapproval,
                "tglapproval" => $pengembalianKasBankHeader->tglapproval,
                "transferkeac" => $pengembalianKasBankHeader->transferkeac,
                "transferkean" => $pengembalianKasBankHeader->transferkean,
                "transferkebank" => $pengembalianKasBankHeader->transferkebank,
                "statusformat" => $formatPengeluaran->id,
                "modifiedby" => $pengembalianKasBankHeader->modifiedby,
                "nowarkat" => $nowarkat,
                "tgljatuhtempo" => $tgljatuhtempo,
                "nominal_detail" => $nominal_detail,
                "coadebet" => $coadebet,
                "coakredit" => $coakredit,
                "keterangan_detail" => $keterangan_detail,
            ];

            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            app(PengeluaranHeaderController::class)->store($pengeluaran);


            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($pengembalianKasBankHeader, $pengembalianKasBankHeader->getTable());
            $pengembalianKasBankHeader->position = $selected->position;
            $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / ($request->limit ?? 10));


            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengembalianKasBankHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(PengembalianKasBankHeader $pengembalianKasBankHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengembalianKasBankHeader->findAll($id),
            'detail' => PengembalianKasBankDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengembalianKasBankHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $group = 'PENGEMBALIAN KASBANK BUKTI';
            $subgroup = 'PENGEMBALIAN KASBANK BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pengembaliankasbankheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader = PengembalianKasBankHeader::lockForUpdate()->findOrFail($id);

            $bank = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $request->bank_id)->first();

            $jenisTransaksi = Bank::from(DB::raw("parameter with (readuncommitted)"))->where('text', $bank->tipe)->first();
            $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'TRANSFER')->first();
            if ($bank->tipe == 'BANK' && $alatBayar->id == $request->alatbayar_id) {
                $request->validate([
                    'transferkeac' => 'required',
                    'transferkean' => 'required',
                    'transferkebank' => 'required',
                ]);
            }
            $pengembalianKasBankHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader->postingdari = $request->postingdari ?? 'EDIT PENGEMBALIAN KAS BANK';
            $pengembalianKasBankHeader->dibayarke = $request->dibayarke ?? '';
            $pengembalianKasBankHeader->transferkeac = $request->transferkeac ?? '';
            $pengembalianKasBankHeader->transferkean = $request->transferkean ?? '';
            $pengembalianKasBankHeader->transferkebank = $request->transferkebank ?? '';
            $pengembalianKasBankHeader->statusformat = $format->id;
            $pengembalianKasBankHeader->modifiedby = auth('api')->user()->name;

            $pengembalianKasBankHeader->save();
            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                'postingdari' => 'EDIT PENGEMBALIAN KAS BANK HEADER',
                'idtrans' => $pengembalianKasBankHeader->id,
                'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pengembalianKasBankHeader->toArray(),
                'modifiedby' => $pengembalianKasBankHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Delete existing detail */
            PengembalianKasBankDetail::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();

            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {


                $datadetail = [
                    'pengembaliankasbank_id' => $pengembalianKasBankHeader->id,
                    'nobukti' => $pengembalianKasBankHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                    'modifiedby' => auth('api')->user()->name,
                ];


                $data = new StorePengembalianKasBankDetailRequest($datadetail);
                $datadetails = app(PengembalianKasBankDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }
                $detaillog[] = $datadetail;
            }
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'EDIT PENGEMBALIAN KAS BANK DETAIL',
                'idtrans' =>  $iddetail,
                'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            $statusApp = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $nominal_total = 0;
            $pengeluaranDetail = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
                $detail = [];

                $detail = [
                    'entriluar' => 1,
                    'nobukti' =>  $pengembalianKasBankHeader->nobukti,
                    'nowarkat' =>  $request->nowarkat[$i],
                    'tgljatuhtempo' =>   date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal_detail' =>  $request->nominal_detail[$i],
                    'coadebet' =>  $request->coadebet[$i],
                    'coakredit' =>  $bank->coa,
                    'keterangan' =>  $request->keterangan_detail[$i],
                    'bulanbeban' =>   date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                    'modifiedby' =>  auth('api')->user()->name,
                ];
                $nominal_total += $request->nominal_detail[$i];
                $pengeluaranDetail[] = $detail;
            }

            //SOTRE PENGELUARAN
            $pengeluaranHeader = [
                'isUpdate' => 1,
                'dibayarke' => $pengembalianKasBankHeader->dibayarke,
                'transferkeacc' => $pengembalianKasBankHeader->transferkeacc,
                'transferkean' => $pengembalianKasBankHeader->transferkean,
                'transferkebank' => $pengembalianKasBankHeader->transferkebank,
                'postingdari' => 'PENGEMBALIAN KAS/BANK',
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $pengeluaranDetail
            ];
            $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pengembalianKasBankHeader->pengeluaran_nobukti)->first();

            $newPengeluaran = new PengeluaranHeader();
            $newPengeluaran = $newPengeluaran->findAll($get->id);
            $pengeluaran = new UpdatePengeluaranHeaderRequest($pengeluaranHeader);
            app(PengeluaranHeaderController::class)->update($pengeluaran, $newPengeluaran);
            DB::commit();

            $selected = $this->getPosition($pengembalianKasBankHeader, $pengembalianKasBankHeader->getTable());
            $pengembalianKasBankHeader->position = $selected->position;
            $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / ($request->limit ?? 10));

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengembalianKasBankHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $getDetail = PengembalianKasBankDetail::lockForUpdate()->where('pengembaliankasbank_id', $id)->get();

        $request['postingdari'] = "DELETE PENGEMBALIAN KAS/BANK";
        $pengembalianKasBankHeader = new PengembalianKasBankHeader;
        $pengembalianKasBankHeader = $pengembalianKasBankHeader->lockAndDestroy($id);


        if ($pengembalianKasBankHeader) {
            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                'postingdari' => 'DELETE PENGEMBALIAN KAS BANK HEADER',
                'idtrans' => $pengembalianKasBankHeader->id,
                'nobuktitrans' => $pengembalianKasBankHeader->id,
                'aksi' => 'DELETE',
                'datajson' => $pengembalianKasBankHeader->toArray(),
                'modifiedby' => $pengembalianKasBankHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENGEMBALIAN KAS BANK DETAIL
            $logTrailPengembalianKasbankDetail = [
                'namatabel' => 'PENGEMBALIANKASBANKDETAIL',
                'postingdari' => 'DELETE PENGEMBALIAN KAS BANK DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengembalianKasbankDetail = new StoreLogTrailRequest($logTrailPengembalianKasbankDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengembalianKasbankDetail);

            $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pengembalianKasBankHeader->pengeluaran_nobukti)->first();
            app(PengeluaranHeaderController::class)->destroy($request, $getPengeluaran->id);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pengembalianKasBankHeader, $pengembalianKasBankHeader->getTable(), true);
            $pengembalianKasBankHeader->position = $selected->position;
            $pengembalianKasBankHeader->id = $selected->id;
            $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengembalianKasBankHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }


    public function approval($id)
    {
        DB::beginTransaction();
        $pengembalianKasBankHeader = PengembalianKasBankHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($pengembalianKasBankHeader->statusapproval == $statusApproval->id) {
                $pengembalianKasBankHeader->statusapproval = $statusNonApproval->id;
            } else {
                $pengembalianKasBankHeader->statusapproval = $statusApproval->id;
            }

            $pengembalianKasBankHeader->tglapproval = date('Y-m-d', time());
            $pengembalianKasBankHeader->userapproval = auth('api')->user()->name;

            if ($pengembalianKasBankHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                    'postingdari' => 'UN/APPROVE REKAP PENGELUARAN HEADER',
                    'idtrans' => $pengembalianKasBankHeader->id,
                    'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $pengembalianKasBankHeader->toArray(),
                    'modifiedby' => $pengembalianKasBankHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $pengembalianKasBankHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = PengembalianKasBankHeader::find($id);
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengembaliankasbankheader')->getColumns();

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
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }
}
