<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengembalianKasGantungDetailRequest;
use App\Models\KasGantungHeader;
use App\Models\Parameter;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanHeader;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;
use App\Models\KasGantungDetail;
use App\Models\PengembalianKasGantungHeader;
use App\Models\PengembalianKasGantungDetail;
use App\Models\Bank;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;

use App\Http\Requests\StorePenerimaanHeaderRequest;
// use App\Http\Controllers\ParameterController;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class PengembalianKasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
        return response([
            'data' => $pengembalianKasGantungHeader->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasGantungHeader->totalRows,
                'totalPages' => $pengembalianKasGantungHeader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pengembaliankasgantung = new PengembalianKasGantungHeader();
        return response([
            'status' => true,
            'data' => $pengembaliankasgantung->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StorePengembalianKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'PENGEMBALIAN KAS GANTUNG BUKTI';
            $subgroup = 'PENGEMBALIAN KAS GANTUNG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'PengembalianKasGantungHeader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            // nobukti penerimaan
            $bankid = $request->bank_id;
            $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpenerimaan',
                    'bank.coa'
                )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->whereRaw("bank.id = $bankid")
                ->first();


            $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENGEMBALIAN KAS GANTUNG')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($coaKasMasuk->memo, true);
            //  return response($memo['JURNAL'],422);

            $statusApproval = DB::table('parameter')
                ->where('grp', 'STATUS APPROVAL')
                ->where('text', 'NON APPROVAL')
                ->first();

            $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->pelanggan_id = $request->pelanggan_id ?? 0;
            $pengembalianKasGantungHeader->bank_id = $request->bank_id;
            $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($request->tgldari)) ?? date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($request->tglsampai)) ?? date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->penerimaan_nobukti = '';
            $pengembalianKasGantungHeader->coakasmasuk = $querysubgrppenerimaan->coa;
            $pengembalianKasGantungHeader->postingdari = $request->postingdari ?? "Pengembalian Kas Gantung";
            $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->statusformat = $format->id;
            $pengembalianKasGantungHeader->statuscetak = $statusCetak->id ?? 0;
            $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;


            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengembalianKasGantungHeader->nobukti = $nobukti;

            try {
                $pengembalianKasGantungHeader->save();
            } catch (QueryException $queryException) {
                if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                    // Check if duplicated
                    if ($queryException->errorInfo[1] == 2601) {
                        goto TOP;
                    }
                }

                throw $queryException;
            }
            if ($pengembalianKasGantungHeader->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENGEMBALIAN KAS GANTUNG HEADER',
                    'idtrans' => $pengembalianKasGantungHeader->id,
                    'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengembalianKasGantungHeader->toArray(),
                    'modifiedby' => $pengembalianKasGantungHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */

                $penerimaanStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id', $pengembalianKasGantungHeader->id)->lockForUpdate()->delete();

                $detaillog = [];

                if ($request->datadetail != '') {
                    $counter = $request->datadetail;
                } else {
                    $counter = $request->kasgantungdetail_id;
                }
                for ($i = 0; $i < count($counter); $i++) {
                    if ($request->datadetail != '') {
                        $kasgantungnobukti = $request->datadetail[$i]['kasgantung_nobukti'];
                        $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
                        $coakreditmemo = json_decode($coakredit->memo, true);
                    } else {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();
                        $kasgantungnobukti = $kasgantung->nobukti;
                    }


                    $datadetail = [
                        "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                        "nobukti" => $pengembalianKasGantungHeader->nobukti,
                        "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $kasgantung->nominal,
                        "coadetail" => ($request->datadetail != '') ? $coakreditmemo['JURNAL'] : $request->coadetail[$i],
                        "keterangandetail" => ($request->datadetail != '') ? $request->datadetail[$i]['keterangandetail'] : $request->keterangandetail[$i],
                        "kasgantung_nobukti" => $kasgantungnobukti,
                    ];
                    $detaillog[] = $datadetail;
                    $data = new StorePengembalianKasGantungDetailRequest($datadetail);
                    $pengembalianKasGantungDetail = app(PengembalianKasGantungDetailController::class)->store($data);

                    if ($pengembalianKasGantungDetail['error']) {
                        return response($pengembalianKasGantungDetail, 422);
                    } else {
                        $iddetail = $pengembalianKasGantungDetail['id'];
                        $tabeldetail = $pengembalianKasGantungDetail['tabel'];
                    }
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENGEMBALIAN KAS GANTUNG DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];
                $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                //INSERT TO PENERIMAAN

                $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengembalianKasGantungHeader->bank_id)->first();
                $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();

                $statusKas = '';
                if ($bank->tipe == 'KAS') {
                    $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
                }
                if ($bank->tipe == 'BANK') {
                    $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN KAS')->first();
                }

                $group = $parameter->grp;
                $subgroup = $parameter->subgrp;
                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $penerimaanRequest = new Request();
                $penerimaanRequest['group'] = $group;
                $penerimaanRequest['subgroup'] = $subgroup;
                $penerimaanRequest['table'] = 'penerimaanheader';
                $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];

                $pengembalianKasGantungHeader->penerimaan_nobukti = $nobuktiPenerimaan;
                $pengembalianKasGantungHeader->save();
                $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();


                $penerimaanDetail = [];

                for ($i = 0; $i < count($counter); $i++) {
                    if ($request->datadetail != '') {
                        $kasgantungnobukti = $request->datadetail[$i]['kasgantung_nobukti'];
                    } else {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();
                        $kasgantungnobukti = $kasgantung->nobukti;
                    }

                    $detail = [];

                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $nobuktiPenerimaan,
                        'nowarkat' => '',
                        'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'coadebet' => $bank->coa,
                        'coakredit' => $memo['JURNAL'],
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangandetail'] : $request->keterangandetail[$i],
                        "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $kasgantung->nominal,
                        'invoice_nobukti' => '',
                        'pelunasanpiutang_nobukti' => '',
                        'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $penerimaanDetail[] = $detail;
                }

                $penerimaanHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiPenerimaan,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'pelanggan_id' => '',
                    'bank_id' => $pengembalianKasGantungHeader->bank_id,
                    'postingdari' => $request->postingdari ?? 'PENGEMBALIAN KAS GANTUNG',
                    'diterimadari' => $request->diterimadari ?? 'PENGEMBALIAN KAS GANTUNG',
                    'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name,
                    'datadetail' => $penerimaanDetail
                ];
                // return response($penerimaanDetail,422);
                // $penerimaan = $this->storePenerimaan($penerimaanHeader, $penerimaanDetail);
                $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
                app(PenerimaanHeaderController::class)->store($penerimaan);

                // if (!$penerimaan['status']) {
                //     throw new \Throwable($penerimaan['message']);
                // }


                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable());
                $pengembalianKasGantungHeader->position = $selected->position;
                $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));

                if (isset($request->limit)) {
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / $request->limit);
                }

                return response([
                    'message' => 'Berhasil disimpan',
                    'data' => $pengembalianKasGantungHeader
                ], 201);
            }
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(PengembalianKasGantungHeader $pengembalianKasGantungHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengembalianKasGantungHeader->findAll($id),
            'detail' => PengembalianKasGantungDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdatePengembalianKasGantungHeaderRequest $request, PengembalianKasGantungHeader $pengembaliankasgantungheader)
    {
        try {

            $isUpdate = $request->isUpdate ?? 0;

            if ($isUpdate == 0) {
                /* Store header */
                $statusApproval = DB::table('parameter')
                    ->where('grp', 'STATUS APPROVAL')
                    ->where('text', 'NON APPROVAL')
                    ->first();
                DB::beginTransaction();
                $bankid = $request->bank_id;
                $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpenerimaan',
                        'bank.coa'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
                $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENGEMBALIAN KAS GANTUNG')->where('subgrp', 'KREDIT')->first();
                $memo = json_decode($coaKasMasuk->memo, true);

                $pengembaliankasgantungheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $pengembaliankasgantungheader->pelanggan_id = $request->pelanggan_id;
                $pengembaliankasgantungheader->bank_id = $request->bank_id;
                $pengembaliankasgantungheader->tgldari = date('Y-m-d', strtotime($request->tgldari));
                $pengembaliankasgantungheader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
                // $pengembaliankasgantungheader->penerimaan_nobukti = $request->penerimaan_nobukti;
                $pengembaliankasgantungheader->coakasmasuk = $querysubgrppenerimaan->coa;
                $pengembaliankasgantungheader->postingdari = $request->postingdari ?? '';
                $pengembaliankasgantungheader->statuscetak = $statusCetak->id ?? 0;
                $pengembaliankasgantungheader->modifiedby = auth('api')->user()->name;
                $pengembaliankasgantungheader->tglkasmasuk = date('Y-m-d', strtotime($request->tglbukti));
                $pengembaliankasgantungheader->save();
            }

            $logTrail = [
                'namatabel' => strtoupper($pengembaliankasgantungheader->getTable()),
                'postingdari' => 'EDIT PENGEMBALIAN KAS GANTUNG HEADER',
                'idtrans' => $pengembaliankasgantungheader->id,
                'nobuktitrans' => $pengembaliankasgantungheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengembaliankasgantungheader->toArray(),
                'modifiedby' => $pengembaliankasgantungheader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            /* Store detail */

            $penerimaanStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id', $pengembaliankasgantungheader->id)->lockForUpdate()->delete();

            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->kasgantungdetail_id;
            }

            for ($i = 0; $i < count($counter); $i++) {
                if ($request->datadetail != '') {
                    $kasgantungnobukti = $request->datadetail[$i]['kasgantung_nobukti'];
                } else {
                    $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                    $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();
                    $kasgantungnobukti = $kasgantung->nobukti;
                }

                $datadetail = [
                    "pengembaliankasgantung_id" => $pengembaliankasgantungheader->id,
                    "nobukti" => $pengembaliankasgantungheader->nobukti,
                    "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $kasgantung->nominal,
                    "coadetail" => ($request->datadetail != '') ? '01' : $request->coadetail[$i],
                    "keterangandetail" => ($request->datadetail != '') ? $request->datadetail[$i]['keterangandetail'] : $request->keterangandetail[$i],
                    "kasgantung_nobukti" => $kasgantungnobukti,
                ];
                $detaillog[] = $datadetail;
                $data = new StorePengembalianKasGantungDetailRequest($datadetail);
                $pengembalianKasGantungDetail = app(PengembalianKasGantungDetailController::class)->store($data);

                if ($pengembalianKasGantungDetail['error']) {
                    return response($pengembalianKasGantungDetail, 422);
                } else {
                    $iddetail = $pengembalianKasGantungDetail['id'];
                    $tabeldetail = $pengembalianKasGantungDetail['tabel'];
                }

                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => $request->postingdari ?? 'EDIT PENGEMBALIAN KAS GANTUNG HEADER',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pengembaliankasgantungheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];
                $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            //EDIT TO PENERIMAAN

            $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengembaliankasgantungheader->bank_id)->first();
            $penerimaanDetail = [];
            for ($i = 0; $i < count($counter); $i++) {
                if ($request->datadetail != '') {
                    $kasgantungnobukti = $request->datadetail[$i]['kasgantung_nobukti'];
                } else {
                    $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                    $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();
                    $kasgantungnobukti = $kasgantung->nobukti;
                }

                $detail = [];

                $detail = [
                    'isUpdate' => 1,
                    'tgljatuhtempo' => $pengembaliankasgantungheader->tglbukti,
                    'coadebet' => ($request->datadetail != '') ? $request->datadetail[$i]['coadebet'] : $bank->coa,
                    'coakredit' => ($request->datadetail != '') ? $request->datadetail[$i]['coakredit'] : $memo['JURNAL'],
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangandetail'] : $request->keterangandetail[$i],
                    "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $kasgantung->nominal,
                    'invoice_nobukti' => '',
                    'pelunasanpiutang_nobukti' => '',
                    'bulanbeban' => $pengembaliankasgantungheader->tglbukti,
                ];
                $penerimaanDetail[] = $detail;
            }

            $penerimaanHeader = [
                'isUpdate' => 1,
                'from' => $request->from ?? '',
                'bank_id' => $pengembaliankasgantungheader->bank_id,
                'postingdari' => $request->postingdari ?? 'EDIT PENGEMBALIAN KAS GANTUNG',
                'nowarkat' => '',
                'datadetail' => $penerimaanDetail
            ];

            $getPenerimaanHeader = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where("nobukti", $pengembaliankasgantungheader->penerimaan_nobukti)->first();
            $newPenerimaanHeader = new PenerimaanHeader();
            $newPenerimaanHeader = $newPenerimaanHeader->findAll($getPenerimaanHeader->id);
            $penerimaan = new UpdatePenerimaanHeaderRequest($penerimaanHeader);
            app(PenerimaanHeaderController::class)->update($penerimaan, $newPenerimaanHeader);
            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($pengembaliankasgantungheader, $pengembaliankasgantungheader->getTable());
            $pengembaliankasgantungheader->position = $selected->position;
            $pengembaliankasgantungheader->page = ceil($pengembaliankasgantungheader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengembaliankasgantungheader->page = ceil($pengembaliankasgantungheader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengembaliankasgantungheader
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
        $getDetail = PengembalianKasGantungDetail::lockForUpdate()->where('pengembaliankasgantung_id', $id)->get();

        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
        $pengembalianKasGantungHeader = $pengembalianKasGantungHeader->lockAndDestroy($id);
        $request['postingdari'] =  $request->postingdari ?? "DELETE PENGEMBALIAN KAS GANTUNG HEADER";
        if ($pengembalianKasGantungHeader) {
            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                'postingdari' => $request['postingdari'] ?? 'DELETE PENGEMBALIAN KAS GANTUNG HEADER',
                'idtrans' => $pengembalianKasGantungHeader->id,
                'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pengembalianKasGantungHeader->toArray(),
                'modifiedby' => $pengembalianKasGantungHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENGEMBALIAN KAS GANTUNG DETAIL
            $logTrailPengembalianKasGantungDetail = [
                'namatabel' => 'PENGEMBALIANKASGANTUNGDETAIL',
                'postingdari' => $request['postingdari'] ?? 'DELETE PENGEMBALIAN KAS GANTUNG DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengembalianKasGantungDetail = new StoreLogTrailRequest($logTrailPengembalianKasGantungDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengembalianKasGantungDetail);

            $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $pengembalianKasGantungHeader->penerimaan_nobukti)->first();
            if ($getPenerimaan != null) {
                
                app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaan->id);
            }
            DB::commit();

            $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable(), true);
            $pengembalianKasGantungHeader->position = $selected->position;
            $pengembalianKasGantungHeader->id = $selected->id;
            $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengembalianKasGantungHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function storePenerimaan($penerimaanHeader, $penerimaanDetail)
    {
        try {


            $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
            $header = app(PenerimaanHeaderController::class)->store($penerimaan);

            $nobukti = $penerimaanHeader['nobukti'];
            $fetchPenerimaan = PenerimaanHeader::whereRaw("nobukti = '$nobukti'")->first();

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $fetchPenerimaan->nobukti,
                'tglbukti' => $fetchPenerimaan->tglbukti,
                'keterangan' => $fetchPenerimaan->keterangan,
                'postingdari' => "ENTRY PENGEMBALIAN KAS GANTUNG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'statusformat' => 0,
                'modifiedby' => auth('api')->user()->name,
            ];
            $jurnal = new StoreJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->store($jurnal);
            $id = $fetchPenerimaan->id;

            foreach ($penerimaanDetail as $value) {

                $value['penerimaan_id'] = $id;
                $penerimaanDetail = new StorePenerimaanDetailRequest($value);
                $tes = app(PenerimaanDetailController::class)->store($penerimaanDetail);

                $fetchId = JurnalUmumHeader::select('id', 'tglbukti')
                    ->where('nobukti', '=', $nobukti)
                    ->first();


                $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();

                if (is_null($getBaris)) {
                    $baris = 0;
                } else {
                    $baris = $getBaris->baris + 1;
                }

                for ($x = 0; $x <= 1; $x++) {
                    if ($x == 1) {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $penerimaanDetail['coakredit'],
                            'nominal' => -$penerimaanDetail['nominal'],
                            'keterangan' => $penerimaanDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $penerimaanDetail['coadebet'],
                            'nominal' => $penerimaanDetail['nominal'],
                            'keterangan' => $penerimaanDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($datadetail);
                    $tes = app(JurnalUmumDetailController::class)->store($detail);
                }
            }


            return [
                'status' => true
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('PengembalianKasGantungHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    //untuk create
    public function getKasGantung(Request $request)
    {


        $KasGantung = new KasGantungHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();

        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));
        return response([
            'data' => $KasGantung->getKasGantung($dari, $sampai),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $KasGantung->totalRows,
                'totalPages' => $KasGantung->totalPages
            ]
        ]);
    }
    public function getPengembalian($id)
    {
        $pengembalian = new PengembalianKasGantungHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pengembalian->getPengembalian($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pengembalian->totalRows,
                'totalPages' => $pengembalian->totalPages
            ]
        ]);
    }

    public function cekvalidasi($id)
    {

        $pengembaliankasgantung = PengembalianKasGantungHeader::find($id);
        $statusdatacetak = $pengembaliankasgantung->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
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
        $pengembalianKasGantung = new PengembalianKasGantungHeader();
        $nobukti = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader"))->where('id', $id)->first();
        $cekdata = $pengembalianKasGantung->cekvalidasiaksi($nobukti->nobukti);
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
}
